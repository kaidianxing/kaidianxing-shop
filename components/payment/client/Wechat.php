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

namespace shopstar\components\payment\client;

use shopstar\components\payment\base\AlipayError;
use shopstar\components\payment\base\BasePay;
use shopstar\components\payment\base\PayClientConstant;
use shopstar\components\payment\base\PayClientInterface;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\components\payment\base\WithdrawOrderTypeConstant;
use shopstar\components\payment\base\WithdrawTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\LogHelper;
use shopstar\models\finance\FinanceRemitModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\PayOrderModel;
use Yansongda\Pay\Exceptions\GatewayException;
use yii\helpers\Json;

/**
 * 微信公众号支付
 * Class Wechat
 * @package shopstar\components\payment\client
 * @author 青岛开店星信息技术有限公司
 */
class Wechat extends BasePay implements PayClientInterface
{
    const SERVICE_MODEL = 11;
    const CLIENT_TYPE = 'wechat';

    /**
     * @var string openid
     */
    public $openid;

    /**
     * 支付
     * @return mixed
     * @throws PaymentException
     * @throws \shopstar\exceptions\order\OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function pay()
    {
        // 写入订单记录
        $this->write();

        // 判断订单来源 抖音的不支持切换
        if ($this->orderData[0]['create_from'] >= 30 && $this->orderData[0]['create_from'] <= 32) {
            throw new PaymentException(PaymentException::PAY_CHANNEL_ERROR);
        }

        // 获取支付方式
        $payType = PayTypeConstant::getIdentify($this->pay_type);

        // 校验支付类型
        $this->checkPayType($payType);

        return $this->$payType();
    }

    /**
     * 退款
     * @return mixed
     * @throws PaymentException
     * @throws \shopstar\exceptions\order\OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function refund()
    {
        if (!in_array($this->pay_type, [PayTypeConstant::PAY_TYPE_BALANCE, PayTypeConstant::PAY_TYPE_WECHAT, PayTypeConstant::PAY_TYPE_ALIPAY])) {
            return false;
        }

        // 余额付款货到付款
        if ($this->pay_type == PayTypeConstant::PAY_TYPE_BALANCE) {
            //返回会员余额
            $result = MemberModel::updateCredit(
                $this->member_id,
                $this->refund_fee,
                0, 'balance',
                1, '订单退款',
                MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND
            );
            if (is_error($result)) {
                return error($result['message']);
            }

            return true;
        }

        // 写入订单记录
        $this->write();

        // 获取支付方式
        $payType = PayTypeConstant::getIdentify($this->pay_type);
        $payTypeMethod = $payType . 'Refund';

        return $this->$payTypeMethod();
    }

    /**
     * 转账
     * @return mixed
     * @throws PaymentException
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function transfer()
    {
        $payConf = self::getWithdrawConfig(self::CLIENT_TYPE, PayTypeConstant::getIdentify($this->transfer_type));
        $withdrawConf = self::getWithdrawMethod();

        if ($this->transfer_type == WithdrawTypeConstant::WITHDRAW_ALIPAY) {
            // 支付宝转账

            $config = $this->setAlipayConfig($payConf);
            $transferOrder = [
                'out_biz_no' => time(),
                'trans_amount' => number_format($this->transfer_fee, 2, ".", ""),
                'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                'biz_scene' => 'DIRECT_TRANSFER',
                'payee_info' => [
                    'identity' => $this->alipay,
                    'identity_type' => 'ALIPAY_LOGON_ID',
                    'name' => $this->real_name
                ],
                'remark' => $this->transfer_desc
            ];

            try {

                // 转账
                $ret = $config->transfer($transferOrder)->toArray();

                // 清空证书
                $this->clearCert();

                if ($ret['code'] == 10000 && $ret['msg'] == 'Success') {
                    return true;
                }

                return error($ret['message']);

            } catch (GatewayException $e) {

                // 清空证书
                $this->clearCert();

                // 记录日志
                LogHelper::error('[WECHAT ALIPAY WITHDRAW ERROR]:' . $e->getMessage(), $transferOrder);

                return error(AlipayError::analysisError($e->raw));
            }
        } elseif ($this->transfer_type == WithdrawTypeConstant::WITHDRAW_WECHAT) {
            // 微信转账

            $config = $this->setWechatConfig($payConf, true);

            try {

                if ($withdrawConf['pay_type_withdraw'] == 1 || $withdrawConf['pay_type_commission'] == 1) {
                    // 企业转账到微信钱包

                    // 余额提现、分销佣金打款、多商户分佣打款
                    if (in_array($this->withdraw_order_type, [
                        WithdrawOrderTypeConstant::WITHDRAW_ORDER_MEMBER_LOG,
                        WithdrawOrderTypeConstant::WITHDRAW_ORDER_COMMISSION,
                        WithdrawOrderTypeConstant::MEMBER_WITHDRAW,
                    ])) {

                        $transferOrder = [
                            'partner_trade_no' => $this->order_no,          //商户订单号
                            'openid' => $this->openid,     //收款人的openid
                            'check_name' => 'NO_CHECK',            //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
                            'amount' => number_format($this->transfer_fee * 100, 0, ".", ""), //企业付款金额，单位为分
                            'desc' => $this->transfer_desc, //付款说明
                        ];

                        //TODO 青岛开店星信息技术有限公司 ERROR:NO_AUTH - 产品权限验证失败,请查看您当前是否具有该产品的权限
                        $ret = $config->transfer($transferOrder)->toArray();
                    }

                } elseif ($withdrawConf['pay_type_withdraw'] == 2 || $withdrawConf['pay_type_commission'] == 2) {
                    // 红包

                    // 余额提现、分销佣金打款、多商户分佣打款
                    if (in_array($this->withdraw_order_type, [
                        WithdrawOrderTypeConstant::WITHDRAW_ORDER_MEMBER_LOG,
                        WithdrawOrderTypeConstant::WITHDRAW_ORDER_COMMISSION,
                    ])) {

                        // 获取红包金额
                        $redpack = $this->getRedpack($withdrawConf);
                        if (count($redpack) > 99) {
                            return error('要发送的红包数量超过100');
                        }

                        // 红包发送成功数量
                        $redpackSuccessCount = 0;
                        foreach ($redpack as $packIndex => $pack) {
                            $transferOrder = [
                                'mch_billno' => $this->order_no . sprintf("%02d", $packIndex), // @change likexin 此处先拼接索引，只支持两位，个位数补零
                                'send_name' => '商户名称',
                                'total_amount' => number_format($pack * 100, 0, ".", ""),
                                're_openid' => $this->openid,
                                'total_num' => 1, //红包发放总人数,必须是1
                                'wishing' => '恭喜发财 大吉大利',
                                'act_name' => '红包活动',
                                'remark' => $this->transfer_desc,
                            ];

                            // TODO 青岛开店星信息技术有限公司 ERROR:NO_AUTH - IP地址非你在商户平台设置的可用IP地址
                            $ret = $config->redpack($transferOrder)->toArray();
                            LogHelper::info('[wxapp-redpack-info]', $ret);

                            if ($ret['return_code'] == 'SUCCESS') {
                                $redpackSuccessCount++;
                            }
                        }

                        // 多个红包一次也没成功时返回错误 @change likexin 10个红包，只发送成功1个算成功，如果都未发送成功算失败
                        if (empty($redpackSuccessCount)) {
                            return error('红包发送失败');
                        }

                    } elseif ($this->withdraw_order_type == WithdrawOrderTypeConstant::WITHDRAW_PLUGIN_POSTER_REWARD) {
                        // 海报奖励

                        // 获取红包金额
                        $redpack = $this->getRedpack($withdrawConf);

                        foreach ($redpack as $pack) {
                            $transferOrder = [
                                'mch_billno' => time(),
                                'send_name' => '商户名称',
                                'total_amount' => strval($pack * 100),
                                're_openid' => $this->openid,
                                'total_num' => '1',
                                'wishing' => '恭喜发财 大吉大利',
                                'act_name' => '红包活动',
                                'remark' => $this->transfer_desc,
                            ];
                            // TODO 青岛开店星信息技术有限公司 ERROR:NO_AUTH - IP地址非你在商户平台设置的可用IP地址
                            $ret = $config->redpack($transferOrder)->toArray();
                            LogHelper::Info('[wechat-redpack-info]', $ret);
                            if ($ret['return_code'] != 'SUCCESS') {
                                return error($ret['return_msg']);
                            }
                        }
                    } else {
                        return error('不支持的提现方式');
                    }

                } else {
                    return error('不支持的打款方式');
                }

                //记录财务日志
                FinanceRemitModel::createLog([
                    'trans_no' => $this->order_no,
                    'scene' => $this->withdraw_order_type,
                    'member_id' => $this->member_id ?: 0,
                    'money' => $this->transfer_fee,
                    'real_money' => $this->transfer_fee,
                    'status' => 10
                ]);


                // 清空证书
                $this->clearCert();

                if ($ret['return_code'] == 'SUCCESS') {
                    return true;
                }

                return error($ret['message']);
            } catch (\Throwable $e) {

                // 清空证书
                $this->clearCert();

                // 记录日志
                LogHelper::error('[WECHAT WECHAT WITHDRAW ERROR]:' . $e->getMessage(), ['params' => $transferOrder, 'ret' => $ret]);


                return error($e->getMessage(), PaymentException::WITHDRAW_WECHAT_ERROR);
            }
        }

        throw new \Exception('公众号暂不支持此类型转账');

    }

    /**
     * wechat 支付
     * @return array
     * @throws PaymentException
     */
    public function wechat(): array
    {
        $payConf = $this->getConfig(
            PayClientConstant::CLIENT_WECHAT,
            PayTypeConstant::getIdentify($this->pay_type));

        $config = $this->setWechatConfig($payConf);

        $payOrder = [
            //获取外部交易单号
            'out_trade_no' => $this->getOutTradeNo(),
            'body' => $this->body,
            'total_fee' => number_format($this->pay_price * 100, 0, ".", ""),
            'time_expire' => date('YmdHis', strtotime('+7days')),
            'attach' => Json::encode([
                'order_id' => $this->order_id,
                'type' => $this->order_type,
                'client_type' => $this->client_type,
                'order_no' => $this->order_no,
            ]),
        ];

        if ($payConf->pay_type == self::SERVICE_MODEL) {
            $payOrder['sub_openid'] = $this->openid;
        } else {
            $payOrder['openid'] = $this->openid;
        }

        try {
            // 支付
            $ret = $config->mp($payOrder);

            // 清空证书
            $this->clearCert();

            return ['data' => $ret->toArray()];
        } catch (\Throwable $e) {

            // 清空证书
            $this->clearCert();

            // 记录日志
            LogHelper::error('[WECHAT WECHAT PAY ERROR]:' . $e->getMessage(), $payOrder);

            return error('微信支付失败', PaymentException::PAY_WECHAT_ERROR);
        }


    }

    /**
     * alipay 支付
     * @return array
     * @throws PaymentException
     * @throws \yii\base\Exception
     */
    public function alipay(): array
    {
        $payConf = $this->getConfig(
            PayClientConstant::CLIENT_WECHAT,
            PayTypeConstant::getIdentify($this->pay_type));
        $config = $this->setAlipayConfig($payConf);

        $payOrder = [
            'out_trade_no' => $this->getOutTradeNo(),
            'subject' => $this->body,
            'total_amount' => number_format($this->pay_price, 2, ".", ""),
            'time_expire' => date('yyyy-MM-dd HH:mm', strtotime('+7 days')),
            'passback_params' => Json::encode([
                'order_id' => $this->order_id,
                'type' => $this->order_type,
                'client_type' => $this->client_type,
                'order_no' => $this->order_no,
            ]),
            'http_method' => 'GET'
        ];

        try {
            // 支付
            $ret = $config->wap($payOrder);

            // 清空证书
            $this->clearCert();

            return ['data' => $ret->headers->get('location')];
        } catch (\Throwable $e) {

            // 清空证书
            $this->clearCert();

            // 记录日志
            LogHelper::error('[WECHAT ALIPAY PAY ERROR]:' . $e->getMessage(), $payOrder);

            return error('支付宝支付失败', PaymentException::PAY_ALIPAY_ERROR);
        }
    }

    /**
     * wechat 退款
     * @return array|null|void
     * @throws PaymentException
     */
    public function wechatRefund()
    {
        $payConf = $this->getConfig(
            PayClientConstant::CLIENT_WECHAT,
            PayTypeConstant::getIdentify($this->pay_type));
        $config = $this->setWechatConfig($payConf, true, true);

        $order = current((array)$this->order);

        $where = [
            'pay_type' => PayTypeConstant::PAY_TYPE_WECHAT,
            'type' => $this->order_type
        ];

        if ($order['out_trade_no']) {
            $where['out_trade_no'] = $order['out_trade_no'];
        } else {
            $where['order_no'] = $order['order_no'];
        }

        //获取相同交易单号的支付总金额
        $totalPay = PayOrderModel::find()->where($where)->sum('pay_price');

        foreach ((array)$this->order as $orderIndex => $orderItem) {

            /**
             * @var $orderItem PayOrderModel
             */
            $refundOrder = [
                'out_trade_no' => $orderItem->out_trade_no ?: $orderItem->order_no . $this->change_price_count,
                'out_refund_no' => (string)(time() . mt_rand(1000, 9999)),
                'total_fee' => intval(round(floatval($totalPay) * 100)),
                'refund_fee' => intval(round(floatval($this->refund_fee) * 100)),
                'refund_desc' => $this->refund_desc,
            ];

            try {
                // 退款
                $ret = $config->refund($refundOrder)->toArray();

                // 清空证书
                $this->clearCert();

                if ($ret['return_code'] == 'SUCCESS' && $ret['return_msg'] == 'OK') {
                    $orderItem->refund_data = Json::encode($ret);
                    $orderItem->save();
                    return true;
                }

                return error($ret['message']);
            } catch (\Throwable $e) {

                // 清空证书
                $this->clearCert();

                // 记录日志
                LogHelper::error('[WECHAT WECHAT REFUND ERROR]:' . $e->getMessage(), $refundOrder);

                return error($e->getMessage(), PaymentException::REFUND_WECHAT_ERROR);
            }
        }
    }

    /**
     * alipay 退款
     * @return bool|array|void
     * @throws PaymentException
     * @throws \yii\base\Exception
     */
    public function alipayRefund()
    {
        $payConf = $this->getConfig(
            PayClientConstant::CLIENT_WECHAT,
            PayTypeConstant::getIdentify($this->pay_type));
        $config = $this->setAlipayConfig($payConf, true);


        foreach ($this->order as $orderIndex => $orderItem) {
            /**
             * @var $orderItem PayOrderModel
             */
            $refundOrder = [
                'out_trade_no' => $orderItem->out_trade_no ?: $orderItem->order_no . $this->change_price_count,
                'refund_amount' => number_format($this->refund_fee, 2, ".", ""),
                'out_request_no' => time(),
            ];

            try {
                // 退款
                $ret = $config->refund($refundOrder)->toArray();

                //清空日志
                $this->clearCert();

                if ($ret['code'] == 10000 && $ret['msg'] == 'Success') {
                    $orderItem->refund_data = Json::encode($ret);
                    $orderItem->save();
                    return true;

                }

                return error($ret['message']);
            } catch (\Throwable $e) {

                // 清空日志
                $this->clearCert();

                // 记录日志
                LogHelper::error('[WECHAT ALIPAY REFUND ERROR]:' . $e->getMessage(), $refundOrder);

                return error('支付宝退款失败', PaymentException::REFUND_ALIPAY_ERROR);
            }
        }
    }
}
