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

use shopstar\constants\commission\CommissionApplyStatusConstant;
use shopstar\constants\commission\CommissionApplyTypeConstant;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionSettings;

/**
 * 提现
 * Class WithdrawController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class WithdrawController extends CommissionClientApiController
{

    /**
     * 分销佣金
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        $settings = CommissionSettings::get('settlement');

        $result = [
            'data' => [
                // 累计佣金
                'commission_total' => (float)$this->agent['commission_total'],

                //累计包含阶梯佣金
                'ladder_commission_total' => (float)$this->agent['ladder_commission_total'],

                // 可提现佣金
                'can_withdraw_commission' => CommissionAgentTotalModel::getCanWithdrawPrice($this->memberId),

                // 待审核佣金
                'wait_check_commission' => CommissionAgentTotalModel::getWaitCheckPrice($this->memberId),

                // 待打款佣金
                'wait_remit_commission' => CommissionAgentTotalModel::getWaitRemitPrice($this->memberId),

                // 待入账(未结算)
                'wait_settlement_commission' => CommissionAgentTotalModel::getWaitSettlementPrice($this->memberId),
            ],

            // 返回设置
            'settings' => [
                // 最低提现额度
                'withdraw_limit' => (float)$settings['withdraw_limit'],
                // 结算天数
                'settlement_days' => (int)$settings['settlement_days'],
            ],
        ];

        return $this->result($result);
    }

    /**
     * 获取提现日志
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetList()
    {
        $params = [
            'searchs' => [
                ['status', 'int'],
            ],
            'andWhere' => [
                ['member_id' => $this->memberId],
            ],
            'select' => ['id', 'type', 'status', 'apply_time', 'apply_commission', 'charge_deduction', 'final_commission'],
            'orderBy' => [
                'apply_time' => SORT_DESC
            ]
        ];

        $options = [
            'callable' => function (&$row) {
                // 返回状态文字
                $row['status_text'] = CommissionApplyStatusConstant::getMessage($row['status']);
                // 返回类型文字
                $row['type_text'] = CommissionApplyTypeConstant::getMessage($row['type']);
            },
        ];

        $result = CommissionApplyModel::getColl($params, $options);

        return $this->result($result);
    }

}