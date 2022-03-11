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
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsActivityModel;
use yii\helpers\Json;

class GoodsActivityService extends BaseService
{
    /**
     * 商品是否可用
     * @param int $goodsId
     * @param string $startTime
     * @param string $endTime
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAvailable(int $goodsId, string $startTime, string $endTime, int $activityId = 0)
    {
        $exist1 = GoodsActivityModel::find()
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


        return $exist1;
    }

    /**
     * 根据商品id获取可用活动
     * @param $goodsId
     * @param int $clientType
     * @param array $options 附加选项
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getJoinActivityByGoodsId($goodsId, int $clientType, array $options = [])
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
    public static function getPreheatActivity(int $goodsId, int $hasOption, int $clientType, int $exist = 0)
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

            //拼团需要查询阶梯团
            if ($activity['activity_type'] == 'groups') {

                $activityRules = ShopMarketingModel::find()->where(['id' => $activity['activity_id']])->select(['inner_type', 'rules'])->first();
                $rules = Json::decode($activityRules['rules']);

                $activity['inner_type'] = $activityRules['inner_type'];

                $priceRange = GroupsGoodsModel::calculateLadderPrice($activity['activity_id'], $goodsId);
                if ($priceRange['has_range']) {
                    $activity['price_range']['min_price'] = $priceRange['min_price'];
                    $activity['price_range']['max_price'] = $priceRange['max_price'];
                } else {
                    $activity['activity_price'] = $priceRange['activity_price'];
                }
                $activity['rules']['success_num'] = $rules['success_num'];
            } else {

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
        }

        return $activity;
    }

    /**
     * @param array $list
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司.
     */
    public static function fullReduceHandler(array $list, int $clientType)
    {

        $goodsId = array_unique(array_column($list, 'goods_id'));

        //获取活动
        $activity = FullReduceActivityService::getActivity($goodsId, $clientType, false);

        //判断活动是否错误 如果错误或者为空的话不处理任何结构
        if (is_error($activity) || empty($activity)) {
            return $list;
        }

        foreach ($activity as $activityItem) {

            //解析活动规则
            $activityRules = Json::decode($activityItem['rules']);

            //全部商品参与
            if ($activityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_TYPE_ALL) {

                //获取所有选中的商品
                $joinGoods = [];
                foreach ($list as $listItem) {
                    if ($listItem['is_selected']) {
                        $listItem['price'] = round2($listItem['price'] * $listItem['total']);
                    } else {
                        $listItem['price'] = 0.00;
                        $listItem['total'] = 0;
                    }
                    $joinGoods[] = $listItem;
                }

                if (empty($joinGoods)) {
                    continue;
                }

                //计算是否复合活动条件
                $condition = self::calculateFullReduce($joinGoods, $activityRules);
                $tempList[] = [
                    'type' => 'full_reduce',
                    'activity_id' => $activityItem['id'],
                    'activity_type' => $activityRules['type'],
                    'preferential_type' => $activityRules['preferential_type'],
                    'activity_rules' => $activityRules['preferential_rules'],
                    'activity_value' => $condition ?: null, //返回符合条件
                    'goods' => $list,
                    'max_card_id' => max(array_column($joinGoods ?? [], 'id'))
                ];

                //删除商品
                $list = [];
                unset($listItem, $joinGoods);
                break;
            }

            //部分商品参与
            if ($activityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_PART_GOODS_JOIN) {

                $joinGoods = [];
                foreach ($list as $listIndex => $listItem) {
                    if (in_array($listItem['goods_id'], $activityItem['goods_ids'])) {
                        $joinGoods[$listIndex] = $listItem;
                    }
                }

                //如果没有商品参与则跳过
                if (empty($joinGoods)) {
                    continue;
                }

                //获取所有选中的商品
                $selectGoods = [];
                foreach ($joinGoods as $joinItem) {
                    if ($joinItem['is_selected']) {
                        $joinItem['price'] = round2($joinItem['price'] * $joinItem['total']);
                    } else {
                        $joinItem['price'] = 0.00;
                        $joinItem['total'] = 0;
                    }
                    $selectGoods[] = $joinItem;
                }

                //计算是否符合活动条件
                $condition = self::calculateFullReduce($selectGoods, $activityRules);

                $tempList[] = [
                    'type' => 'full_reduce',
                    'activity_id' => $activityItem['id'],
                    'activity_type' => $activityRules['type'],
                    'goods' => array_values($joinGoods),
                    'preferential_type' => $activityRules['preferential_type'],
                    'activity_rules' => $activityRules['preferential_rules'],
                    'activity_value' => $condition ?: null,  //返回符合条件
                    'max_card_id' => max(array_column($joinGoods ?? [], 'id'))
                ];

                //删除商品
                $list = array_diff_key($list, $joinGoods);

                unset($listItem, $joinItem);
            }

            //除部分商品外全部参与
            if ($activityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_PART_GOODS_NOT_JOIN) {

                $joinGoods = [];
                foreach ($list as $listIndex => $listItem) {
                    if (!in_array($listItem['goods_id'], $activityItem['goods_ids'])) {
                        $joinGoods[$listIndex] = $listItem;
                    }
                }

                //如果没有商品参与则跳过
                if (empty($joinGoods)) {
                    continue;
                }

                //获取所有选中的商品
                $selectGoods = [];
                foreach ($joinGoods as $joinItem) {
                    if ($joinItem['is_selected']) {
                        $joinItem['price'] = round2($joinItem['price'] * $joinItem['total']);
                    } else {
                        $joinItem['price'] = 0.00;
                        $joinItem['total'] = 0;
                    }
                    $selectGoods[] = $joinItem;
                }

                //计算是否符合活动条件
                $condition = self::calculateFullReduce($selectGoods, $activityRules);

                $tempList[] = [
                    'type' => 'full_reduce',
                    'activity_id' => $activityItem['id'],
                    'activity_type' => $activityRules['type'],
                    'goods' => array_values($joinGoods),
                    'preferential_type' => $activityRules['preferential_type'],
                    'activity_rules' => $activityRules['preferential_rules'],
                    'activity_value' => $condition ?: null,  //返回符合条件
                    'max_card_id' => max(array_column($joinGoods ?? [], 'id'))
                ];

                //删除商品
                $list = array_diff_key($list, $joinGoods);
                unset($listItem, $joinItem);

            }
        }

        //根据购物车id进行排序
        if (!empty($tempList)) {
            array_multisort(array_column($tempList, 'max_card_id'), SORT_DESC, $tempList);

            $list = array_merge($tempList, [['goods' => array_values($list)]]);
        }

        return $list;
    }

    /**
     * @param array $goods
     * @param array $rules
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    private static function calculateFullReduce(array $goods, array $rules)
    {

        $type = 'price';

        //判断类型获取字段值
        if ($rules['type'] == FullReduceTypeConstant::FULL_REDUCE_TYPE_FULL_A_REDUCE_MONEY || $rules['type'] == FullReduceTypeConstant::FULL_REDUCE_TYPE_FULL_A_REDUCE_DISCOUNT) {
            $type = 'total';
        }

        //商品字段总数
        $goodsTotal = array_sum(array_column($goods, $type));

        //阶梯
        if ($rules['preferential_type'] == FullReduceRuleTypeConstant::FULL_REDUCE_RULE_TYPE_LADDER) {

            $rule = $rules['preferential_rules'];
            $rule = array_column($rule, null, 'condition');
            rsort($rule);
            foreach ($rule as $ruleItem) {
                if ($ruleItem['condition'] <= $goodsTotal) {

                    //返回满足活动
                    return $ruleItem;
                }
            }
        } else if ($rules['preferential_type'] == FullReduceRuleTypeConstant::FULL_REDUCE_RULE_TYPE_LOOP) { //循环最多

            //循环满减取首个指针
            $rule = current($rules['preferential_rules']);

            //向下取整获取可执行次数
            $intCondition = floor($goodsTotal / $rule['condition']);
            //一次优惠都不满足
            if ($intCondition <= 0) {
                return false;
            }

            //返回满足活动
            return $rule;
        }

        return false;
    }

}