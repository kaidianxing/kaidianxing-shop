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

use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\groups\GroupsCrewModel;
use shopstar\models\groups\GroupsGoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;

/**
 * 拼团服务类
 * Class GroupsCrewService
 * @package shopstar\services\groups
 * @author likexin
 */
class GroupsCrewService
{

    /**
     * 根据团队ID获取团员以及订单
     * @param int $teamId
     * @return array|\yii\db\ActiveRecord[]
     * @author likexin
     */
    public static function getTeamMemberAndOrderByTeamId(int $teamId): array
    {
        $groupsMember = GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(MemberModel::tableName() . 'as member', 'member.id = crew.member_id')
            ->select([
                'crew.is_leader',
                'crew.order_id',
                'member.nickname',
                'member.avatar',
                'member.mobile',
            ])
            ->where([
                'crew.team_id' => $teamId,
                'crew.is_valid' => 1,
            ])
            ->get();

        if (empty($groupsMember)) {
            return error('未查询到团员');
        }

        $orderId = array_column($groupsMember, 'order_id');

        $orderInfo = OrderModel::find()
            ->alias('order')
            ->leftJoin(OrderGoodsModel::tableName() . 'as order_goods', 'order_goods.order_id = order.id')
            ->select([
                'order.id',
                'order.status',
                'order.created_at',
                'order.order_no',
                'order.pay_type',
                'order.dispatch_price',
                'order.create_from',
                'order.dispatch_type',
                'order_goods.title',
                'order_goods.price_unit',
                'order_goods.total',
                'order_goods.price',
                'order_goods.option_title',
                'order_goods.thumb',
            ])
            ->where([
                'order.id' => $orderId
            ])
            ->indexBy('id')
            ->get();

        if (empty($orderInfo)) {
            return error('未查询到团员订单');
        }

        foreach ($groupsMember as $key => &$info) {
            if (empty($orderInfo[$info['order_id']])) {
                continue;
            }
            $info = array_merge($info, $orderInfo[$info['order_id']]);
        }
        unset($info);

        return $groupsMember;
    }

    /**
     * 获取团队下商品详情基本信息
     * @param int $teamId
     * @param int $activityId
     * @return array|null
     * @author likexin
     */
    public static function getGoodsInfoByTeam(int $teamId, int $activityId): ?array
    {
        $goodsInfo = GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(OrderGoodsModel::tableName() . 'as order_goods', 'order_goods.order_id = crew.order_id')
            ->where([
                'crew.team_id' => $teamId,
            ])
            ->select([
                'order_goods.goods_id as id',
                'order_goods.title',
                'order_goods.thumb',
            ])
            ->first();

        // 需要商品原价
        $goods = GoodsModel::find()
            ->where([
                'id' => $goodsInfo['id'],
            ])
            ->select([
                'min_price',
                'has_option',
                'sub_name',
            ])
            ->first();

        $goodsInfo['original_price'] = $goods['min_price'];
        $goodsInfo['has_option'] = $goods['has_option'];
        $goodsInfo['sub_name'] = $goods['sub_name'];

        // 查询活动商品
        $activityGoods = ShopMarketingGoodsMapModel::find()
            ->where([
                'activity_id' => $activityId,
                'goods_id' => $goodsInfo['id'],
            ])
            ->indexBy('option_id')
            ->get();

        // 多规格
        if ($goods['has_option']) {
            // 查询规格
            $goodsOptions = GoodsOptionModel::find()
                ->where([
                    'goods_id' => $goodsInfo['id'],
                ])
                ->select('id, stock')
                ->indexBy('id')
                ->get();

            $activityStock = 0;
            foreach ($activityGoods as $optionId => &$item) {
                if ($item['is_join']) {
                    // 哪个库存小用哪个
                    if ($goodsOptions[$optionId]['stock'] < $item['activity_stock']) {
                        $item['activity_stock'] = $goodsOptions[$optionId]['stock'];
                    }
                    $activityStock += $item['activity_stock'];
                }
            }
            unset($item);
            $goodsInfo['activity_stock'] = $activityStock;
        } else {
            // 商品库存小 用商品库存
            if (!empty($options['stock']) && $activityGoods[0]['activity_stock'] > $options['stock']) {
                $activityGoods[0]['activity_stock'] = $options['goods_stock'];
            }
            $goodsInfo['activity_stock'] = $activityGoods[0]['activity_stock'];
        }

        // 获取最低价格
        $priceRange = GroupsGoodsModel::calculateLadderPrice($activityId, $goodsInfo['id']);

        if ($priceRange['has_range']) {
            $goodsInfo['price_range']['min_price'] = $priceRange['min_price'];
            $goodsInfo['price_range']['max_price'] = $priceRange['max_price'];
        } else {
            $goodsInfo['activity_price'] = $priceRange['activity_price'];
        }

        return $goodsInfo;
    }

    /**
     * 根据团队ID获取参与人
     * @param int|array $teamId
     * @return array|\yii\db\ActiveRecord[]
     * @author likexin
     */
    public static function getCrewByTeamId($teamId): array
    {
        return GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(MemberModel::tableName() . 'member', 'member.id = crew.member_id')
            ->select([
                'crew.id',
                'crew.created_at',
                'crew.order_id',
                'crew.team_id',
                'member.id as member_id',
                'member.nickname',
                'member.avatar',
            ])
            ->where([
                'crew.team_id' => $teamId,
                'crew.is_valid' => 1,
            ])
            ->get();
    }

    /**
     * 保存参团人 可兼容生成虚拟人数
     * @param int $orderId
     * @param int $teamId
     * @param int $memberId
     * @param int $goodsId
     * @param int $isLeader
     * @param int $virtualNum
     * @return mixed
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function saveCrewInfo(int $orderId, int $teamId, int $memberId, int $goodsId, int $isLeader, int $virtualNum = 0)
    {
        // 预定义数据
        $data = [];

        // 获取时间
        $date = DateTimeHelper::now();

        // 判断是否是虚拟
        if (!$virtualNum) {

            // 如果是真实人，需要判断是否参加过该团队
            $exists = GroupsCrewModel::find()
                ->where([
                    'team_id' => $teamId,
                    'member_id' => $memberId,
                    'is_valid' => 1
                ])
                ->select(['id'])
                ->first();

            if ($exists) {
                return error('已参与过该团队');
            }

            $data[] = [
                'team_id' => $teamId,
                'member_id' => $memberId,
                'is_leader' => $isLeader,
                'created_at' => $date,
                'order_id' => $orderId,
                'goods_id' => $goodsId
            ];
        } else {

            // 获取虚拟的会员
            $virtualMemberId = MemberModel::getRandMember($virtualNum);

            // 循环造数据
            foreach ($virtualMemberId as $index => $item) {
                $data[] = [
                    'team_id' => $teamId,
                    'member_id' => $item,
                    'is_leader' => 0, //是否团长 虚拟成团强制0
                    'created_at' => $date
                ];
            }
        }

        // 批量入库并返回
        return GroupsCrewModel::batchInsert(array_keys(current($data)), $data);
    }

}