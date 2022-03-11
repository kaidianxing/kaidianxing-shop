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

namespace shopstar\models\order\create;

/**
 * 订单创建事件
 * Class OrderCreatorEvent
 * @package App\Model\Logic\OrderCreator
 */
class OrderCreatorEventAssistant
{
    /**
     * 事件列表
     * [class,method]
     * @var array
     */
    public static $eventList = [];

    /**
     * 注册事件
     * @param string $eventSign
     * @param $eventFunction
     */
    public static function register(string $eventSign, $eventFunction)
    {
        // 初始化数组
        if (empty(static::$eventList[$eventSign])) {
            static::$eventList[$eventSign] = [];
        }
        static::$eventList[$eventSign][] = $eventFunction;
    }

    /**
     * 触发事件
     * @param string $eventSign
     * @param OrderCreatorKernel $orderCreator
     * @author 青岛开店星信息技术有限公司
     */
    public static function trigger(string $eventSign, OrderCreatorKernel $orderCreator)
    {
        if (empty(static::$eventList[$eventSign]) || !is_array(static::$eventList[$eventSign])) {
            return;
        }

        foreach (static::$eventList[$eventSign] as $callable) {
            if (!is_array($callable)) {
                continue;
            }

            $method = $callable[1];
            if (!class_exists($callable[0]) || !method_exists($callable[0], $method)) {
                continue;
            }
            $callable[0]::$method($orderCreator);
        }
    }

}