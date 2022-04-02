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

namespace shopstar\services\commission;

use shopstar\bases\service\BaseService;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\OrderConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\order\OrderGoodsModel;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CommissionLevelService extends BaseService
{

    /**
     * 分销商升级方法
     * @param int $memberId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function agentUpgrade(int $memberId)
    {
        if (empty($memberId)) {
            return error('参数错误');
        }
        // 要升级的会员id
        $upgradeIds = [];

        if (CommissionAgentModel::isAutoUpgrade($memberId)) {
            // 获取该用户是否可自动升级
            $upgradeIds[] = $memberId;
        }
        // 获取系统设置
        $set = CommissionSettings::get('set');
        // 一级上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        if (!empty($parentId) && CommissionAgentModel::isAutoUpgrade($parentId)) {
            $upgradeIds[] = $parentId;
        }
        // 二级上级
        if ($set['commission_level'] > 1 && !empty($parentId)) {
            $parent2Id = CommissionRelationModel::getParentId($memberId, 2);
            if (!empty($parent2Id) && CommissionAgentModel::isAutoUpgrade($parent2Id)) {
                $upgradeIds[] = $parent2Id;
            }
        }
        // 升级
        foreach ($upgradeIds as $id) {
            self::upgrade($id);
        }
        return true;
    }

    /**
     * 分销商升级
     * @param int $memberId
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function upgrade(int $memberId)
    {
        // 分销设置
        $set = CommissionSettings::get('set');

        // 获取分销商信息
        $agent = CommissionAgentModel::findOne(['member_id' => $memberId]);
        if (empty($agent)) {
            return error('分销商不存在');
        }
        if (!empty($agent->not_auto_update)) {
            return error('该分销商不能自动升级');
        }

        // 获取当前等级
        $currentLevel = CommissionLevelModel::find()->select('id, level, name')->where(['id' => $agent->level_id])->first();
        if (empty($currentLevel)) {
            return error('等级错误');
        }

        // 获取可升级的等级
        $canUpgradeLevel = CommissionLevelModel::find()
            ->where([
                'and',
                ['>', 'level', $currentLevel['level']],
                ['status' => 1]
            ])
            ->orderBy(['level' => SORT_DESC])
            ->get();

        if (empty($canUpgradeLevel)) {
            return error('当前已是最高等级');
        }

        // 降级时间
        // @change 倪增超 产品要求的新的逻辑: 有过降级, 则所有的升级条件, 需要查询降级之后的数据
        $degradeTime = '';


        // 获取所有可升级等级的条件的并集
        $conditionMerge = [];
        foreach ($canUpgradeLevel as $value) {
            // 获取升级条件
            $levelCondition = array_intersect_key($value, CommissionLevelModel::$upgradeCondition);
            $conditionMerge = array_merge($conditionMerge, ArrayHelper::arrayFilterEmpty($levelCondition));
        }

        // 根据升级条件查询出当前已经满足的条件
        $selfConditionValue = [];
        foreach ($conditionMerge as $condition => $value) {
            switch ($condition) {
                case 'order_money': // 分销订单总额
                    $selfConditionValue['order_money'] = CommissionOrderDataModel::getOrderPrice($memberId, 0, $degradeTime);
                    break;
                case 'order_money_1': // 一级分销订单金额
                    $selfConditionValue['order_money_1'] = CommissionOrderDataModel::getOrderPrice($memberId, 1, $degradeTime);
                    break;
                case 'order_count': // 分销订单总数
                    $selfConditionValue['order_count'] = CommissionOrderDataModel::getOrderCount($memberId, 0, OrderConstant::ORDER_STATUS_SUCCESS, $degradeTime);
                    break;
                case 'order_count_1': // 一级分销订单总数
                    $selfConditionValue['order_count_1'] = CommissionOrderDataModel::getOrderCount($memberId, 1, OrderConstant::ORDER_STATUS_SUCCESS, $degradeTime);
                    break;
                case 'self_order_money': // 自购订单金额
                    $selfConditionValue['self_order_money'] = CommissionOrderModel::getOrderPrice($memberId, $agent->become_time, $degradeTime);
                    break;
                case 'self_order_count': // 自购订单数量
                    $selfConditionValue['self_order_count'] = CommissionOrderModel::getOrderCount($memberId, $agent->become_time, $degradeTime);
                    break;
                case 'child_count': // 下线总人数
                    $selfConditionValue['child_count'] = CommissionAgentModel::getChildTotal($memberId, 0, null, $set['commission_level'], $degradeTime);
                    break;
                case 'child_count_1': // 一级下线人数
                    $selfConditionValue['child_count_1'] = CommissionAgentModel::getChildTotal($memberId, 1, null, null, $degradeTime);
                    break;
                case 'child_agent_count': // 下级分销商总人数
                    $selfConditionValue['child_agent_count'] = CommissionAgentModel::getChildTotal($memberId, 0, true, $set['commission_level'], $degradeTime);
                    break;
                case 'child_agent_count_1': // 一级分销商人数
                    $selfConditionValue['child_agent_count_1'] = CommissionAgentModel::getChildTotal($memberId, 1, true, $set['commission_level'], $degradeTime);
                    break;
                case 'withdraw_money': // 已提现佣金总金额
                    $selfConditionValue['withdraw_money'] = CommissionApplyModel::getMemberApplyCommission($memberId, $degradeTime);
                    break;
                case 'goods_ids': // 购买商品
                    $selfConditionValue['goods_ids'] = OrderGoodsModel::getMemberOrderGoodsIds($memberId, OrderStatusConstant::ORDER_STATUS_SUCCESS, [], $agent->become_time, $degradeTime);
                    break;
            }
        }
        unset($value);

        // 遍历获取满足升级条件的
        $newLevel = [];
        foreach ($canUpgradeLevel as $level) {

            // 满足升级条件的个数
            $meetCount = 0;
            // 需要满足的升级条件个数
            $needCount = 0;
            foreach ($conditionMerge as $condition => $value) {
                // 如果为空则不统计
                if (empty($level[$condition]) || $level[$condition] == 0) {
                    continue;
                }
                // 需要满足条件个数 加一
                $needCount++;
                // 不是商品条件  比较大小 不满足跳出
                if ($condition != 'goods_ids' && $level[$condition] > $selfConditionValue[$condition]) {
                    continue;
                } else if ($condition == 'goods_ids') {
                    // 商品是否满足
                    $isExists = array_intersect(explode(',', $level['goods_ids']), $selfConditionValue['goods_ids']);
                    if (empty($isExists)) {
                        continue;
                    }
                }
                // 满足条件增加计数
                $meetCount++;
            }

            if ($level['upgrade_type'] == 1) {
                // 多项升级条件全部满足才可升级
                if ($meetCount == $needCount) {
                    $newLevel = $level;
                    break;
                }
            } else {
                // 多项升级条件满足一条即可升级
                if ($meetCount > 0) {
                    $newLevel = $level;
                    break;
                }
            }
        }

        if (empty($newLevel)) {
            return error('不满足升级条件');
        }

        // 处理升级
        $agent->level_id = $newLevel['id'];
        if ($agent->save() === false) {
            return error('升级失败');
        }

        // 发送通知  买家成为分销商
        $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_UPGRADE, [
            'old_commission_level' => $currentLevel['name'],
            'new_commission_level' => $newLevel['name'],
            'change_time' => DateTimeHelper::now(),
        ], 'commission');
        if (!is_error($result)) {
            $result->sendMessage($memberId);
        }

        return true;
    }


}