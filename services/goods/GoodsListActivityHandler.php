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


use apps\groups\models\GroupsGoodsModel;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionGoodsModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsMemberLevelDiscountModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;

class GoodsListActivityHandler
{
    /**
     * 商品id
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $goodsInfo = [];

    /**
     * 商品信息
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $activityInfo = [];

    /**
     * 会员信息
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $memberInfo = [];

    /**
     * @var array 商品参加活动信息
     */
    private $goodsActivity = [];

    /**
     * @var int 客户端类型
     */
    private $clientType = 0;

    /**
     * @var int 活动id
     */
    private $activityId = 0;

    public function __construct(array $goodsInfo, int $memberId, $clientType, int $activityId = 0)
    {
        $this->goodsInfo = $goodsInfo;
        $this->clientType = $clientType;
        $this->memberInfo = MemberModel::findOne($memberId);
        $this->activityId = $activityId;

        $goodsIds = array_keys($goodsInfo);
        $this->goodsActivity = GoodsActivityModel::find()
            ->where(['goods_id' => $goodsIds, 'is_delete_activity' => 0])
            ->andWhere(['<', 'start_time', DateTimeHelper::now()])
            ->andWhere(['>', 'end_time', DateTimeHelper::now()])
            ->indexBy('goods_id')
            ->get();
    }

    /**
     * 初始化
     * @param array $goodsInfo
     * @param int $memberId
     * @param int $clientType
     * @param int $activityId
     * @return GoodsListActivityHandler
     * @author 青岛开店星信息技术有限公司
     */
    public static function init(array $goodsInfo, int $memberId, int $clientType, int $activityId = 0)
    {
        return new self($goodsInfo, $memberId, $clientType, $activityId);
    }

    /**
     * 设置活动
     * @param array $activities
     * @param string $activityType
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function setActivity(array $activities, string $activityType)
    {
        foreach ($activities as $goodsId => $activity) {
            if (!empty($activity) || $activity == 0) {
                $this->activityInfo[$goodsId][$activityType] = $activity;
                continue;
            }
        }

        return true;
    }

    /**
     * 获取商品
     * @param string $goodsId
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function getActivity($goodsId = 'all')
    {
        if ($goodsId == 'all') {
            return $this->activityInfo;
        }

        return $this->activityInfo[$goodsId];
    }

    /**
     * 自动化执行，活动执行方法可以单独调用也可以执行自动化
     * @author 青岛开店星信息技术有限公司
     */
    public function automation()
    {

        $this->seckill();

        /**
         * 查询预热活动
         */
        $this->preheat();

        if (empty($this->goodsInfo) || empty($this->memberInfo)) return true;

        /**
         * 会员价
         */
        $this->memberPrice();


        /**
         * 佣金
         */
        $this->commission();

        return true;
    }

    /**
     * 计算会员价
     * @author 青岛开店星信息技术有限公司
     */
    public function memberPrice()
    {
        //开启折扣的商品映射
        $goodsMemberPriceTypeMap = [];
        foreach ($this->goodsInfo as $goodsIndex => $goodsItem) {
            // 有预售 不执行会员价
            if ($this->goodsActivity[$goodsIndex]['activity_type'] == 'presell') {
                continue;
            }
            if ($goodsItem['member_level_discount_type'] > 0) {
                $goodsMemberPriceTypeMap[$goodsItem['member_level_discount_type']][] = [
                    'goods_id' => $goodsItem['id'],//商品id
                    'price' => $goodsItem['price']//商品价格
                ];
            }
        }

        if (empty($goodsMemberPriceTypeMap)) return;

        //获取用户会员等级
        $memberLevel = MemberLevelModel::findOne(['id' => $this->memberInfo['level_id'], 'state' => 1]);
        if (empty($memberLevel)) return;

        //会员价存储
        $goodsDiscountPrice = [];

        //如果存在是默认会员等级折扣的商品
        !empty($goodsMemberPriceTypeMap['1']) && $this->defaultMemberLevelDiscount($goodsMemberPriceTypeMap['1'], $goodsDiscountPrice);

        //指定会员等级
        !empty($goodsMemberPriceTypeMap['2']) && $this->designatedMembershipLevel($goodsMemberPriceTypeMap['2'], $goodsDiscountPrice);

        //规格商品指定会员等级
        !empty($goodsMemberPriceTypeMap['3']) && $this->optionDesignatedMembershipLevel($goodsMemberPriceTypeMap['3'], $goodsDiscountPrice);

        //设置活动
        $this->setActivity($goodsDiscountPrice, 'member_price');
    }

    /**
     * 默认会员价格
     * @param array $goodsMemberPriceTypeMap
     * @param array $memberLevel
     * @param array $goodsDiscountPrice
     * @author 青岛开店星信息技术有限公司
     */
    private function defaultMemberLevelDiscount(array $goodsMemberPriceTypeMap, array &$goodsDiscountPrice)
    {
        //获取用户会员等级
        $memberLevel = MemberLevelModel::findOne(['id' => $this->memberInfo['level_id'], 'state' => 1]);

        if (empty($memberLevel)) return;
        $memberLevel = $memberLevel->toArray();

        //如果没有会员折扣则返回
        if (empty($memberLevel['is_discount']) || empty($memberLevel['discount'])) return;


        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {
            //默认会员等级折扣没有 固定金额
            $goodsDiscountPrice[$goodsMemberPriceTypeMapItem['goods_id']] = round2($goodsMemberPriceTypeMapItem['price'] * ($memberLevel['discount'] / 10));
        }

        return;
    }

    /**
     * 指定会员等级
     * @param array $goodsMemberPriceTypeMap
     * @param array $memberLevel
     * @param array $goodsDiscountPrice
     * @author 青岛开店星信息技术有限公司
     */
    private function designatedMembershipLevel(array $goodsMemberPriceTypeMap, array &$goodsDiscountPrice)
    {
        $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
            'goods_id' => array_column($goodsMemberPriceTypeMap, 'goods_id'), //会员折扣类型是 2 = 指定会员等级
            'level_id' => $this->memberInfo['level_id'],
            'option_id' => 0
        ])->indexBy('goods_id')->asArray()->all();

        if (empty($goodsLevelDiscountMap)) return;

        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {
            //如果当前商品没有会员折扣 或者折扣为0 则跳过
            if (empty($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]) || empty($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'])) continue;

            if ($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['type'] == 1) {
                $price = $goodsMemberPriceTypeMapItem['price'] * ($goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'] / 10);
            } else {
                $price = $goodsLevelDiscountMap[$goodsMemberPriceTypeMapItem['goods_id']]['discount'];
            }

            $goodsDiscountPrice[$goodsMemberPriceTypeMapItem['goods_id']] = round2($price);
        }
    }

    /**
     * 多规格指定会员等级
     * @param $goodsMemberPriceTypeMap
     * @param $goodsDiscountPrice
     * @author 青岛开店星信息技术有限公司
     */
    private function optionDesignatedMembershipLevel($goodsMemberPriceTypeMap, &$goodsDiscountPrice)
    {
        $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
            'and',
            ['goods_id' => array_column($goodsMemberPriceTypeMap, 'goods_id')], //会员折扣类型是 2 = 指定会员等级
            ['level_id' => $this->memberInfo['level_id']],
            ['!=', 'option_id', 0],
        ])->asArray()->all();

        if (empty($goodsLevelDiscountMap)) return;

        foreach ((array)$goodsMemberPriceTypeMap as $goodsMemberPriceTypeMapIndex => $goodsMemberPriceTypeMapItem) {

            $discountPrice = [];
            foreach ($goodsLevelDiscountMap as $goodsLevelDiscountMapIndex => $goodsLevelDiscountMapItem) {
                //如果当前商品没有会员折扣则跳过

                if ($goodsLevelDiscountMapItem['goods_id'] != $goodsMemberPriceTypeMapItem['goods_id'] || empty($goodsLevelDiscountMapItem['discount'])) continue;

                if ($goodsLevelDiscountMapItem['type'] == 1) {
                    $price = round2($goodsMemberPriceTypeMapItem['price'] * ($goodsLevelDiscountMapItem['discount'] / 10));
                } else {
                    $price = $goodsLevelDiscountMapItem['discount'];
                }

                $discountPrice[] = (float)$price;
            }

            $discountPrice = array_filter($discountPrice);
            $goodsDiscountPrice[$goodsMemberPriceTypeMapItem['goods_id']] = round2($discountPrice ? min($discountPrice) : 0);
        }

        return;
    }

    /**
     * 秒杀信息
     * @author 青岛开店星信息技术有限公司
     */
    public function seckill()
    {
        $seckill = [];
        foreach ($this->goodsActivity as $goodsId => $item) {
            if ($item['activity_type'] == 'seckill') {
                // 获取包含商品的活动
                $activity = ShopMarketingModel::getActivityInfo($goodsId, $this->clientType, 'seckill', $this->goodsInfo[$goodsId]['has_option'], ['activity_id' => $this->activityId]);
                if (!is_error($activity)) {
                    $seckill[$goodsId] = $activity;
                }
            }
        }
        $this->setActivity($seckill, 'seckill');

        return true;
    }

    /**
     * 拼团
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    public function groups()
    {
        $groups = [];

        foreach ($this->goodsActivity as $goodsId => $item) {
            if ($item['activity_type'] == 'groups') {

                // 获取包含商品的活动
                $activity = ShopMarketingModel::getActivityInfo($goodsId, $this->clientType, 'groups', $this->goodsInfo[$goodsId]['has_option'], ['activity_id' => $this->activityId]);
                $activity['ladder_info'] = $activity['rules'];
                if (!is_error($activity)) {

                    $activity['goods_info'] = GroupsGoodsModel::getGoodsOptionInfo($activity['id'], $activity['goods_ids']);

                    //如果是阶梯团，需要再计算一遍最低价最高价
                    if ($activity['inner_type'] || $this->goodsInfo['has_option']) {
                        $priceRange = GroupsGoodsModel::calculateLadderPrice($activity['id'], $item['goods_id']);

                        if ($priceRange['has_range']) {
                            $activity['price_range']['min_price'] = $priceRange['min_price'];
                            $activity['price_range']['max_price'] = $priceRange['max_price'];
                        } else {
                            $activity['activity_price'] = $priceRange['activity_price'];
                        }
                    }

                    $groups[$goodsId] = $activity;
                }
            }
        }

        $this->setActivity($groups, 'groups');

        return true;
    }

    /**
     * 预热活动
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function preheat()
    {
        $preheatActivity = [];
        foreach ($this->goodsInfo as $goods) {
            $activity = GoodsActivityService::getPreheatActivity($goods['id'], $goods['has_option'], $this->clientType);
            if (!empty($activity)) {
                $preheatActivity[$goods['id']] = $activity;
            }
        }
        $this->setActivity($preheatActivity, 'preheat_activity');

        return true;
    }

    /**
     * 预计佣金
     * @author 青岛开店星信息技术有限公司
     */
    public function commission()
    {
        // 获取当前会员分销等级
        $agent = CommissionAgentModel::find()->select('status, level_id')->where(['member_id' => $this->memberInfo['id']])->first();
        // 非分销商则返回
        if (empty($agent) || $agent['status'] != 1) {
            return error('该会员非分销商');
        }
        // 获取是否开启佣金显示
        $set = CommissionSettings::get('set.show_commission');
        if ($set != 1) {
            return error('未开启佣金显示');
        }
        $commission = [];
        foreach ($this->goodsInfo as $goods) {
            // 判断该商品是否参与分销
            if ($goods['is_commission'] != 1) {
                continue;
            }
            $money = CommissionGoodsModel::getMaxCommission($goods['id'], $goods['type'], $goods['has_option'], $agent['level_id'], $this->clientType);
            if (!empty($money)) {
                $commission[$goods['id']] = $money;
            }
        }
        $this->setActivity($commission, 'commission');

        return true;
    }

}
