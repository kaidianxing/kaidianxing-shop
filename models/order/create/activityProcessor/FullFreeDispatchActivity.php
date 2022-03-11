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

use shopstar\models\core\CoreAddressModel;
use shopstar\models\order\create\interfaces\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 满额包邮
 * Class FullFreeDispatchActivity
 * @package shopstar\models\order\create\activityProcessor
 */
class FullFreeDispatchActivity implements OrderCreatorActivityProcessorInterface
{
    /**
     * 接收优惠活动分发器指派过来的活动处理任务
     * ---------------------------------------------------
     * 优惠处理器processor方法使用注意 ：
     * 期望能返回 \shopstar\models\order\OrderAssistant对象，
     * 以便订单能够自动处理订单结果。
     * 如果你返回其他类型的值，
     * 那么你需要自己修改订单数据，因为这将不能自动合并优惠结果！
     * ---------------------------------------------------
     * @param OrderCreatorActivityAssistant $assistant 传递过来的订单助手实例，里面包含了当前活动所支持的商品片段
     * @param array $activityInfo 当前活动信息都会原样传递回来
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实例，里面包含了关于当前订单的一切
     *
     * @return OrderCreatorActivityAssistant|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function processor(OrderCreatorActivityAssistant $assistant, array $activityInfo, OrderCreatorKernel &$orderCreatorKernel)
    {
        //根据商品id分组
        $orderGoodsGroup = [];
        //获取商品设置的包邮信息
        foreach ($orderCreatorKernel->orderGoodsData as $orderGoodsIndex => $orderGoodsItem) {

            $orderGoods = [
                'goods_id' => $orderGoodsItem['goods_id'],
                'option_id' => $orderGoodsItem['option_id'],
                'total' => $orderGoodsItem['total'],
                'price' => $orderGoodsItem['price'],
            ];

            $orderGoodsGroup[$orderGoodsItem['goods_id']] = [
                'single_full_unit_switch' => $orderGoodsItem['single_full_unit_switch'],
                'single_full_unit' => $orderGoodsItem['single_full_unit'],
                'single_full_quota' => $orderGoodsItem['single_full_quota'],
                'single_full_quota_switch' => $orderGoodsItem['single_full_quota_switch'],
                'order_goods' => array_merge($orderGoodsGroup[$orderGoodsItem['goods_id']]['order_goods'] ?: [], [$orderGoods])
            ];

        }

        //循环分组好的商品
        foreach ($orderGoodsGroup as $orderGoodsGroupItem) {
            //单品满件包邮
            if ($orderGoodsGroupItem['single_full_unit_switch'] == 1 && $orderGoodsGroupItem['single_full_unit'] <= array_sum(array_column($orderGoodsGroupItem['order_goods'], 'total'))) {
                // 设置运费
                $assistant = $this->setDispatchPrice($assistant, $orderGoodsGroupItem['order_goods']);
            }

            //单品满额包邮
            $goodsPayPrice = round2(array_sum(array_column($orderGoodsGroupItem['order_goods'], 'price')));
            if ($orderGoodsGroupItem['single_full_quota_switch'] == 1 && $orderGoodsGroupItem['single_full_quota'] <= $goodsPayPrice) {
                // 设置运费
                $assistant = $this->setDispatchPrice($assistant, $orderGoodsGroupItem['order_goods']);
            }
        }


        // 获取设置
        $set = ShopSettings::get('sale.basic.enough_free');

        // 开启包邮
        if ($set['state'] == 1) {
            // 解析
            $set['enough_areas_code'] = Json::decode($set['enough_areas_code']);
            // 收货地址
            $address = $orderCreatorKernel->address;
            // 地址在不包邮列表中
            if (!empty($set['enough_areas_code']) && in_array($address['area_code'], $set['enough_areas_code']['areas'])) {
                return $assistant;
            }

            // 查询地址是否在地址库
            $addressExist = CoreAddressModel::find()
                ->where([
                    'code_id' => $address['area_code'],
                ])
                ->exists();
            if (!$addressExist) {
                return $assistant;
            }

            // 订单商品
            $goods = $orderCreatorKernel->orderGoodsData;
            // 订单商品金额
            $totalPrice = $assistant->getTotalPayPrice();

            // 不满足包邮金额
            if (bccomp($totalPrice, $set['order_enough'], 2) < 0) {
                return $assistant;
            }
            // 设置运费
            foreach ($goods as $item) {
                // 如果商品在不参与满额包邮的商品里  跳出
                if ($set['is_participate'] == 0 && in_array($item['goods_id'], $set['goods_ids'] ?? [])) {
                    continue;
                }
                // 如果商品不在只参与满额包邮的商品里  跳出
                if ($set['is_participate'] == 1 && !in_array($item['goods_id'], $set['goods_ids'] ?? [])) {
                    continue;
                }

                // 运费置为0
                $assistant->setDispatchPrice($item['goods_id'], $item['option_id'] ?? 0, 0.0, []);
            }
        }

        return $assistant;
    }

    /**
     * 设置运费
     * @param $assistant
     * @param $orderGoods
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function setDispatchPrice($assistant, $orderGoods)
    {
        foreach ((array)$orderGoods as $orderGoodsItem) {
            $assistant->setDispatchPrice($orderGoodsItem['goods_id'], $orderGoodsItem['option_id'] ?? 0, 0, []);
        }

        return $assistant;
    }


}
