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

namespace shopstar\components\printer\bases;

use shopstar\bases\constant\BaseConstant;

/**
 * 打印机驱动常量
 * Class PrinterDriverConstant
 * @method getMessage($code) string
 * @package shopstar\components\printer\bases
 * @author 青岛开店星信息技术有限公司
 */
class PrinterDriverConstant extends BaseConstant
{
    /**
     * @message("易联云")
     */
    public const DRIVE_YLY = 'yly';

    public const DRIVER_FEY = 'fey';

    /**
     * @var array 映射Map
     */
    private static $map = [
        self::DRIVE_YLY => [
            'name' => '易联云',
            'class' => 'shopstar\components\printer\driver\YlyDriver'
        ],
        self::DRIVER_FEY => [
            'name' => '飞鹅云',
            'class' => 'shopstar\components\printer\driver\FeyDriver'
        ],

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