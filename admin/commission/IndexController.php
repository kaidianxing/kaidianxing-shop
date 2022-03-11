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

namespace shopstar\admin\commission;

use shopstar\helpers\ArrayHelper;
use shopstar\models\member\MemberModel;
use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\constants\commission\CommissionApplyStatusConstant;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\bases\KdxAdminApiController;

/**
 * Class IndexController
 * @package apps\commission\manage
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 分销概览
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        // 提现待审核

        // 提现待打款

        // 提现佣金
        $applyData = CommissionApplyModel::getCommissionInfo();

        $final['pre_check'] = ArrayHelper::arrayGet($applyData, 'pre_check', 0);
        $final['check_agree'] = ArrayHelper::arrayGet($applyData, 'check_agree', 0);
        $final['remit_success'] = ArrayHelper::arrayGet($applyData, 'remit_success', 0);

        // 累计佣金
        $commissionTotal = CommissionAgentModel::find()
            ->sum('commission_total');

        $final['commission_total'] = !empty($commissionTotal) ? $commissionTotal : 0;

        // 待审核人 分销商
        $agentData = CommissionAgentModel::find()
            ->select('status, count(1) as total')
            ->where([
                'and',
                ['is_deleted' => 0],
                ['in', 'status', [CommissionAgentConstant::AGENT_STATUS_WAIT, CommissionAgentConstant::AGENT_STATUS_SUCCESS]]
            ])
            ->groupBy('status')
            ->asArray()
            ->all();

        $agentData = array_column($agentData, 'total', 'status');

        $final['agent_wait'] = ArrayHelper::arrayGet($agentData, CommissionAgentConstant::AGENT_STATUS_WAIT, 0);
        $final['agent'] = ArrayHelper::arrayGet($agentData, CommissionAgentConstant::AGENT_STATUS_SUCCESS, 0);

        // 会员数
        $member = MemberModel::find()
            ->where(['is_deleted' => 0])
            ->count();

        $final['member'] = !empty($member) ? $member : 0;
        $final['agent_member_per'] = empty($member) ? 0 : round($final['agent'] / $member, 2);

        // 分销商等级
        $level = CommissionLevelModel::find()
            ->select('id, name, is_default' )
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->get();
        $levelCount = count($level);
        foreach ($level as &$item) {
            $item['total'] = CommissionAgentModel::find()
                ->where(['level_id' => $item['id']])
                ->count();
            if (!empty($item['total'])) {
                $item['chain'] = bcdiv($levelCount, $item['total'], 2);
            } else {
                $item['chain'] = 0;
            }
        }
        
        $final['agent_level'] = $level;

        // 新增分销商数
        $newAgentStartDate = date('Y-m-d', strtotime('-7 day'));
        $newAgentEndDate = date('Y-m-d');
        $newAgent = CommissionAgentModel::find()
            ->select('date(become_time) as date, count(1) as total')
            ->where([
                'and',
                ['status'  => 1],
                ['is_deleted' => 0],
                ['between', 'become_time', $newAgentStartDate, $newAgentEndDate]
            ])
            ->groupBy('date')
            ->asArray()
            ->all();
        $newAgentFormat = [];
        $newAgent = array_column($newAgent, null, 'date');
        $sTimestamp = strtotime($newAgentStartDate);
        $eTimestamp = strtotime($newAgentEndDate);
        while ($sTimestamp <= $eTimestamp) {
            $dateIndex = date('Y-m-d', $sTimestamp);
            if (!isset($newAgent[$dateIndex])) {
                $newAgentFormat[$dateIndex]['date'] = $dateIndex;
                $newAgentFormat[$dateIndex]['total'] = 0;
            } else {
                $newAgentFormat[$dateIndex]['date'] = $dateIndex;
                $newAgentFormat[$dateIndex]['total'] = $newAgent[$dateIndex]['total'];
            }
            $sTimestamp += 86400;
        }
        $final['new_agent'] = $newAgentFormat;

        // 分销商排行TOP10 累计佣金 下线会员
        $agentTotalRank = CommissionAgentModel::find()
            ->leftJoin(MemberModel::tableName().' m', 'm.id = a.member_id')
            ->select('member_id, nickname, commission_total')
            ->alias('a')
            ->where([
                'a.status'  => 1,
                'a.is_black' => 0,
                'm.is_black' => 0,
                'a.is_deleted' => 0,
            ])
            ->orderBy('commission_total desc')
            ->limit(10)
            ->asArray()
            ->all();
        $final['total_rank'] = $agentTotalRank;
        $agentChildRank = CommissionAgentModel::find()
            ->leftJoin(MemberModel::tableName().' m', 'm.id = a.member_id')
            ->select('member_id, nickname, commission_child')
            ->alias('a')
            ->where([
                'a.status'  => 1,
                'a.is_black' => 0,
                'm.is_black' => 0,
                'a.is_deleted' => 0,
            ])
            ->orderBy('commission_child desc')
            ->limit(10)
            ->asArray()
            ->all();
        $final['total_child'] = $agentChildRank;

        return $this->result(['data' => $final]);
    }

}