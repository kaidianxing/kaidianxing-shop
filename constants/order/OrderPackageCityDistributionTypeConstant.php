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

namespace shopstar\constants\order;

use shopstar\bases\constant\BaseConstant;

/**
 * 包裹同城配送方式
 * Class OrderPackageCityDistributionTypeConstant
 * @method getText($code) static
 * @method getIdentify($code) static
 * @package shopstar\constants\order
 * @author 青岛开店星信息技术有限公司
 */
class OrderPackageCityDistributionTypeConstant extends BaseConstant
{
    /**
     * @Text("商家配送")
     */
    public const MERCHANT = 0;

    /**
     * @Text("达达配送")
     * @Identify("dada")
     */
    public const DADA = 1;

    /**
     * @Text("码科配送")
     * @Identify("make")
     */
    public const MAKE = 2;

    /**
     * @Text("闪送配送")
     * @Identify("shansong")
     */
    public const SHANSONG = 3;

    /**
     * @Text("顺丰配送")
     * @Identify("sf")
     */
    public const SF = 4;
}