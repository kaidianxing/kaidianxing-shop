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

namespace shopstar\admin\sysset;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\CreditLogConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\sysset\CreditException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\helpers\StringHelper;

/**
 * 余额设置
 * Class CreditController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class BalanceController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'update',
        ],
        'allowPermActions' => [
            'get-info'
        ]
    ];

    /**
     * 获取余额配置信息
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo(): \yii\web\Response
    {
        $res = ShopSettings::get('sysset.credit');
        $res = ArrayHelper::filter($res, ['balance_text', 'recharge_state', 'recharge_money_low', 'withdraw_state', 'withdraw_type', 'withdraw_limit_type', 'withdraw_limit_money', 'withdraw_fee_type', 'withdraw_fee', 'free_fee_type', 'free_fee_start', 'free_fee_end']);

        // 原抵扣设置
        $deductSet = ShopSettings::get('sale.basic.deduct');
        // 过滤设置
        $deductSet = ArrayHelper::filter($deductSet, ['balance_state']);

        return $this->success(array_merge($res, $deductSet));
    }

    /**
     * 更新积分余额配置信息
     * @return \yii\web\Response
     * @throws CreditException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = [
            'balance_text' => '余额', // 余额文字
            'recharge_state' => RequestHelper::post('recharge_state', '0'), // 充值设置
            'recharge_money_low' => RequestHelper::post('recharge_money_low', 0.1), // 最低充值
            'withdraw_state' => RequestHelper::post('withdraw_state', '0'), // 余额提现设置
            'withdraw_type' => RequestHelper::post('withdraw_type', ''), // 提现方式
            'withdraw_limit_type' => RequestHelper::post('withdraw_limit_type', '1'), // 提现限额设置
            'withdraw_limit_money' => RequestHelper::post('withdraw_limit_money', '0'), // 最低提现金额
            'withdraw_fee_type' => RequestHelper::post('withdraw_fee_type', '1'), // 提现手续费设置
            'withdraw_fee' => RequestHelper::post('withdraw_fee', '0'), // 提现手续费
            'free_fee_type' => RequestHelper::post('free_fee_type', '1'), // 免手续费区间设置
            'free_fee_start' => RequestHelper::post('free_fee_start', '0'), // 免手续费开始
            'free_fee_end' => RequestHelper::post('free_fee_end', '0'), // 免手续费开始
        ];

        // 兼容旧数据
        $deductData = [
            'balance_state' => RequestHelper::post('balance_state', 0), // 余额抵扣设置
        ];

        // 余额定义文字不能为空
        if (empty($post['balance_text'])) {
            throw new CreditException(CreditException::BALANCE_TEXT_EMPTY);
        }
        // 最低充值0.1
        if ($post['recharge_state'] == '1' && bccomp($post['recharge_money_low'], 0.1, 2) < 0) {
            throw new CreditException(CreditException::RECHARGE_MONEY_LOW);
        }
        // 自定义提现金额
        if ($post['withdraw_limit_type'] == SyssetTypeConstant::CUSTOMER_WITHDRAW_LIMIT) {
            // 余额提现金额不能为空
            if (empty($post['withdraw_limit_money'])) {
                throw new CreditException(CreditException::WITHDRAW_MONEY_EMPTY);
            }
            // 余额提现金额不能为负数
            if ($post['withdraw_limit_money'] < 0) {
                throw new CreditException(CreditException::WITHDRAW_MONEY_ERROR);
            }
        }
        // 自定义提现手续费
        if ($post['withdraw_fee_type'] == SyssetTypeConstant::CUSTOMER_WITHDRAW_FEE) {
            // 提现手续费设置错误
            if (bccomp($post['withdraw_fee'], 0.1, 2) < 0 || bccomp($post['withdraw_fee'], 99.9) > 0) {
                throw new CreditException(CreditException::WITHDRAW_MONEY_FEE_ERROR);
            }
            // 免手续费区间
            if ($post['free_fee_type'] == SyssetTypeConstant::CUSTOMER_FREE_FEE) {
                // 免手续费开始区间不能为负数
                if ($post['free_fee_start'] < 0) {
                    throw new CreditException(CreditException::WITHDRAW_MONEY_NOT_FEE_START_ERROR);
                }
                // 免手续费区间开始不能大于结束
                if (bccomp($post['free_fee_start'], $post['free_fee_end'], 2) > 0) {
                    throw new CreditException(CreditException::WITHDRAW_MONEY_NOT_FEE_ERROR);
                }
            }
        }

        try {
            // 原设置
            $res = ShopSettings::get('sysset.credit');
            $post = array_merge($res, $post);
            ShopSettings::set('sysset.credit', $post);

            // 兼容老数据
            $deductSet = ShopSettings::get('sale.basic.deduct');
            $deductData = array_merge($deductSet, $deductData);
            ShopSettings::set('sale.basic.deduct', $deductData);

            // 拼装提现方式
            $withdraw = array_flip(StringHelper::explode($post['withdraw_type']));
            $withdrawType = ['20' => '微信钱包', '30' => '支付宝'];
            $withdrawType = array_intersect_key($withdrawType, $withdraw);
            $withdrawText = implode('、', $withdrawType);
            // 日志
            LogModel::write(
                $this->userId,
                CreditLogConstant::BALANCE_SET_EDIT,
                CreditLogConstant::getText(CreditLogConstant::BALANCE_SET_EDIT),
                '0',
                [
                    'log_data' => $post,
                    'log_primary' => [
                        '余额文字' => $post['balance_text'], // 余额文字
                        '充值设置' => $post['recharge_state'] == 1 ? '开启' : '关闭', // 充值设置
                        '最低充值' => $post['recharge_money_low'] . '元', // 最低充值
                        '余额抵扣' => $deductData['balance_state'] == 1 ? '开启' : '关闭', // 余额抵扣设置
                        '余额提现设置' => $post['withdraw_state'] == 1 ? '开启' : '关闭', // 余额提现设置
                        '提现方式' => $withdrawText, // 提现方式
                        '提现限额设置' => $post['withdraw_limit_type'] == 1 ? '不限制' : '自定义', // 提现限额设置
                        '最低提现金额' => '余额满 ' . $post['withdraw_limit_money'] . ' 元可提现', // 最低提现金额
                        '提现手续费设置' => $post['withdraw_fee_type'] == 1 ? '不扣除' : '自定义', // 提现手续费设置
                        '提现手续费' => '手续费 ' . $post['withdraw_fee'] . ' %', // 提现手续费
                        '免手续费区间设置' => $post['free_fee_type'] == 1 ? '不免手续费' : '自定义免手续费区间', // 免手续费区间设置
                        '免手续费区间' => $post['free_fee_start'] . ' 元 - ' . $post['free_fee_end'] . ' 元时不扣除手续费', // 免手续费区间
                    ],
                    'dirty_identify_code' => [
                        CreditLogConstant::BALANCE_SET_EDIT,
                    ],
                ]
            );
        } catch (Exception $exception) {
            throw new CreditException(CreditException::CREDIT_SAVE_FAIL);
        }

        return $this->success();
    }

    /**
     * 获取数据
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetStatistics()
    {
        // 累计发放
        $totalSend = MemberCreditRecordModel::getSumByType(2, 'balanceSendType');
        $totalBack = MemberCreditRecordModel::getSumByType(2, 'balanceBackType');
        // 减去返还的
        $totalSend = bcsub($totalSend, $totalBack, 2);

        // 累计使用
        $totalUse = -MemberCreditRecordModel::getSumByType(2, 'balanceUseType');
        $totalRefund = MemberCreditRecordModel::getSumByType(2, 'balanceRefundType');
        // 退款返还
        $totalUse = bcsub($totalUse, $totalRefund, 2);

        $data = [
            'total_send' => $totalSend,
            'total_member' => (float)MemberModel::find()->sum('balance') ?? 0, // 不过滤删除会员的
            'total_use' => $totalUse,
        ];

        return $this->result(['data' => $data]);
    }

}