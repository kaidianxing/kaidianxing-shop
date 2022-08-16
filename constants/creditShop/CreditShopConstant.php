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

namespace shopstar\constants\creditShop;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分商城常量类
 * Class CreditShopConstant.
 * @package shopstar\constants\creditShop
 */
class CreditShopConstant extends BaseConstant
{
    /**
     * 会员等级限制
     * @Text("不限制")
     */
    public const MEMBER_LEVEL_LIMIT_TYPE_NOT_LIMIT = 0;

    /**
     * 会员等级限制
     * @Text("指定会员等级可购买")
     */
    public const MEMBER_LEVEL_LIMIT_TYPE_ALLOW = 1;

    /**
     * 会员等级限制
     * @Text("指定会员等级不可购买")
     */
    public const MEMBER_LEVEL_LIMIT_TYPE_DENY = 2;

    /**
     * 会员标签限制
     * @Text("不限制")
     */
    public const MEMBER_GROUP_LIMIT_TYPE_NOT_LIMIT = 0;

    /**
     * 会员标签限制
     * @Text("指定会员标签可购买")
     */
    public const MEMBER_GROUP_LIMIT_TYPE_ALLOW = 1;

    /**
     * 会员标签限制
     * @Text("指定会员标签不可购买")
     */
    public const MEMBER_GROUP_LIMIT_TYPE_DENY = 2;

    /**
     * 商品购买限制
     * @Text("不限制")
     */
    public const GOODS_LIMIT_TYPE_NOT_LIMIT = 0;

    /**
     * 商品购买限制
     * @Text("每人限购")
     */
    public const GOODS_LIMIT_TYPE_LIMIT_NUM = 1;

    /**
     * 商品购买限制
     * @Text("每人/天限购")
     */
    public const GOODS_LIMIT_TYPE_LIMIT_DAY = 2;
}
