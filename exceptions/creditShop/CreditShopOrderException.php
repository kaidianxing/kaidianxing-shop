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

namespace shopstar\exceptions\creditShop;

use shopstar\bases\exception\BaseException;

/**
 * 积分商城订单异常类
 * Class CreditShopOrderException.
 * @package shopstar\exceptions\creditShop
 */
class CreditShopOrderException extends BaseException
{
    /**
     * @Message("商品不存在")
     */
    public const GOODS_HANDLER_CREDIT_SHOP_GOODS_NOT_EXISTS = 521000;

    /**
     * @Message("缺少商品购买权限")
     */
    public const GOODS_HANDLER_CREDIT_SHOP_NOT_BUY_LIMIT = 521001;

    /**
     * @Message("库存不足")
     */
    public const GOODS_HANDLER_CREDIT_SHOP_STOCK_INVALID_ERROR = 521002;

    /**
     * @Message("商品不支持同城配送发货")
     */
    public const GOODS_HANDLER_CREDIT_SHOP_INTRACITY_UNABLE = 521003;

    /**
     * @Message("积分商城未开启")
     */
    public const INIT_HANDLER_CREDIT_SHOP_STATUS_ERROR = 521004;

    /**
     * @Message("普通快递配送方式未开启")
     */
    public const DISPATCH_HANDLER_CREDIT_SHOP_DISPATCH_EXPRESS_ERROR = 521005;

    /**
     * @Message("运费计算失败")
     */
    public const DISPATCH_HANDLER_CREDIT_SHOP_DELIVERY_PRICE_ERROR = 521006;

    /**
     * @Message("运费模板不存在")
     */
    public const DISPATCH_HANDLER_CREDIT_SHOP_TEMPLATE_NOT_FOUND_ERROR = 521007;

    /**
     * @Message("不在可配送范围内")
     */
    public const DISPATCH_HANDLER_CREDIT_SHOP_NOT_IN_DELIVERY_AREA_ERROR = 521008;

    /**
     * @Message("创建订单失败")
     */
    public const ORDER_SAVE_HANDLER_CREATE_ORDER_ERROR = 521009;

    /**
     * @Message("创建订单商品失败")
     */
    public const ORDER_SAVE_HANDLER_CREATE_ORDER_GOODS_ERROR = 521010;

    /**
     * @Message("创建订单虚拟卡密数据失败")
     */
    public const ORDER_SAVE_HANDLER_CREATE_ORDER_VIRTUAL_ACCOUNT_ERROR = 521011;

    /**
     * @Message("创建订单失败")
     */
    public const ORDER_SAVE_HANDLER_ORDER_GOODS_ORDER_SAVE_ERROR = 521012;

    /**
     * @Message("积分不足")
     */
    public const GOODS_HANDLER_SUBMIT_MEMBER_CREDIT_NOT_ENOUGH = 521013;

    /**
     * @Message("超过购买限制")
     */
    public const GOODS_HANDLER_GOODS_MAX_BUY_ERROR = 521014;

    /**
     * @Message("创建订单失败")
     */
    public const ORDER_SAVE_HANDLER_CREDIT_SHOP_ORDER_SAVE_ERROR = 521015;

    /**
     * @Message("库存修改失败")
     */
    public const GOODS_HANDLER_UPDATE_STOCK_ERROR = 521016;

    /**
     * @Message("库存不足")
     */
    public const ORDER_PAY_STOCK_ERROR = 521017;
}
