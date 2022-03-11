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

namespace shopstar\admin\commission\settings;

use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\exceptions\commission\CommissionSetException;
use shopstar\models\commission\CommissionSettings;
use shopstar\bases\KdxAdminApiController;

/**
 * 其他设置
 * Class OtherController
 * @package apps\commission\manage\settings
 */
class OtherController extends KdxAdminApiController
{

    /**
     * @var string[] 需要POST请求的Actions
     */
    public $configActions = [
        'postActions' => [
            'set',
        ]
    ];


    /**
     * 获取设置
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $settings = CommissionSettings::get('other');

        return $this->result([
            'settings' => $settings,
        ]);
    }
    
    /**
     * 保存设置
     * @return array|int[]|\yii\web\Response
     * @throws CommissionSetException
     * @author likexin
     */
    public function actionSet()
    {
        $defaultSettings = CommissionSettings::getDefaultSettings('other');

        $settings = [];

        foreach ($defaultSettings as $field => $defaultValue) {
            $settings[$field] = RequestHelper::post($field, $defaultValue);
        }

        try {
            CommissionSettings::set('other', $settings);
            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::OTHER_EDIT,
                CommissionLogConstant::getText(CommissionLogConstant::OTHER_EDIT),
                '0',
                [
                    'log_data' => $settings,
                    'log_primary' => [
                        '成为分销商' => $settings['become_agent'],
                        '总店' => $settings['head_agent'],
                        '分销商名称' => $settings['agent_name'],
                        '分销中心' => $settings['agent_center'],
                        '分销佣金' => $settings['agent_commission'],
                        '分销订单' => $settings['commission_order'],
                        '提现明细' => $settings['withdraw_detail'],
                        '我的下线' => $settings['my_down_line'],
                        '等级说明' => $settings['level_description'],
                        '佣金排名' => $settings['commission_rank'],
                        '佣金' => $settings['commission'],
                        '提现' => $settings['withdraw'],
                        '可提现佣金' => $settings['can_withdraw_commission'],
                        '累计佣金' => $settings['count_commission'],
                        '待审核佣金' => $settings['wait_audit_commission'],
                        '待打款佣金' => $settings['wait_pay_commission'],
                        '待入账佣金' => $settings['wait_account_commission'],
                        '一级' => $settings['level_name_1'],
                        '二级' => $settings['level_name_2'],
                        '三级' => $settings['level_name_3'],
                        '协议名称' => $settings['agreement_title'],
                        '协议内容' => $settings['agreement_content'],
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::OTHER_EDIT,
                    ]
                ]
            );
            
        } catch (\Throwable $exception) {
            throw new CommissionSetException(CommissionSetException::SET_SAVE_FAIL);
        }

        return $this->success();
    }

}