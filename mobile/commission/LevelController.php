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

namespace shopstar\mobile\commission;

use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\commission\CommissionLevelException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\order\OrderGoodsModel;

/**
 * 分销等级说明
 * Class LevelController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class LevelController extends CommissionClientApiController
{
    /**
     * 获取等级列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList()
    {
        $memberLevel = CommissionAgentModel::findOne(['member_id' => $this->memberId]);
        $list = CommissionLevelModel::find()
            ->select('id, name, is_default, status')
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->get();
        // 如果等级禁用 且用户不是该等级 则删除
        foreach ($list as $index => $item) {
            if ($item['status'] == 0 && $item['id'] != $memberLevel->level_id) {
                unset($list[$index]);
            }
        }

        return $this->result(['list' => array_values($list), 'member_level_id' => $memberLevel->level_id]);
    }

    /**
     * 获取等级详情
     * @return array|\yii\web\Response
     * @throws CommissionLevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetDetail()
    {
        $levelId = RequestHelper::get('level_id');
        if (empty($levelId)) {
            throw new CommissionLevelException(CommissionLevelException::MEMBER_LEVEL_DELETE_PARAMS_ERROR);
        }
        // 获取等级详情
        $detail = CommissionLevelModel::find()->where(['id' => $levelId])->first();
        if (empty($detail)) {
            throw new CommissionLevelException(CommissionLevelException::MEMBER_LEVEL_DELETE_NOT_EXISTS);
        }
        // 开启层级
        $commissionLevel = CommissionSettings::get('set.commission_level');

        // 所有升级条件
        $condition = [];
        // 选择的商品
        $goodsInfo = [];
        // 非默认等级
        if ($detail['is_default'] == 0) {
            // 分销设置
            $set = CommissionSettings::get('set');

            // 取条件
            $condition = array_intersect_key($detail, CommissionLevelModel::$upgradeCondition);
            $condition = ArrayHelper::arrayFilterEmpty($condition);

            // 判断是否满足升级条件
            // 分销订单总额
            if ($condition['order_money'] != 0) {
                $condition['member_order_money'] = CommissionOrderDataModel::getOrderPrice($this->memberId);
                // 已完成 返回完成状态
                if (bccomp($condition['order_money'], $condition['member_order_money'], 2) <= 0) {
                    $condition['order_money_finish'] = 1;
                }
            }
            // 一级分销总额
            if ($condition['order_money_1'] != 0) {
                $condition['member_order_money_1'] = CommissionOrderDataModel::getOrderPrice($this->memberId, 1);
                // 已完成 返回完成状态
                if (bccomp($condition['order_money_1'], $condition['member_order_money_1'], 2) <= 0) {
                    $condition['order_money_1_finish'] = 1;
                }
            }
            // 分销订单数
            if ($condition['order_count'] != 0) {
                $condition['member_order_count'] = CommissionOrderDataModel::getOrderCount($this->memberId);
                // 已完成 返回完成状态
                if (bccomp($condition['order_count'], $condition['member_order_count'], 2) <= 0) {
                    $condition['order_count_finish'] = 1;
                }
            }
            // 一级分销订单数
            if ($condition['order_count_1'] != 0) {
                $condition['member_order_count_1'] = CommissionOrderDataModel::getOrderCount($this->memberId);
                // 已完成 返回完成状态
                if (bccomp($condition['order_count_1'], $condition['member_order_count_1'], 2) <= 0) {
                    $condition['order_count_1_finish'] = 1;
                }
            }
            // 自购订单金额
            if ($condition['self_order_money'] != 0) {
                $condition['member_self_order_money'] = CommissionOrderModel::getOrderPrice($this->memberId, $this->agent['become_time']);
                // 已完成 返回完成状态
                if (bccomp($condition['self_order_money'], $condition['member_self_order_money']) <= 0) {
                    $condition['self_order_money_finish'] = 1;
                }
            }
            // 自购订单数
            if ($condition['self_order_count'] != 0) {
                $condition['member_self_order_count'] = CommissionOrderModel::getOrderCount($this->memberId, $this->agent['become_time']);
                // 已完成 返回完成状态
                if (bccomp($condition['self_order_count'], $condition['member_self_order_count']) <= 0) {
                    $condition['self_order_count_finish'] = 1;
                }
            }
            // 下线总数
            if ($condition['child_count'] != 0) {
                $condition['member_child_count'] = CommissionAgentModel::getChildTotal($this->memberId, 0, null, $set['commission_level']);
                // 已完成 返回完成状态
                if (bccomp($condition['child_count'], $condition['member_child_count']) <= 0) {
                    $condition['child_count_finish'] = 1;
                }
            }
            // 一级下线总数
            if ($condition['child_count_1'] != 0) {
                $condition['member_child_count_1'] = CommissionAgentModel::getChildTotal($this->memberId, 1);
                // 已完成 返回完成状态
                if (bccomp($condition['child_count_1'], $condition['member_child_count_1']) <= 0) {
                    $condition['child_count_1_finish'] = 1;
                }
            }
            // 下级分销商总人数
            if ($condition['child_agent_count'] != 0) {
                $condition['member_child_agent_count'] = CommissionAgentModel::getChildTotal($this->memberId, 0, true, $set['commission_level']);
                // 已完成 返回完成状态
                if (bccomp($condition['child_agent_count'], $condition['member_child_agent_count']) <= 0) {
                    $condition['child_agent_count_finish'] = 1;
                }
            }
            // 一级分销商人数
            if ($condition['child_agent_count_1'] != 0) {
                $condition['member_child_agent_count_1'] = CommissionAgentModel::getChildTotal($this->memberId, 1, true);
                // 已完成 返回完成状态
                if (bccomp($condition['child_agent_count_1'], $condition['member_child_agent_count_1']) <= 0) {
                    $condition['child_agent_count_1_finish'] = 1;
                }
            }
            // 已提现佣金总金额
            if ($condition['withdraw_money'] != 0) {
                $condition['member_withdraw_money'] = CommissionApplyModel::getMemberApplyCommission($this->memberId);
                // 已完成 返回完成状态
                if (bccomp($condition['withdraw_money'], $condition['member_withdraw_money']) <= 0) {
                    $condition['withdraw_money_finish'] = 1;
                }
            }
            // 购买商品
            $goodsInfo = [];
            if (!empty($condition['goods_ids'])) {
                // 条件中设置的商品
                // 获取用户已购买的商品信息 成为分销商后的订单
                $goodsInfo['goods_ids'] = explode(',', $condition['goods_ids']);
                $goodsInfo['member_goods_ids'] = OrderGoodsModel::getMemberOrderGoodsIds($this->memberId, OrderStatusConstant::ORDER_STATUS_SUCCESS, $goodsInfo['goods_ids'], $this->agent['become_time']);
            }
        }

        return $this->result(['detail' => $detail, 'condition' => $condition, 'goods_info' => $goodsInfo, 'commission_level' => $commissionLevel]);
    }

}
