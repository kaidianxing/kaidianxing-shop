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

namespace shopstar\components\electronicSheet;

use shopstar\components\electronicSheet\bases\ElectronicSheetApiConstant;
use shopstar\components\electronicSheet\bases\ElectronicSheetApiInterface;
use Yii;

/**
 * Class ElectronicSheetComponents
 * @package shopstar\components\electronicSheet\bases
 * @author 青岛开店星信息技术有限公司
 */
class ElectronicSheetComponents
{

    /**
     * @var ElectronicSheetApiInterface 存储驱动接口
     */
    private static $instance = null;

    /**
     * api类型
     * @var null
     * @author 青岛开店星信息技术有限公司
     */
    private static $apiType = null;

    /**
     * 获取实例
     * @param string $apiType
     * @return array|ElectronicSheetApiInterface|object|null
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInstance(string $apiType)
    {
        if (is_null(self::$instance) || self::$apiType != $apiType) {
            $apiType = strtolower($apiType);

            // 获取实现类
            $class = ElectronicSheetApiConstant::getClass($apiType);
            if (!$class) {
                return error("`{$apiType}` API not Found.");
            }

            // 注入固定参数
            $config = [
                'class' => $class,
            ];

            // 注入实现类
            self::$instance = Yii::createObject($config);
            unset($config['class']);
        }

        return self::$instance;
    }

}