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

namespace shopstar\constants\coupon;

use shopstar\bases\constant\BaseConstant;

/**
 * 优惠券常量
 * Class CouponConst
 * @method getMessage($code)
 * @package shopstar\constants\coupon
 */
class CouponConstant extends BaseConstant
{
    /**
     * @Message("未使用")
     */
    public const COUPON_LIST_TYPE_NORMAL = 1;

    /**
     * @Message("已使用")
     */
    public const COUPON_LIST_TYPE_USED = 2;

    /**
     * @Message("已过期")
     */
    public const COUPON_LIST_TYPE_EXPIRE = 3;
    
    
    
    /**
     * @Message("优惠券优惠类型 立减")
     */
    public const COUPON_SALE_TYPE_SUB = 1;
    /**
     * @Message("优惠券优惠类型 折扣")
     */
    public const COUPON_SALE_TYPE_SCALE = 2;
    
    
    /**
     * @Message("每人领取张数限制类型 不限制")
     */
    public const COUPON_GET_MAX_TYPE_NOT_LIMIT = 0;
    /**
     * @Message("每人领取张数限制类型 限制")
     */
    public const COUPON_GET_MAX_TYPE_LIMIT = 1;
    
    
    /**
     * @Message("发送数量限制 无限制")
     */
    public const COUPON_STOCK_TYPE_NOT_LIMIT = 0;
    /**
     * @Message("发送数量限制 限制")
     */
    public const COUPON_STOCK_TYPE_LIMIT = 1;
    
    
    /**
     * @Message("时间限制类型 时间区间")
     */
    public const COUPON_TIME_LIMIT_AREA = 0;
    /**
     * @Message("时间限制类型 有效天数")
     */
    public const COUPON_TIME_LIMIT_DAYS = 1;
    
    
    
    /**
     * @Message("领取类型 领券中心")
     */
    public const COUPON_PICK_TYPE_CENTER = 0;
    /**
     * @Message("领取类型 链接领取")
     */
    public const COUPON_PICK_TYPE_LINK = 1;
    /**
     * @Message("领取类型 活动领取")
     */
    public const COUPON_PICK_TYPE_ACTIVITY = 2;
    
    
    
    /**
     * @Message("免费领取")
     */
    public const IS_FREE = 1;
    /**
     * @Message("不免费领取")
     */
    public const IS_NOT_FREE = 0;
    
    
    
    /**
     * @Message("是否限制领取会员等级  限制")
     */
    public const COUPON_LIMIT_MEMBER = 1;
    /**
     * @Message("是否限制领取会员等级  不限制")
     */
    public const COUPON_NOT_LIMIT_MEMBER = 0;
    
    
    
    /**
     * @Message("优惠使用限制  限制")
     */
    public const COUPON_SALE_LIMIT = 1;
    /**
     * @Message("优惠使用限制  不限制")
     */
    public const COUPON_SALE_NOT_LIMIT = 0;
    
    
    
    /**
     * @Message("商品使用限制  不限制")
     */
    public const COUPON_GOODS_NOT_LIMIT = 0;
    /**
     * @Message("商品使用限制  1允许以下产品使用")
     */
    public const COUPON_GOODS_LIMIT_ALLOW_GOODS = 1;
    /**
     * @Message("商品使用限制  2不允许一下产品使用")
     */
    public const COUPON_GOODS_LIMIT_NOT_ALLOW_GOODS = 2;
    /**
     * @Message("商品使用限制  3允许以下分类使用")
     */
    public const COUPON_GOODS_LIMIT_ALLOW_GOODS_CATE = 3;
    
    
    
    
    
}