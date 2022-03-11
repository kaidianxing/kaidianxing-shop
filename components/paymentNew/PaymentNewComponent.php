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


namespace shopstar\components\paymentNew;

use shopstar\components\paymentNew\bases\PaymentNewDriverConstant;
use shopstar\components\paymentNew\bases\PaymentNewDriverInterface;
use Yii;

/**
 * 新支付组件
 * Class PaymentNewComponent
 * @package shopstar\components\paymentNew
 * @author likexin
 */
class PaymentNewComponent
{
    /**
     * @var PaymentNewDriverInterface 支付驱动实例
     */
    private static $instance = null;

    /**
     * @var string 实例支付驱动类型
     */
    private static $instanceType = null;

    /**
     * 获取实例
     * @param string $payDriver 支付驱动类型
     * @param array $params 参数
     * @return PaymentNewDriverInterface|array
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getInstance(string $payDriver, array $params = [])
    {
        if (is_null(self::$instance) || self::$instanceType != $payDriver) {
            $payDriver = strtolower($payDriver);

            // 获取存储驱动
            $driverClass = PaymentNewDriverConstant::getDriver($payDriver);
            if (!$driverClass) {
                return error("`{$payDriver}` Payment Driver not Found.");
            }

            $params['payTypeIdentity'] = $payDriver;
            $params['class'] = $driverClass;
            self::$instance = Yii::createObject($params);
        }

        return self::$instance;
    }

}