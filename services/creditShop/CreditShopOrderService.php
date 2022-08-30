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

namespace shopstar\services\creditShop;

use shopstar\constants\creditShop\CreditShopGoodsTypeConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopGoodsOptionModel;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsStockLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\sale\CouponUseOrderMapModel;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\services\sale\CouponMemberService;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * 积分商城订单服务类
 * Class CreditShopOrderService.
 * @package shopstar\services\creditShop
 */
class CreditShopOrderService
{
    /**
     * 支付完成后
     * 修改状态  发放优惠券
     * @param int $memberId
     * @param int $orderId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function paySuccess(int $memberId, int $orderId)
    {
        // 查找积分商城订单
        $creditShopOrder = CreditShopOrderModel::findOne(['order_id' => $orderId]);
        if (empty($creditShopOrder)) {
            return error('订单不存在');
        }

        // 修改状态
        $creditShopOrder->status = 1;

        // 发放优惠券
        if ($creditShopOrder->type == CreditShopGoodsTypeConstant::COUPON) {
            $memberCouponId = [];

            for ($i = 0; $i < $creditShopOrder->total; $i ++) {
                $coupon = CouponModel::find()->where(['id' => $creditShopOrder->shop_goods_id])->first();
                $memberCouponId[] = CouponMemberService::sendCoupon($memberId, $coupon, 21, ['get_id' => 1]);
            }

            $creditShopOrder->member_coupon_id = Json::encode($memberCouponId);
        }

        $creditShopOrder->save();

        return true;
    }

    /**
     * 检查是否可维权
     * @param int $orderId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkRefund(int $orderId)
    {
        // 判断优惠券 是否可维权
        // 查找订单
        $order = CreditShopOrderModel::find()->where(['order_id' => $orderId])->first();
        $memberCouponId = Json::decode($order['member_coupon_id']);

        // 查找优惠券 判断有没有使用了的
        // 查找优惠券 判断有没有使用了的
        $memberCoupon = CouponUseOrderMapModel::find()->where(['coupon_member_id' => $memberCouponId])->exists();

        if (!empty($memberCoupon)) {
            return error('优惠券已使用');
        }

        return true;
    }

    /**
     * 维权
     * 回收优惠券 关闭订单
     * @param int $orderId
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function refund(int $orderId)
    {
        // 查找订单
        $order = CreditShopOrderModel::findOne(['order_id' => $orderId]);
        // 关闭订单
        $order->status = -1;

        $order->save();

        // 返还优惠券
        if ($order->type == CreditShopGoodsTypeConstant::COUPON) {
            $memberCouponId = Json::decode($order['member_coupon_id']);

            CouponMemberModel::deleteAll(['id' => $memberCouponId, 'order_id' => 0]);
        }
    }

    /**
     * 关闭订单
     * @param int $orderId
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeOrder(int $orderId)
    {
        $creditShopOrder = CreditShopOrderModel::findOne(['order_id' => $orderId]);
        // 取积分
        MemberModel::updateCredit($creditShopOrder->member_id, $creditShopOrder->pay_credit, 0, 'credit', 1, '订单退款-积分抵扣返还', MemberCreditRecordStatusConstant::CREDIT_STATUS_CREDIT_SHOP_REFUND);
        $creditShopOrder->status = -1;
        $creditShopOrder->save();
    }

    /**
     * 获取渠道金额
     * @param $clientType
     * @param string $type
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getClientPrice($clientType = 0, string $type = 'pay_price')
    {
        $query = CreditShopOrderModel::find()->where(['status' => 1]);

        if ($clientType != 0) {
            $query->andWhere(['client_type' => $clientType]);
        }

        return $query->sum($type) ?? 0;
    }

    /**
     * 获取已购买数量
     * 除去维权
     * @param int $goodsId
     * @param int $memberId
     * @param int $limitDay
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getBuyTotal(int $goodsId, int $memberId, int $limitDay = 0)
    {
        $query = CreditShopOrderModel::find()->where(['goods_id' => $goodsId, 'member_id' => $memberId])->andWhere(['<>', 'status', -1]);

        if ($limitDay != 0) {
            $startTime = date('Y-m-d H:i:s', strtotime('- '.$limitDay.' days'));
            $query->andWhere(['>', 'created_at', $startTime]);
        }

        return $query->sum('total') ?? 0;
    }

    /**
     * 检测库存
     * @param int $orderId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkStock(int $orderId)
    {
        // 获取订单
        $creditShopOrder = CreditShopOrderModel::find()->where(['order_id' => $orderId])->first();
        // 获取积分商品
        $creditShopGoods = CreditShopGoodsModel::find()->where(['id' => $creditShopOrder['goods_id']])->first();

        if ($creditShopOrder['option_id'] != 0) {
            $creditShopOption = CreditShopGoodsOptionModel::find()->where(['id' => $creditShopOrder['option_id']])->first();
            if ($creditShopOption['credit_shop_stock'] < $creditShopOrder['total']) {
                // 库存不足
                return error('库存不足');
            }
        } else {
            if ($creditShopGoods['credit_shop_stock'] < $creditShopOrder['total']) {
                // 库存不足
                return error('库存不足');
            }
        }

        // 查找原商品
        if ($creditShopOrder['type'] == CreditShopGoodsTypeConstant::GOODS) {
            $shopGoods = GoodsModel::find()->where(['id' => $creditShopOrder['shop_goods_id']])->first();
            if ($shopGoods['stock'] < $creditShopOrder['total']) {
                // 库存不足
                return error('库存不足');
            }
        } else {
            $shopCoupon = CouponModel::find()->where(['id' => $creditShopOrder['shop_goods_id']])->first();
            if ($shopCoupon['stock_type'] == 1 && ($shopCoupon['stock'] - $shopCoupon['get_total']) < $creditShopOrder['total']) {
                // 库存不足
                return error('库存不足');
            }
        }

        return true;
    }

    /**
     * 更新库存
     * 都是付款减库存
     * @param bool $reduce
     * @param int $orderId
     * @param array $options
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStock(bool $reduce, int $orderId, array $options = []): bool
    {
        // 获取订单
        $creditShopOrder = CreditShopOrderModel::find()->where(['order_id' => $orderId])->first();

        // 减商品库存
        if ($creditShopOrder['type'] == CreditShopGoodsTypeConstant::GOODS) {
            // 减库存
            $stockReduce = 'stock ' . ($reduce ? '- ' : '+ ') . $creditShopOrder['total'];
            // 销量
            $realReduce = 'real_sales ' . ($reduce ? '+ ' : '- ') . $creditShopOrder['total'];

            $data = [
                'real_sales' => new Expression($realReduce),
                'stock' => new Expression($stockReduce),
            ];

            // 记录日志
            GoodsStockLogModel::saveData([
                'order_id' => $orderId,
                'goods_id' => $creditShopOrder['shop_goods_id'],
                'method' => $reduce ? 0 : 1,
                'stock' => $data['stock'] ? $creditShopOrder['total'] : 0,
                'sales' => $creditShopOrder['total'],
                'reason' => $options['reason']
            ]);

            // 更新
            GoodsModel::updateAll($data, [
                'id' => $creditShopOrder['shop_goods_id'],
            ]);

            if ($creditShopOrder['shop_option_id'] != 0) {
                GoodsOptionModel::updateAll([
                    'stock' => new Expression($stockReduce)
                ], [
                    'id' => $creditShopOrder['shop_option_id'],
                    'goods_id' => $creditShopOrder['shop_goods_id'],
                ]);
            }
            // 查找商品
            $goods = GoodsModel::find()->select('type')->where(['id' => $creditShopOrder['shop_goods_id']])->first();
            // 如果是虚拟卡密订单 需要额外处理卡密库的库存数量
            if ($goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                VirtualAccountDataModel::updateVirtualAccountReduceStock($reduce, $orderId, $creditShopOrder['total'], $creditShopOrder['shop_goods_id']);
            }

        } else {
            // 减优惠券库存
            // 不需要  在发送优惠券方法里就扣了库存了
        }

        // 处理积分商品库存
        // 减库存
        $stockReduce = 'credit_shop_stock ' . ($reduce ? '- ' : '+ ') . $creditShopOrder['total'];
        $saleReduce = 'sale ' . ($reduce ? '+ ' : '- ') . $creditShopOrder['total'];
        $data = [
            'credit_shop_stock' => new Expression($stockReduce),
            'sale' => new Expression($saleReduce),
        ];
        // 更新
        CreditShopGoodsModel::updateAll($data, ['id' => $creditShopOrder['goods_id']]);

        // 多规格
        if ($creditShopOrder['option_id'] != 0) {
            CreditShopGoodsOptionModel::updateAll($data, ['id' => $creditShopOrder['option_id']]);
        }

        return true;
    }
}
