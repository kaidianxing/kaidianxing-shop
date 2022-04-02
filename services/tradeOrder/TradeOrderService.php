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

namespace shopstar\services\tradeOrder;

/**
 * 交易订单服务类
 * Class TradeOrderService
 * @method static TradeOrderPay pay(array $config) 支付
 * @method static TradeOrderOperation operation(array $config) 操作
 * @method static TradeOrderNotify notify(array $config) 支付回调
 * @package shopstar\services\tradeOrder
 * @author likexin
 */
class TradeOrderService
{

    /**
     * @param string $method
     * @param array $params
     * @return array|object
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function __callStatic(string $method, array $params = [])
    {
        $class = __NAMESPACE__ . '\\TradeOrder' . ucfirst($method);

        if (class_exists($class)) {
            $params = $params[0] ?? [];
            $params['class'] = $class;
            return \Yii::createObject($params);
        }

        return error("Method [{$method}] Not Support");
    }


}