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

use shopstar\constants\OrderConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\mobile\commission\CommissionClientApiController;
use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\exceptions\commission\CommissionAgentException;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;

/**
 * 我的下线
 * Class DownLineController
 * @package apps\commission\client
 */
class DownLineController extends CommissionClientApiController
{
    /**
     * 获取每个分销级别的用户数量
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCount()
    {
        // 分销层级设置
        $setLevel = CommissionSettings::get('set.commission_level');
        $childCount = CommissionAgentModel::getChildCountInfo($this->memberId, $setLevel);

        return $this->result(['level' => $setLevel, 'count' => $childCount]);
    }

    /**
     * 获取列表
     * @return array|\yii\web\Response
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList()
    {
        $level = RequestHelper::get('level', 1);
        // 分销层级设置
        $setLevel = CommissionSettings::get('set.commission_level');
        // 层级错误
        if ($setLevel < $level) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_DOWN_LINE_LEVEL_ERROR);
        }
        $select = [
            'member.nickname', // 用户名
            'member.avatar', // 头像
            'agent.become_time', // 成为分销商时间
            'relation.level', // 分销层级
            'member.id member_id',
            'agent.status',
            'relation.child_time', // 成为下线时间
            'agent.commission_total', // 累计佣金
        ];

        $leftJoins = [
            [MemberModel::tableName() . ' member', 'member.id = relation.member_id'],
            [CommissionAgentModel::tableName() . ' agent', 'agent.member_id = member.id']
        ];
        $where = [
            ['relation.parent_id' => $this->memberId],
            ['<', 'relation.level', 4], // 最多查找三级
            ['relation.level' => $level]
        ];
        $params = [
            'select' => $select,
            'alias' => 'relation',
            'leftJoins' => $leftJoins,
            'andWhere' => $where,
            'orderBy' => ['relation.child_time' => SORT_DESC],
        ];
        // 分销层级设置
        $setLevel = CommissionSettings::get('set.commission_level');
        // 获取列表
        $list = CommissionRelationModel::getColl($params, [
            'callable' => function (&$row) use ($setLevel) {
                $row['become_time'] = date('Y-m-d', strtotime($row['become_time']));
                $row['child_time'] = date('Y-m-d', strtotime($row['child_time']));
                $row['commission_child'] = CommissionAgentModel::getChildTotal($row['member_id'], 0, null, $setLevel);
                // 不是分销商 且 佣金为0 显示已完成订单数
                if ($row['status'] != CommissionAgentConstant::AGENT_STATUS_SUCCESS && $row['commission_total'] == 0) {
                    $row['order_count'] = CommissionOrderDataModel::find()
                        ->alias('commission')
                        ->leftJoin(OrderModel::tableName() . ' o', 'o.id=commission.order_id')
                        ->where([
                            'and',
                            ['commission.member_id' => $row['member_id']],
                            ['commission.agent_id' => $this->memberId],
                            ['commission.is_count_refund' => 1],// 只统计未维权的
                            ['o.status' => OrderConstant::ORDER_STATUS_SUCCESS],// 只查已完成
                        ])
                        ->count('1');
//                    $row['order_count'] = OrderModel::getOrderCount($row['member_id'], OrderConstant::ORDER_STATUS_SUCCESS);
                }
            }
        ]);

        return $this->result($list);
    }

}
