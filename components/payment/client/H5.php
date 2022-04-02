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
use shopstar\components\payment\base\WithdrawTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\LogHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\order\PayOrderModel;
use Yansongda\Pay\Exceptions\GatewayException;
use yii\helpers\Json;

/**
 * H5支付
 * Class H5
 * @package shopstar\components\payment\client
 * @author 青岛开店星信息技术有限公司
 */
class H5 extends BasePay implements PayClientInterface
{

    const CLIENT_TYPE = 'h5';

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
     * @return array|bool
     * @throws PaymentException
     * @throws \yii\base\Exception
     */
    public function transfer()
    {
        $payConf = self::getWithdrawConfig(self::CLIENT_TYPE, PayTypeConstant::getIdentify($this->transfer_type));
        // 支付宝转账
        if ($this->transfer_type == WithdrawTypeConstant::WITHDRAW_ALIPAY) {
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
                LogHelper::error('[H5 ALIPAY WITHDRAW ERROR]:' . $e->getMessage(), $transferOrder);

                return error(AlipayError::analysisError($e->raw));
            }
        }

        throw new \Exception('H5暂不支持此类型转账');
    }

    /**
     * @throws \Exception
     */
    public function wechat()
    {
        throw new \Exception('h5 undefined wechat method');
    }

    /**
     * 支付宝支付
     * @return array
     * @throws PaymentException
     */
    public function alipay()
    {
        $payConf = $this->getConfig(
            self::CLIENT_TYPE,
            PayTypeConstant::getIdentify($this->pay_type));

        $config = self::setAlipayConfig($payConf);

        $payOrder = [
            'out_trade_no' => $this->getOutTradeNo(),
            'subject' => $this->body,
            'total_amount' => number_format($this->pay_price, 2, ".", ""),
            'time_expire' => date('yyyy-MM-dd HH:mm', strtotime('+7 days')),
            'passback_params' => Json::encode([
                'order_id' => $this->order_id,
                'type' => $this->order_type,
                'order_no' => $this->order_no,
                'client_type' => $this->client_type,
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
            LogHelper::error('[H5 ALIPAY PAY ERROR]:' . $e->getMessage(), $payOrder);

            return error('支付宝支付失败', PaymentException::PAY_ALIPAY_ERROR);
        }

    }


    /**
     * alipay 退款
     * @return bool|array
     * @throws PaymentException|\yii\base\Exception
     */
    public function alipayRefund()
    {
        $payConf = $this->getConfig(
            PayClientConstant::CLIENT_H5,
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

                // 清空证书
                $this->clearCert();

                if ($ret['code'] == 10000 && $ret['msg'] == 'Success') {
                    $orderItem->refund_data = Json::encode($ret);
                    $orderItem->save();
                    return true;
                }

                return error($ret['message']);
            } catch (\Throwable $e) {

                // 清空证书
                $this->clearCert();

                // 记录日志
                LogHelper::error('[H5 ALIPAY REFUND ERROR]:' . $e->getMessage(), $refundOrder);

                return error('支付宝退款失败', PaymentException::REFUND_ALIPAY_ERROR);
            }
        }

        return true;
    }
}
