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

namespace shopstar\mobile\order;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\exceptions\order\RefundException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\order\refund\OrderRefundService;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 整单维权类
 * Class RefundController
 * @package app\controllers\wap\order
 * @author 青岛开店星信息技术有限公司
 */
class RefundController extends BaseMobileApiController
{
    /**
     * 维权订单列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $select = ['refund.id', 'refund.status', 'refund.price', 'refund.created_at', 'refund.refund_type', 'refund.order_goods_id', 'refund.order_id', 'refund.credit', 'order.activity_type', 'order.order_type'];
        $leftJoin = [];

        $leftJoin[] = [OrderModel::tableName() . ' order', 'order.id=refund.order_id'];

        $params = [
            'select' => $select,
            'alias' => 'refund',
            'where' => [
                'and',
                ['refund.is_history' => 0],
                ['between', 'refund.status', 0, 9],
                ['refund.member_id' => $this->memberId]
            ],
            'leftJoins' => $leftJoin,
            'orderBy' => ['refund.created_at' => SORT_DESC]
        ];

        $list = OrderRefundModel::getColl($params, [
            'callable' => function (&$row) {
                if ($row['order_goods_id'] == 0) {
                    // 整单
                    $row['order_goods'] = OrderGoodsModel::find()
                        ->select('id, title, thumb, option_title, price, ext_field, goods_id')
                        ->where(['order_id' => $row['order_id']])
                        ->get();
                } else {
                    // 单品
                    $row['order_goods'] = OrderGoodsModel::find()
                        ->select('id, title, thumb, option_title, price, ext_field, goods_id')
                        ->where(['order_id' => $row['order_id'], 'id' => $row['order_goods_id']])
                        ->get();
                }
            }
        ]);

        foreach ($list['list'] as $key => $value) {
            foreach ($value['order_goods'] as $index => $item) {
                $list['list'][$key]['order_goods'][$index]['ext_field'] = Json::decode($item['ext_field']);
                $list['list'][$key]['order_goods'][$index]['credit'] = $value['credit'];
            }
        }

        return $this->result($list);
    }

    /**
     * 整单申请维权
     * 退款 退货退款 换货
     * @return Response
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): Response
    {
        // 订单id
        $orderId = RequestHelper::getInt('order_id');
        // 订单商品id 单品维权用
        $orderGoodsId = RequestHelper::getInt('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::REFUND_APPLY_PARAMS_ERROR);
        }
        // 订单商品
        $orderGoods = null;
        $data = [];
        // 获取订单信息
        $order = OrderModel::findOne(['id' => $orderId]);
        if (empty($order)) {
            throw new RefundException(RefundException::REFUND_APPLY_ORDER_NOT_EXISTS);
        }
        // 如果是单品维权  获取订单商品信息
        if (!empty($orderGoodsId)) {
            $orderGoods = OrderGoodsModel::findOne(['id' => $orderGoodsId, 'order_id' => $orderId]);
            if (empty($orderGoods)) {
                // 找不到订单商品
                throw new RefundException(RefundException::SINGLE_REFUND_APPLY_ORDER_GOODS_NOT_EXISTS);
            }
        }
        // 首次发起维权
        if ($order->is_refund == OrderConstant::IS_REFUND_NO) {
            // 检查是否可以维权
            $check = OrderRefundService::checkRefund($order, $orderGoods);
            // 不可维权
            if (is_error($check)) {
                throw new RefundException(RefundException::REFUND_APPLY_CHECK_ERROR, $check['message']);
            }
        } else {
            // 存在维权信息
            // 如果申请整单维权
            if (empty($orderGoodsId)) {
                // 如果已维权的类型是单品 查找订单的所有维权信息
                if ($order->refund_type == OrderConstant::REFUND_TYPE_SINGLE) {
                    $refunds = OrderRefundModel::findAll(['order_id' => $orderId, 'is_history' => 0]);
                    // 遍历判断是否拒绝 全部拒绝可以再次申请 否则抛异常
                    foreach ($refunds as $item) {
                        if ($item['status'] != RefundConstant::REFUND_STATUS_REJECT) {
                            throw new RefundException(RefundException::REFUND_APPLY_ORDER_IS_SINGLE_REFUND);
                        }
                    }
                    // 使用后销毁
                    unset($refunds);
                } else {
                    // 如果申请的是整单维权 查找整单维权信息
                    $refund = OrderRefundModel::getRefundByOrder($orderId);
                    if (is_error($refund)) {
                        throw new RefundException(RefundException::REFUND_APPLY_GET_REFUND_INFO_ERROR, $refund['message']);
                    }
                }
            } else { // 单品维权申请
                // 如果已维权的是整单类型
                if ($order->refund_type == OrderConstant::REFUND_TYPE_ALL) {
                    // 获取整单维权信息 判断是否已拒绝
                    $refund = OrderRefundModel::getRefundByOrder($orderId);
                    if ($refund->status != RefundConstant::REFUND_STATUS_REJECT) {
                        throw new RefundException(RefundException::REFUND_APPLY_ORDER_IS_ALL_REFUND);
                    }
                    // 使用后销毁
                    unset($refund);
                } else {
                    // 如果已维权的是单品
                    if ($orderGoods->is_single_refund == 1) {
                        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
                        if (is_error($refund)) {
                            throw new RefundException(RefundException::REFUND_APPLY_GET_REFUND_INFO_ERROR, $refund['message']);
                        }
                    } else {
                        // 另一件商品维权 检查该商品
                        $check = OrderRefundService::checkRefund($order, $orderGoods);
                        if (is_error($check)) {
                            throw new RefundException(RefundException::REFUND_APPLY_OTHER_SINGLE_CHECK_ERROR, $check['message']);
                        }
                    }
                }
            }
            if (!empty($refund)) {
                // 申请中 获取申请信息
                if ($refund->status == RefundConstant::REFUND_STATUS_APPLY) {
                    $data['refund'] = $refund;
                } else if ($refund->status == RefundConstant::REFUND_STATUS_REJECT) {
                    // 已拒绝 重新检查是否可以维权
                    $check = OrderRefundService::checkRefund($order, $orderGoods);
                    // 不可维权
                    if (is_error($check)) {
                        throw new RefundException(RefundException::REFUND_APPLY_REJECT_CHECK_ERROR, $check['message']);
                    }
                } else {
                    // 其他情况不允许修改
                    throw new RefundException(RefundException::REFUND_APPLY_ORDER_NOT_CHANG_REFUND_INFO);
                }
            }
        }

        // 维权设置
        $set = ShopSettings::get('sysset.refund');
        // 获取维权的类型
        $data['refund_type'] = OrderRefundService::getCanRefundType($order, $orderGoodsId);
        if (is_error($data['refund_type'])) {
            throw new RefundException(RefundException::REFUND_APPLY_ORDER_NOT_ALLOW_REFUND, $data['refund_type']['message']);
        }
        // 商品信息
        if (empty($orderGoodsId)) {
            $data['goods_info'] = Json::decode($order->goods_info);
        } else {
            // 订单商品
            $data['goods_info'] = Json::decode($order->goods_info);
            foreach ($data['goods_info'] as $key => $item) {
                if ($orderGoods->goods_id != $item['goods_id'] || $orderGoods->option_id != $item['option_id']) {
                    unset($data['goods_info'][$key]);
                }
            }
            $data['goods_info'] = array_values($data['goods_info']);
        }

        // 维权金额包
        $data['refund_price'] = OrderRefundService::calculatePrice($order, $orderGoods);

        // 维权说明
        $data['refund_info'] = $set['refund_info'];

        return $this->success($data);
    }

    /**
     * 售后申请 提交
     * @return Response
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit(): Response
    {
        // 获取提交数据
        $post = RequestHelper::post();
        $data['order_id'] = $post['order_id'];
        $data['order_goods_id'] = $post['order_goods_id'] ?? 0; // 订单商品id 单品维权用
        $data['refund_type'] = $post['refund_type']; // 1:退款 2:退货退款 3:退货
        $data['content'] = $post['content'];
        $data['reason'] = $post['reason'] ?? '';
        $data['images'] = $post['images'] ?? '';
        $data['price'] = $post['price'] ?: 0;
        $data['status'] = 0; // 提交默认0


        // 订单商品
        $orderGoods = null;
        // 参数错误
        if (empty($post['order_id']) || empty($post['refund_type']) || empty($post['content'])) {
            throw new RefundException(RefundException::REFUND_SUBMIT_PARAMS_ERROR);
        }
        // 获取订单信息
        $order = OrderModel::findOne(['id' => $post['order_id']]);
        if (empty($order)) {
            throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_NOT_EXISTS);
        }
        // 获取订单商品信息
        if (!empty($data['order_goods_id'])) {
            $orderGoods = OrderGoodsModel::findOne(['id' => $data['order_goods_id'], 'order_id' => $data['order_id']]);
            if (empty($orderGoods)) {
                // 找不到订单商品
                throw new RefundException(RefundException::SINGLE_REFUND_SUBMIT_ORDER_GOODS_NOT_EXISTS);
            }
        }
        // 检查订单是否可维权
        $check = OrderRefundService::checkRefund($order, $orderGoods);
        // 不可维权
        if (is_error($check)) {
            throw new RefundException(RefundException::REFUND_SUBMIT_CHECK_ERROR, $check['message']);
        }

        // 维权支持的方式
        $canRefundType = OrderRefundService::getCanRefundType($order, $data['order_goods_id']);
        // 不支持该维权方式
        if (!$canRefundType[OrderRefundModel::$refundMap[$post['refund_type']]]) {
            throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_REFUND_TYPE_ERROR);
        }
        // 非换货类型 换货不用退款
        if ($post['refund_type'] != RefundConstant::TYPE_EXCHANGE) {
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

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 维权订单
            $refund = OrderRefundModel::getRefundByOrder($order->id, $data['order_goods_id']);
            // 如果不存在 则新增
            if (is_error($refund)) {
                // 多商户 第一次维权 不能平台介入
                if ($post['need_platform'] == 1) {
                    throw new RefundException(RefundException::REFUND_NOT_NEED_PLATFORM);
                }
                $refund = new OrderRefundModel();
                $refund->member_id = $this->memberId;
                $refund->refund_no = OrderNoHelper::getOrderNo('RE', $this->clientType);
            } else if ($refund->status == RefundConstant::REFUND_STATUS_REJECT) {
                // 需要获取是拒绝的订单 要改为历史维权
                $refund->is_history = 1;
                if ($refund->save() === false) {
                    throw new RefundException(RefundException::REFUND_SUBMIT_CHANGE_HISTORY_FAIL);
                }
                // 重新创建
                $refund = new OrderRefundModel();
                $refund->need_platform = (int)$post['need_platform'];
                $refund->member_id = $this->memberId;
                $refund->refund_no = OrderNoHelper::getOrderNo('RE', $this->clientType);
            }

            $refund->setAttributes($data);
            if ($refund->save() === false) {
                throw new RefundException(RefundException::REFUND_SUBMIT_ORDER_REFUND_FAIL, $refund->getErrorMessage());
            }

            // 整单维权
            if (empty($data['order_goods_id'])) {

                // 订单表 is_refund 1 维权中,  refund_type 1 整单维权
                OrderModel::updateAll(
                    ['is_refund' => OrderConstant::IS_REFUND_YES, 'refund_type' => OrderConstant::REFUND_TYPE_ALL],
                    ['id' => $post['order_id'], 'member_id' => $this->memberId]
                );

                // 订单商品表
                OrderGoodsModel::updateAll(
                    ['refund_type' => $post['refund_type'], 'refund_status' => 0],
                    ['order_id' => $post['order_id']]
                );

            } else { // 单品维权
                // 订单表 refund_type 是 是否单品维权
                OrderModel::updateAll(
                    ['is_refund' => OrderConstant::IS_REFUND_YES, 'refund_type' => OrderConstant::REFUND_TYPE_SINGLE],
                    ['id' => $data['order_id'], 'member_id' => $this->memberId]
                );
                // 订单商品表  is_single_refund 1是单品维权 refund_type 是 退款类型
                OrderGoodsModel::updateAll(
                    ['is_single_refund' => 1, 'refund_type' => $data['refund_type'], 'refund_status' => 0],
                    ['id' => $data['order_goods_id'], 'member_id' => $this->memberId]
                );
            }

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
                'address_info' => $order['address_state'] . '-' . $order['address_city'] . '-' . $order['address_area'] . '-' . $order['address_detail'],
                'pay_price' => $order['pay_price'],
                'status' => OrderStatusConstant::getText($order['status']),
                'created_at' => $order['created_at'],
                'pay_time' => $order['pay_time'],
                'finish_time' => $order['finish_time'],
                'send_time' => $order['send_time'],
                'remark' => $order['remark'],
                'order_no' => $order['order_no'],
                'refund_price' => $order['pay_price'],
                'refund_type' => $post['refund_type'] == 1 ? '退款' : ($post['refund_type'] == 2 ? '退款退货' : '换货'),
                'apply_time' => DateTimeHelper::now(),
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::SELLER_ORDER_REFUND, $messageData, '');
            if (!is_error($notice)) {
                $notice->sendMessage();
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode(), $exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 维权详情 进度
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $orderId = RequestHelper::getInt('order_id');
        $orderGoodsId = RequestHelper::getInt('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::REFUND_DETAIL_PARAMS_ERROR);
        }
        // 维权信息
        $data['refund'] = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($data['refund'])) {
            throw new RefundException(RefundException::REFUND_DETAIL_REFUND_INFO_NOT_EXISTS, $data['refund']['message']);
        }

        // 获取订单信息
        $order = OrderModel::findOne(['id' => $orderId]);
        if (empty($order)) {
            throw new RefundException(RefundException::REFUND_DETAIL_ORDER_NOT_EXISTS);
        }
        // 返回订单商品信息
        $data['goods_info'] = Json::decode($order->goods_info);
        // 单品维权只返回维权商品的信息
        if (!empty($orderGoodsId)) {
            $orderGoods = OrderGoodsModel::findOne(['id' => $orderGoodsId, 'order_id' => $orderId]);
            if (empty($orderGoods)) {
                // 找不到订单商品
                throw new RefundException(RefundException::SINGLE_REFUND_APPLY_ORDER_GOODS_NOT_EXISTS);
            }
            foreach ($data['goods_info'] as $key => $item) {
                if ($orderGoods->goods_id != $item['goods_id'] || $orderGoods->option_id != $item['option_id']) {
                    unset($data['goods_info'][$key]);
                }
            }
            $data['goods_info'] = array_values($data['goods_info']);
        }
        // 维权文字
        $data['refund'] = $data['refund']->toArray();
        $data['refund']['refund_type_text'] = OrderRefundModel::$refundTypeText[$data['refund']['refund_type']];
        // 维权设置
        $set = ShopSettings::get('sysset.refund');
        // 维权说明
        $data['refundInfo'] = $set['refund_info'];

        return $this->success($data);
    }

    /**
     * 用户提交快递单号
     * 换货 退货退款
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExpress()
    {
        $post = RequestHelper::post();
        if (empty($post['order_id']) || empty($post['express_sn']) || empty($post['express_code']) || empty($post['express_name'])) {
            throw new RefundException(RefundException::REFUND_EXPRESS_PARAMS_ERROR);
        }

        if (empty($post['express_encoding']) && strtolower($post['express_code']) != RefundConstant::EXPRESS_CODE_QITA) {
            throw new RefundException(RefundException::REFUND_EXPRESS_PARAMS_ERROR);
        }
        //判断是否已经超时维权时间 是则自动取消订单
        $refund = OrderRefundModel::getRefundByOrder($post['order_id']);
        if (!isset($post['is_edit'])) {
            if ($refund->queue_id != '0') {
                if (DateTimeHelper::now() > $refund->timeout_cancel) {
                    throw new RefundException(RefundException::REFUND_TIMEOUT_CANCEL_ALREADY);
                }
            }
        }
        // 保存物流信息
        $save = OrderRefundModel::setExpress();
        if (is_error($save)) {
            throw new RefundException(RefundException::REFUND_EXPRESS_SAVE_FAIL, $save['message']);
        }
        QueueHelper::remove($refund->queue_id);
        return $this->success();
    }

    /**
     * 取消申请
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCancel()
    {
        $orderId = RequestHelper::getInt('order_id');
        $orderGoodsId = RequestHelper::getInt('order_goods_id', 0);
        if (empty($orderId)) {
            throw new RefundException(RefundException::REFUND_CANCEL_PARAMS_ERROR);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 更新维权表状态
            $res = OrderRefundService::cancelRefund($orderId, $orderGoodsId);
            if (is_error($res)) {
                throw new RefundException(RefundException::REFUND_ORDER_CANCEL_FAIL, $res['message']);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
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
            throw new RefundException(RefundException::MEMBER_EXCHANGE_CLOSE_PARAMS_ERROR);
        }

        // 检查订单是否符合
        $order = OrderRefundModel::getRefundOrder($orderId, $orderGoodsId);
        if (is_error($order)) {
            throw new RefundException(RefundException::MEMBER_EXCHANGE_CLOSE_ORDER_NOT_ALLOW_REFUND, $order['message']);
        }

        // 获取维权信息
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refund)) {
            throw new RefundException(RefundException::MEMBER_EXCHANGE_CLOSE_ORDER_REFUND_NOT_EXISTS, $refund['message']);
        }

        // 如果不是等待完成状态，不允许关闭
        if ($refund->status != RefundConstant::REFUND_STATUS_WAIT) {
            throw new RefundException(RefundException::MEMBER_EXCHANGE_CLOSE_REFUND_NOT_ALLOW_CLOSE);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 修改
            $refund->status = RefundConstant::REFUND_STATUS_SUCCESS;
            $refund->finish_time = date('Y-m-d H:i:s');

            if (!$refund->save()) {
                throw new RefundException(RefundException::MEMBER_EXCHANGE_CLOSE_REFUND_CLOSE_FAIL);
            }

            if (!empty($orderGoodsId)) {
                // 订单商品表
                OrderGoodsModel::updateAll(
                    ['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS],
                    ['order_id' => $orderId, 'id' => $orderGoodsId, 'member_id' => $this->memberId]
                );
            } else {
                // 订单商品表
                OrderGoodsModel::updateAll(
                    ['refund_status' => RefundConstant::REFUND_STATUS_SUCCESS],
                    ['order_id' => $orderId, 'member_id' => $this->memberId]
                );
            }

            // 判断整单是否可完成
            OrderRefundService::successOrder($orderId, $orderGoodsId);

            // 发送通知


            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new RefundException($exception->getCode());
        }

        return $this->success();
    }

    /**
     * 获取所有快递公司
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAllExpress()
    {
        $list = CoreExpressModel::getAll(false);
        return $this->result(['list' => $list]);
    }

    /**
     * 查询物流信息
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionQueryExpress()
    {
        $get = RequestHelper::get();
        $orderInfo = OrderModel::findOne(['id' => $get['order_id']]);
        if (empty($orderInfo)) {
            throw new RefundException(RefundException::REFUND_QUERY_EXPRESS_ORDER_IS_NOT_EXISTS);
        }
        $express = CoreExpressModel::queryExpress($get['express_sn'], $get['express_code'], $get['express_encoding'], [
            'buyer_mobile' => $orderInfo->buyer_mobile
        ]);
        $express = CoreExpressModel::decodeExpressDate($express);

        // 获取维权地址
        $refund = OrderRefundModel::getRefundByOrder($get['order_id'], $get['order_goods_id'] ?? 0);
        $refundInfo = [];
        // 买家信息
        if ($get['type'] == 1) {
            $refundInfo['address'] = $refund->refund_address; // 收货地址
            $refundInfo['express_sn'] = $refund->member_express_sn; // 运单号
            $refundInfo['express_name'] = $refund->member_express_name; // 快递公司
        } else { // 卖家信息
            $refundInfo['address'] = $orderInfo->address_state . " " . $orderInfo->address_city . " " . $orderInfo->address_area . " " . $orderInfo->address_detail; // 收货地址
            $refundInfo['express_sn'] = $refund->seller_express_sn; // 运单号
            $refundInfo['express_name'] = $refund->seller_express_name; // 快递公司
        }

        return $this->success(['data' => $express, 'refund_info' => $refundInfo]);
    }

}
