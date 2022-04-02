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

namespace shopstar\components\printer;

use shopstar\components\printer\bases\PrinterDriverConstant;
use Yii;
use yii\base\Component;

/**
 * 打印机组件
 * Class PrinterComponent
 * @package shopstar\components\printer
 * @author 青岛开店星信息技术有限公司
 */
class PrinterComponent extends Component
{

    private static $instance = null;

    /**
     * @var string 实例存储驱动类型
     */
    private static $instanceType = null;

    /**
     * 获取实例
     * @param string $storageType
     * @param array $config
     * @return array|object|null
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInstance(string $storageType, array $config = [])
    {
        if (is_null(self::$instance) || self::$instanceType != $storageType) {
            $storageType = strtolower($storageType);

            // 获取存储驱动
            $driver = PrinterDriverConstant::getDriver($storageType);
            if (!$driver) {
                return error("`{$storageType}` Storage Driver not Found.");
            }

            $config['class'] = $driver['class'];
            self::$instance = Yii::createObject($config);
        }

        return self::$instance;
    }
}