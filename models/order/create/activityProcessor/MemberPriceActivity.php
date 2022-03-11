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

namespace shopstar\models\order\create\activityProcessor;

use shopstar\models\goods\GoodsMemberLevelDiscountModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\order\create\interfaces\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;

/**
 * 会员价处理器
 * Class MemberPriceActivity
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\models\order\create\activityProcessor
 */
class MemberPriceActivity implements OrderCreatorActivityProcessorInterface
{
    /**
     * 接收优惠活动分发器指派过来的活动处理任务
     * ---------------------------------------------------
     * 优惠处理器processor方法使用注意 ：
     * 期望能返回\shopstar\models\order\OrderAssistant对象，
     * 以便订单能够自动处理订单结果。
     * 如果你返回其他类型的值，
     * 那么你需要自己修改订单数据，因为这将不能自动合并优惠结果！
     * ---------------------------------------------------
     * @param OrderCreatorActivityAssistant $assistant 传递过来的订单助手实例，里面包含了当前活动所支持的商品片段
     * @param array $activityInfo 当前活动信息都会原样传递回来
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实例，里面包含了关于当前订单的一切
     *
     * @return OrderCreatorActivityAssistant|void
     */
    public function processor(OrderCreatorActivityAssistant $assistant, array $activityInfo, OrderCreatorKernel &$orderCreatorKernel)
    {
        //开启折扣的商品映射
        $goodsMemberPriceTypeMap = [];
        foreach ($orderCreatorKernel->orderGoodsData as $orderGoodsDataIndex => $orderGoodsDataItem) {
            if ($orderGoodsDataItem['member_level_discount_type'] > 0) {
                $goodsMemberPriceTypeMap[$orderGoodsDataItem['member_level_discount_type']][] = [
                    'goods_id' => $orderGoodsDataItem['goods_id'],//商品id
                    'option_id' => $orderGoodsDataItem['option_id'],//规格id
                    'price' => $orderGoodsDataItem['price'],//商品价格
                    'total' => $orderGoodsDataItem['total']//商品价格
                ];
            }
        }

        if (empty($goodsMemberPriceTypeMap)) return;

        //获取用户会员等级
        $memberLevel = MemberLevelModel::findOne(['id' => $orderCreatorKernel->member['level_id'], 'state' => 1]);
        if (empty($memberLevel)) return;
        $memberLevel = $memberLevel->toArray();

        //如果存在是默认会员等级折扣的商品
        !empty($goodsMemberPriceTypeMap['1']) && $this->defaultMemberLevelDiscount($goodsMemberPriceTypeMap['1'], $assistant, $orderCreatorKernel, $memberLevel);

        //指定会员等级
        !empty($goodsMemberPriceTypeMap['2']) && $this->designatedMembershipLevel($goodsMemberPriceTypeMap['2'], $assistant, $orderCreatorKernel, $memberLevel);

        //规格商品指定会员等级
        !empty($goodsMemberPriceTypeMap['3']) && $this->optionDesignatedMembershipLevel($goodsMemberPriceTypeMap['3'], $assistant, $orderCreatorKernel, $memberLevel);


        return $assistant;
    }

    /**
     * 默认折扣
     * @param array $goodsMemberPriceTypeMap
     * @param $assistant
     * @param $orderCreatorKernel
     * @param $memberLevel
     * @author 青岛开店星信息技术有限公司
     */
    private function defaultMemberLevelDiscount(array $goodsMemberPriceTypeMap, OrderCreatorActivityAssistant &$assistant, OrderCreatorKernel $orderCreatorKernel, array $memberLevel)
    {
        //如果没有会员折扣则返回
        if (empty($memberLevel['is_discount']) || empty($memberLevel['discount'])) return;
        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {

            //默认会员等级折扣
            $assistant->setCutPrice(
                $goodsMemberPriceTypeMapItem['goods_id'],
                $goodsMemberPriceTypeMapItem['option_id'],
                round2($goodsMemberPriceTypeMapItem['price'] - round2($goodsMemberPriceTypeMapItem['price'] * ($memberLevel['discount'] / 10))),
                'member_price',
                [
                    'member_price' => [
                        'member_level' => $orderCreatorKernel->member['level_id'],
                        'discount_type' => 1,
                        'discount' => $memberLevel['discount']
                    ]
                ]
            );
        }

        return;
    }

    /**
     * 指定会员等级
     * @param array $goodsMemberPriceTypeMap
     * @param OrderCreatorActivityAssistant $assistant
     * @param OrderCreatorKernel $orderCreatorKernel
     * @param array $memberLevel
     * @author 青岛开店星信息技术有限公司
     */
    private function designatedMembershipLevel(array $goodsMemberPriceTypeMap, OrderCreatorActivityAssistant &$assistant, OrderCreatorKernel $orderCreatorKernel, array $memberLevel)
    {
        $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
            'goods_id' => array_column($goodsMemberPriceTypeMap, 'goods_id'), //会员折扣类型是 2 = 指定会员等级
            'level_id' => $orderCreatorKernel->member['level_id'],
            'option_id' => 0
        ])->indexBy('goods_id')->asArray()->all();

        if (empty($goodsLevelDiscountMap)) return;

        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {
            //如果当前商品没有会员折扣 或者折扣为0 则跳过
            if (empty($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]) || empty($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'])) continue;

            if ($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['type'] == 1) {
                $price = $goodsMemberPriceTypeMapItem['price'] * ($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'] / 10);
            } else {
                $price = round2($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'] * $goodsMemberPriceTypeMapItem['total']);
            }

            $assistant->setCutPrice(
                $goodsMemberPriceTypeMapItem['goods_id'],
                $goodsMemberPriceTypeMapItem['option_id'],
                round2($goodsMemberPriceTypeMapItem['price'] - $price),
                'member_price',
                [
                    'member_price' => [
                        'member_level' => $orderCreatorKernel->member['level_id'],
                        'discount_type' => $goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['type'],
                        'discount' => $memberLevel['discount'],
                    ]
                ]
            );
        }
    }

    /**
     * 多规格指定会员等级
     * @param array $goodsMemberPriceTypeMap
     * @param OrderCreatorActivityAssistant $assistant
     * @param OrderCreatorKernel $orderCreatorKernel
     * @param array $memberLevel
     * @author 青岛开店星信息技术有限公司
     */
    private function optionDesignatedMembershipLevel(array $goodsMemberPriceTypeMap, OrderCreatorActivityAssistant &$assistant, OrderCreatorKernel $orderCreatorKernel, array $memberLevel)
    {
        $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
            'and',
            ['goods_id' => array_column($goodsMemberPriceTypeMap, 'goods_id')], //会员折扣类型是 2 = 指定会员等级
            ['level_id' => $orderCreatorKernel->member['level_id']],
            ['!=', 'option_id', 0],
        ])->asArray()->all();

        if (empty($goodsLevelDiscountMap)) return;

        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {
            foreach ($goodsLevelDiscountMap as $goodsLevelDiscountMapIndex => $goodsLevelDiscountMapItem) {

                //如果当前商品没有会员折扣则跳过
                if ($goodsLevelDiscountMapItem['goods_id'] != $goodsMemberPriceTypeMapItem['goods_id'] || $goodsLevelDiscountMapItem['option_id'] != $goodsMemberPriceTypeMapItem['option_id']) continue;

                if ($goodsLevelDiscountMapItem['type'] == 1) {
                    $price = round2($goodsMemberPriceTypeMapItem['price'] * ($goodsLevelDiscountMapItem['discount'] / 10));
                } else {
                    $price = round2($goodsLevelDiscountMapItem['discount'] * $goodsMemberPriceTypeMapItem['total']);
                }

                $assistant->setCutPrice(
                    $goodsMemberPriceTypeMapItem['goods_id'],
                    $goodsMemberPriceTypeMapItem['option_id'],
                    round2($goodsMemberPriceTypeMapItem['price'] - $price),
                    'member_price',
                    [
                        'member_price' => [
                            'member_level' => $orderCreatorKernel->member['level_id'],
                            'discount_type' => $goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['type'],
                            'discount' => $memberLevel['discount'],
                        ]
                    ]
                );


            }
        }
    }
}
