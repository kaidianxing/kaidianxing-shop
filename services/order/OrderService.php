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

namespace shopstar\services\order;

use shopstar\bases\service\BaseService;
use shopstar\components\notice\NoticeComponent;
use shopstar\components\payment\base\PayOrderTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\finance\RefundLogConstant;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\goods\GoodsVirtualConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderPackageCityDistributionTypeConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\OrderConstant;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\constants\RefundConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\constants\virtualAccount\VirtualAccountDataConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\StringHelper;
use shopstar\jobs\order\AutoCommentJob;
use shopstar\jobs\order\AutoReceiveOrderJob;
use shopstar\jobs\order\GiveCreditJob;
use shopstar\jobs\printer\AutoPrinterOrder;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\finance\RefundLogModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\shoppingReward\ShoppingRewardLogModel;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;
use shopstar\services\commission\CommissionOrderService;
use shopstar\services\commission\CommissionService;
use shopstar\services\consumeReward\ConsumeRewardLogService;
use shopstar\services\goods\GoodsService;
use shopstar\services\member\MemberLevelService;
use shopstar\services\sale\CouponMemberService;
use shopstar\services\tradeOrder\TradeOrderService;
use shopstar\structs\order\OrderPaySuccessStruct;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class OrderService extends BaseService
{

    /**
     * 处理字段文字
     * @param array $row
     */
    public static function handleFieldText(array &$row)
    {
        if (isset($row['status'])) {
            $row['status_text'] = OrderModel::$orderStatus[$row['status']];
        }
        if (isset($row['pay_type'])) {
            $row['pay_type_text'] = OrderModel::$orderPayType[$row['pay_type']];
        }
        if (isset($row['create_from'])) {
            $row['create_from_text'] = ClientTypeConstant::getText($row['create_from']);
        }
    }

    /**
     * 关闭订单
     * @param $order
     * @param $closeType
     * @param int $operator
     * @param array $options
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeOrder($order, $closeType, int $operator = 0, array $options = [])
    {
        $options = array_merge([
            'cancel_reason' => '', //关闭理由
            'transaction' => true,
        ], $options);

        //判断是否等于当前类
        if (!$order instanceof OrderModel) {
            $order = OrderModel::findOne(['id' => $order]);
        }

        //判断不可更改！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
        if ($order['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PAY) {
            return error('订单状态错误');
        }

        // 秒杀订单 返回库存
        if ($order['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL) {
            $res = ShopMarketingGoodsMapModel::closeSeckillUpdateStock($order->id);
            if (is_error($res)) {
                return $res;
            }
        }

        if ($options['cancel_reason'] && StringHelper::length($options['cancel_reason']) >= 50) {
            return error('取消理由过长');
        }

        $order->setAttributes([
            'status' => OrderStatusConstant::ORDER_STATUS_CLOSE,
            'close_type' => $closeType,
            'cancel_time' => DateTimeHelper::now(),
            'cancel_reason' => $options['cancel_reason']
        ]);

        $options['transaction'] && $tr = \Yii::$app->db->beginTransaction();

        try {

            //解析订单商品
            $orderGoods = Json::decode($order['goods_info']);

            //修改订单状态
            if (!$order->save()) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR);
            }

            //修改订单商品
            OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_CLOSE], ['order_id' => $order->id,]);

            // 需要返还库存 (切换规格导致订单关闭不需要返还
            if (empty($options['un_update_stock'])) {
                //返还库存
                $result = GoodsService::updateQty(false, $order->id, [], GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_ORDER, [
                    'transaction' => false,
                    'reason' => '关闭订单，返还库存',
                    'presell_activity_id' => $presellOrder['activity_id'] ?? 0, // 预售活动id
                ]);
            }

            //返还营销
            self::returnActivity($order->id, $operator, '关闭订单返还');

            if (is_error($result)) {
                throw new \Exception($result['message']);
            }

            // 修改分销订单状态
            CommissionOrderService::updateRefundStatus($order['member_id'], $order['id']);


            $options['transaction'] && $tr->commit();
        } catch (\Throwable $throwable) {
            $options['transaction'] && $tr->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }


    /**
     * 发货
     * @param $orderInfo //订单信息
     * @param array $data 用户提交的数据
     * @return array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function ship($orderInfo, array $data)
    {
        if (is_numeric($orderInfo)) {
            $orderInfo = OrderModel::getOrderAndOrderGoods($orderInfo, 0);
        }

        //订单商品
        $orderGoodsInfo = OrderGoodsModel::find()->where(['order_id' => $orderInfo['id']])->indexBy('id')->asArray()->all();

        if (empty($orderInfo)) {
            return error('订单不存在');
        }

        if (empty($data)) {
            return error('参数不能为空');
        }

        //校验达达配送的商品重量
        if ($data['city_distribution_type'] == OrderPackageCityDistributionTypeConstant::DADA) {
            $orderWeight = array_sum(array_column($orderInfo['orderGoods'], 'weight'));
            if ($orderWeight <= 0) {
                return error('达达配送订单重量不能为空，建议使用其他配送方式');
            }
        }

        $orderGoods = ArrayHelper::index($orderInfo['orderGoods'], 'id');
        if ((int)$orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_SEND && (int)$orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND) {
            return error('订单状态错误，无法进行发货！(1)');
        }

        if ($orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_ORDINARY) {
            // 过滤虚拟商品
            if (!empty($orderInfo['dispatch_type'])) {
                if (!in_array($orderInfo['dispatch_type'], [OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS, OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH, OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY])) {
                    return error('订单配送方式错误，无法进行发货！(2)');
                }
            }
        }

        // 校验是否开启配送方式
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {
            $intracity = ShopSettings::get('dispatch.intracity');

            if ($intracity['enable'] == 0) {
                return error('同城配送未开启');
            }
        }

        $orderGoodsIds = $data['order_goods_id'];
        if (empty($orderGoodsIds)) {
            return error('请选择要发货的商品');
        }

        foreach ((array)$orderGoodsIds as $orderGoodsId) {
            if (!isset($orderGoods[$orderGoodsId])) {
                return error("无效的订单商品 #{$orderGoodsId}");
            }

            if ($orderGoods[$orderGoodsId]['package_id'] > 0) {
                return error("订单商品已经是发货状态 #{$orderGoodsId}");
            }

            if (!empty($orderGoodsInfo[$orderGoodsId]) && $orderGoodsInfo[$orderGoodsId]['refund_type'] != 0 && $orderGoodsInfo[$orderGoodsId]['refund_status'] >= RefundConstant::REFUND_STATUS_APPLY) {
                return error("订单商品 \"{$orderGoods[$orderGoodsId]['title']}\" 正在维权中，不能进行发货操作");
            }
        }
        unset($orderGoodsId);

        // 虚拟订单
        if (GoodsService::checkOrderGoodsVirtualType($orderInfo)) {
            return self::virtualShip($orderInfo['id'], $orderInfo['order_type'], ['transaction' => false,]);
        }

        $noExpress = (int)$data['no_express'];
        $expressId = $data['express_id'];
        $expressSn = $data['express_sn'];
        if (empty($noExpress)) {
            if (empty($expressId)) {
                return error('请选择物流公司');
            }

            $express = CoreExpressModel::getExpressById($expressId);
            if (empty($express)) {
                return error('错误的物流公司');
            }

            if (empty($expressSn)) {
                return error('请填写物流单号');
            }
        }

        $package = [
            'member_id' => $orderInfo['member_id'],
            'order_id' => $orderInfo['id'],
            'order_goods_ids' => implode(',', $orderGoodsIds),
            'remark' => $data['remark'],
            'send_time' => date('Y-m-d H:i:s'),
        ];

        $package['no_express'] = $noExpress;
        $package['express_id'] = (int)$expressId;
        $package['express_sn'] = $expressSn ?: '';
        $package['express_com'] = $express['code'] ?? '';
        $package['express_name'] = $data['express_name'] ?? '';

        // 同城配送
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {
            $package['is_city_distribution'] = 1;//是否是同城配送

            $package['city_distribution_type'] = isset($data['city_distribution_type']) ? intval($data['city_distribution_type']) : 0;
        }

        $data['transaction'] && $tr = OrderModel::getDB()->beginTransaction();

        try {

            //如果是普通订单 则修改订单商品
            if ($orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_ORDINARY) {
                $orderPackage = new OrderPackageModel();
                $orderPackage->attributes = $package;
                if (!$orderPackage->save()) {
                    throw new \Exception('包裹信息保存失败');
                }

                //同步订单商品状态
                $rs = OrderGoodsModel::updateAll(['package_id' => $orderPackage->id, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK], ['id' => $orderGoodsIds]);
                if (!$rs) {
                    throw new \Exception('订单商品信息同步失败');
                }
            }

            //同步订单状态
            $orderUpdate = [];
            //每次发货都会重置发货时间
            $orderUpdate['send_time'] = date('Y-m-d H:i:s');

            //获取发货总数
            $sendCount = OrderGoodsModel::find()->where(['order_id' => $orderInfo['id']])->andWhere(['>', 'package_id', 0])->count();

            if ($sendCount == count($orderInfo['orderGoods'])) {
                //如果全部发送了
                $orderUpdate['status'] = OrderStatusConstant::ORDER_STATUS_WAIT_PICK;

                //预计自动确认收货时间
                $autoReceiveDays = OrderModel::getAutoReceiveDays();
                if (OrderModel::getAutoReceive() && $autoReceiveDays > 0) {
                    $orderUpdate['auto_finish_time'] = date('Y-m-d H:i:s', strtotime("+ {$autoReceiveDays} days", time()));
                }
            } else {
                //已经维权（仅退款）过的订单商品的维权id集合（不包括当前需要发货的订单商品）
                $refundCount = 0;
                foreach ($orderGoods as $id => $goods) {
                    if (!in_array($id, $orderGoodsIds) && $goods['package_id'] == 0) {
                        //如果是仅退款，并且是退款完成的
                        if (in_array($goods['refund_status'], [RefundConstant::REFUND_STATUS_SUCCESS, RefundConstant::REFUND_STATUS_MANUAL]) && $goods['refund_type'] == RefundConstant::TYPE_REFUND) {
                            $refundCount++;
                        }
                    }
                }

                //已经同意退款的订单商品不需要发货。
                if (!empty($refundCount)) {
                    if ($sendCount + $refundCount == count($orderInfo['orderGoods'])) {
                        $orderUpdate['status'] = OrderStatusConstant::ORDER_STATUS_WAIT_PICK;
                    }
                } else {
                    //如果没有维权记录 那么就对比是否全部发货，如果不是全部发货 变成部分发货状态
                    if ($sendCount == count($orderInfo['orderGoods'])) {
                        $orderUpdate['status'] = OrderStatusConstant::ORDER_STATUS_WAIT_PICK;
                    }
                }

                //如果发货个数不等于订单商品个数  并且 发货个数加上退款完成个数不等于订单商品个数 那就是部分发货
                if ($orderUpdate['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
                    $orderUpdate['status'] = OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND;
                }
            }

            if (!empty($orderUpdate)) {
                $rs = OrderModel::updateAll($orderUpdate, ['id' => $orderInfo['id']]);
                if (!$rs) {
                    throw new \Exception('订单信息同步失败');
                }
            }

            $data['transaction'] && $tr->commit();

            // 投递队列自动完成订单
            $shopSetting = ShopSettings::get('sysset.trade');

            //如果是已全部发货 并 开启自动收货 并 自动完成时间不为空
            $orderAutoCloseCondition = $orderUpdate['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_PICK && $shopSetting['auto_receive'] == SyssetTypeConstant::CUSTOMER_AUTO_RECEIVE_TIME && !empty($orderUpdate['auto_finish_time']);

            //核销订单不走这
            $verifyAutoCloseCondition = $orderInfo['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH;

            //多条件判断 请谨慎
            if ($orderAutoCloseCondition || $verifyAutoCloseCondition) {

                //获取剩余秒数
                $delay = strtotime($orderUpdate['auto_finish_time']) - time();

                //判断剩余时间是否大于0
                if ($delay > 0) {
                    QueueHelper::push(new AutoReceiveOrderJob([
                        'orderId' => $orderInfo['id'],
                    ]), $delay);
                }
            }


            $goodsTitle = array_shift($orderGoodsInfo)['title'];
            if (!empty($orderGoodsInfo)) {
                $goodsTitle .= '等';
            }

            //消息通知
            $messageData = [
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                'member_nickname' => $orderInfo['member_nickname'],
                'dispatch_price' => $orderInfo['dispatch_price'],
                'goods_title' => $goodsTitle,
                'buyer_name' => $orderInfo['buyer_name'],
                'buyer_mobile' => $orderInfo['buyer_mobile'],
                'address_info' => $orderInfo['address_state'] . '-' . $orderInfo['address_city'] . '-' . $orderInfo['address_area'] . '-' . $orderInfo['address_detail'],
                'pay_price' => $orderInfo['pay_price'],
                'status' => OrderStatusConstant::getText($orderInfo->status),
                'created_at' => $orderInfo['created_at'],
                'pay_time' => $orderInfo['pay_time'],
                'finish_time' => $orderInfo['finish_time'],
                'send_time' => DateTimeHelper::now(),
                'remark' => $orderInfo['remark'],
                'order_no' => $orderInfo['order_no'],
                'express_no' => $expressSn ?: '',
                'express_name' => $express['name'] ?: '',
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_ORDER_SEND, $messageData);
            if (!is_error($notice)) {
                $notice->sendMessage($orderInfo['member_id']);
            }

        } catch (\Throwable $throwable) {
            $data['transaction'] && $tr->rollBack();
            return error($throwable->getMessage());
        }

        return success();
    }


    /**
     * 虚拟商品发货
     * @param int $orderId
     * @param int $orderType
     * @param array $options
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    private static function virtualShip(int $orderId, int $orderType, array $options = [])
    {
        $options['transaction'] && $tr = OrderModel::getDB()->beginTransaction();

        try {
            //同步订单状态
            $orderUpdate = [
                'send_time' => date('Y-m-d H:i:s'), //每次发货都会重置发货时间
                'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK,
            ];
            //预计自动确认收货时间
            $autoReceiveDays = OrderModel::getAutoReceiveDays();
            if (OrderModel::getAutoReceive() && $autoReceiveDays > 0) {
                $orderUpdate['auto_finish_time'] = date('Y-m-d H:i:s', strtotime("+ {$autoReceiveDays} days", time()));
            }

            if (!empty($orderUpdate)) {
                $rs = OrderModel::updateAll($orderUpdate, ['id' => $orderId]);
                if (!$rs) {
                    throw new \Exception('订单信息同步失败');
                }
                // 同步更新订单商品表的订单状态
                OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK], ['order_id' => $orderId]);
            }
            $options['transaction'] && $tr->commit();

        } catch (\Throwable $throwable) {
            $options['transaction'] && $tr->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }


    /**
     * 取消发货
     * @param int $orderId
     * @param $packageId
     * @param string $reason
     * @return array|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function cancelShip(int $orderId, $packageId, string $reason = '')
    {
        //包裹信息查询
        $package = OrderPackageModel::find()
            ->where(['id' => $packageId])
            ->asArray()
            ->all();

        if (empty($package)) {
            return error('包裹信息不存在');
        }

        foreach ($package as $packageItem) {
            if ($packageItem['finish_time'] > 0) {
                return error('包裹已被收货，不能取消');
            }
        }

        //订单信息
        $order = OrderModel::find()
            ->select([
                'id',
                'status',
                'order_no',
                'is_refund'
            ])
            ->where([
                'id' => $orderId,
            ])
            ->asArray()
            ->one();

        if (!in_array($order['status'], [OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND, OrderStatusConstant::ORDER_STATUS_WAIT_PICK])) {
            return error('订单状态错误');
        }

        //获取是否有维权
        $refund = OrderRefundModel::getValidRefundOrderByOrderId($orderId);
        if ($refund) {
            return error('订单正在维权中，不支持此操作');
        }

        $transaction = OrderModel::getDb()->beginTransaction();
        try {
            //修改订单商品状态
            $result = OrderGoodsModel::updateAll(['package_id' => -1, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND, 'package_cancel_reason' => $reason], [
                'package_id' => $packageId,
                'order_id' => $orderId,
            ]);
            if (empty($result)) {
                throw new \Exception('订单商品状态修改失败');
            }

            foreach ($packageId as $packageIdItem) {
                //删除包裹信息
                $result = OrderPackageModel::deleteAll(['id' => $packageIdItem]);
                if (empty($result)) {
                    throw new \Exception('取消发货失败');
                }
            }

            //该订单下的包裹有发货订单
            $hasPackageGoodsCount = OrderGoodsModel::find()
                ->where(['order_id' => $orderId])
                ->andWhere(['>', 'package_id', 0])
                ->count();
            // 有发货商品
            if (!empty($hasPackageGoodsCount)) {
                //修改订单为未发货状态
                if ($order['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND) {

                    $result = OrderModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND], ['id' => $order['id']]);
                    if (empty($result)) {
                        throw new \Exception('订单状态修改失败');
                    }
                }
            } else {
                // 全部未发货
                //修改订单为未发货状态
                $result = OrderModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND, 'send_time' => '0000-00-00 00:00:00'], ['id' => $order['id']]);
                if (empty($result)) {
                    throw new \Exception('订单状态修改失败');
                }
            }

            $transaction->commit();
            return success();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            return error($throwable->getMessage());
        }
    }


    /**
     * 完成订单
     * @param $orderInfo
     * @param int $type 1实体商品 2虚拟商品
     * @param array $options
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function complete($orderInfo, int $type = 1, array $options = [])
    {
        $options = array_merge([
            'transaction' => true,
            'auto_receive' => false
        ], $options);

        if (empty($orderInfo)) {
            return error('订单不存在');
        }

        //订单修改字段
        $updateOrder = [
            'status' => OrderStatusConstant::ORDER_STATUS_SUCCESS,
            'finish_time' => date('Y-m-d H:i:s')
        ];

        //订单包裹修改字段
        $updatePackage = [];

        //获取是否有维权
        $refund = OrderRefundModel::getValidRefundOrderByOrderId($orderInfo['id']);
        if ($refund) {
            //是否存在正在维权的订单
            $refundGoods = OrderGoodsModel::find()->where([
                'and',
                ['order_id' => $orderInfo['id']],
                ['>', 'refund_type', 0],
                ['between', 'refund_status', RefundConstant::REFUND_STATUS_APPLY, RefundConstant::REFUND_STATUS_WAIT]
            ])->one();

            if (!empty($refundGoods)) {
                return error('订单正在维权中，不支持此操作');
            }
        }

        // 实体商品
        if ($type == 1) {
            //确认收货
            if ($orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
                return error('订单状态错误，无法进行收货确认');
            }

            $updatePackage = ['finish_time' => date('Y-m-d H:i:s')];
        }

        // 判断是是虚拟商品
        if (GoodsService::checkOrderGoodsVirtualType($orderInfo)) {
            // 虚拟订单没有包裹，不更新
            $updatePackage = [];
        }

        $options['transaction'] && $tr = \Yii::$app->db->beginTransaction();
        try {

            if (!is_array($orderInfo['goods_info'])) {
                $orderInfo['goods_info'] = Json::decode($orderInfo['goods_info']);
            }

            if ($orderInfo['order_type'] == 2) {
                // 获取商城商品ID，积分商城要特殊处理，取shop_goods_id字段
                $shopGoodsId = $orderInfo['order_type'] == 3 ? $orderInfo['goods_info'][0]['shop_goods_id'] : $orderInfo['goods_info'][0]['goods_id'];
                $goods = GoodsModel::find()->where(['id' => $shopGoodsId])->select(['auto_delivery', 'auto_delivery_content'])->asArray()->one();
                if ($goods['auto_delivery'] == 1) {
                    $updateOrder['auto_delivery_content'] = $goods['auto_delivery_content'];
                }
            }

            //同步订单状态
            $rs = OrderModel::updateAll($updateOrder, ['id' => $orderInfo['id']]);
            if (!$rs) {
                throw new \Exception('订单状态修改失败');
            }

            //同步订单商品状态
            OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_SUCCESS], [
                'order_id' => $orderInfo['id'],
            ]);

            //同步包裹状态
            if (!empty($updatePackage)) {
                $rs = OrderPackageModel::updateAll($updatePackage, ['order_id' => $orderInfo['id'], 'finish_time' => 0]);
                if (!$rs) {
                    throw new \Exception('包裹信息修改失败');
                }
            }

            $memberLevelUpdateType = ShopSettings::get('member.level.update_type');
            //会员自动升级
            if ($memberLevelUpdateType == 1) {
                MemberLevelService::autoUpLevel($orderInfo['member_id'], $orderInfo['pay_price'], $orderInfo['id']);
            }

            $isCommission = true;

            // 订单参加的活动
            // 订单参加的活动
            if (StringHelper::isJson($orderInfo['extra_price_package'])) {
                $orderInfo['extra_price_package'] = Json::decode($orderInfo['extra_price_package']);
            }
            $orderActivity = array_keys($orderInfo['extra_price_package']);

            if (StringHelper::isJson($orderInfo['extra_discount_rules_package'])) {
                $orderInfo['extra_discount_rules_package'] = Json::decode($orderInfo['extra_discount_rules_package']);
            }
            $discountRules = $orderInfo['extra_discount_rules_package'];

            // 需要检查是否参与分销的活动
            $checkCommissionActivity = [
                'seckill', // 秒杀
            ];

            // 取活动交集
            $activityIntersect = array_intersect($orderActivity, $checkCommissionActivity);

            // 如果有活动  检测活动是否支持分销
            if (!empty($activityIntersect)) {
                // 其他活动
                // 取订单参与的活动
                $activity = array_column($discountRules, $activityIntersect[0]);
                // 不支持
                if ($activity[0]['rules']['is_commission'] == 0) {
                    $isCommission = false;
                }
            }

            // 积分商城不支持分销
            if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                $isCommission = false;
            }

            CommissionService::orderFinish($orderInfo['id'], $orderInfo['member_id']);

            // 消费奖励
            ConsumeRewardLogService::sendReward($orderInfo['member_id'], $orderInfo['id'], 0);

            // 购物奖励
            ShoppingRewardLogModel::sendReward($orderInfo['member_id'], $orderInfo['id'], 1);

            $options['transaction'] && $tr->commit();

            // 订单自动评价
            $tradeSet = ShopSettings::get('sysset.trade');
            if ($tradeSet['auto_comment'] == 1) {
                $delay = $tradeSet['auto_comment'] * 24 * 60 * 60;
                QueueHelper::push(new AutoCommentJob([
                    'orderId' => $orderInfo['id'],
                    'memberId' => $orderInfo['member_id'],
                    'content' => $tradeSet['auto_comment_content'],
                ]), $delay);
            }

            // 是否开启送积分
            $creditSet = ShopSettings::get('sysset.credit');
            if ($creditSet['give_credit_status'] == 1 && $orderInfo['activity_type'] != OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                // 计算时间
                $delay = $creditSet['give_credit_settle_day'] * 86400;
                QueueHelper::push(new GiveCreditJob([
                    'orderId' => $orderInfo['id'],
                    'memberId' => $orderInfo['member_id'],
                ]), $delay);
            }


            //消息通知
            $messageData = [
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                'member_nickname' => $orderInfo['member_nickname'],
                'dispatch_price' => $orderInfo['dispatch_price'],
                'goods_title' => $orderInfo['goods_info'][0]['title'] ?: '' . (count($orderInfo['goods_info']) > 1 ? '' : '等'),
                'buyer_name' => $orderInfo['buyer_name'],
                'buyer_mobile' => $orderInfo['buyer_mobile'],
                'address_info' => $orderInfo['address_state'] . '-' . $orderInfo['address_city'] . '-' . $orderInfo['address_area'] . '-' . $orderInfo['address_detail'],
                'pay_price' => $orderInfo['pay_price'],
                'status' => OrderStatusConstant::getText($orderInfo['status']),
                'created_at' => $orderInfo['created_at'],
                'pay_time' => $orderInfo['pay_time'],
                'finish_time' => $updateOrder['finish_time'],
                'send_time' => $orderInfo['send_time'],
                'remark' => $orderInfo['remark'],
                'order_no' => $orderInfo['order_no'],
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::SELLER_ORDER_RECEIVE, $messageData, '');
            if (!is_error($notice)) {
                $notice->sendMessage();
            }

            // 打印小票
            QueueHelper::push(new AutoPrinterOrder([
                'job' => [
                    'scene' => PrinterSceneConstant::PRINTER_CONFIRM_RECEIPT,
                    'order_id' => $orderInfo['id']
                ]
            ]));


        } catch (\Throwable $throwable) {
            $options['transaction'] && $tr->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }


    /**
     * 支付完成后
     * @param OrderPaySuccessStruct $orderPaySuccessStruct
     * @return array|bool
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function paySuccess(OrderPaySuccessStruct $orderPaySuccessStruct)
    {
        $order = OrderModel::findOne([
            'id' => $orderPaySuccessStruct->orderId,
            'member_id' => $orderPaySuccessStruct->accountId,
        ]);
        if (empty($order)) {
            return error('订单不存在');
        }

        // 不是待支付状态
        if ($order->status != OrderStatusConstant::ORDER_STATUS_WAIT_PAY) {
            if ($order->status == OrderStatusConstant::ORDER_STATUS_CLOSE) {  //如果等于已关闭

                $config = [
                    "member_id" => $order['member_id'],
                    "order_id" => $order['id'],
                    "order_no" => $order['order_no'],
                    "refund_fee" => $order['pay_price'],
                    "client_type" => $order['create_from'],
                    "pay_type" => $orderPaySuccessStruct->payType,
                    "pay_price" => $order['pay_price'],
                    "order_type" => PayOrderTypeConstant::ORDER_TYPE_ORDER,
                    "refund_desc" => "关闭订单退款"
                ];

                // 退款记录数据
                $refundLogData = [
                    'member_id' => $order['member_id'],
                    'money' => $order['pay_price'],
                    'order_id' => $order['id'],
                    'order_no' => $order['order_no'],
                    'type' => RefundLogConstant::TYPE_ORDER_STATUS_EXCEPTION
                ];

                //订单退款
                $result = self::refund($config, $refundLogData);
                if (is_error($result)) {
                    LogHelper::error('[ORDER PASSIVE REFUND ERROR]', [
                        'message' => $result['message']
                    ]);
                    return error('订单被动退款失败:' . $result['message']);
                }
            }

            return error('订单状态不正确');
        }

        // 修改order状态
        $order->status = OrderStatusConstant::ORDER_STATUS_WAIT_SEND;
        $payTime = DateTimeHelper::now();
        $order->pay_time = $payTime;
        $order->pay_type = $orderPaySuccessStruct->payType;
        $order->trade_no = $orderPaySuccessStruct->tradeNo ?: '';
        $order->out_trade_no = $orderPaySuccessStruct->outTradeNo ?: '';
        if (!$order->save()) {
            return error('订单状态修改失败');
        }

        $result = OrderGoodsModel::updateAll([
            'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND,
            'pay_time' => $payTime
        ], [
            'order_id' => $orderPaySuccessStruct->orderId,
            'member_id' => $orderPaySuccessStruct->accountId,
        ]);

        if (is_error($result)) {
            return error('订单商品状态修改失败');
        }

        $goodsInfo = $order->goods_info;
        if (empty($goodsInfo)) {
            return error('订单商品不存在');
        }
        $goodsInfo = Json::decode($goodsInfo);

        // 修改虚拟卡密数据表状态
        if ($order->order_type == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT) {
            $orderVirtualAccountData = VirtualAccountOrderMapModel::getMapList($orderPaySuccessStruct->orderId);
            if ($orderVirtualAccountData) {
                VirtualAccountDataModel::updateStatus($orderVirtualAccountData, VirtualAccountDataConstant::ORDER_VIRTUAL_ACCOUNT_DATA_SUCCESS);
            }
        }

        // 升级
        $memberLevelUpdateType = ShopSettings::get('member.level.update_type');
        //会员自动升级
        if ($memberLevelUpdateType == 2) {
            MemberLevelService::autoUpLevel($orderPaySuccessStruct->accountId, $order->pay_price, $orderPaySuccessStruct->orderId);
        }

        // 消费奖励
        ConsumeRewardLogService::sendReward($orderPaySuccessStruct->accountId, $orderPaySuccessStruct->orderId, 1);

        // 购物奖励
        ShoppingRewardLogModel::sendReward($orderPaySuccessStruct->accountId, $orderPaySuccessStruct->orderId, 0);

        $messageData = [
            'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
            'member_nickname' => $order->member_nickname,
            'dispatch_price' => $order->dispatch_price,
            'goods_title' => $goodsInfo[0]['title'] ?: '' . (count($goodsInfo) > 1 ? '' : '等'),
            'goods_detail' => $goodsInfo[0]['sub_name'] ? ($goodsInfo[0]['sub_name'] . (count($goodsInfo) > 1 ? '' : '等')) : '',
            'buyer_name' => $order->buyer_name,
            'buyer_mobile' => $order->buyer_mobile,
            'address_info' => $order->address_state . '-' . $order->address_city . '-' . $order->address_area . '-' . $order->address_detail,
            'pay_price' => $order->pay_price,
            'status' => OrderStatusConstant::getText($order->status),
            'created_at' => $order->created_at,
            'pay_time' => $order->pay_time,
            'remark' => $order->remark,
            'order_no' => $order->order_no,
            'member_balance' => MemberModel::getBalance($order->member_id),
        ];

        //消息通知
        $notice = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_ORDER_PAY, $messageData);
        if (!is_error($notice)) {
            $notice->sendMessage($order->member_id);
        }

        //消息通知
        $notice = NoticeComponent::getInstance(NoticeTypeConstant::SELLER_ORDER_PAY, $messageData, '');
        if (!is_error($notice)) {
            $notice->sendMessage();
        }

        // 虚拟卡密发送邮件
        if ($order->order_type == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT) {
            VirtualAccountDataModel::sendMailer($orderPaySuccessStruct->orderId);
        }

        // 打印小票
        QueueHelper::push(new AutoPrinterOrder([
            'job' => [
                'scene' => PrinterSceneConstant::PRINTER_PAY,
                'order_id' => $orderPaySuccessStruct->orderId
            ]
        ]));

        // 分销

        $isCommission = true;
        // 订单参加的活动
        $orderActivity = array_keys(Json::decode($order['extra_price_package']));
        $discountRules = Json::decode($order['extra_discount_rules_package']);
        // 需要检查是否参与分销的活动
        $checkCommissionActivity = [
            'presell', // 预售
            'seckill', // 秒杀
            'groups',//拼团
            'full_reduce', // 满减折
        ];

        // 取活动交集
        $activityIntersect = array_intersect($orderActivity, $checkCommissionActivity);

        // 如果有活动  检测活动是否支持分销
        if (!empty($activityIntersect)) {
            // 如果包含预售
            if (in_array('presell', $activityIntersect)) {
                // 不支持
                $discountRules[0]['presell']['is_commission'] == 0 && $isCommission = false;
            } else {
                // 其他活动
                // 取订单参与的活动
                $activity = array_column($discountRules, $activityIntersect[0]);
                // 不支持
                if ($activity[0]['rules']['is_commission'] == 0) {
                    $isCommission = false;
                }
            }
        }

        // 积分商城不支持分销
        if ($order->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            $isCommission = false;
        }

        // 支持分销 且有权限
        if ($isCommission) {
            CommissionService::orderPay($orderPaySuccessStruct->orderId, $orderPaySuccessStruct->accountId);
        }

        // 不是核销的判断是否自动发货
        // 判断虚拟商品不走这里
        if (GoodsService::checkOrderGoodsVirtualType($order)) {
            $virtualRes = self::ship($order, [
                'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                'no_express' => 1, // 不需要快递
                'transaction' => false,
            ]);
            if ($goodsInfo[0]['auto_deliver'] == GoodsVirtualConstant::GOODS_VIRTUAL_AUTO_DELIVERY) {
                // 虚拟商品&&自动发货 自动完成
                if (is_error($virtualRes)) {
                    return error($virtualRes['message']);
                }
                $virtualRes = self::complete($order, 2);
            }

            if (is_error($virtualRes)) {
                return error($virtualRes['message']);
            }
        }

        //减库存
        $result = GoodsService::updateQty(true, $orderPaySuccessStruct->orderId, [], GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT, [
            'transaction' => false,
            'reason' => '付款减库存',
        ]);
        if (is_error($result)) {
            return error($result['message']);
        }

        return true;
    }

    /**
     * 虚拟自动发货
     * @param int $orderId
     * @return bool | array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public static function virtualAutoSend(int $orderId)
    {
        //查询订单
        $order = OrderModel::findOne(['id' => $orderId]);
        if (empty($order)) {
            return error('订单未找到');
        }

        //解析商品
        $goodsInfo = Json::decode($order->goods_info);

        if ($goodsInfo[0]['auto_deliver'] == GoodsVirtualConstant::GOODS_VIRTUAL_AUTO_DELIVERY) {
            // 虚拟商品&&自动发货 自动完成
            $virtualRes = self::ship($order, [
                'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                'no_express' => 1 // 不需要快递
            ]);
            if (is_error($virtualRes)) {
                return error($virtualRes['message']);
            }
            $virtualRes = self::complete($order, 2);
        } else {
            // 虚拟商品&&不自动发货
            $virtualRes = self::ship($order, [
                'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                'no_express' => 1 // 不需要快递
            ]);
        }

        if (is_error($virtualRes)) {
            return error($virtualRes['message']);
        }

        return true;
    }


    /**
     * 关闭订单并退款
     * @param int $operator
     * @param $orderInfo
     * @param array $options
     * @return array|bool
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeAndRefund($orderInfo, $operator = 0, $options = [])
    {

        $options = array_merge([
            'transaction' => true,
            'refund_reason' => ''
        ], $options);

        if (is_numeric($orderInfo)) {
            $orderInfo = OrderModel::find()->where(['id' => $orderInfo])->asArray()->one();
        }

        if (empty($orderInfo)) {
            return error('订单不存在');
        }

        // 核销的 待收货 货到付款的
        if ($orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_SEND
            && ($orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK && $orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH && $orderInfo['pay_type'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY)) {
            return error('订单状态错误，无法进行关闭操作');
        }

        // 不是核销
        if ($orderInfo['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
            $orderPackages = OrderModel::getPackages($orderInfo['id'], 0, 0, $orderInfo);
            // 如果是待发货的货到付款 就不进行判断
            if ($orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK && $orderInfo['pay_type'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY) {
                if ($orderInfo['send_time'] != 0 || !empty($orderPackages)) {
                    return error('订单中的部分商品已经发货，无法进行关闭操作');
                }
            }
        }

        //获取是否有维权
        $refund = OrderRefundModel::getValidRefundOrderByOrderId($orderInfo['id']);
        if ($refund) {
            return error('此订单有过维权记录，不支持此操作');
        }


        $options['transaction'] && $transaction = OrderModel::getDB()->beginTransaction();
        try {

            //修改订单状态
            $update = [
                'status' => OrderStatusConstant::ORDER_STATUS_CLOSE,
                'cancel_time' => date('Y-m-d H:i:s'),
                'close_type' => empty($operator) ? OrderConstant::ORDER_CLOSE_TYPE_SELLER_CLOSE : $operator
            ];

            // 退款原因
            isset($options['refund_reason']) && !empty($options['refund_reason']) && $update['refund_reason'] = $options['refund_reason'];
            $result = OrderModel::updateAll($update, ['id' => $orderInfo['id']]);
            if (empty($result)) {
                throw new \Exception('订单状态更新失败');
            }

            //关闭订单商品
            $result = OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_CLOSE], ['order_id' => $orderInfo['id']]);
            if (empty($result)) {
                throw new \Exception('订单商品状态更新失败');
            }

            //返还营销
            self::returnActivity($orderInfo['id'], $operator, '手动退款返还');

            //返还库存
            $result = GoodsService::updateQty(false, $orderInfo['id'], [], GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT, [
                'transaction' => false,
                'reason' => '退款返还库存',
            ]);

            if (is_error($result)) {
                return error($result['message']);
            }

            StringHelper::isJson($orderInfo['goods_info']) && $orderInfo['goods_info'] = Json::decode($orderInfo['goods_info']);

            // 退款记录数据
            $refundLogData = [
                'member_id' => $orderInfo['member_id'],
                'money' => $orderInfo['pay_price'],
                'order_id' => $orderInfo['id'],
                'order_no' => $orderInfo['order_no']
            ];

            // 商家退尾款 预售类型
            if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL) {

                $refundLogData['type'] = RefundLogConstant::TYPE_PRESELL_ORDER_SELLER_FINAL_REFUND;
            } elseif ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS) { //拼团退款

                $refundLogData['type'] = RefundLogConstant::TYPE_GROUPS_REFUND;

            } else {

                // 商家正常退款
                $refundLogData['type'] = RefundLogConstant::TYPE_ORDER_SELLER_REFUND;
            }

            $config = [
                "member_id" => $orderInfo['member_id'],
                "order_id" => $orderInfo['id'],
                "order_no" => $orderInfo['order_no'],
                "refund_fee" => $orderInfo['pay_price'],
                "client_type" => $orderInfo['create_from'],
                "pay_type" => $orderInfo['pay_type'],
                "pay_price" => $orderInfo['pay_price'],
                "order_type" => PayOrderTypeConstant::ORDER_TYPE_ORDER,
                "refund_desc" => "关闭订单退款"
            ];

            //后台确认付款 不产生退款记录
            if ($orderInfo['pay_type'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM) {
                $result = self::refund($config, $refundLogData);
                if (is_error($result)) {
                    throw new \Exception($result['message']);
                }
            }

            // 修改分销订单状态
            CommissionOrderService::updateRefundStatus($orderInfo['member_id'], $orderInfo['id']);

            $options['transaction'] && $transaction->commit();

            //消息通知
            $messageData = [
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                'member_nickname' => $orderInfo['member_nickname'],
                'dispatch_price' => $orderInfo['dispatch_price'],
                'goods_title' => ($orderInfo['goods_info'][0]['title'] ? $orderInfo['goods_info'][0]['title'] : '') . (count($orderInfo['goods_info']) > 1 ? '等' : ''),
                'buyer_name' => $orderInfo['buyer_name'],
                'buyer_mobile' => $orderInfo['buyer_mobile'],
                'address_info' => $orderInfo['address_state'] . '-' . $orderInfo['address_city'] . '-' . $orderInfo['address_area'] . '-' . $orderInfo['address_detail'],
                'pay_price' => $orderInfo['pay_price'],
                'status' => OrderStatusConstant::getText($orderInfo->status),
                'created_at' => $orderInfo['created_at'],
                'pay_time' => $orderInfo['pay_time'],
                'finish_time' => $orderInfo['finish_time'],
                'send_time' => $orderInfo['send_time'],
                'remark' => $orderInfo['remark'],
                'order_no' => $orderInfo['order_no'],
                'refund_price' => $orderInfo['pay_price'],
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_ORDER_CANCEL_AND_REFUND, $messageData);

            if (!is_error($notice)) {
                $notice->sendMessage($orderInfo['member_id']);
            }

        } catch (\Throwable $throwable) {
            $options['transaction'] && $transaction->rollBack();
            LogHelper::error('[ORDER REFUND ERROR]:', [$throwable->getMessage(), $throwable->getFile(), $throwable->getLine()]);
            return error($throwable->getMessage());
        }

        return true;
    }


    /**
     * 退款
     * @param array $config
     * @param array $refundLogData
     * @return array|bool
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司.
     */
    public static function refund(array $config, array $refundLogData)
    {
        try {
            TradeOrderService::operation([
                'orderNo' => $config['order_no']  // 请使用订单编号
            ])->refund($config['refund_fee'], $config['refund_desc']);
        } catch (\Exception $exception) {
            // 兼容旧版支付
            if ($exception->getCode() == '108130') {
                $payDriver = PayComponent::getInstance($config);
                $result = $payDriver->refund();
                if (is_error($result)) {
                    $refundLogData['status'] = 0;
                    $refundLogData['remark'] = $result['message'];

                    // 写入退款记录
                    RefundLogModel::writeLog($refundLogData);
                    return error($result['message']);
                }
            } else {
                $refundLogData['status'] = 0;
                $refundLogData['remark'] = $exception->getMessage();

                // 写入退款记录
                RefundLogModel::writeLog($refundLogData);
                return error($exception->getMessage());
            }
        }

        $refundLogData['status'] = 1;

        // 写入退款记录
        return RefundLogModel::writeLog($refundLogData);
    }


    /**
     * 返还活动
     * @param int $orderId
     * @param int $operator
     * @param string $reason
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function returnActivity(int $orderId, int $operator = 0, $reason = '')
    {
        $order = OrderModel::findOne(['id' => $orderId]);
        if (empty($order)) {
            return false;
        }

        $extraDiscountRulesPackage = Json::decode($order->extra_discount_rules_package);
        if (empty($extraDiscountRulesPackage)) {
            return false;
        }

        //格式化
        $extraDiscountRulesPackageNew = [];
        foreach ($extraDiscountRulesPackage as $extraDiscountRulesPackageIndex => $extraDiscountRulesPackageItem) {
            foreach ($extraDiscountRulesPackageItem as $extraDiscountRulesPackageItemIndex => $extraDiscountRulesPackageItemItem) {
                if ($extraDiscountRulesPackageItemIndex == 'credit' || $extraDiscountRulesPackageItemIndex == 'platform_credit') {
                    $extraDiscountRulesPackageNew['credit'] += $extraDiscountRulesPackageItemItem['credit'];
                } elseif ($extraDiscountRulesPackageItemIndex == 'balance' || $extraDiscountRulesPackageItemIndex == 'platform_balance') {
                    $extraDiscountRulesPackageNew['balance'] += $extraDiscountRulesPackageItemItem['price'];
                } elseif ($extraDiscountRulesPackageItemIndex == 'coupon' || $extraDiscountRulesPackageItemIndex == 'platform_coupon') {
                    $extraDiscountRulesPackageNew['coupon'][] = $extraDiscountRulesPackageItemItem['id'];
                } else if ($extraDiscountRulesPackageItemIndex == 'gift_card') {
                    $extraDiscountRulesPackageNew['gift_card'] = $extraDiscountRulesPackageItemItem;
                }
            }
        }

        // 返还优惠券
        if (!empty($extraDiscountRulesPackageNew['coupon'])) {
            // 只有一张优惠券
            CouponMemberService::returnCoupon($extraDiscountRulesPackageNew['coupon'][0], $order->member_id, $orderId);
        }


        //返还余额抵扣
        if (!empty($extraDiscountRulesPackageNew['balance'])) {
            MemberModel::updateCredit($order->member_id, round2($extraDiscountRulesPackageNew['balance']), $operator, 'balance', 1, $reason, MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND, [
                'order_id' => $orderId
            ]);
        }

        //返还积分抵扣
        if (!empty($extraDiscountRulesPackageNew['credit'])) {
            MemberModel::updateCredit($order->member_id, round2($extraDiscountRulesPackageNew['credit']), $operator, 'credit', 1, $reason, MemberCreditRecordStatusConstant::CREDIT_STATUS_REFUND, [
                'order_id' => $orderId
            ]);
        }

        return true;
    }


}