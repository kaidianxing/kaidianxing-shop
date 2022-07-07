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

namespace shopstar\models\order\create;

use shopstar\helpers\ArrayHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\services\goods\GoodsActivityService;

class OrderCreatorActivityConfig
{
    /**
     * 商城订单所支持的全部优惠活动
     * 注意：有严格的先后顺序，订单和订单活动排序 商品和商品活动排序，不支持所有活动进行排序执行。顺序值不允许相同！
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    private static $rules = [

        // 秒杀
        'seckill' => [
            'sequence' => 231,
            'coexist' => ['credit', 'balance'],
            'is_order_level' => false,
            'class' => 'shopstar\models\seckill\SeckillActivity',
        ],

        //拼团
        'groups' => [
            'sequence' => 229,
            'coexist' => ['coupon'],
            'is_order_level' => false,
            'class' => 'shopstar\services\groups\GroupsActivity',
        ],

        // 会员价
        'member_price' => [
            'sequence' => 240, //优先级 越小越在前
            'coexist' => ['coupon', 'credit', 'balance', 'full_free_dispatch', 'gift_card'],// 必须明确指定哪些活动标识允许与限时购共存，只需指定优先级低的活动
            'is_order_level' => false,// true 为订单级别优惠 ，false 为商品级别优惠，商品优惠允许一单多享，订单优惠每单只享一次
            'class' => '',// 处理器实体类  如果没有则认为是商品内置活动将自动查找到 shopstar\models\order\create\activityProcessor 下
        ],

        // 满额包邮
        'full_free_dispatch' => [
            'sequence' => 250,
            'coexist' => ['credit', 'balance', 'coupon', 'gift_card'],
            'is_order_level' => false,
        ],

        // 优惠券
        'coupon' => [
            'sequence' => 260,
            'coexist' => ['credit', 'balance', 'gift_card',],
            'is_order_level' => false,
        ],


        // 积分抵扣
        'credit' => [
            'sequence' => 290,
            'coexist' => ['balance', 'gift_card'],
            'is_order_level' => true,
        ],

        // 余额抵扣
        'balance' => [
            'sequence' => 295,
            'coexist' => ['gift_card'],
            'is_order_level' => true,
        ],
    ];

    /**
     * 商城基础活动（可配置，每次下单都会走）
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public static $baseActivity = [
        [
            'type' => 'credit',  //积分抵扣
            'rules' => [] //活动规则
        ],
        [
            'type' => 'balance' //余额抵扣
        ],
        [
            'type' => 'coupon' //优惠券
        ],
        [
            'type' => 'full_deduct' //满额立减
        ],
        [
            'type' => 'full_free_dispatch' //满额包邮
        ],
        [
            'type' => 'member_price' //会员价
        ],
        [
            'type' => 'gift_card' // 礼品卡
        ],
        [
            'type' => 'full_reduce' // 满减折
        ],
    ];

    /**
     * 获取处理兼容后的活动
     * @param array $goodsIds
     * @param array $optionIds
     * @param int $clientType
     * @param int $isOriginalBuy 是否原价购买 不参与活动
     * @param int $activityId 可以强行选中一个活动执行
     * @return array
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public static function loadActivityProcessorList(array $goodsIds, array $optionIds, int $clientType, int $isOriginalBuy, int $activityId = 0): array
    {
        $activityGroup = $orderLevelTypes = [];

        $activityList = [];
        if (!$isOriginalBuy) {

            // 非内置活动
            //获取当前可用的活动插件
            $goodsActivity = [];
            foreach ($goodsIds as $goodsId) {
                $goodsActivity = GoodsActivityService::getJoinActivityByGoodsId($goodsId, $clientType);
            }

            //强制指定活动执行
            if ($activityId) {
                $activity = ShopMarketingModel::find()->where(['id' => $activityId])->select([
                    'id as activity_id',
                    'type as activity_type'
                ])->first();

                $goodsActivity = array_merge($goodsActivity, [$activity]);
            }

            // 活动
            foreach ($goodsActivity as $item) {
                $activityList[] = [
                    'activity_id' => $item['activity_id'],
                    'type' => $item['activity_type'],
                ];
            }
        }
        //合并非基础活动和基础活动
        $activityList = array_merge(array_unique(array_filter($activityList)), self::$baseActivity);

        //获取优先级排序完成的活动规则
        $rules = self::getRules();

        //根据活动规则排序活动
        foreach ($rules as $type => $rule) {
            $activityGroup[$type] = [];
            foreach ($activityList as $activity) {
                if ($activity['type'] === $type) { //符合
                    $activityGroup[$type][] = $activity;
                }
            }

            // 所有订单级别优惠的标识集合
            if (!empty($rule['is_order_level'])) {
                $orderLevelTypes[] = $type;
            }
        }
        $activityGroup = array_filter($activityGroup);

        // 根据订单级别活动，获取商品活动
        $goodsLevelTypes = array_diff(array_keys($activityGroup), $orderLevelTypes);

        //可用活动
        $allowedActivities = [];
        foreach ($goodsLevelTypes as $goodsLevelType) {
            $allowedActivities[] = $goodsLevelType;
            //获取所有活动的可用的交集
            $activityGroup = ArrayHelper::intercept($activityGroup, array_merge($allowedActivities, $rules[$goodsLevelType]['coexist']));
        }

        $allLevelTypes = array_keys($activityGroup);

        //释放商品级别活动重新获取
        unset($goodsLevelTypes);

        // 现在构建订单级别优惠可能性
        $orderLevelTypes = [];
        $goodsLevelTypes = [];
        foreach ($allLevelTypes as $type) {
            if (empty($rules[$type]['is_order_level'])) {
                $goodsLevelTypes[] = $type;
            } else {
                $orderLevelTypes[] = $type;
            }
        }

        // 获取所有订单活动组合
        $orderActivityCombinations = [];
        foreach ($orderLevelTypes as $index => $type) {
            $orderActivityCombinations[$type] = array_intersect($rules[$type]['coexist'], $orderLevelTypes);
        }

        //开始计算活动互斥
        $newOrderActivityCombinations = [];
        $combinationKeys = array_keys($orderActivityCombinations);
        while ($combinationKeys) {

            //弹出最后一个活动
            $lastActivityType = array_pop($combinationKeys);
            $arrays = [[$lastActivityType]];
            $arraysCopy = $arrays;

            //计算互斥
            foreach ($orderActivityCombinations as $type => &$combination) {
                if (in_array($lastActivityType, $combination)) {
                    foreach ($arrays as $index => &$array) {
                        $isCompatible = self::isCompatible($type, $array);
                        if ($isCompatible === true) {
                            $arraysCopy[$index] = array_merge([$type], $array);
                        } else {
                            $arraysCopy[] = $isCompatible;
                        }
                    }
                    $arrays = $arraysCopy;
                }
            }


            foreach ($arrays as $array) {
                $newOrderActivityCombinations[] = $array;
            }
            prev($orderActivityCombinations);
        }

        foreach ($newOrderActivityCombinations as $index => &$newOrderActivityCombination) {
            $newOrderActivityCombination = array_intersect($orderLevelTypes, $newOrderActivityCombination);
            $sameCount = -1;
            foreach ($newOrderActivityCombinations as $newOrderActivityCombinationItem) {
                $intersect = array_intersect($newOrderActivityCombinationItem, $newOrderActivityCombination);
                if ($intersect === $newOrderActivityCombination) {
                    $sameCount++;
                }
            }
            if ($sameCount > 0) {
                unset($newOrderActivityCombinations[$index]);
            }
        }
        unset($newOrderActivityCombination);

        $goodsLevel = []; // 商品级别优惠

        foreach ($activityGroup as $type => $activitys) {
            foreach ($activitys as $activity) {
                if (!in_array($type, $orderLevelTypes)) {
                    $goodsLevel[] = $activity;
                }
            }
        }

        $orderLevels = []; // 订单级别优惠的所有优惠组合
        foreach ($newOrderActivityCombinations as $index => $newOrderActivityCombination) {
            $arrays = ArrayHelper::intercept($activityGroup, $newOrderActivityCombination);
            foreach ($arrays as $array) {
                $orderLevels[$index] = array_merge($orderLevels[$index] ?: [], $array);
            }
        }

        // 务必保证 $orderLevels 和 $goodsLevel 均为数组，否则下单将异常
        return [$orderLevels ?: [], $goodsLevel ?: []];
    }

    /**
     * 获取优先级规则排序
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRules()
    {
        $sortIds = $rules = [];
        foreach (self::$rules as $type => $rule) {
            $sortIds[$type] = $rule['sequence'];
        }

        $sortIds = array_flip($sortIds);
        ksort($sortIds);
        foreach ($sortIds as $id => $type) {
            foreach (self::$rules as $rule) {
                if ($id === $rule['sequence']) {
                    $rules[$type] = $rule;
                }
            }
        }


        return $rules;
    }

    /**
     * 计算互斥
     * @param array $activity
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCompatibleActivity(array $activity = [])
    {
        //如果只有一个或者没有活动，那么直接返回
        if (count($activity) <= 1) {
            return $activity;
        }


        return $activity;
    }

    /**
     * 检查一个优惠活动与其他一些优惠活动的兼容情况
     * @param string $activityType
     * @param array $activityTypes
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    static function isCompatible(string $activityType, array $activityTypes)
    {
        $rules = self::getRules();
        $return = [];
        foreach ($activityTypes as $type) {
            $yoursCoexsit = $rules[$type]['coexist'];
            $myCoexsit = $rules[$activityType]['coexist'];

            if (in_array($activityType, $yoursCoexsit) || in_array($type, $myCoexsit)) {

            } else {
                // 不可兼容，收集不可兼容类型
                $return[] = $type;
            }
        }
        if (empty($return)) {
            return true;
        }
        $compatible = array_diff($activityTypes, $return);
        return array_merge($compatible, [$activityType]);
    }

}
