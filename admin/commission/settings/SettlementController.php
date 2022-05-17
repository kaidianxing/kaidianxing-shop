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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\exceptions\commission\CommissionSetException;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\log\LogModel;

/**
 * 结算设置
 * Class SettlementController
 * @package shopstar\admin\commission\settings
 */
class SettlementController extends KdxAdminApiController
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
        $settings = CommissionSettings::get('settlement');

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
        // 接收设置参数
        $settings = [
            'calculate_type' => RequestHelper::post('calculate_type', '2'),
            'withdraw_limit' => RequestHelper::postInt('withdraw_limit', 1),
            'withdraw_fee_type' => RequestHelper::postInt('withdraw_fee_type', 1),
            'withdraw_fee' => RequestHelper::post('withdraw_fee', ''),
            'free_fee_type' => RequestHelper::postInt('free_fee_type', 1),
            'free_fee_start' => RequestHelper::post('free_fee_start', ''),
            'free_fee_end' => RequestHelper::post('free_fee_end', ''),

            'settlement_day_type' => RequestHelper::postInt('settlement_day_type', 1),
            'settlement_days' => RequestHelper::postInt('settlement_days', 0),

            'withdraw_audit' => RequestHelper::postInt('withdraw_audit', 1),
            'auto_check_level' => RequestHelper::postInt('auto_check_level', 1),
            'auto_check_price' => RequestHelper::post('auto_check_price', ''),

            'withdraw_type' => RequestHelper::postArray('withdraw_type'),
        ];

        // 最低提现额度
        if (bccomp($settings['withdraw_limit'], 1, 2) < 0 || bccomp($settings['withdraw_limit'], 9999999.99) > 0) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_WITHDRAW_LIMIT_ERROR);
        }
        // 自定义提现手续费
        if ($settings['withdraw_fee_type'] == 2) {
            if (empty($settings['withdraw_fee']) || $settings['withdraw_fee'] <= 0 || $settings['withdraw_fee'] >= 100) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_WITHDRAW_TYPE_ERROR);
            }
        }
        // 免手续费区间
        if ($settings['free_fee_type'] == 2) {
            // 免手续费开始区间不能为负数
            if ($settings['free_fee_start'] < 0) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_WITHDRAW_MONEY_NOT_FEE_START_ERROR);
            }
            // 免手续费区间开始不能大于结束
            if (bccomp($settings['free_fee_start'], $settings['free_fee_end'], 2) >= 0) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_WITHDRAW_MONEY_NOT_FEE_ERROR);
            }
        }

        // 自定义结算天数
        if ($settings['settlement_day_type'] == 2) {
            // 天数不能为空
            if (empty($settings['settlement_days'])) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_CALCULATE_DAYS_NOT_EMPTY);
            }
            // 天数必须为正整数
            if (!is_numeric($settings['settlement_days']) || !is_int((int)$settings['settlement_days']) || $settings['settlement_days'] < 0) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_CALCULATE_DAYS_ERROR);
            }
        }

        // 自动审核
        if ($settings['withdraw_audit'] == 2) {
            // 分销等级不能为空
            if ($settings['auto_check_level'] == '') {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_LEVEL_NOT_EMPTY);
            }
            // 提现金额不能为空
            if (empty($settings['auto_check_price']) || $settings['auto_check_price'] <= 0 || $settings['auto_check_price'] >= 10000000) {
                throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_AUTO_WITHDRAW_LIMIT_ERROR);
            }
        }

        // 处理提现类型
        $settings['withdraw_type'] = array_filter((array)$settings['withdraw_type']);

        try {
            CommissionSettings::set('settlement', $settings);
            //日志
            $level = CommissionLevelModel::find()
                ->select('name')
                ->where(['id' => $settings['auto_check_level']])
                ->first();
            $withdrawType = [];
            foreach ($settings['withdraw_type'] as $item) {
                switch ($item) {
                    case '10':
                        $withdrawType[] = '商城余额';
                        break;
                    case '20':
                        $withdrawType[] = '微信钱包';
                        break;
                    case '30':
                        $withdrawType[] = '支付宝';
                        break;
                }
            }

            LogModel::write(
                $this->userId,
                CommissionLogConstant::COMMISSION_SETTLEMENT_EDIT,
                CommissionLogConstant::getText(CommissionLogConstant::COMMISSION_SETTLEMENT_EDIT),
                '0',
                [
                    'log_data' => $settings,
                    'log_primary' => [
                        '佣金计算方式' => $settings['calculate_type'] == 1 ? '商品折扣价' : '实际支付价',
                        '最低提现额度' => $settings['withdraw_limit'] . ' 元',
                        '提现手续费' => $settings['withdraw_fee_type'] == 1 ? '不扣除' : '自定义',
                        '手续费' => $settings['withdraw_fee'] ? $settings['withdraw_fee'] . ' %' : '-',
                        '免手续费' => $settings['free_fee_type'] == 1 ? '不免手续费' : '自定义免手续费区间',
                        '免手续费区间' => $settings['free_fee_type'] == 2 ? $settings['free_fee_start'] . ' 元 - ' . $settings['free_fee_end'] . ' 元' : '-',

                        '结算天数类型' => $settings['settlement_day_type'] == 1 ? '订单完成后即可提现' : '自定义结算天数',
                        '结算天数' => $settings['settlement_days'] ? '订单确认收货后 ' . $settings['settlement_days'] . ' 天可申请提现' : '-',

                        '提现审核' => $settings['withdraw_audit'] == 1 ? '手动审核' : '自动审核',
                        '分销商等级' => $level['name'] ?: '-',
                        '提现金额' => $settings['auto_check_price'] ? $settings['auto_check_price'] . ' 元' : '-',

                        '提现方式' => implode(', ', $withdrawType) ?: '-',
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::COMMISSION_SETTLEMENT_EDIT,
                    ]
                ]
            );

        } catch (\Throwable $exception) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_SETTLEMENT_SAVE_FAIL);
        }

        return $this->success();
    }

}