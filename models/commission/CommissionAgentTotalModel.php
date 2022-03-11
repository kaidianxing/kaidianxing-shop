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

namespace shopstar\models\commission;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\order\OrderGoodsModel;

/**
 * 分销商数据统计
 * Class CommissionAgentTotalModel
 * @package shopstar\models\commission
 */
class CommissionAgentTotalModel extends CommissionAgentModel
{

    /**
     * 获取提现佣金
     * @param int $memberId 会员ID
     * @param string $sumSelect sum字段
     * @return float
     * @author likexin
     */
    public static function getCanWithdrawPrice(int $memberId, $sumSelect = 'order_goods.can_withdraw_commission')
    {
        $where = [
            'and',
            [
                'order_goods.agent_id' => $memberId,
                'order_goods.is_count_refund' => 1,
            ],
            ['<=', 'order.account_time', DateTimeHelper::now()],
            ['<>', 'order.account_time', 0],
            ['>', 'order_goods.can_withdraw_commission', 0],
        ];

        // 查询订单可以先佣金
        return (float)CommissionOrderGoodsModel::find()
            ->alias('order_goods')
            ->leftJoin(OrderGoodsModel::tableName() . ' as shop_order_goods', 'shop_order_goods.id = order_goods.order_goods_id and shop_order_goods.shop_goods_id=0')
            ->leftJoin(CommissionOrderModel::tableName() . ' as order', 'order.order_id = order_goods.order_id')
            ->where($where)
            ->sum($sumSelect);
    }

    /**
     * 获取无效佣金
     * @param int $memberId 会员ID
     * @return float
     * @author likexin
     */
    public static function getInvalidPrice(int $memberId)
    {
        return (float)CommissionApplyModel::find()
            ->where([
                'member_id' => $memberId,
                'status' => [30, 31],
            ])
            ->sum('apply_commission');
    }

    /**
     * 获取待审核佣金
     * @param int $memberId 会员ID
     * @return float
     * @author likexin
     */
    public static function getWaitCheckPrice(int $memberId)
    {
        return (float)CommissionApplyModel::find()
            ->where([
                'member_id' => $memberId,
                'status' => 0,
            ])
            ->sum('apply_commission');
    }

    /**
     * 获取待打款佣金
     * @param int $memberId 会员ID
     * @return float
     * @author likexin
     */
    public static function getWaitRemitPrice(int $memberId)
    {
        return (float)CommissionApplyModel::find()
            ->where([
                'member_id' => $memberId,
                'status' => 10,
            ])
            ->sum('final_commission');
    }

    /**
     * 获取待入账(未结算)佣金
     * @param int $memberId
     * @param string $sumSelect sun字段
     * @return float
     * @author likexin
     */
    public static function getWaitSettlementPrice(int $memberId, $sumSelect = 'order_goods.commission')
    {
        $where = [
            'and',
            ['order_goods.agent_id' => $memberId],
            ['order_goods.is_count_refund' => 1],
            [
                'or',
                ['>', 'order.account_time', DateTimeHelper::now()],
                ['order.account_time' => 0],
            ],
            ['order_goods.status' => 0],
        ];

        return (float)CommissionOrderGoodsModel::find()
            ->alias('order_goods')
            ->leftJoin(CommissionOrderModel::tableName() . ' as order', 'order.order_id = order_goods.order_id')
            ->andWhere($where)
            ->sum($sumSelect);
    }

    /**
     * 获取提现数量
     * @param int $memberId
     * @return int|string
     * @author likexin
     */
    public static function getApplyTotal(int $memberId)
    {
        return CommissionApplyModel::find()
            ->where([
                'member_id' => $memberId,
            ])
            ->count();
    }

    /**
     * 获取分销商统计信息
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTotalInfo()
    {
        $data = [
            'agent_count' => 0,
            'wait_agent_count' => 0,
            'wait_audit_commission' => 0,
            'wait_pay_commission' => 0,
        ];
        // 分销商总人数
        $data['agent_count'] = CommissionAgentModel::find()->where(['status' => 1, 'is_deleted' => 0])->count();
        $data['wait_agent_count'] = CommissionAgentModel::find()->where(['status' => 0, 'is_deleted' => 0])->count();

        return $data;
    }

    /**
     * 获取用户累计佣金
     * @param int $memberId
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberCommission(int $memberId)
    {
        $total = CommissionOrderDataModel::find()
            ->where(['agent_id' => $memberId, 'is_count_refund' => 1])
            ->sum('commission');
        return $total ?? '0.00';
    }

    /**
     * 更新分销商下级数量
     * 审核通过/取消分销商资格/修改上级分销商/手动设置分销商/删除会员/自动成为分销商
     * @param int $memberId
     * @param array $oldAgent 旧的上级
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateAgentChildCount(int $memberId, array $oldAgent = [])
    {
        if (empty($memberId)) {
            return error('参数错误');
        }
        // 要更新的会员id(该用户所有上级)
        $updateIds = [];

        // 获取系统设置
        $set = CommissionSettings::get('set');
        // 一级上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        if (!empty($parentId)) {
            $updateIds[] = $parentId;
        }
        // 二级上级
        if ($set['commission_level'] > 1 && !empty($parentId)) {
            $parent2Id = CommissionRelationModel::getParentId($memberId, 2);
            if (!empty($parent2Id)) {
                $updateIds[] = $parent2Id;
            }
        }
        // 三级上级
        if ($set['commission_level'] > 2 && !empty($parent2Id)) {
            $parent3Id = CommissionRelationModel::getParentId($memberId, 3);
            if (!empty($parent3Id)) {
                $updateIds[] = $parent3Id;
            }
        }
        // 合并旧上级
        if (!empty($oldAgent)) {
            $updateIds = array_merge($updateIds, $oldAgent);
        }

        // 重新计算各级下级分销商数量
        foreach ($updateIds as $id) {
            $count = CommissionAgentTotalModel::getChildTotal($id, 0, true, $set['commission_level']);
            CommissionAgentModel::updateAll(['commission_child' => $count], ['member_id' => $id]);
        }

        return true;
    }

    /**
     * 更新旧的上级的下级数量
     * 手动修改上级的情况
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOldAgentChildCount(int $memberId)
    {
        if (empty($memberId)) {
            return error('参数错误');
        }
        // 要更新的会员id(该用户所有上级)
        $updateIds = [];

        // 获取系统设置
        $set = CommissionSettings::get('set');
        // 一级上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        if (!empty($parentId)) {
            $updateIds[] = $parentId;
        }
        // 二级上级
        if ($set['commission_level'] > 1 && !empty($parentId)) {
            $parent2Id = CommissionRelationModel::getParentId($memberId, 2);
            if (!empty($parent2Id)) {
                $updateIds[] = $parent2Id;
            }
        }
        // 三级上级
        if ($set['commission_level'] > 2 && !empty($parent2Id)) {
            $parent3Id = CommissionRelationModel::getParentId($memberId, 3);
            if (!empty($parent3Id)) {
                $updateIds[] = $parent3Id;
            }
        }

        return $updateIds;
    }

}