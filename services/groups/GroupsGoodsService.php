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

namespace shopstar\services\groups;

use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\groups\GroupsGoodsModel;
use shopstar\models\order\OrderActivityModel;
use yii\helpers\Json;

/**
 * 拼团商品服务类
 * Class GroupsGoodsService
 * @package shopstar\services\groups
 * @author likexin
 */
class GroupsGoodsService
{

    /**
     * 订单支付时检测库存
     * @param int $activityId
     * @param int $goodsId
     * @param int $optionId
     * @param int $total
     * @return array|bool
     * @author likexin
     */
    public static function orderPayCheckGroupsGoodsStock(int $activityId, int $goodsId, int $optionId, int $total)
    {
        return self::checkGroupsGoodsStock($activityId, $goodsId, $optionId, $total, [
            'change' => false
        ]);
    }

    /**
     * 检查拼团活动库存
     * @param int $activityId
     * @param int $goodsId
     * @param int $optionId
     * @param int $total
     * @param array $options
     * @return bool|array
     * @author likexin
     */
    public static function checkGroupsGoodsStock(int $activityId, int $goodsId, int $optionId, int $total, array $options = [])
    {
        $options = array_merge([
            'change' => true
        ], $options);

        //redis key
        $redisKey = 'activity_groups_' . $activityId . '_' . $goodsId . '_' . $optionId;

        //启动redis
        $redis = \Yii::$app->redisPermanent;

        //获取已购买数量
        $buyNum = (int)$redis->get($redisKey);

        //获取活动商品
        $activityGoods = ShopMarketingGoodsMapModel::findOne([
            'activity_id' => $activityId,
            'goods_id' => $goodsId,
            'option_id' => $optionId,
        ]);

        // 判断库存是否充足
        if ($activityGoods->original_stock < ($buyNum + $total)) {
            return error('拼团商品库存不足');
        }

        //判断是否需要执行变更操作
        if ($options['change']) {

            //原子减库存
            $redisTotalNum = $redis->incrby($redisKey, $total);

            //判断是否超卖
            if ($redisTotalNum > $activityGoods->original_stock) {

                //返还redis库存
                $redis->decrby($redisKey, $total);
                return error('拼团商品库存不足');
            }

            //判断数据库库存
            if (($activityGoods->activity_stock - $total) < 0) {
                return error('拼团商品库存不足');
            }

            //赋值减库存
            $activityGoods->activity_stock = $activityGoods->original_stock - $redisTotalNum;

            $activityGoods->save();
        }

        return true;
    }

    /**
     * 返还拼团库存
     * @param int $orderId
     * @param int $goodsId
     * @param int $optionId
     * @param int $total
     * @return bool
     * @author likexin
     */
    public static function restitutionGroupsGoodsStock(int $orderId, int $goodsId, int $optionId, int $total): bool
    {
        $orderActivity = OrderActivityModel::find()
            ->where([
                'order_id' => $orderId,
            ])
            ->select([
                'activity_id'
            ])
            ->first();

        //redis key
        $redisKey = 'activity_groups_rebate_' . $orderActivity['activity_id'] . '_' . $goodsId . '_' . $optionId;

        //启动redis
        $redis = \Yii::$app->redisPermanent;

        //反库存
        $redis->decrby($redisKey, $total);

        // 返还数据库库存
        ShopMarketingGoodsMapModel::updateAllCounters([
            'activity_stock' => $total
        ], [
            'goods_id' => $goodsId,
            'option_id' => $optionId,
            'activity_id' => $orderActivity['activity_id'],
        ]);

        return true;
    }

    /**
     * 获取活动下每个商品的最高最低价
     * @param int $activityId
     * @return array
     * @author likexin
     */
    public static function getAllGoodsOptionInfo(int $activityId): array
    {
        // 拼团商品表
        $goodsList = GroupsGoodsModel::find()
            ->where([
                'activity_id' => $activityId,
            ])
            ->indexBy('goods_id')
            ->get();

        // 活动商品表(后期库存用)
        $activityGoodsList = ShopMarketingGoodsMapModel::find()
            ->where([
                'activity_id' => $activityId,
            ])
            ->indexBy('goods_id')
            ->get();
        // 把库存之类信息塞进去
        foreach ($goodsList as $k => &$v) {
            foreach ($activityGoodsList as $kk => $vv) {
                if ($v['goods_id'] == $vv['goods_id'] && $v['option_id'] == $vv['option_id'] && $v['activity_id'] == $vv['activity_id']) {
                    $v = array_merge($v, $vv);
                }
            }
        }
        unset($v);

        // 先组成三维数组
        $returnData = [];
        foreach ($goodsList as $goodsId => $goodsInfo) {
            $returnData[$goodsId][] = $goodsInfo;
        }

        foreach ($returnData as $goodsId => $list) {

            // 如果商品有规格，就是多规格商品，需要查最低价格
            $hasOption = !empty($list[0]['option_id']);

            // 如果是阶梯团，同样要查阶梯价格
            $hasLadder = !empty((float)reset($list)['is_ladder']);

            $priceInfo = [];
            foreach ($list as $info) {
                if ($hasLadder) {
                    $priceInfo = array_merge($priceInfo, Json::decode($info['ladder_price']));
                } elseif ($hasOption) {
                    $priceInfo[] = $info['price'];
                } else {

                    $priceInfo[] = $info['price'];
                }
            }

            $priceRange['has_range'] = true;

            //如果没规格没阶梯，直接取
            if (empty($hasLadder) && empty($hasOption)) {
                $priceRange['has_range'] = false;
                $priceRange['activity_price'] = min($priceInfo);
            } else {
                $priceRange['min_price'] = min($priceInfo);
                $priceRange['max_price'] = max($priceInfo);
            }

            $returnData[$goodsId]['price_range'] = $priceRange;
        }

        return $returnData;
    }

    /**
     * 根据活动及商品ID查阶梯价格
     * @param int $activityId
     * @param int $goodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author likexin
     */
    public static function getGoodsOptionInfo(int $activityId, int $goodsId): array
    {
        //同时查活动map表来判定参没参加
        $goodsInfo = GroupsGoodsModel::find()
            ->alias('groups_goods')
            ->leftJoin(ShopMarketingGoodsMapModel::tableName() . 'as goods_map', 'goods_map.option_id = groups_goods.option_id and goods_map.activity_id = ' . $activityId . ' and goods_map.goods_id = ' . $goodsId)
            ->select([
                'groups_goods.goods_id',
                'groups_goods.option_id',
                'groups_goods.activity_id',
                'groups_goods.price',
                'groups_goods.ladder_price',
                'groups_goods.leader_price',
                'goods_map.is_join',
                'goods_map.activity_stock',
            ])
            ->where([
                'groups_goods.activity_id' => $activityId,
                'groups_goods.goods_id' => $goodsId,
            ])
            ->indexBy('option_id')
            ->get();

        array_walk($goodsInfo, function (&$row) {
            if (!empty($row['ladder_price'])) {
                $row['ladder_price'] = Json::decode($row['ladder_price']);
            }
        });

        return $goodsInfo;
    }

}