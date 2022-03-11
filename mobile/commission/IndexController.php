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

use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
 
use shopstar\models\member\MemberModel;
use shopstar\mobile\commission\CommissionClientApiController;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionSettings;
use yii\helpers\Url;

/**
 * 分销中心首页
 * Class IndexController
 * @package apps\commission\client
 */
class IndexController extends CommissionClientApiController
{

    public $allowAgentActions = [
        'get-set',
        'commission-status',
    ];

    /**
     * 分销中心首页
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        // 判断是否成为分销商后第一次进入
        $showSuccess = 0;
        $isAudit = 0;
        $banner = '';
        $key = 'show_success_' . '_' . $this->memberId;
        if (!empty(\Yii::$app->redis->get($key))) {
            $showSuccess = 1;
            \Yii::$app->redis->del($key);
            // 获取是否需要审核字段
            $set = CommissionSettings::get('set');
            $isAudit = $set['is_audit'];
            $banner = $set['banner'];
        }
        // 总店名称
        $headAgent = CommissionSettings::get('other.head_agent');
        $member = MemberModel::find()->select('nickname')->where(['id' => $this->memberId])->first();
        // 分销商信息
        $agentInfo = [
            'nickname' => $member['nickname'],
            'avatar' => (string)$this->member['avatar'],
            'agent_id' => (int)$this->agent['agent_id'],
            'agent_name' => $headAgent,
            'level_id' => (int)$this->agent['level_id'],
            'level_name' => '默认等级',
        ];

        // 上线不是总店时查询上线的信息
        if ($agentInfo['agent_id'] > 0) {
            $agent = MemberModel::find()->where(['id' => $agentInfo['agent_id']])->select(['nickname'])->first();
            $agentInfo['agent_name'] = (string)$agent['nickname'];
        }

        // 等级不是默认等级时查询等级名称
        if (!empty($agentInfo['level_id'])) {
            $level = CommissionLevelModel::find()->where([
                'id' => $agentInfo['level_id'],
            ])->select(['id', 'name'])->first();
            $agentInfo['level_name'] = (string)$level['name'];
        }

        // 佣金金额
        $commissionPrice = [
            // 成功提现佣金
            'commission_pay' => $this->agent['commission_pay'],

            // 可提现佣金
            'can_withdraw' => CommissionAgentTotalModel::getCanWithdrawPrice($this->memberId),
        ];
        // 层级设置
        $setLevel = CommissionSettings::get('set.commission_level');

        // 数据统计
        $total = [
            // 累计佣金
            'commission_total' => $this->agent['commission_total'],

            // 分销订单数量
            'commission_order' => CommissionOrderDataModel::getOrderCount($this->memberId, 0, 0),

            // 分销下线数量
            'commission_child' => CommissionAgentModel::getChildTotal($this->memberId, 0, null, $setLevel),

            // 待入账(未结算)
            'wait_settlement_commission' => CommissionAgentTotalModel::getWaitSettlementPrice($this->memberId),

            // 累计提现数量
            'withdraw_count' => CommissionAgentTotalModel::getApplyTotal($this->memberId),
        ];
        
        $data = [
            'agent_info' => $agentInfo,
            'commission_price' => $commissionPrice,
            'total' => $total,
            'can_withdraw' => $commissionPrice['can_withdraw'] > 0,
            'show_success' => $showSuccess, // 是否展示通过页
            'is_audit' => $isAudit, // 是否需要审核
            'banner' => $banner
        ];

        return $this->result($data);
    }
    
    /**
     * 获取分销设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSet()
    {
        $set = CommissionSettings::get('other');
        $set['show_commission_level'] = CommissionSettings::get('set')['show_commission_level'];
        $set['agent_name'] = '分销商';
        $set['head_agent'] = '总店';
        return $this->result($set);
    }

    /**
     * 获取是否开启分销
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCommissionStatus()
    {
        $set = CommissionSettings::get('set.commission_level');
        if ($set == 0) {
            $isOpen = 0;
        } else {
            $isOpen = 1;
        }
        return $this->result(['is_open' => $isOpen]);
    }

}
