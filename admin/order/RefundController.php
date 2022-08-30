<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\payment\base\PayOrderTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\constants\finance\RefundLogConstant;
use shopstar\constants\log\order\OrderLogConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\RefundConstant;
use shopstar\exceptions\order\RefundException;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\order\AutoTimeoutCancelOrderJob;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\finance\RefundLogModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\shoppingReward\ShoppingRewardLogModel;
use shopstar\models\sysset\RefundAddressModel;
use shopstar\services\commission\CommissionOrderService;
use shopstar\services\consumeReward\ConsumeRewardLogService;
use shopstar\services\creditShop\CreditShopOrderService;
use shopstar\services\order\refund\OrderRefundService;
use shopstar\services\tradeOrder\TradeOrderService;
use shopstar\services\wxTransactionComponent\WxTransactionComponentOrderService;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\Response;

/**
 * 整单维权处理类
 * Class RefundController
 * @package shopstar\admin\order
 */
class RefundController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'query-express',
            'all-express'
        ]
    ];

    /**
     * 驳回申请
     * 任何状态 拒绝就完成
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionReject()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        $rejectReason = RequestHelper::post('reject_reason', '无');
        if (empty($orderId)) {
            throw new RefundException(RefundException::REJECT_PARAMS_ERROR);
        }
        // 检查该订单是否符合维权条件
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::REJECT_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }
        // 维权
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::REJECT_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }

        // 用户已寄出 不能驳回申请
        if ($refund->status == RefundConstant::REFUND_STATUS_SHOP || $refund->status == RefundConstant::REFUND_STATUS_WAIT) {
            throw new RefundException(RefundException::REJECT_MEMBER_IS_SEND_REJECT_FAIL);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 驳回逻辑
            $refund->status = RefundConstant::REFUND_STATUS_REJECT;
            $refund->reject_reason = $rejectReason;
            $refund->finish_time = date('Y-m-d H:i:s');
            if (!$refund->save()) {
                throw new RefundException(RefundException::ORDER_REFUND_SAVE_REJECT_FAIL);
            }

            // 判断是否是视频号订单(拒绝售后)
            if ($order['scene'] == OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST) {
                $result = WxTransactionComponentOrderService::rejectRefund($refund->aftersale_id);

                if (is_error($result)) {
                    throw new \Exception($result['message'], $result['error']);
                }
            }

            // 更新订单商品表
            if (empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_REJECT], ['order_id' => $orderId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_REJECT], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            }

            // 发送通知

            // 记录日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_REJECT,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_REJECT),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        'id' => $refund->id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '驳回申请',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_REJECT,
                    ],
                ]
            );

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode());
        }
        return $this->success();
    }

    /**
     * 退货退款　通过申请　
     * 下一步 等待客户寄回
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionReturnAccept()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        $refundAddressId = RequestHelper::post('refund_address_id', 0);
        $sellerMessage = RequestHelper::post('seller_message', '');
        if (empty($orderId)) {
            throw new RefundException(RefundException::RETURN_ACCEPT_PARAMS_ERROR);
        }
        // 检查订单是否符合
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::RETURN_ACCEPT_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }
        // 检查退货地址是否存在
        $refundAddress = RefundAddressModel::findOne(['id' => $refundAddressId]);
        if (empty($refundAddress)) {
            // 退货地址不存在，请重新选择
            throw new RefundException(RefundException::RETURN_ACCEPT_REFUND_ADDRESS_NOT_EXISTS);
        }
        // 获取维权信息
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::RETURN_ACCEPT_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }
        // 维权状态不是申请中
        if ($refund->status != RefundConstant::REFUND_STATUS_APPLY) {
            throw new RefundException(RefundException::RETURN_ACCEPT_REFUND_STATUS_NOT_APPLY);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 修改
            $refund->status = RefundConstant::REFUND_STATUS_MEMBER;
            $refund->refund_address_id = $refundAddressId;
            $refund->seller_message = $sellerMessage;
            $refund->refund_name = $refundAddress->name;
            $refund->refund_mobile = $refundAddress->mobile;
            $refund->refund_address = $refundAddress->province . ' ' . $refundAddress->city . ' ' . $refundAddress->area . ' ' . $refundAddress->address;
            $refund->seller_accept_time = date('Y-m-d H:i:s');
            // 查询此订单是否开启超时维权时间
            $shopSetting = ShopSettings::get('sysset.refund');
            if ($shopSetting['timeout_cancel_refund'] == '1') {
                $refund->timeout_cancel = date("Y-m-d H:i:s", (strtotime($refund->seller_accept_time) + (intval($shopSetting['timeout_cancel_refund_days']) * 24 * 3600)));
                //获取剩余秒数
                $delay = strtotime($refund->timeout_cancel) - strtotime($refund->seller_accept_time);
                if ($delay > 0) {
                    // 推送任务队列
                    $queueId = QueueHelper::push(new AutoTimeoutCancelOrderJob([
                        'orderId' => $orderId,
                        'orderGoodsId' => $orderGoodsId,
                    ]), $delay);
                    // 保存队列id，用户自主提交后移出队列用
                    $refund->queue_id = $queueId;
                }
            }
            if (!$refund->save()) {
                throw new RefundException(RefundException::RETURN_ACCEPT_REFUND_ACCEPT_FAIL);
            }

            // 判断是否是视频号订单(同意退货)
            if ($order['scene'] == OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST) {
                $result = WxTransactionComponentOrderService::acceptReturn($refund->aftersale_id, $refundAddress);

                if (is_error($result)) {
                    throw new Exception($result['message'], $result['error']);
                }
            }

            // 订单商品表
            if (empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_MEMBER], ['order_id' => $orderId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_MEMBER], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            }

            // 发送通知

            // 记录日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_ACCEPT,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_ACCEPT),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        '订单id' => $refund->order_id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '通过申请',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_ACCEPT,
                    ]
                ]
            );

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode(), $exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 确认发货  换货 卖家发货
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExchangeSend()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        $expressCode = RequestHelper::post('seller_express_code');
        $sellerExpressEncoding = RequestHelper::post('seller_express_encoding');
        $expressName = RequestHelper::post('seller_express_name');
        $expressSn = RequestHelper::post('seller_express_sn');
        $noExpress = RequestHelper::post('no_express'); // 是否需要快递

        if (!$noExpress) {
            if (empty($orderId) || empty($expressCode) || empty($expressName) || empty($expressSn)) {
                throw new RefundException(RefundException::EXCHANGE_SEND_PARAMS_ERROR);
            }
        }

        // 检查订单是否符合
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::EXCHANGE_SEND_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::EXCHANGE_SEND_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }
        // 维权状态不是申请中 且 不是等待店家发货 且 不是等待买家发货
        if ($refund->status != RefundConstant::REFUND_STATUS_APPLY
            && $refund->status != RefundConstant::REFUND_STATUS_SHOP
            && $refund->status != RefundConstant::REFUND_STATUS_MEMBER) {
            throw new RefundException(RefundException::EXCHANGE_SEND_ORDER_STATUS_NOT_ALLOW);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 修改
            $refund->status = RefundConstant::REFUND_STATUS_WAIT;
            $refund->seller_express_code = $expressCode ?: '';
            $refund->seller_express_encoding = $sellerExpressEncoding ?: '';
            $refund->seller_express_name = $expressName ?: '';
            $refund->seller_express_sn = $expressSn ?: '';
            $refund->seller_express_time = date('Y-m-d H:i:s');

            if (!$refund->save()) {
                throw new RefundException(RefundException::EXCHANGE_SEND_REFUND_SELLER_SEND_FAIL);
            }
            // 订单商品表
            if (!empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_WAIT], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_WAIT], ['order_id' => $orderId]);
            }

            // 发送通知

            // 记录日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_EXCHANGE_SEND,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_EXCHANGE_SEND),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        '订单id' => $refund->order_id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '确认发货',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_EXCHANGE_SEND,
                    ]
                ]
            );

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode(), $exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 关闭申请
     * 换货
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExchangeClose()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::EXCHANGE_CLOSE_PARAMS_ERROR);
        }
        // 检查订单是否符合
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::EXCHANGE_CLOSE_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::EXCHANGE_CLOSE_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }
        if ($refund->status != RefundConstant::REFUND_STATUS_WAIT) {
            throw new RefundException(RefundException::EXCHANGE_CLOSE_REFUND_NOT_ALLOW_CLOSE);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 修改
            $refund->status = RefundConstant::REFUND_STATUS_SUCCESS;
            $refund->finish_time = date('Y-m-d H:i:s');

            if (!$refund->save()) {
                throw new RefundException(RefundException::EXCHANGE_CLOSE_REFUND_CLOSE_FAIL);
            }
            // 订单商品表
            if (empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS], ['order_id' => $orderId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            }

            // 检测订单可以完成
            OrderRefundService::successOrder($orderId, $orderGoodsId);


            // 发送通知

            // 记录日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_EXCHANGE_CLOSE,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_EXCHANGE_CLOSE),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        '订单id' => $refund->order_id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '关闭申请',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_EXCHANGE_CLOSE,
                    ]
                ]
            );

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode());
        }

        return $this->success();
    }

    /**
     * 手动退款
     * 换货的没有 其他所有情况都可以
     * 手动退款忽略其他状态 直接完成
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManual()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::ORDER_REFUND_MANUAL_PARAMS_ERROR);
        }
        // 检查订单是否符合
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::MANUAL_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }
        // 订单商品信息
        $orderGoods = null;
        // 如果是单品维权  获取订单商品信息
        if (!empty($orderGoodsId)) {
            $orderGoods = OrderGoodsModel::findOne(['id' => $orderGoodsId, 'order_id' => $orderId]);
            if (empty($orderGoods)) {
                // 找不到订单商品
                throw new RefundException(RefundException::SINGLE_REFUND_APPLY_ORDER_GOODS_NOT_EXISTS);
            }
        }
        // 维权信息
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::MANUAL_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }
        // 换货类型不允许退款
        if ($refund->refund_type == RefundConstant::TYPE_EXCHANGE) {
            throw new RefundException(RefundException::MANUAL_EXCHANGE_TYPE_NOT_REFUND);
        }
        if ($refund->status >= RefundConstant::REFUND_STATUS_SUCCESS) {
            throw new RefundException(RefundException::REFUND_MANUAL_REFUND_IS_FINISH);
        }

        // 积分商城优惠券订单 检测是否有用了的优惠券
        if ($order->order_type == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON) {
            // 积分商城优惠券订单 单独判断
            $res = CreditShopOrderService::checkRefund($order->id);

            // 不可维权
            if (is_error($res)) {
                throw new RefundException(RefundException::REFUND_MANUAL_COUPON_IS_USE);
            }
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 修改
            $refund->status = RefundConstant::REFUND_STATUS_MANUAL;
            $refund->finish_time = date('Y-m-d H:i:s');
            if ($refund->save() === false) {
                throw new RefundException(RefundException::MANUAL_REFUND_FAIL);
            }
            // 订单商品表
            if (empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_MANUAL, 'is_count' => 0], ['order_id' => $orderId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_MANUAL, 'is_count' => 0], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            }

            // 退款后的处理  返还积分/余额  改变商品销量  更新订单状态 记录日志  发送通知 退还优惠券
            // 积分余额抵扣信息
            $deductInfo = OrderRefundModel::getRefundDeductInfo($order, $orderGoodsId);

            // 获取该维权最多可退金额
            $refundPrice = OrderRefundService::calculatePrice($order, $orderGoods);

            // 如果有积分抵扣  只有全部退款时才退回积分
            $isRefundCredit = false;
            if ($deductInfo['credit_deduct'] != 0 && bccomp($refund->price, $refundPrice['price'], 2) == 0) {
                $isRefundCredit = true;
                // 退回积分
                $resCredit = MemberModel::updateCredit($order->member_id, $deductInfo['credit_deduct'], 0, 'credit', 1, '订单退款-积分抵扣返还', MemberCreditRecordStatusConstant::CREDIT_STATUS_REFUND);
                if (is_error($resCredit)) {
                    // 退回积分抵扣出错
                    throw new RefundException(RefundException::MANUAL_ORDER_BACK_CREDIT_FAIL);
                }
            }

            // 余额抵扣
            // 获取维权金额 优先返回余额抵扣
            $refundPrice = $refund->price;
            $backBalance = 0;
            if ($deductInfo['balance_deduct'] != 0) {
                // 返回余额数量
                if (bccomp($refundPrice, $deductInfo['balance_deduct'], 2) <= 0) {
                    $backBalance = $refundPrice;
                    $refundPrice = 0;
                } else {
                    $backBalance = $deductInfo['balance_deduct'];
                    $refundPrice = bcsub($refundPrice, $backBalance, 2);
                }
                $resCredit = MemberModel::updateCredit($order->member_id, $backBalance, 0, 'balance', 1, '订单退款-余额抵扣返还', MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND);
                if (is_error($resCredit)) {
                    // 退回余额抵扣出错
                    throw new RefundException(RefundException::MANUAL_ORDER_BACK_BALANCE_FAIL);
                }
            }

            // 修改 order表 refund_price 字段
            $order->refund_price = bcadd($order->refund_price, $refundPrice, 2);
            $order->is_count = 0;
            if ($order->save() === false) {
                // 修改失败
                throw new RefundException(RefundException::MANUAL_ORDER_CHANGE_REFUND_PRICE_FAIL);
            }

            // 保存订单原始状态
            $oldOrderStatus = $order->status;

            // 维权完成 关闭订单
            $isCloseOrder = OrderRefundService::closeOrder($order, $orderGoods, $refund);
            if (is_error($isCloseOrder)) {
                throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_CLOSE_FAIL, $isCloseOrder['message']);
            }

            // 修改分销订单状态
            CommissionOrderService::updateRefundStatus($order->member_id, $orderId, $orderGoodsId);

            // 消费奖励
            ConsumeRewardLogService::refundBack($refund->member_id, $orderId, $orderGoodsId);
            // 购物奖励
            ShoppingRewardLogModel::refundBack($refund->member_id, $orderId, $orderGoodsId);

            // 如果有积分 退积分 (目前只是积分商城
            if ($refund->credit != 0) {
                $resCredit = MemberModel::updateCredit($order->member_id, $refund->credit, 0, 'credit', 1, '订单退款-积分支付退还', MemberCreditRecordStatusConstant::CREDIT_STATUS_CREDIT_SHOP_REFUND);
                if (is_error($resCredit)) {
                    // 退回余额抵扣出错
                    throw new RefundException(RefundException::CREDIT_STATUS_CREDIT_SHOP_REFUND);
                }
            }

            // 发送通知

            // 日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_MANUAL,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_MANUAL),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        '订单id' => $refund->order_id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '手动退款',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_MANUAL,
                    ]
                ]
            );

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode());
        }

        return $this->success();
    }

    /**
     * 同意退款
     * 仅退款 直接完成
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRefundAccept()
    {
        $orderId = RequestHelper::post('order_id');
        $orderGoodsId = RequestHelper::post('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::REFUND_ACCEPT_PARAMS_ERROR);
        }
        // 订单信息
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_NOT_ALLOW_REFUND);
        }
        // 订单商品信息
        $orderGoods = null;
        // 如果是单品维权  获取订单商品信息
        if (!empty($orderGoodsId)) {
            $orderGoods = OrderGoodsModel::findOne(['id' => $orderGoodsId, 'order_id' => $orderId]);
            if (empty($orderGoods)) {
                // 找不到订单商品
                throw new RefundException(RefundException::SINGLE_REFUND_APPLY_ORDER_GOODS_NOT_EXISTS);
            }
        }
        // 维权信息
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }
        if ($refund->status >= RefundConstant::REFUND_STATUS_SUCCESS) {
            throw new RefundException(RefundException::REFUND_MANUAL_REFUND_IS_FINISH);
        }

        // 积分商城优惠券订单 检测是否有用了的优惠券
        if ($order->order_type == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON) {
            // 积分商城优惠券订单 单独判断
            $res = CreditShopOrderService::checkRefund($order->id);

            // 不可维权
            if (is_error($res)) {
                throw new RefundException(RefundException::REFUND_MANUAL_COUPON_IS_USE);
            }
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 如果退款单号为空 则生成
            if (empty($refund->refund_no)) {
                // 生成退款单号
                $refund->refund_no = OrderNoHelper::getOrderNo('RE', $this->clientType);
            }
            $refund->status = RefundConstant::REFUND_STATUS_SUCCESS;
            $refund->finish_time = date('Y-m-d H:i:s');
            if ($refund->save() === false) {
                throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_ACCEPT_REFUND_FAIL);
            }
            // 订单商品表
            if (empty($orderGoodsId)) {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS, 'is_count' => 0], ['order_id' => $orderId]);
            } else {
                OrderGoodsModel::updateAll(['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS, 'is_count' => 0], ['order_id' => $orderId, 'id' => $orderGoodsId]);
            }

            // 退款后的处理  返还积分/余额  改变商品销量  更新订单状态 记录日志  发送通知 退还优惠券
            // 积分余额抵扣信息
            $deductInfo = OrderRefundModel::getRefundDeductInfo($order, $orderGoodsId);

            // 获取该维权最多可退金额
            $refundPrice = OrderRefundService::calculatePrice($order, $orderGoods);
            $isRefundCredit = false;
            // 如果有积分抵扣  只有全部退款时才退回积分
            if ($deductInfo['credit_deduct'] != 0 && bccomp($refund->price, $refundPrice['price'], 2) == 0) {
                $isRefundCredit = true;
                // 退回积分
                $resCredit = MemberModel::updateCredit($order->member_id, $deductInfo['credit_deduct'], 0, 'credit', 1, '订单退款-积分抵扣返还', MemberCreditRecordStatusConstant::CREDIT_STATUS_REFUND, [
                    'order_id' => $orderId
                ]);
                if (is_error($resCredit)) {
                    // 退回积分抵扣出错
                    throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_BACK_CREDIT_FAIL);
                }
            }

            // 余额抵扣
            // 优先返回余额抵扣  剩下的钱按支付方式返回
            // 保存余额抵扣完剩下的钱 再通过支付方式返回
            $refundPrice = $refund->price;
            $backBalance = 0;
            if ($deductInfo['balance_deduct'] != 0) {
                // 返回余额数量
                if (bccomp($refundPrice, $deductInfo['balance_deduct'], 2) < 0) {
                    $backBalance = $refundPrice;
                    $refundPrice = 0;
                } else {
                    $backBalance = $deductInfo['balance_deduct'];
                    $refundPrice = bcsub($refundPrice, $backBalance, 2);
                }
                if ($backBalance != 0) {
                    $resBalance = MemberModel::updateCredit($order->member_id, $backBalance, 0, 'balance', 1, '订单退款-余额抵扣返还', MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND, [
                        'order_id' => $orderId
                    ]);
                    if (is_error($resBalance)) {
                        // 退回余额抵扣出错
                        throw new RefundException(RefundException::MANUAL_ORDER_BACK_BALANCE_FAIL);
                    }
                }
            }

            // 保存订单原始状态
            $oldOrderStatus = $order->status;

            // 修改 order表 refund_price 字段
            $order->refund_price = bcadd($order->refund_price, $refundPrice, 2);
            $order->is_count = 0;
            if ($order->save() === false) {
                // 修改失败
                throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_CHANGE_REFUND_PRICE_FAIL);
            }
            // 维权完成 关闭订单
            $isCloseOrder = OrderRefundService::closeOrder($order, $orderGoods, $refund);
            if (is_error($isCloseOrder)) {
                throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_CLOSE_FAIL, $isCloseOrder['message']);
            }
            // 修改分销订单状态
            CommissionOrderService::updateRefundStatus($order->member_id, $orderId, $orderGoodsId);

            // 消费奖励
            $res = ConsumeRewardLogService::refundBack($refund->member_id, $orderId, $orderGoodsId);

            // 购物奖励
            $res = ShoppingRewardLogModel::refundBack($refund->member_id, $orderId, $orderGoodsId);


            // 如果有积分 退积分 (目前只是积分商城
            if ($refund->credit != 0) {
                $resCredit = MemberModel::updateCredit($order->member_id, $refund->credit, 0, 'credit', 1, '订单退款-积分支付退还', MemberCreditRecordStatusConstant::CREDIT_STATUS_CREDIT_SHOP_REFUND, [
                    'order_id' => $order->id
                ]);
                if (is_error($resCredit)) {
                    // 退回余额抵扣出错
                    throw new RefundException(RefundException::CREDIT_STATUS_CREDIT_SHOP_REFUND);
                }
            }

            // 积分商城优惠券订单 回收优惠券
            if ($order->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                CreditShopOrderService::refund($order->id);
            }

            // 日志
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_REFUND_REFUND_ACCEPT,
                OrderLogConstant::getText(OrderLogConstant::ORDER_REFUND_REFUND_ACCEPT),
                $refund->id,
                [
                    'log_data' => $refund->attributes,
                    'log_primary' => [
                        '订单id' => $refund->order_id,
                        '订单号' => $order->order_no,
                        '维权单号' => $refund->refund_no,
                        '维权类型' => OrderRefundModel::$refundTypeText[$refund->refund_type],
                        '是否单品维权' => $refund->order_goods_id ? '是' : '否',
                        '操作' => '自动退款',
                    ],
                    'dirty_identity_code' => [
                        OrderLogConstant::ORDER_REFUND_REFUND_ACCEPT,
                    ]
                ]
            );

            //  最后退款  保证不出错
            // 如果剩下可退余额大于0 原支付方式返回 否则不需要返回
            //  如果是余额支付 或 货到付款  退到余额
            if ($refundPrice > 0 && $order->pay_type != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM) {
                // 退款记录数据
                $refundLogData = [
                    'member_id' => $order->member_id,
                    'money' => $refundPrice,
                    'order_id' => $orderId,
                    'order_no' => $order->order_no,
                    'status' => 1,
                    'type' => RefundLogConstant::TYPE_ORDER_BUYER_REFUND,
                ];

                try {
                    // 调用交易订单服务进行退款
                    TradeOrderService::operation([
                        'orderNo' => $order->order_no,  // 请使用订单编号
                    ])->refund($refundPrice, '维权完成退款', [
                        'videoRefund' => (bool)($order['scene'] == OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST),
                        'aftersale_id' => $refund->aftersale_id,
                    ]);

                } catch (\Exception $exception) {
                    // 如果找不到订单  使用旧版
                    if ($exception->getCode() == '108130') {
                        $config = [
                            "member_id" => $order->member_id,
                            "order_id" => $orderId,
                            "order_no" => $order->order_no,
                            "refund_fee" => $refundPrice,
                            "client_type" => $order->create_from,
                            "pay_type" => $order->pay_type,
                            "pay_price" => $order->pay_price,
                            "order_type" => PayOrderTypeConstant::ORDER_TYPE_ORDER,
                            "refund_desc" => "维权完成退款",
                        ];
                        $payDriver = PayComponent::getInstance($config);
                        $result = $payDriver->refund();
                        if (is_error($result)) {
                            $refundLogData['status'] = 0;
                            $refundLogData['remark'] = $result['message'];
                            // 写入退款记录
                            RefundLogModel::writeLog($refundLogData);
                            throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_ACCEPT_REFUND_OTHER_FAIL, $result['message']);
                        }
                    } else {
                        // 写入退款记录
                        $refundLogData['status'] = 0;
                        $refundLogData['remark'] = $exception->getMessage();
                        RefundLogModel::writeLog($refundLogData);
                        throw new RefundException(RefundException::REFUND_ACCEPT_ORDER_ACCEPT_REFUND_OTHER_FAIL, $exception->getMessage());
                    }
                }

                // 写入退款记录
                RefundLogModel::writeLog($refundLogData);
            }

            $transaction->commit();

        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException(RefundException::REFUND_ACCEPT_FAIL, $exception->getMessage() . '(' . $exception->getCode() . ')');
        }

        return $this->success();
    }

    /**
     * 所有快递公司
     * @return array|Response
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAllExpress()
    {
        $list = CoreExpressModel::getAll(false);
        return $this->result(['list' => $list]);
    }

    /**
     * 查询退换货物流
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionQueryExpress()
    {
        $get = RequestHelper::get();

        $orderInfo = OrderModel::findOne(['id' => $get['order_id']]);
        if (empty($orderInfo)) {
            throw new RefundException(RefundException::QUERY_EXPRESS_ORDER_IS_NOT_EXISTS);
        }

        $express = CoreExpressModel::queryExpress($get['express_sn'], $get['express_code'], $get['express_encoding'], [
            'buyer_mobile' => $orderInfo->buyer_mobile
        ]);

        $express = CoreExpressModel::decodeExpressDate($express);

        return $this->success(['data' => $express]);
    }

}
