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
use shopstar\constants\ClientTypeConstant;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Pay;

/**
 * 支付宝驱动类
 * Class AlipayDriver
 * @package shopstar\components\paymentNew
 * @author likexin
 */
class AlipayDriver extends BasePaymentNewDriver implements PaymentNewDriverInterface
{

    /**
     * 生成当前支付配置
     * @return array
     * @author likexin
     */
    public function buildConfig(): array
    {
        $config = [
            'app_id' => $this->paymentModel->appid,
            'private_key' => $this->paymentModel->ali_private_key,
            'ali_public_key' => $this->buildTmpCert($this->paymentModel->alipay_cert_public_key_rsa2, 'crt'),
            'app_cert_public_key' => $this->buildTmpCert($this->paymentModel->app_cert_public_key, 'crt'),
            'alipay_root_cert' => $this->buildTmpCert($this->paymentModel->alipay_root_cert, 'crt'),

            'notify_url' => $this->notifyUrl,
            'return_url' => $this->callbackUrl ?? '',

            // 记录日志
            'log' => [
                'file' => SHOP_STAR_RUNTIME_PATH . '/logs/payment_logs/alipay.log',
                'level' => YII_DEBUG ? 'debug' : 'info',
                'type' => 'daily',
                'max_file' => 30,
            ],
        ];

        // 服务商模式
        if ($this->isServiceMode) {
            // app_id
            $config['pid'] = $this->paymentModel->appid;
            $config['mode'] = 'service';
        }

        return $config;
    }

    /**
     * 生成当前支付订单参数
     * @return array
     * @author likexin
     */
    public function buildOrderParams(): array
    {
        return [
            'out_trade_no' => $this->tradeNo,
            'subject' => $this->subject,
            'total_amount' => (string)$this->orderPrice,
            'time_expire' => $this->closeTime,
            'http_method' => 'GET',
        ];
    }

    /**
     * 获取客户端映射
     * @return array
     * @author likexin
     */
    public function getClientMap(): array
    {
        return [
            ClientTypeConstant::CLIENT_WECHAT => 'wap',
            ClientTypeConstant::CLIENT_WXAPP => 'wap',
            ClientTypeConstant::CLIENT_H5 => 'wap',
            ClientTypeConstant::MANAGE_PC => 'scan',

        ];
    }

    /**
     * 统一下单
     * @return array
     * @author likexin
     */
    public function unify(): array
    {
        // 根据clientType获取method
        $method = $this->getClientMap()[$this->clientType];
        if (!$method) {
            return error('不支持的clientType: ' . $this->clientType);
        }

        try {
            // 调用支付SDK
            if ($this->clientType == ClientTypeConstant::MANAGE_PC) {
                $pay = Pay::alipay($this->buildConfig())->$method($this->buildOrderParams())->qr_code;
            } else {
                $pay = Pay::alipay($this->buildConfig())->$method($this->buildOrderParams())->headers->get('location');
            }

        } catch (GatewayException $exception) {
            return error($exception->getMessage());
        } finally {
            // 清理证书
            $this->clearTmpCert();
        }

        return [
            'pay_params' => [
                'pay_url' => $pay,
            ],
            'payment_id' => $this->paymentId,
        ];
    }

    /**
     * 关闭支付订单
     * @return array
     * @author likexin
     */
    public function close(): array
    {
        try {
            Pay::alipay($this->buildConfig())->close($this->tradeNo);
        } catch (GatewayException $exception) {
            return error($exception->getMessage());
        } catch (InvalidConfigException $exception) {
            return error($exception->getMessage());
        } catch (InvalidSignException $exception) {
            return error($exception->getMessage());
        } finally {
            // 清理证书
            $this->clearTmpCert();
        }

        return success();
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
        try {
            // 调用三方SDK进行退款
            Pay::alipay($this->buildConfig())->refund([
                'out_request_no' => $refundNo,
                'out_trade_no' => $this->tradeNo,
                'refund_amount' => $refundPrice,
                'refund_reason' => $this->subject,
            ]);
        } catch (GatewayException $exception) {
            return error($exception->getMessage());
        } catch (InvalidConfigException $exception) {
            return error($exception->getMessage());
        } catch (InvalidSignException $exception) {
            return error($exception->getMessage());
        } finally {
            // 清理证书
            $this->clearTmpCert();
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
        try {
            Pay::alipay($this->buildConfig())->verify($data);
        } catch (InvalidConfigException $exception) {
            return error($exception->getMessage());
        } catch (InvalidSignException $exception) {
            return error($exception->getMessage());
        }

        return success();
    }

}