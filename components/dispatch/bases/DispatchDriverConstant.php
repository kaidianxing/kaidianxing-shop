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

namespace shopstar\components\dispatch\bases;

use shopstar\bases\constant\BaseConstant;

/**
 * 第三方配送驱动常量
 * Class DispatchDriverConstant
 * @method getMessage($code) static
 * @method getCode($code) static
 * @package shopstar\components\dispatch\bases
 * @author 青岛开店星信息技术有限公司
 */
class DispatchDriverConstant extends BaseConstant
{
    /**
     * @Message("达达")
     * @Code("1")
     */
    public const DRIVE_DADA = 'dada';

    /**
     * @Message("码科")
     * @Code("2")
     */
    public const DRIVER_MAKE = 'make';

    /**
     * @Message("闪送")
     * @Code("3")
     */
    public const DRIVER_SHANSONG = 'shansong';

    /**
     * @Message("顺丰")
     * @Code("4")
     */
    public const DRIVER_SF = 'sf';

    /**
     * @var array 映射Map
     */
    private static $map = [
        self::DRIVE_DADA => [
            'name' => '达达',
            'class' => 'shopstar\components\dispatch\driver\DadaDriver'
        ],
        self::DRIVER_MAKE => [
            'name' => '码科',
            'class' => 'shopstar\components\dispatch\driver\MakeDriver'
        ],
        self::DRIVER_SHANSONG => [
            'name' => '闪送',
            'class' => 'shopstar\components\dispatch\driver\ShansongDriver'
        ],
        self::DRIVER_SF => [
            'name' => '顺丰',
            'class' => 'shopstar\components\dispatch\driver\SfDriver'
        ]
    ];

    /**
     * 获取驱动
     * @param string $type 存储类型
     * @return bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDriver(string $type)
    {
        return self::$map[$type] ?? false;
    }
}