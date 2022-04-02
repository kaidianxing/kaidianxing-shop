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
use shopstar\components\payment\base\PayClientInterface;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\components\payment\base\WithdrawTypeConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\HttpHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 字节跳动支付
 * Class ByteDance
 * @package shopstar\components\payment\client
 * @author 青岛开店星信息技术有限公司
 */
class ByteDance extends BasePay implements PayClientInterface
{
    const CLIENT_TYPE = 'byte_dance';

    /**
     * @var string openid
     */
    public $openid;

    /**
     * 支付api
     * @var string
     */
    private $apiPay = "https://developer.toutiao.com/api/apps/ecpay/v1/create_order";

    /**
     * 退款api
     * @var string
     */
    private $apiRefund = "https://developer.toutiao.com/api/apps/ecpay/v1/create_refund";

    /**
     * 结算api
     * @var string
     */
    private $settleRefund = "https://developer.toutiao.com/api/apps/ecpay/v1/settle";

    /**
     * 支付
     * @throws PaymentException|\shopstar\exceptions\order\OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function pay()
    {
        // 写入订单记录
        $this->write();

        // 判断订单来源
        if ($this->order[0]['client_type'] < 30 || $this->order[0]['client_type'] > 32) {
            throw new PaymentException(PaymentException::PAY_CHANNEL_ERROR);
        }

        // 获取配置
        $settings = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance');
        if (empty($settings['salt'])) {
            throw new PaymentException(PaymentException::PAY_CONFIG_ERROR);
        }
        // 获取appid
        $appid = ShopSettings::get('channel_setting.byte_dance.appid');

        $extData = [
            'order_id' => $this->order_id,
            'type' => $this->order_type,
        ];

        // 支付
        $params = [
            'app_id' => $appid,
            'out_order_no' => $this->getOutTradeNo(),
            'total_amount' => (int)($this->pay_price * 100),
            'subject' => $this->body,
            'body' => $this->body,
            'valid_time' => 3600,
            'cp_extra' => Json::encode($extData)
        ];
        $params['sign'] = $this->createSign($params, $settings['salt']);
        $params = Json::encode($params);

        $res = HttpHelper::post($this->apiPay, $params);

        $res = Json::decode($res);
        if ($res['err_no'] != 0) {
            LogHelper::error('[BYTEDANCE PAY ERROR]:' . $res['err_tips'], Json::decode($params));
            return error($res['err_tips']);
        }

        return ['data' => $res['data']];
    }

    /**
     * 退款
     * @throws PaymentException
     * @throws \shopstar\exceptions\order\OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function refund()
    {

        // 写入订单记录
        $this->write();

        // 获取配置
        $settings = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance');
        if (empty($settings['salt'])) {
            throw new PaymentException(PaymentException::PAY_CONFIG_ERROR);
        }

        // 获取appid
        $appid = ShopSettings::get('channel_setting.byte_dance.appid');

        foreach ((array)$this->order as $orderIndex => $orderItem) {
            // 退款
            $params = [
                'app_id' => $appid,
                'out_order_no' => $orderItem['out_trade_no'],
                'out_refund_no' => (string)(time() . mt_rand(1000, 9999)),
                'refund_amount' => intval(round(floatval($this->refund_fee) * 100)),
                'reason' => $this->refund_desc
            ];

            $params['sign'] = $this->createSign($params, $settings['salt']);

            $params = Json::encode($params);

            $res = HttpHelper::post($this->apiRefund, $params);

            $res = Json::decode($res);

            if ($res['err_no'] != 0) {
                LogHelper::error('[BYTEDANCE REFUND ERROR ERROR]:' . $res['err_tips'], Json::decode($params));
                return error($res['err_tips']);
            }

            $orderItem->refund_data = Json::encode($res);
            $orderItem->save();
        }
    }

    /**
     * 转账
     * @throws PaymentException
     * @throws \yii\base\Exception
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
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
                return error($ret['message'], $ret['code']);
            } catch (\Throwable $e) {

                // 清空证书
                $this->clearCert();

                // 记录日志
                LogHelper::error('[BYTE DANCE ALIPAY WITHDRAW ERROR]:' . $e->getMessage(), $transferOrder);

                return error($e->getMessage(), PaymentException::WITHDRAW_ALIPAY_ERROR);
            }
        }

        throw new \Exception('H5暂不支持此类型转账');
    }

    /**
     * 生成签名
     * @param $params
     * @param $payAppSecret
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function createSign($params, $payAppSecret): string
    {
        unset($params["sign"]);
        unset($params["app_id"]);
        unset($params["thirdparty_id"]);
        $paramArray = [];
        foreach ($params as $param) {
            $paramArray[] = trim($param);
        }
        $paramArray[] = trim($payAppSecret);
        sort($paramArray, 2);
        $signStr = trim(implode('&', $paramArray));
        return md5($signStr);
    }

}