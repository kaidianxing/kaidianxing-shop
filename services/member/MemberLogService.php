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


namespace shopstar\services\member;

use shopstar\components\notice\NoticeComponent;
use shopstar\components\payment\base\PayOrderTypeConstant;
use shopstar\components\payment\base\WithdrawOrderTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberLogPayTypeConstant;
use shopstar\constants\member\MemberLogStatusConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\PayOrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\tradeOrder\TradeOrderService;

class MemberLogService
{

    /**
     * 充值退款
     * @param MemberLogModel $order
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function refund(MemberLogModel $order)
    {
        if ($order->type != MemberLogTypeConstant::ORDER_TYPE_RECHARGE) {
            // 校验订单类型
            return error('订单类型错误，无法进行操作');
        } elseif ($order->status != MemberLogStatusConstant::ORDER_STATUS_SUCCESS) {
            // 校验订单状态
            return error('订单状态错误，无法进行操作');
        }

        $transaction = MemberLogModel::getDB()->beginTransaction();

        try {
            // 修改member_order status
            $logStatusRet = MemberLogModel::updateStatus($order->id, MemberLogStatusConstant::ORDER_RECHARGE_REFUND);
            if (is_error($logStatusRet)) {
                throw new \Exception($logStatusRet['message']);
            }

            // 修改用户余额
            if (!empty($order->real_money)) {
                $resBalance = MemberModel::updateCredit($order->member_id, $order->real_money, 0, 'balance', 2, MemberLogTypeConstant::getMessage($order->type) . '退款', MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND);
                if (is_error($resBalance)) {
                    throw new \Exception($resBalance['message']);
                }
            }

            // 修改用户积分
            if ($order->type == MemberLogTypeConstant::ORDER_TYPE_RECHARGE && $order->send_credit != 0) {
                // 比较当前用户积分与扣除积分
                $memberCredit = MemberModel::getCredit($order->member_id);
                $sendCredit = $memberCredit < $order->send_credit ? $memberCredit : $order->send_credit;
                $resCredit = MemberModel::updateCredit($order->member_id, $sendCredit, 0, 'credit', 2, '退款积分扣除', MemberCreditRecordStatusConstant::CREDIT_STATUS_SEND_BACK);
                if (is_error($resCredit)) {
                    throw new \Exception($resCredit['message']);
                }
            }
            // 微信或支付宝退款 或抖音
            if ($order->pay_type == PayTypeConstant::PAY_TYPE_WECHAT || $order->pay_type == PayTypeConstant::PAY_TYPE_ALIPAY || $order->pay_type == PayTypeConstant::PAY_TYPE_BYTEDANCE) {

                try {
                    // 调用交易订单服务进行退款
                    TradeOrderService::operation([
                        'orderNo' => $order->log_sn,
                    ])->refund($order->real_money, '充值退款');
                } catch (\Exception $exception) {
                    // 兼容旧版 如果找不到订单 使用旧版退款
                    if ($exception->getCode() == '108130') {
                        $payOrder = PayOrderModel::findOne(['order_id' => $order->id, 'order_no' => $order->log_sn]);
                        // 退款
                        $config = [
                            'member_id' => $order->member_id,
                            'order_id' => $order->id,
                            'order_no' => $order->log_sn,
                            'refund_fee' => $order->real_money, //退款金额
                            'client_type' => $payOrder->client_type,
                            'pay_type' => $payOrder->pay_type,
                            'pay_price' => $payOrder->pay_price,
                            'order_type' => PayOrderTypeConstant::ORDER_TYPE_MEMBER_LOG,
                            'refund_desc' => '充值退款'
                        ];
                        $payInstance = PayComponent::getInstance($config);
                        $refundResult = $payInstance->refund();
                        if (is_error($refundResult)) {
                            throw new \Exception($refundResult['message']);
                        }
                    } else {
                        // 否则抛出异常
                        throw $exception;
                    }
                }

            }
            $transaction->commit();
        } catch (\Throwable $throwable) {

            $transaction->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }



    /**
     * 提现打款
     * @param MemberLogModel $order
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function transfer($order)
    {

        if ($order->type == MemberLogTypeConstant::ORDER_FROM_WITHDRAW) {// 申请提现
            // 校验订单状态
            if ($order->status != MemberLogStatusConstant::ORDER_STATUS_NOT) {
                return error('订单状态错误，无法进行操作');
            }

            $updateMemberLogStatus = MemberLogStatusConstant::ORDER_STATUS_SUCCESS;
            $refundDesc = '账户提现';
        } else {
            return error('订单类型错误，无法进行操作');
        }

        $transaction = MemberLogModel::getDB()->beginTransaction();
        try {

            // 验证提现类型
            if ($order->pay_type != MemberLogPayTypeConstant::ORDER_PAY_TYPE_WECHAT &&
                $order->pay_type != MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY) {
                throw new \Exception('提现类型错误');
            }
            // 微信或支付宝退款
            $config = [
                'transfer_fee' => $order->real_money,
                'transfer_desc' => $refundDesc,
                'transfer_type' => $order->pay_type, // 20微信 30支付宝
                'order_no' => $order->log_sn,
                'client_type' => $order->client_type,
                'withdraw_order_type' => WithdrawOrderTypeConstant::WITHDRAW_ORDER_MEMBER_LOG
            ];
            // 公众号小程序支付宝微信都支持
            if ($order->client_type == ClientTypeConstant::CLIENT_WXAPP || $order->client_type == ClientTypeConstant::CLIENT_WECHAT) {
                if ($order->pay_type == PayTypeConstant::PAY_TYPE_WECHAT) {
                    // 根据设置获取openid
                    // 打款方式是红包  都获取公众号的openid  如果是转账 则根据提现账户打款
                    // 获取设置
                    $settings = ShopSettings::get('sysset.payment.payset');
                    // 企业打款
                    if ($settings['pay_type_withdraw'] == 1) {
                        if ($order->client_type == ClientTypeConstant::CLIENT_WXAPP) {
                            $config['openid'] = MemberWxappModel::getOpenId($order->member_id);
                        } else if ($order->client_type == ClientTypeConstant::CLIENT_WECHAT) {
                            $config['openid'] = MemberWechatModel::getOpenId($order->member_id);
                        }
                    } else if ($settings['pay_type_withdraw'] == 2) {
                        // 红包打款
                        $config['openid'] = MemberWechatModel::getOpenId($order->member_id);
                        if (empty($config['openid'])) {
                            return error('该申请不支持红包打款，请选择其他打款方式。');
                        }

                    }
                }
                if ($order->pay_type == PayTypeConstant::PAY_TYPE_ALIPAY) {
                    $config['alipay'] = $order->alipay;
                    $config['real_name'] = $order->real_name;
                }
            }
            // H5只支持支付宝提现
            if ($order->client_type == ClientTypeConstant::CLIENT_H5) {
                $config['alipay'] = $order->alipay;
                $config['real_name'] = $order->real_name;
            }
            // 转账
            $payInstance = PayComponent::getInstance($config);
            $refundResult = $payInstance->transfer();
            if (is_error($refundResult)) {
                throw new \Exception($refundResult['message']);
            }

            // 修改提现状态
            $logStatusRet = MemberLogModel::updateStatus($order->id, $updateMemberLogStatus);
            if (is_error($logStatusRet)) {
                throw new \Exception($logStatusRet['message']);
            }

            $transaction->commit();

            $member = MemberModel::findOne(['id' => $order->member_id]);
            //消息通知
            $result = NoticeComponent::getInstance( NoticeTypeConstant::BUYER_PAY_WITHDRAW, [
                'member_nickname' => $member->nickname,
                'withdraw_price' => $order->money,
                'withdraw_time' => DateTimeHelper::now(),
                'member_balance' => $member->balance,
                'balance_change_reason' => '余额提现'
            ]);

            if (!is_error($result)) {
                $result->sendMessage($order->member_id);
            }
        } catch (\Throwable $throwable) {

            $transaction->rollBack();
            return error($throwable->getMessage());
        }

        return true;
    }

}