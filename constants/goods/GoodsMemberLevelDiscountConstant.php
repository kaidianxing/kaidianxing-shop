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

namespace shopstar\constants\goods;

use shopstar\bases\constant\BaseConstant;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsMemberLevelDiscountConstant extends BaseConstant
{

    /**
     * @Text("不支持")
     */
    public const GOODS_MEMBER_LEVEL_DISCOUNT_NOT_SUPPORTED = 0;

    /**
     * @Text("系统默认")
     */
    public const GOODS_MEMBER_LEVEL_DISCOUNT_SYSTEM_DEFAULT = 1;

    /**
     * @Text("单商品")
     */
    public const GOODS_MEMBER_LEVEL_DISCOUNT_GOODS = 2;

    /**
     * @Text("按规格")
     */
    public const GOODS_MEMBER_LEVEL_DISCOUNT_OPTION = 3;

}
