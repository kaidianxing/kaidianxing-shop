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

namespace shopstar\constants\printer;

use shopstar\bases\constant\BaseConstant;

/**
 * 打印机类型
 * Class PrinterTypeConstant
 * @package shopstar\constants\printer
 * @method getIdentify($code) static 获取标识
 */
class PrinterTypeConstant extends BaseConstant
{
//    /**
//     * @Text("易联云K1、K2、K3")
//     */
//    public const PRINTER_YLY_K1K2K3 = 1;
//
//    /**
//     * @Text("易联云K2S、K3S、K4、M1")
//     */
//    public const PRINTER_YLY_K2SK3SK4M1 = 2;

    /**
     * @Text("易联云auth2.0接口(支持K4及以上)")
     * @Identify("yly")
     */
    public const PRINTER_YLY_AUTH = 1;

    /**
     * @Text("飞鹅打印机")
     * @Identify("fey")
     */
    public const PRINTER_FEY = 2;

//    /**
//     * @Text("飞鹅打印机(新接口)")
//     */
//    public const PRINTER_FEY_NEW = 5;
}