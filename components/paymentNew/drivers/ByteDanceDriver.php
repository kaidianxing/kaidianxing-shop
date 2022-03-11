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


namespace shopstar\components\paymentNew\drivers;

use shopstar\components\paymentNew\bases\BasePaymentNewDriver;
use shopstar\components\paymentNew\bases\PaymentNewDriverInterface;
use shopstar\helpers\HttpHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * 字节跳动驱动类
 * Class ByteDanceDriver
 * @package shopstar\components\paymentNew\drivers
 */
class ByteDanceDriver extends BasePaymentNewDriver implements PaymentNewDriverInterface
{
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
    private $settleApi = 'https://developer.toutiao.com/api/apps/ecpay/v1/settle';

    /**
     * 生成当前支付配置
     * @return array
     * @author likexin
     */
    public function buildConfig(): array
    {
        $extData = [];

        $notifyUrl = Url::base(true) . '/notify/pay.php?bytedance=1';
        $config = [
            'app_id' => $this->bytedanceAppid,
            'out_order_no' => $this->tradeNo,
            'total_amount' => $this->orderPrice * 100,
            'subject' => $this->subject,
            'body' => $this->subject,
            'notify_url' => $notifyUrl,
            'valid_time' => 3600,
            'cp_extra' => Json::encode($extData)
        ];
        $config['sign'] = $this->createSign($config);

        return $config;
    }

    /**
     * 生成当前支付订单参数
     * @return array
     * @author likexin
     */
    public function buildOrderParams(): array
    {
        return [];
    }
    
    /**
     * 统一下单
     * @return array
     * @author likexin
     */
    public function unify(): array
    {
        $params = Json::encode($this->buildConfig());
        $res = HttpHelper::post($this->apiPay, $params);
    
        $res = Json::decode($res);
        if ($res['err_no'] != 0) {
            LogHelper::error('[BYTEDANCE PAY ERROR]:' . $res['err_tips'], Json::decode($params));
            return error($res['err_tips']);
        }

        return ['pay_params' => $res['data']];
    }


    /**
     * 退款
     * @param float $orderPrice 订单总金额
     * @param float $refundPrice 退款金额
     * @param string $refundNo 退款编号
     * @return array
     * @author likexin
     */
    public function refund(float $orderPrice, float $refundPrice, string $refundNo): array
    {
        $config = [
            'app_id' => $this->bytedanceAppid,
            'out_order_no' => $this->tradeNo,
            'out_refund_no' => $refundNo,
            'refund_amount' => $refundPrice * 100,
            'reason' => '退款'
        ];
    
        $config['sign'] = $this->createSign($config);
    
        $config = Json::encode($config);
    
        $res = HttpHelper::post($this->apiRefund, $config);
        $res = Json::decode($res);
        
        if ($res['err_no'] != 0) {
            LogHelper::error('[BYTEDANCE REFUND ERROR ERROR]:' . $res['err_tips'], Json::decode($config));
            return error($res['err_tips']);
        }
        
        return success();
    }

    /**
     * 验签
     * @param $data
     * @return array
     * @author likexin
     */
    public function verifySign($data): array
    {
        $data = Json::decode($data);
        // 取设置的token
        $token = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance.token');
        $signData = [
            $token,
            $data['timestamp'],
            $data['nonce'],
            $data['msg']
        ];
        sort($signData,2);
        $sign = sha1(implode('', $signData));
        if ($sign != $data['msg_signature']) {
            LogHelper::info('[bytedance_notify]-验签失败', $data);
            return error('验签失败');
        }
        return success();
    }
    
    /**
     * 生成签名
     * @param $params
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function createSign($params): string
    {
        $settings = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance');
        unset($params["app_id"]);
        $paramArray = [];
        foreach ($params as $param) {
            $paramArray[] = trim($param);
        }
        $paramArray[] = trim($settings['salt']);
        sort($paramArray, 2);
        $signStr = trim(implode('&', $paramArray));
        return md5($signStr);
    }
    
    /**
     * 获取支付方法
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getClientMap(): array
    {
        return [];
    }
    
    
    /**
     * 关闭支付订单
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function close(): array
    {
        return [];
    }
}