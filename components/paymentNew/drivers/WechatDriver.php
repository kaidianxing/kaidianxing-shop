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
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Pay;

/**
 * 微信支付驱动类
 * Class WechatDriver
 * @package shopstar\components\paymentNew\drivers
 * @author likexin
 */
class WechatDriver extends BasePaymentNewDriver implements PaymentNewDriverInterface
{

    /**
     * @var string|null 微信OPENID
     */
    public $openid;

    /**
     * 生成当前支付配置
     * @param bool $withCert 携带证书
     * @return array
     * @author likexin
     */
    public function buildConfig(bool $withCert = false): array
    {
        // 根据渠道不同定义app_id的key值
        $appIdKey = $this->clientType == ClientTypeConstant::CLIENT_WXAPP ? 'miniapp_id' : 'app_id';

        $config = [
            'miniapp_id' => $this->paymentModel->sub_appid,
            'app_id' => $this->paymentModel->sub_appid,
            'mch_id' => $this->paymentModel->sub_mch_id,
            'key' => $this->paymentModel->api_key,

            'notify_url' => $this->notifyUrl,

            // 记录日志
            'log' => [
                'file' => SHOP_STAR_RUNTIME_PATH . '/logs/payment_logs/wechat.log',
                'level' => YII_DEBUG ? 'debug' : 'info',
                'type' => 'daily',
                'max_file' => 30,
            ],
        ];

        // 服务商模式
        if ($this->isServiceMode) {
            // app_id
            $config[$appIdKey] = $this->paymentModel->appid;
            // sub_app_id
            $config['sub_' . $appIdKey] = $this->paymentModel->sub_appid;

            $config['mch_id'] = $this->paymentModel->mch_id;
            $config['sub_mch_id'] = $this->paymentModel->sub_mch_id;
            $config['mode'] = 'service';
        }

        // 处理证书
        if ($withCert) {
            $config['cert_key'] = self::buildTmpCert($this->paymentModel->wechat_key, 'pem');
            $config['cert_client'] = self::buildTmpCert($this->paymentModel->wechat_cert, 'cert');
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
        $openid = $this->isServiceMode ? 'sub_openid' : 'openid';

        return [
            'out_trade_no' => $this->tradeNo,
            'body' => $this->subject,
            'total_fee' => $this->orderPrice * 100,
            $openid => $this->openid,
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
            ClientTypeConstant::CLIENT_WECHAT => 'mp',
            ClientTypeConstant::CLIENT_WXAPP => 'miniapp',
            ClientTypeConstant::CLIENT_H5 => 'wap',
            ClientTypeConstant::MANAGE_PC => 'scan',
        ];
    }

    /**
     * 统一下单(此处可根据业务逻辑进行转发)
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
            if ($this->clientType == ClientTypeConstant::CLIENT_H5) {
                $pay = Pay::wechat($this->buildConfig())->wap($this->buildOrderParams())->headers->get('');
            } else {
                $pay = Pay::wechat($this->buildConfig())->$method($this->buildOrderParams())->toArray();
            }
        } catch (InvalidArgumentException | GatewayException $exception) {
            return error($exception->getMessage());
        } finally {
            // 清理证书
            $this->clearTmpCert();
        }

        return ['pay_params' => $pay];
    }

    /**
     * @return array
     * @author likexin
     */
    public function close(): array
    {
        try {
            $pay = Pay::wechat($this->buildConfig())->close($this->tradeNo)->toArray();
        } catch (InvalidArgumentException | GatewayException | InvalidSignException $exception) {
            return error($exception->getMessage());
        } finally {
            // 清理证书
            $this->clearTmpCert();
        }

        return $pay;
    }

    /**
     * 退款
     * @param float $orderPrice 订单总金额
     * @param float $refundPrice 退款金额
     * @param string $refundNo 退款编号
     * @return array
     * @throws InvalidArgumentException
     * @author likexin
     */
    public function refund(float $orderPrice, float $refundPrice, string $refundNo): array
    {
        try {
            // 调用三方SDK进行退款
            Pay::wechat($this->buildConfig(true))->refund([
                'out_refund_no' => $refundNo,
                'out_trade_no' => $this->tradeNo,
                'total_fee' => $orderPrice * 100,
                'refund_fee' => $refundPrice * 100,
            ]);
        } catch (GatewayException | InvalidConfigException | InvalidSignException $exception) {
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
     * @throws InvalidArgumentException
     * @author likexin
     */
    public function verifySign($data): array
    {
        try {
            Pay::wechat($this->buildConfig())->verify($data);
        } catch (InvalidConfigException | InvalidSignException $exception) {
            return error($exception->getMessage());
        }

        return success();
    }

}