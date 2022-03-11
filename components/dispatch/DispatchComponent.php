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

namespace shopstar\components\dispatch;

use shopstar\components\dispatch\bases\DispatchDriverConstant;
use Yii;
use yii\base\Component;


/**
 * 第三方配送
 * Class DispatchComponent
 * @package shopstar\components\dispatch
 */
class DispatchComponent extends Component
{
    private static $instance = null;

    /**
     * @var string 实例存储驱动类型
     */
    private static $instanceType = null;

    /**
     * 获取实例
     */
    public static function getInstance(string $dispatchType, array $config = [])
    {
        if (is_null(self::$instance) || self::$instanceType != $dispatchType) {
            $dispatchType = strtolower($dispatchType);

            // 获取配送驱动
            $driver = DispatchDriverConstant::getDriver($dispatchType);
            if (!$driver) {
                return error("`{$dispatchType}` Dispatch Driver not Found.");
            }

            $config['class'] = $driver['class'];
            self::$instance = Yii::createObject($config);
        }

        return self::$instance;
    }
}