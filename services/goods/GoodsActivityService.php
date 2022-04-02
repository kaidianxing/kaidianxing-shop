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

namespace shopstar\services\goods;

use shopstar\bases\service\BaseService;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\goods\GoodsActivityModel;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsActivityService extends BaseService
{
    /**
     * 商品是否可用
     * @param int $goodsId
     * @param string $startTime
     * @param string $endTime
     * @param int $activityId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAvailable(int $goodsId, string $startTime, string $endTime, int $activityId = 0): bool
    {
        return GoodsActivityModel::find()
            ->where(['goods_id' => $goodsId, 'is_delete_activity' => 0])
            ->andWhere(['>', 'end_time', DateTimeHelper::now()])
            ->andWhere([
                'or',
                [ // 开始时间不能在时间段内
                    'and',
                    ['<=', 'start_time', $startTime],
                    ['>=', 'end_time', $startTime],
                ],
                [ // 结束时间不能在时间段内
                    'and',
                    ['<=', 'start_time', $endTime],
                    ['>=', 'end_time', $endTime],
                ],
                [ // 开始时间比现有小  结束时间比现有大
                    'and',
                    ['>=', 'start_time', $startTime],
                    ['<=', 'end_time', $endTime]
                ]
            ])
            ->exists();
    }

    /**
     * 根据商品id获取可用活动
     * @param $goodsId
     * @param int $clientType
     * @param array $options 附加选项
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getJoinActivityByGoodsId($goodsId, int $clientType, array $options = []): array
    {
        $nowDate = DateTimeHelper::now();

        $query = GoodsActivityModel::find()
            ->where([
                'goods_id' => $goodsId,
                'is_delete_activity' => 0,
            ])
            ->andWhere([
                'and',
                // 开始时间不能在时间段内
                ['<=', 'start_time', $nowDate],
                ['>=', 'end_time', $nowDate],
            ])
            ->andWhere('find_in_set(' . $clientType . ',client_type)')
            ->select([
                'activity_id',
                'activity_type',
                'goods_id',
            ]);

        return $query->get();
    }

    /**
     * 根据商品id获取可用活动并跟去商品id分组
     * @param $goodsId
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getJoinActivityByGoodsIdGroup($goodsId, int $clientType): array
    {
        //获取商品可用活动
        $goodsMap = self::getJoinActivityByGoodsId($goodsId, $clientType);

        //如果为空
        if (empty($goodsMap)) {
            return [];
        }

        $data = [];
        foreach ($goodsMap as $index => $item) {
            $data[$item['goods_id']][] = $item['activity_type'];
        }

        return $data;
    }

    /**
     * 获取预热中的活动
     * @param int $goodsId
     * @param int $hasOption
     * @param int $clientType
     * @param int $exist 仅查询是否存在
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPreheatActivity(int $goodsId, int $hasOption, int $clientType, int $exist = 0): array
    {
        $nowDate = DateTimeHelper::now();
        $activity = GoodsActivityModel::find()
            ->where(['goods_id' => $goodsId, 'is_delete_activity' => 0])
            ->andWhere([
                'and',
                ['is_preheat' => 1],
                ['<', 'preheat_time', $nowDate],
                ['>', 'end_time', $nowDate],
            ])
            ->andWhere('find_in_set(' . $clientType . ',client_type)')
            ->orderBy(['start_time' => SORT_ASC]) // 先开始的活动
            ->first();
        if ($exist) {
            return $activity;
        }

        // 获取最低价格
        if (!empty($activity)) {
            // 查找商品
            $goodsInfo = ShopMarketingGoodsMapModel::find()
                ->where(['goods_id' => $goodsId, 'activity_id' => $activity['activity_id']])
                ->get();

            if ($hasOption) {
                $priceRange = [
                    'min_price' => $goodsInfo[0]['activity_price'],
                ];

                foreach ($goodsInfo as $item) {
                    if ($item['is_join']) {
                        $priceRange['min_price'] = min($priceRange['min_price'], $item['activity_price']);
                        $priceRange['max_price'] = max($priceRange['max_price'], $item['activity_price']);
                    }
                }

                $activity['price_range'] = $priceRange;
            } else {
                $activity['activity_price'] = $goodsInfo[0]['activity_price'];
            }
        }

        return $activity;
    }

}