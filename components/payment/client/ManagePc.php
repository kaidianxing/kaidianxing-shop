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

use shopstar\components\payment\base\BasePay;
use shopstar\components\payment\base\PayClientConstant;
use shopstar\components\payment\base\PayClientInterface;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\LogHelper;
use yii\helpers\Json;

/**
 * 商家端PC支付
 * Class ManagePc
 * @package shopstar\components\payment\client
 * @author 青岛开店星信息技术有限公司
 */
class ManagePc extends BasePay implements PayClientInterface
{
    const CLIENT_TYPE = 'pc';

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

        // 获取支付方式
        $payType = PayTypeConstant::getIdentify($this->pay_type);

        // 校验支付类型
        $this->checkPayType($payType);

        return $this->$payType();
    }

    /**
     * 微信支付
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function wechat(): array
    {
        $config = $this->setWechatConfig(
            $this->getConfig(
                PayClientConstant::CLIENT_WECHAT,
                PayTypeConstant::getIdentify($this->pay_type))
        );

        $payOrder = [
            'out_trade_no' => $this->getOutTradeNo(),
            'body' => $this->body,
            'total_fee' => number_format($this->pay_price * 100, 0, ".", ""),
            'time_expire' => date('YmdHis', strtotime('+7days')),
            'attach' => Json::encode([
                'order_id' => $this->order_id,
                'type' => $this->order_type,
                'client_type' => $this->client_type,
            ]),
        ];

        try {
            // 支付
            $ret = $config->scan($payOrder);

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
     * 支付宝支付
     * @return array
     * @throws PaymentException|\yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function alipay(): array
    {
        $payConf = $this->getConfig(
            self::CLIENT_TYPE,
            PayTypeConstant::getIdentify($this->pay_type));

        //绑定回调
        $this->extendParams = Json::encode([
            'order_id' => $this->order_id,
            'type' => $this->order_type,
            'client_type' => $this->client_type,
        ]);

        $config = self::setAlipayConfig($payConf);

        $payOrder = [
            'out_trade_no' => $this->getOutTradeNo(),
            'subject' => $this->body,
            'total_amount' => round2($this->pay_price, 2),
            'http_method' => 'GET'
        ];

        try {
            // 支付
            $ret = $config->scan($payOrder);

            // 清空证书
            $this->clearCert();

            return ['data' => $ret->qr_code];
        } catch (\Throwable $e) {
            // 清空证书
            $this->clearCert();
            // 记录日志
            LogHelper::error('[H5 ALIPAY PAY ERROR]:' . $e->getMessage(), $payOrder);
            return error('支付宝支付失败', PaymentException::PAY_ALIPAY_ERROR);
        }
    }

    /**
     * 退款
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function refund()
    {
    }

    /**
     * 转账
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function transfer()
    {
    }

    /**
     * 退款
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function alipayRefund()
    {
    }

    public function close()
    {

    }

    public function alipayClose()
    {

    }
}
