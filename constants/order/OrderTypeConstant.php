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
 * 订单类型const
 * Class OrderTypeConst
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\order
 */
class OrderTypeConstant extends BaseConstant
{
    /**
     * @Text("普通订单")
     */
    public const ORDER_TYPE_ORDINARY = 10;

    /**
     * @Text("虚拟订单")
     */
    public const ORDER_TYPE_VIRTUAL = 20;

    /**
     * @Text("虚拟卡密")
     */
    public const ORDER_TYPE_VIRTUAL_ACCOUNT = 21;

    /**
     * 积分商城兑换的优惠券订单
     * @Text("优惠券")
     */
    public const ORDER_TYPE_CREDIT_SHOP_COUPON = 40;

}
