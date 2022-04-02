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

namespace shopstar\models\seckill;

use shopstar\constants\activity\ActivityConstant;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\seckill\SeckillException;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\order\create\activityProcessor\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

/**
 * @author 青岛开店星信息技术有限公司
 */
class SeckillActivity implements OrderCreatorActivityProcessorInterface
{
    /**
     * 秒杀活动
     * @param OrderCreatorActivityAssistant $assistant
     * @param array $activityInfo
     * @param OrderCreatorKernel $orderCreatorKernel
     * @return OrderCreatorActivityAssistant
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor(OrderCreatorActivityAssistant $assistant, array $activityInfo, OrderCreatorKernel &$orderCreatorKernel)
    {
        $redis = \Yii::$app->redisPermanent;
        // 商品信息
        $goodsInfo = $assistant->getGoodsInfo();
        // 只能购买一个商品
        if (count($goodsInfo) > 1) {
            throw new SeckillException(SeckillException::ORDER_SECKILL_GOODS_INFO_ERROR);
        }

        foreach ($goodsInfo as $goods) {
            // 校验商品是不是下单减库存
            if ($goods['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_ORDER) {
                throw new SeckillException(SeckillException::ORDER_SECKILL_REDUCTION_TYPE_ERROR);
            }

            // 查找规则
            $activityRules = ShopMarketingModel::getActivityInfo($goods['goods_id'], $orderCreatorKernel->clientType, 'seckill', $goods['option_id'], ['member_id' => $orderCreatorKernel->memberId]);
            // 活动商品规则
            $goodsRules = $activityRules['goods_info'][$goods['option_id']];
            // 数量
            $total = $assistant->getGoodsTotal($goods['goods_id'], $goods['option_id']);

            // 判断限购
            if ($activityRules['rules']['limit_type'] != ActivityConstant::ACTIVITY_LIMIT_TYPE_NOT_LIMIT) {
                if (($activityRules['buy_count'] + $total) > $activityRules['rules']['limit_num']) {
                    throw new SeckillException(SeckillException::ORDER_SECKILL_BUY_LIMIT);
                }
            }

            // 查找已购数量
            $key = 'seckill_' . '_' . $activityRules['id'] . '_' . $goods['goods_id'] . '_' . $goods['option_id'];
            $seckillTotal = $redis->get($key);
            // 校验库存
            if ($goodsRules['original_stock'] < ($total + $seckillTotal)) {
                throw new SeckillException(SeckillException::ORDER_SECKILL_CONFIRM_GOODS_STOCK_NOT_ENOUGH);
            }
            // 商品价格
            $goodsPrice = $assistant->getGoodsPrice($goods['goods_id'], $goods['option_id']);
            // 优惠价格
            $cutPrice = bcsub($goodsPrice, bcmul($goodsRules['activity_price'], $total, 2), 2);

            // 减订单价
            $assistant->setCutPrice($goods['goods_id'], $goods['option_id'], $cutPrice, 'seckill', ['seckill' => $activityRules]);
            $orderCreatorKernel->orderData['activity_type'] = OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL;
            // 提交
            if (!$orderCreatorKernel->isConfirm) {
                $num = $redis->incrby($key, $total);
                $sub = bcsub($goodsRules['original_stock'], $num);
                if ($sub < 0) {
                    // 返还
                    $redis->incrby($key, -$total);
                    throw new SeckillException(SeckillException::ORDER_SECKILL_GOODS_STOCK_NOT_ENOUGH);
                }
                // 可以下单
                // 下单减活动库存
                ShopMarketingGoodsMapModel::updateAll(
                    ['activity_stock' => $sub],
                    ['activity_id' => $activityRules['id'], 'goods_id' => $goods['goods_id'], 'option_id' => $goods['option_id']]
                );

                // 记录
                SeckillLogModel::createLog($orderCreatorKernel->memberId, $goods['goods_id'], $total, $activityRules['id']);

            }
            // 修改订单关闭时间 读取秒杀设置
            $setting = ShopSettings::get('activity.seckill');
            // 永不关闭
            if ($setting['close_type'] == 0) {
                $orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_NOT_CLOSE;
                $orderCreatorKernel->confirmData['auto_close_time'] = 0;
                $orderCreatorKernel->autoCloseTime = 0;
            } else {
                // 关闭时间
                $orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME;
                // 自动关闭时间不直接运用计算
                $orderCreatorKernel->confirmData['auto_close_time'] = $setting['close_time'];
                $orderCreatorKernel->autoCloseTime = date('Y-m-d H:i:s', strtotime($orderCreatorKernel->createTime) + ($setting['close_time'] * 60));
            }
            // 订单活动
            $orderCreatorKernel->orderActivity[] = [
                'id' => $activityRules['id'],
                'type' => 'seckill',
                'rule_index' => 0,
            ];
        }

        return $assistant;
    }
}