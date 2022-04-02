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

namespace shopstar\services\order\refund;

use shopstar\bases\service\BaseService;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\order\OrderService;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class OrderRefundService extends BaseService
{

    /**
     * 检查订单是否可以维权
     * @param OrderModel $order
     * @param OrderGoodsModel|null $orderGoods
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkRefund(OrderModel $order, OrderGoodsModel $orderGoods = null)
    {
        if ($order->status <= OrderStatusConstant::ORDER_STATUS_WAIT_PAY) {
            return error("订单未支付或已关闭");
        }
        // 订单有维权
        if ($order->is_refund == 1) {
            // 整单维权过
            if ($order->refund_type == 1) {
                $refund = OrderRefundModel::getRefundByOrder($order->id);
            } else if ($orderGoods->is_single_refund == 1) {
                // 单品维权过
                $refund = OrderRefundModel::getRefundByOrder($order->id, $orderGoods->id);
            }
            if (!empty($refund)) {
                // 如有错误直接返回
                if (is_error($refund)) {
                    return $refund;
                }
                //  维权已完成
                if ($refund->status == RefundConstant::REFUND_STATUS_SUCCESS || $refund->status == RefundConstant::REFUND_STATUS_MANUAL) {
                    return error('维权已完成');
                }
            }
        }

        // 如果订单已完成 且 (订单没维权状态 或者 拒绝过)
        if ($order->status == OrderStatusConstant::ORDER_STATUS_SUCCESS
            && (empty($refund) || (!empty($refund) && $refund->status == RefundConstant::REFUND_STATUS_REJECT))) {

            // 维权设置
            $set = ShopSettings::get('sysset.refund');
            if (!empty($orderGoods) && $set['single_refund_enable'] == 0) {
                return error('未开启单品维权');
            }

            // 积分商城走自己的设置
            if ($order->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                // 获取积分商城设置
                $creditSet = ShopSettings::get('plugin_credit_shop');
                // 走系统默认
                if ($creditSet['refund_type'] == 0) {
                    // 设置了维权天数
                    if (!empty($set) && $set['apply_type'] == 2) {
                        // 超过设置天数
                        if (date('Y-m-d H:i:s', strtotime('-' . $set['apply_days'] . ' day')) > $order->finish_time) {
                            return error('订单完成已超过' . $set['apply_days'] . '天，无法发起退款申请');
                        }
                    } else {
                        return error('订单已完成，不能申请售后');
                    }
                } else {
                    // 已完成的允许售后
                    if ($creditSet['finish_order_refund_type'] == 1) {
                        // 超过设置天数
                        if (date('Y-m-d H:i:s', strtotime('-' . $creditSet['finish_order_refund_days'] . ' day')) > $order->finish_time) {
                            return error('订单完成已超过' . $set['finish_order_refund_days'] . '天，无法发起退款申请');
                        }
                    } else {
                        return error('订单已完成，不能申请售后');
                    }
                }
            } else {
                // 设置了维权天数
                if (!empty($set) && $set['apply_type'] == 2) {
                    // 超过设置天数
                    if (date('Y-m-d H:i:s', strtotime('-' . $set['apply_days'] . ' day')) > $order->finish_time) {
                        return error('订单完成已超过' . $set['apply_days'] . '天，无法发起退款申请');
                    }
                } else {
                    return error('订单已完成，不能申请售后');
                }
            }
        }
    }

    /**
     * 换货完成  完成订单
     * @param int $orderId
     * @param int $orderGoodsId
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function successOrder(int $orderId, int $orderGoodsId = 0)
    {
        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId);
        // 整单换货维权完成  订单完成
        if (empty($orderGoodsId)) {
            OrderService::complete($orderInfo, 1, ['transaction' => false]);
        } else {
            // 单商品维权  只通过
            OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_SUCCESS], ['id' => $orderGoodsId, 'order_id' => $orderId]);

            // 查找其他订单
            $otherOrderGoods = OrderGoodsModel::find()
                ->select('status')
                ->where(['order_id' => $orderId])
                ->andWhere(['<>', 'id', $orderGoodsId])
                ->get();

            // 遍历其他订单商品 都为已收货 或已关闭 则整单完成
            $refundSuccess = true;
            foreach ($otherOrderGoods as $item) {
                if ($item['status'] != OrderStatusConstant::ORDER_STATUS_CLOSE && $item['status'] != OrderStatusConstant::ORDER_STATUS_SUCCESS) {
                    $refundSuccess = false;
                    break;
                }
            }
            // 可以完成订单
            if ($refundSuccess) {
                OrderService::complete($orderInfo, 1, ['transaction' => false]);
            }
        }

        return true;
    }

    /**
     * 获取可以维权的类型
     * 整单维权
     * 退款/退货退款/退货
     * @param OrderModel $order
     * @param int $orderGoodsId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCanRefundType(OrderModel $order, int $orderGoodsId = 0): array
    {
        $data = [
            'refund' => true,
            'return' => true,
            'exchange' => true
        ];


        $goodsInfo = Json::decode($order->goods_info);

        // 判断维权类型
        foreach ($goodsInfo as $item) {
            if (!empty($orderGoodsId) && $item['order_goods_id'] != $orderGoodsId) {
                continue;
            } elseif (empty($item['ext_field'])) {
                continue;
            }

            // 解析(如果已经是数组了就不处理了)
            if (!is_array($item['ext_field'])) {
                $item['ext_field'] = Json::decode($item['ext_field']);
            }

            // 退款
            if ($item['ext_field']['refund'] == 0) {
                $data['refund'] = false;
            }
            // 退货退款 商品未设置
            if ($item['ext_field']['return'] == 0) {
                $data['return'] = false;
            }
            // 换货 商品未设置
            if ($item['ext_field']['exchange'] == 0) {
                $data['exchange'] = false;
            }
        }

        // 获取商品
        // 先查找订单商品表
        $orderGoodsQuery = OrderGoodsModel::find()->where(['order_id' => $order->id]);
        // 如果是单品维权  只查找维权的商品
        if (!empty($orderGoodsId)) {
            $orderGoodsQuery->andWhere(['id' => $orderGoodsId]);
        }
        $orderGoods = $orderGoodsQuery->get();

        // 判断订单
        foreach ($orderGoods as $item) {
            // 订单状态为待发货
            if ($item['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
                $data['return'] = false;
            }
            // 订单状态为待发货
            if ($item['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
                $data['exchange'] = false;
            }
        }
        // 核销的支持支退款
        if ($order->dispatch_type == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
            $data['exchange'] = false;
            $data['return'] = false;
        }

        if (!$data['refund'] && !$data['return'] && !$data['exchange']) {
            return error('该订单不允许维权');
        }
        return $data;
    }

    /**
     * 计算最多可维权的金额
     * @param OrderModel $order
     * @param OrderGoodsModel|null $orderGoods
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculatePrice(OrderModel $order, OrderGoodsModel $orderGoods = null): array
    {
        $data = [];
        // 整单维权
        if (empty($orderGoods)) {
            // 是否可编辑价格
            // 核销的未完成之前都不可以 其他的未发货之前不可以
            if (($order->dispatch_type == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH && $order->status == OrderStatusConstant::ORDER_STATUS_WAIT_PICK)
                || ($order->dispatch_type != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH && $order->status == OrderStatusConstant::ORDER_STATUS_WAIT_SEND)) {
                $data['is_can_edit'] = 0;
            } else {
                $data['is_can_edit'] = 1;
            }

            $data['is_contain_dispatch'] = 1;
            // 整单余额抵扣信息
            $deductInfo = Json::decode($order->extra_price_package);
            if (isset($deductInfo['balance']) && $deductInfo['balance'] != 0) {
                $data['balance_deduct'] = $deductInfo['balance'];
            }

            // 可维权价格
            $data['price'] = bcadd($order->pay_price, $data['balance_deduct'], 2);
            // 如果有礼品卡
            if ($deductInfo['gift_card'] != 0) {
                $data['gift_card_deduct'] = $deductInfo['gift_card'];
                $data['price'] = bcadd($data['price'], $data['gift_card_deduct'], 2);
            }

            // 含运费
            $data['dispatch_price'] = $order->dispatch_price;
            // 已发货的减去运费  （包括部分发货
            if ($order->status > OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
                $data['price'] = bcsub($data['price'], $data['dispatch_price'], 2);
                $data['dispatch_price'] = 0;
            }
        } else {
            // 单品维权
            // 是否可编辑价格
            if (($order->dispatch_type == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH && $orderGoods->status == OrderStatusConstant::ORDER_STATUS_WAIT_PICK)
                || ($order->dispatch_type != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH && $orderGoods->status == OrderStatusConstant::ORDER_STATUS_WAIT_SEND)) {
                $data['is_can_edit'] = 0;
            } else {
                $data['is_can_edit'] = 1;
            }

            // 余额抵扣
            $deductInfo = Json::decode($orderGoods->activity_package);
            if (!empty($deductInfo['balance']['price'])) {
                $data['balance_deduct'] = $deductInfo['balance']['price'];
            }
            // 可维权价格 ($orderGoods->price 不包含运费)
            $data['price'] = bcadd($orderGoods->price, $data['balance_deduct'], 2);

            // 礼品卡
            if (!empty($deductInfo['gift_card']['price'])) {
                $data['gift_card_deduct'] = $deductInfo['gift_card']['price'];
                $data['price'] = bcadd($data['price'], $data['gift_card_deduct'], 2);
            }

            // 运费信息
            $data['dispatch_price'] = 0;

            // 已发货不退运费（包含部分发货
            // 待发货最后一件退运费（满足条件
            if ($order->status == OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
                // 未发货状态 判断同订单下其他商品
                // 如果本次维权商品是最后一件维权商品 且 （其他商品仅退款同意 或 没有其他商品） 则退运费  否则不退运费
                // 退的运费是整单的运费
                // 获取订单下其他商品
                $otherOrderGoods = OrderGoodsModel::find()
                    ->where(['and', ['order_id' => $order->id], ['<>', 'id', $orderGoods->id]])
                    ->get();
                // 如果为空  说明就一个商品  维权退运费
                if (empty($otherOrderGoods)) {
                    $data['price'] = bcadd($order->dispatch_price, $data['price'], 2);
                    $data['dispatch_price'] = $order->dispatch_price;
                } else {
                    // 临时字段 其他订单是否有维权完成 默认是  碰到不是的设为false
                    $refundSuccess = true;
                    foreach ($otherOrderGoods as $item) {
                        if ($item['refund_status'] < RefundConstant::REFUND_STATUS_SUCCESS) {
                            $refundSuccess = false;
                            break;
                        }
                    }
                    // 如果还是true 说明其他都维权完成  可退运费
                    if ($refundSuccess) {
                        // 先减去自己的  再加上整单的
                        $data['price'] = bcadd($order->dispatch_price, $data['price'], 2);
                        $data['dispatch_price'] = $order->dispatch_price;
                    }
                }
            }
        }

        // 积分商城订单
        if ($order->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            // 积分商城的 加上积分字段  单品和整单没区别
            // 判断设置 是否退积分
            $creditShopSetting = ShopSettings::get('plugin_credit_shop');
            if ($creditShopSetting['refund_rule'] == 0) {
                $creditShopOrder = CreditShopOrderModel::find()->select(['order_id', 'pay_credit'])->where(['order_id' => $order->id])->first();
                $data['credit'] = $creditShopOrder['pay_credit'];
            }
            // 不允许编辑
            $data['is_can_edit'] = 0;
        }

        return $data;
    }

    /**
     * 取消维权
     * @param int $orderId 订单id
     * @param int $orderGoodsId 订单商品id 单品维权有
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function cancelRefund(int $orderId, int $orderGoodsId = 0): array
    {
        $refund = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);

        if (is_error($refund)) {
            return error('维权信息不存在');
        }
        // 整单维权 可能存在把单品维权信息查出来的问题
        if (empty($orderGoodsId) && !empty($refund->order_goods_id)) {
            return error('该订单为单品维权订单');
        }
        // 换货 且 等待完成状态不能取消售后   拒绝维权    维权完成
        if (($refund->refund_type == RefundConstant::TYPE_EXCHANGE && $refund->status == RefundConstant::REFUND_STATUS_WAIT)
            || $refund->status == RefundConstant::REFUND_STATUS_SUCCESS
            || $refund->status == RefundConstant::REFUND_STATUS_REJECT
            || $refund->status == RefundConstant::REFUND_STATUS_MANUAL) {

            return error('该状态下不允许取消维权');
        }
        $refund->status = RefundConstant::REFUND_STATUS_CANCEL;
        $refund->finish_time = DateTimeHelper::now();
        $refund->is_history = 1;

        if ($refund->save() === false) {
            return error('取消维权失败');
        }

        // 整单维权取消
        if (empty($orderGoodsId)) {
            // 更新订单表
            OrderModel::updateAll(
                ['is_refund' => 0, 'refund_type' => 0],
                ['id' => $orderId,]
            );
            // 更新订单商品表状态
            OrderGoodsModel::updateAll(
                ['refund_status' => RefundConstant::REFUND_STATUS_CANCEL, 'refund_type' => 0],
                ['order_id' => $orderId,]
            );


        } else {
            // 其他的都取消后 再改订单表状态
            // 获取订单维权信息
            $orderRefund = OrderRefundModel::find()
                ->where(['order_id' => $orderId,])
                ->andWhere(['<>', 'status', RefundConstant::REFUND_STATUS_CANCEL])
                ->andWhere(['is_history' => 0])
                ->get();
            // 如果为空 则修改订单表
            if (empty($orderRefund)) {
                OrderModel::updateAll(
                    ['is_refund' => 0, 'refund_type' => 0],
                    ['id' => $orderId,]
                );

            }
            // 单品维权取消
            OrderGoodsModel::updateAll(
                ['refund_status' => RefundConstant::REFUND_STATUS_CANCEL, 'refund_type' => 0, 'is_single_refund' => 0],
                ['order_id' => $orderId, 'id' => $orderGoodsId]
            );
        }
    }


    /**
     * 关闭订单
     * @param OrderModel $order
     * @param OrderGoodsModel|null $orderGoods
     * @param OrderRefundModel|null $refund
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeOrder(OrderModel $order, OrderGoodsModel $orderGoods = null, OrderRefundModel $refund = null)
    {
        // 整单维权 直接关闭
        if (empty($orderGoods)) {
            $order->status = OrderStatusConstant::ORDER_STATUS_CLOSE;
            $order->close_type = OrderConstant::ORDER_CLOSE_TYPE_REFUND_SUCCESS_CLOSE;
            $order->finish_time = DateTimeHelper::now();

            if ($order->save() === false) {
                return error('订单关闭失败');
            }
            OrderGoodsModel::updateAll(['status' => OrderStatusConstant::ORDER_STATUS_CLOSE], ['order_id' => $order->id]);

            return true;
        } else {
            // 单品维权  判断其他商品是否关闭  全部关闭则整单关闭
            $orderGoods->status = OrderStatusConstant::ORDER_STATUS_CLOSE;
            if ($orderGoods->save() === false) {
                return error('订单关闭失败');
            }

            $otherOrderGoods = OrderGoodsModel::find()
                ->select('status')
                ->where(['order_id' => $order->id])
                ->andWhere(['<>', 'status', OrderStatusConstant::ORDER_STATUS_CLOSE])
                ->andWhere(['<>', 'id', $orderGoods->id])
                ->get();

            // 遍历其他订单商品 如果存在未关闭的  订单不关闭
            $refundSuccessClose = true;
            foreach ($otherOrderGoods as $item) {
                if ($item['status'] != OrderStatusConstant::ORDER_STATUS_CLOSE) {
                    $refundSuccessClose = false;
                    break;
                }
            }

            // 可以关闭订单
            if ($refundSuccessClose) {
                $order->status = OrderStatusConstant::ORDER_STATUS_CLOSE;
                $order->close_type = OrderConstant::ORDER_CLOSE_TYPE_REFUND_SUCCESS_CLOSE;
                $order->finish_time = DateTimeHelper::now();
                if ($order->save() === false) {
                    return error('订单关闭失败');
                }

                // 已经关闭订单不需要往下执行了
                return true;
            }

            // 有其他商品  并且订单状态非完成
            if (!empty($otherOrderGoods) && $order->status == OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND) {
                // 可以发货订单 有其他商品 且 其他商品全部为分包裹状态
                $refundSuccessSend = true;
                foreach ($otherOrderGoods as $item) {
                    if ($item['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK
                        || (!empty($item['refund_type']) && !in_array($item['refund_status'], [
                                RefundConstant::REFUND_STATUS_REJECT,
                                RefundConstant::REFUND_STATUS_CANCEL,
                                RefundConstant::REFUND_STATUS_SUCCESS,
                                RefundConstant::REFUND_STATUS_MANUAL
                            ]))) {
                        $refundSuccessSend = false;
                        break;
                    }
                }

                // 可以发货
                if ($refundSuccessSend) {
                    $order->status = OrderStatusConstant::ORDER_STATUS_WAIT_PICK;
                    $result = $order->save();
                    if ($result === false) {
                        return error('订单发货失败');
                    }
                }
            }
            // 到这说明订单没关闭
            return false;
        }
    }

}