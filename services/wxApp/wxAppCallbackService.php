<?php

namespace shopstar\services\wxApp;

use Exception;
use shopstar\components\notice\NoticeComponent;
use shopstar\components\wechat\helpers\MiniProgramWxTransactionComponentHelper;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\wxAppOrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\constants\tradeOrder\TradeOrderStatusConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\exceptions\order\RefundException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\tradeOrder\TradeOrderModel;
use shopstar\models\wxTransactionComponent\WxAuditCategoryModel;
use shopstar\models\wxTransactionComponent\WxTransactionComponentModel;
use shopstar\services\order\OrderService;
use shopstar\services\order\refund\OrderRefundService;
use shopstar\services\tradeOrder\TradeOrderService;
use shopstar\services\wxTransactionComponent\WxTransactionComponentOrderService;
use shopstar\services\wxTransactionComponent\WxTransactionComponentService;
use Throwable;
use Yii;
use yii\helpers\Json;

class wxAppCallbackService
{
    /**
     * 微信事件回调映射 新增回调增加对映映射与方法
     * @var string[]
     * @author maiobowen
     * https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/ministore/minishopopencomponent2/callback/Introduction.html
     */
    private static array $eventMap = [
        'open_product_spu_audit' => 'openProductSpuAudit', // 商品审核结果
        'open_product_spu_status_update' => 'openProductSpuStatusUpdate', // 商品系统下架通知
        'open_product_category_audit' => 'openProductCategoryAudit', // 类目审核结果
        'open_product_order_cancel' => 'openProductOrderCancel', // 订单取消
        'open_product_order_pay' => 'openProductOrderPay', // 订单支付成功
        'open_product_order_refund' => 'openProductOrderRefund', // 订单全部退款
        'open_product_order_confirm' => 'openProductOrderConfirm', // 订单确认收货
        'open_product_order_settle' => 'openProductOrderSettle', // 订单结算成功
        'aftersale_new_order' => 'afterSaleNewOrder', // 顾客申请售后
        'aftersale_refund_success' => 'afterSaleRefundSuccess', // 售后单退款成功
        'aftersale_wait_merchant_offline_refund' => 'afterSaleWaitMerchantOfflineRefund', // 待商家线下退款
        'aftersale_wait_merchant_confirm_receipt' => 'afterSaleWaitMerchantConfirmReceipt', // 待商家确认收货
        'aftersale_user_cancel' => 'afterSaleUserCancel', // 用户取消售后单
        'aftersale_update_order' => 'afterSaleNewOrder', // 用户修改申请
    ];

    private static array $eventOrderMap = [
        'open_product_order_pay',
        'open_product_order_cancel',
        'open_product_order_confirm',
    ];

    private static array $eventOrderRefundMap = [
        'aftersale_new_order',
        'aftersale_user_cancel',
        'aftersale_update_order',
        'aftersale_wait_merchant_confirm_receipt',
    ];

    /**
     * @var int 会员id
     */
    private int $memberId;

    /**
     * @var array 回调信息
     */
    private array $inputData;

    /**
     * @var int 订单id
     */
    private int $orderId;

    /**
     * @var OrderModel 订单信息
     */
    private OrderModel $orderInfo;

    /**
     * @var TradeOrderModel 交易订单信息
     */
    private TradeOrderModel $tradeOrder;

    /**
     * @var array 交易组件订单详情
     */
    private array $wxAppOrder;

    /**
     * @var OrderRefundModel|null 维权信息
     */
    private ?OrderRefundModel $refundOrderInfo = null;

    /**
     * @var array 交易组件维权订单详情
     */
    private array $wxRefundDetail;

    /**
     * @var GoodsModel 商品信息
     */
    private GoodsModel $goodsInfo;

    /**
     * @param array $inputData
     * @return void
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function init(array $inputData)
    {
        $this->inputData = $inputData;

        // 处理订单参数
        try {
            in_array($this->inputData['Event'], self::$eventOrderMap) && $this->checkOrderParams();
            in_array($this->inputData['Event'], self::$eventOrderRefundMap) && $this->checkRefundParams();

            // 处理事件回调
            $eventMap = self::$eventMap;
            $function = $eventMap[$this->inputData['Event']];

            $this->$function();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 处理订单参数
     * @return void
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function checkOrderParams()
    {
        // 查询订单
        $this->orderInfo = OrderModel::findOne([
            'id' => $this->inputData['order_info']['out_order_id'],
        ]);
        $this->memberId = $this->orderInfo->member_id ?: 0;
        $this->orderId = $this->orderInfo->id ?: 0;

        // 获取交易订单
        $this->tradeOrder = TradeOrderModel::findOne([
            'order_id' => $this->orderInfo->id,
            'order_no' => $this->orderInfo->order_no,
            'status' => TradeOrderStatusConstant::STATUS_WAIT_PAY,
        ]);

        // 查询交易组件订单状态
        $params = [
            'out_order_id' => $this->orderId,
            'openid' => MemberWxappModel::getOpenId($this->memberId),
        ];

        $this->wxAppOrder = MiniProgramWxTransactionComponentHelper::getOrder($params);

        if (isset($this->wxAppOrder['errcode']) ? $this->wxAppOrder['errcode'] != 0 : $this->wxAppOrder['error'] != 0) {
            throw new Exception($this->wxAppOrder['message'], $this->wxAppOrder['error']);
        }
    }

    /**
     * 处理维权参数
     * @return void
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function checkRefundParams()
    {
        $this->refundOrderInfo = OrderRefundModel::findOne([
            'aftersale_id' => $this->inputData['aftersale_info']['aftersale_id'],
        ]);

        // 获取维权单详情
        $this->wxRefundDetail = WxTransactionComponentOrderService::getRefundDetail($this->inputData['aftersale_info']['aftersale_id'] ?: 0);

        if (is_error($this->wxRefundDetail)) {
            throw new Exception($this->wxRefundDetail['message'], $this->wxRefundDetail['error']);
        }

        $this->goodsInfo = GoodsModel::findOne([
            'id' => $this->wxRefundDetail['after_sales_order']['product_info']['out_product_id'] ?: 0,
        ]);

        if (empty($this->goodsInfo)) {
            throw new Exception('未查询到商品信息', -1);
        }
    }

    /**
     * 同步商品审核状态
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductSpuAudit(): string
    {
        WxTransactionComponentModel::updateAll([
            'status' => WxTransactionComponentService::$statusMap[$this->inputData['OpenProductSpuAudit']['status']],
            'remote_status' => WxTransactionComponentService::$remoteStatus[$this->inputData['OpenProductSpuAudit']['spu_status']],
        ], ['goods_id' => $this->inputData['OpenProductSpuAudit']['out_product_id']]);

        return 'SUCCESS';
    }

    /**
     * 同步商品系统下架通知
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductSpuStatusUpdate(): string
    {
        WxTransactionComponentModel::updateAll([
            'remote_status' => WxTransactionComponentService::$remoteStatus[$this->inputData['OpenProductSpuStatusUpdate']['spu_status']],
        ], ['goods_id' => $this->inputData['OpenProductSpuStatusUpdate']['out_product_id']]);

        return 'SUCCESS';
    }

    /**
     * 同步类目审核结果
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductCategoryAudit(): string
    {
        WxAuditCategoryModel::updateAll([
            'status' => $this->inputData['QualificationAuditResult']['status'],
        ], ['audit_id' => $this->inputData['QualificationAuditResult']['audit_id']]);

        return 'SUCCESS';
    }

    /**
     * 订单取消
     * @return string
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductOrderCancel(): string
    {
        // 判断订单状态是否是用户取消
        if ($this->wxAppOrder['order']['status'] != wxAppOrderStatusConstant::WX_APP_ORDER_STATUS_CLOSE) {
            throw new Exception('订单取消失败', -1);
        }

        // 订单已支付 不能取消
        if ($this->orderInfo->status > OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            throw new Exception(OrderException::ORDER_OP_CANCEL_ORDER_STATUS_SEND_ERROR);
        }

        //订单已取消
        if ($this->orderInfo->status < OrderStatusConstant::ORDER_STATUS_CLOSE) {
            throw new Exception(OrderException::ORDER_OP_CANCEL_ORDER_STATUS_CLOSE_ERROR);
        }

        //关闭订单
        $result = OrderService::closeOrder($this->orderInfo, OrderConstant::ORDER_CLOSE_TYPE_BUYER_CLOSE, 0, [
            'is_refund_front' => 0,
            'is_video_close' => false,
        ]);

        if (is_error($result)) {
            throw new Exception($result['message'], OrderException::ORDER_OP_CANCEL_ORDER_ERROR);
        }

        return 'SUCCESS';
    }

    /**
     * 订单支付成功回调
     * @return int|string
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductOrderPay()
    {
        // 如果订单状态不是待发货 认为支付失败
        if ($this->wxAppOrder['order']['status'] != wxAppOrderStatusConstant::WX_APP_ORDER_STATUS_WAIT_SEND) {
            throw new Exception('订单支付失败', -1);
        }

        /**
         * 新增参数is_verify_sign 交易组件不走封装支付 跳过支付验签
         * ['order']['order_detail']['price_info']['order_price'] 订单价格
         * ['order']['order_detail']['price_info']['final_price'] 最终价格 文档没有
         */
        // 拼写订单成功订单需要的参数
        $params = [
            'type' => 'wechat',
            'raw' => ArrayHelper::toXML([
                'out_trade_no' => $this->tradeOrder->trade_no,
                'transaction_id' => $this->inputData['order_info']['transaction_id'],
                'total_fee' => $this->wxAppOrder['order']['order_detail']['price_info']['order_price'],
                'pay_time' => $this->inputData['order_info']['pay_time'],
                'is_verify_sign' => true, // 跳出支付验签
            ]),
        ];

        // 调用交易订单服务处理
        return TradeOrderService::notify($params)->handler();
    }

    private function openProductOrderRefund()
    {
    }

    /**
     * 订单确认收货
     * @return string
     * @throws OrderException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function openProductOrderConfirm(): string
    {
        // 如果订单状态不是待发货 认为支付失败
        if ($this->wxAppOrder['order']['status'] != wxAppOrderStatusConstant::WX_APP_ORDER_STATUS_SUCCESS) {
            throw new Exception('订单确认收货失败', -1);
        }

        $order = OrderModel::getOrderAndOrderGoods($this->orderId);
        if ($order['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
            throw new OrderException(OrderException::ORDER_OP_FINISH_ORDER_STATUS_ERROR);
        }

        $result = OrderService::complete($order, 1, ['videoRefund' => false]);

        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_OP_FINISH_ORDER_ERROR, $result['message']);
        }

        return 'SUCCESS';
    }

    private function openProductOrderSettle()
    {
    }

    /**
     * 用户申请维权|修改维权
     * @return string
     * @throws RefundException
     * @throws Throwable
     * @author 青岛开店星信息技术有限公司
     */
    private function afterSaleNewOrder(): string
    {
        $images = [];
        if (!empty($this->wxRefundDetail['after_sales_order']['media_list'])) {
            foreach ($this->wxRefundDetail['after_sales_order']['media_list'] as $image) {
                // 删除目前不支持上传视频过滤
                if ($image['type'] != 1) {
                    continue;
                }

                $images[] = $image['thumb_url'];
            }
        }

        $data = [
            'order_id' => $this->wxRefundDetail['after_sales_order']['out_order_id'] ?: 0,
            'order_goods_id' => 0,
            'refund_type' => $this->wxRefundDetail['after_sales_order']['type'] ?: 1,
            'content' => $this->wxRefundDetail['after_sales_order']['refund_reason'] ?: '',
            'reason' => '',
            'price' => $this->wxRefundDetail['after_sales_order']['orderamt'] ? bcdiv($this->wxRefundDetail['after_sales_order']['orderamt'], 100, 2) : 0.00,
            'status' => 0,
            'images' => $images ? Json::encode($images) : '',
        ];

        // 参数错误
        if (empty($data['order_id']) || empty($data['refund_type'])) {
            throw new RefundException(RefundException::REFUND_SUBMIT_PARAMS_ERROR);
        }

        // 获取订单信息
        $order = OrderModel::findOne(['id' => $data['order_id']]);
        if (empty($order)) {
            throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_NOT_EXISTS);
        }

        // 获取订单商品信息
        $orderGoods = OrderGoodsModel::findOne([
            'goods_id' => $this->wxRefundDetail['after_sales_order']['product_info']['out_product_id'] ?: 0,
            'order_id' => $data['order_id'],
            'option_id' => $this->goodsInfo->has_option == 1 ? ($this->wxRefundDetail['after_sales_order']['product_info']['out_sku_id'] ?: 0) : 0, // 生成订单时必须上传sku_id 所以回调需要查询是否时单规格商品
        ]);

        if (empty($orderGoods)) {
            // 找不到订单商品
            throw new RefundException(RefundException::SINGLE_REFUND_SUBMIT_ORDER_GOODS_NOT_EXISTS);
        }

        $data['order_goods_id'] = $orderGoods->id;

        // 检查订单是否可维权
        $check = OrderRefundService::checkRefund($order, $orderGoods);

        // 不可维权
        if (is_error($check)) {
            throw new RefundException(RefundException::REFUND_SUBMIT_CHECK_ERROR, $check['message']);
        }

        // 维权支持的方式
        $canRefundType = OrderRefundService::getCanRefundType($order, $data['order_goods_id']);

        // 不支持该维权方式
        if (!$canRefundType[OrderRefundModel::$refundMap[$data['refund_type']]]) {
            throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_REFUND_TYPE_ERROR);
        }

        // 非换货类型 换货不用退款
        if ($data['refund_type'] != RefundConstant::TYPE_EXCHANGE) {
            // 维权金额
            $refundPrice = OrderRefundService::calculatePrice($order, $orderGoods);

            // 如果不可修改  维权金额不等于可维权金额
            if ($refundPrice['is_can_edit'] == 0 && bccomp($refundPrice['price'], $data['price'], 2) != 0) {
                throw new RefundException(RefundException::MEMBER_REFUND_SUBMIT_PRICE_ERROR);
            }
            // 申请金额不能大于最多可维权金额
            if (bccomp($refundPrice['price'], $data['price'], 2) < 0) {
                throw new RefundException(RefundException::MEMBER_REFUND_SUBMIT_PRICE_BIG);
            }

            // 包含运费
            if ($refundPrice['dispatch_price'] != 0) {
                $data['is_contain_dispatch'] = 1;
            }
            // 退积分
            if (!empty($refundPrice['credit'])) {
                $data['credit'] = $refundPrice['credit'];
            }
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 维权订单
            $refund = OrderRefundModel::getRefundByOrder($order->id, $data['order_goods_id']);

            // 如果不存在 则新增
            if (is_error($refund)) {
                $refund = new OrderRefundModel();
                $refund->member_id = $order->member_id;
                $refund->refund_no = OrderNoHelper::getOrderNo('RE', ClientTypeConstant::CLIENT_WXAPP);
                $refund->aftersale_id = $this->wxRefundDetail['after_sales_order']['aftersale_id'];
            } else if ($refund->status == RefundConstant::REFUND_STATUS_REJECT) {
                // 需要获取是拒绝的订单 要改为历史维权
                $refund->is_history = 1;
                if ($refund->save() === false) {
                    throw new RefundException(RefundException::REFUND_SUBMIT_CHANGE_HISTORY_FAIL);
                }

                // 重新创建
                $refund = new OrderRefundModel();
                $refund->need_platform = 0;
                $refund->member_id = $order->member_id;
                $refund->refund_no = OrderNoHelper::getOrderNo('RE', ClientTypeConstant::CLIENT_WXAPP);
                $refund->aftersale_id = $this->wxRefundDetail['after_sales_order']['aftersale_id'];
            }

            $refund->setAttributes($data);
            if ($refund->save() === false) {
                throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_REFUND_FAIL, $refund->getErrorMessage());
            }

            // 单品维权 目前交易组件只支持单品维权
            // 订单表 refund_type 是 是否单品维权
            OrderModel::updateAll(
                ['is_refund' => OrderConstant::IS_REFUND_YES, 'refund_type' => OrderConstant::REFUND_TYPE_SINGLE],
                ['id' => $data['order_id'], 'member_id' => $order->member_id]
            );
            // 订单商品表  is_single_refund 1是单品维权 refund_type 是 退款类型
            OrderGoodsModel::updateAll(
                ['is_single_refund' => 1, 'refund_type' => $data['refund_type'], 'refund_status' => 0],
                ['id' => $data['order_goods_id'], 'member_id' => $order->member_id]
            );

            $order = $order->toArray();
            $order['goods_info'] = Json::decode($order['goods_info']);

            //消息通知
            $messageData = [
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                'member_nickname' => $order['member_nickname'],
                'dispatch_price' => $order['dispatch_price'],
                'goods_title' => ($order['goods_info'][0]['title'] ?: '') . (count($order['goods_info']) > 1 ? '等' : ''),
                'buyer_name' => $order['buyer_name'],
                'buyer_mobile' => $order['buyer_mobile'],
                'address_info' => $order['address_province'] . '-' . $order['address_city'] . '-' . $order['address_area'] . '-' . $order['address_detail'],
                'pay_price' => $order['pay_price'],
                'status' => OrderStatusConstant::getText($order['status']),
                'create_time' => $order['create_time'],
                'pay_time' => $order['pay_time'],
                'finish_time' => $order['finish_time'],
                'send_time' => $order['send_time'],
                'remark' => $order['remark'],
                'order_no' => $order['order_no'],
                'refund_price' => $order['pay_price'],
                'refund_type' => $data['refund_type'] == 1 ? '退款' : ($data['refund_type'] == 2 ? '退款退货' : '换货'),
                'apply_time' => DateTimeHelper::now(),
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::SELLER_ORDER_REFUND, $messageData);

            if (!is_error($notice)) {
                $notice->sendMessage();
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode(), $exception->getMessage());
        }

        return 'SUCCESS';
    }

    private function afterSaleRefundSuccess()
    {
    }

    private function afterSaleWaitMerchantOfflineRefund()
    {
    }

    private function afterSaleWaitMerchantConfirmReceipt()
    {
    }

    /**
     * 用户取消维权
     * @return string
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function afterSaleUserCancel(): string
    {
        // 获取订单商品信息
        $orderGoods = OrderGoodsModel::findOne([
            'goods_id' => $this->wxRefundDetail['after_sales_order']['product_info']['out_product_id'] ?: 0,
            'order_id' => $this->wxRefundDetail['after_sales_order']['out_order_id'] ?: 0,
            'option_id' => $this->goodsInfo->has_option == 1 ? ($this->wxRefundDetail['after_sales_order']['product_info']['out_sku_id'] ?: 0) : 0, // 生成订单时必须上传sku_id 所以回调需要查询是否时单规格商品
        ]);

        if (empty($orderGoods)) {
            // 找不到订单商品
            throw new RefundException(RefundException::SINGLE_REFUND_SUBMIT_ORDER_GOODS_NOT_EXISTS);
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 更新维权表状态
            $res = OrderRefundService::cancelRefund(($this->wxRefundDetail['after_sales_order']['out_order_id'] ?: 0), $orderGoods->id, ['videoRefund' => false]);
            if (is_error($res)) {
                throw new RefundException(RefundException::REFUND_ORDER_CANCEL_FAIL, $res['message']);
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        return 'SUCCESS';
    }
}
