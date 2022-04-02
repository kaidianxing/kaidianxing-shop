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

namespace shopstar\exceptions\order;

use shopstar\bases\exception\BaseException;

/**
 * 手机端订单创建异常  23
 * Class OrderCreatorException
 * @method getMessages($code) static string
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\order
 */
class OrderCreatorException extends BaseException
{
    /******************************核心处理器100******************************/

    /**
     * @Message("关键参数无效")
     */
    const ORDER_CREATOR_KERNEL_PARAMS_INVALID_ERROR = 230100;

    /**
     * @Message("商品不合法")
     */
    const ORDER_CREATOR_KERNEL_GOODS_INVALID_ERROR = 230101;

    /**
     * @Message("会员不存在")
     */
    const ORDER_CREATOR_KERNEL_MEMBER_HANDLER_MEMBER_NOT_FOUND_ERROR = 230102;

    /**
     * @Message("您所选商品中有下架商品，请重新选择商品支付。")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_NOT_FOUND_ERROR = 230103;

    /**
     * @Message("商品规格加载错误")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_OPTION_NOT_FOUND_ERROR = 230104;

    /**
     * @Message("无效的商品数量")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_STOCK_INVALID_ERROR = 230105;

    /**
     * @Message("库存不足")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_UNDER_STOCK_ERROR = 230106;

    /**
     * @Message("修改库存失败,请重试")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_UPDATE_STOCK_ERROR = 230107;

    /**
     * @Message("商品缺少购买权限")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_NOT_PERM_ERROR = 230108;

    /**
     * @Message("会员地址为空")
     */
    const ORDER_CREATOR_KERNEL_ADDRESS_HANDLER_ADDRESS_EMPTY_ERROR = 230109;

    /**
     * @Message("没有支付方式")
     */
    const ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_PAYMENT_EMPTY_ERROR = 230110;

    /**
     * @Message("没有可用支付方式")
     */
    const ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_USABLE_PAYMENT_EMPTY_ERROR = 230111;

    /**
     * @Message("运费模板不存在")
     */
    const ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_TEMPLATE_NOT_FOUND_ERROR = 230112;

    /**
     * @Message("不在可配送范围内")
     */
    const ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_NOT_IN_DELIVERY_AREA_ERROR = 230113;

    /**
     * @Message("运费计算失败")
     */
    const ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_DELIVERY_PRICE_ERROR = 230114;

    /**
     * @Message("创建订单失败")
     */
    const ORDER_CREATOR_KERNEL_CREATE_ORDER_ERROR = 230115;

    /**
     * @Message("创建订单商品失败")
     */
    const ORDER_CREATOR_KERNEL_CREATE_ORDER_GOODS_ERROR = 230116;

    /**
     * @Message("无效优惠活动")
     */
    const ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_ERROR = 230117;

    /**
     * @Message("未找到活动处理模块")
     */
    const ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_PROCESSOR_CLASS_ERROR = 230118;

    /**
     * @Message("未找到活动处理方法")
     */
    const ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_PROCESSOR_METHOD_ERROR = 230119;

    /**
     * @Message("不足单笔最小购买个数")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_SINGLE_MIN_BUY_ERROR = 230120;

    /**
     * @Message("达到单笔最大购买个数")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_SINGLE_MAX_BUY_ERROR = 230121;

    /**
     * @Message("达到商品购买个数")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_MAX_BUY_ERROR = 230122;

    /**
     * @Message("商品活动保存失败")
     */
    const ORDER_CREATOR_KERNEL_ORDER_ACTIVITY_ERROR = 230123;

    /**
     * @Message("渠道类型错误")
     */
    const ORDER_CREATOR_KERNEL_GET_ORDER_NO_CHANNEL_ERROR = 230124;

    /**
     * @Message("订单二次保存失败")
     */
    const ORDER_CREATOR_KERNEL_ORDER_GOODS_ORDER_SAVE_ERROR = 230125;


    /**
     * @Message("会员地址不可配送")
     */
    const ORDER_CREATOR_KERNEL_ADDRESS_HANDLER_DENY_ADDRESS_ERROR = 230126;

    /**
     * @Message("缺少商品")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_INVALID_ERROR = 230127;

    /**
     * @Message("同城配送买家地址不能为空")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_MEMBER_ADDRESS_ERROR = 230128;

    /**
     * @Message("同城配送地址库升级，请重新添加新的地址信息！")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_MEMBER_ADDRESS_POINT_ERROR = 230129;

    /**
     * @Message("同城配送店铺地址不能为空")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_SHOP_ADDRESS_ERROR = 230130;

    /**
     * @Message("同城配送配送区域不能为空")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_AREA_ERROR = 230131;

    /**
     * @Message("同城配送没有可配送区域")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_AREA_INVALID = 230132;

    /**
     * @Message("同城配送商品价格低于起送价格")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_GOODSPROCE_LOWER_INITPRICE = 230133;

    /**
     * @Message("同城配送获取实际距离错误")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_ACTUAL_DISTANCE_ERROR = 230134;

    /**
     * @Message("同城配送行政区域配送区域不合法")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_DISPATCH_INTRACITY_AREA_INVALID = 230135;

    /**
     * @Message("同城配送商品价格低于起送价格")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_BARRO_INITIAL_PRICE_UNDER = 230136;

    /**
     * @Message("同城配送行政区域获取实际距离错误")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_BARRO_ACTUAL_DISTANCE_ERROR = 230137;

    /**
     * @Message("同城配送方式未开启")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_ENABLE_ERROR = 230138;

    /**
     * @Message("普通快递配送方式未开启")
     */
    const ORDER_CREATOR_KERNEL_EXPRESS_ENABLE_ERROR = 230139;

    /**
     * @Message("商品不支持普通快递发货")
     */
    const ORDER_CREATOR_KERNEL_GOODS_EXPRESS_UNABLE = 230140;

    /**
     * @Message("商品不支持同城配送发货")
     */
    const ORDER_CREATOR_KERNEL_GOODS_INTRACITY_UNABLE = 230141;

    /**
     * @Message("没有可用支付方式(2)")
     */
    const ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_USABLE_PAYMENT_EMPTY_TWO_ERROR = 230142;

    /**
     * @Message("处理器异常")
     */
    const ORDER_CREATOR_KERNEL_HANDLER_INVALID_ERROR = 230143;

    /**
     * @Message("同城配送不支持国外地址")
     */
    const ORDER_CREATOR_KERNEL_INTRACITY_FOREIGN_ERROR = 230144;

    /**
     * @Message("创建订单虚拟卡密数据失败")
     */
    const ORDER_CREATOR_KERNEL_CREATE_ORDER_VIRTUAL_ACCOUNT_ERROR = 230145;

    /**
     * @Message("商品购买失败,错误码为:230146")
     */
    const ORDER_CREATOR_KERNEL_GOODS_HANDLER_VIRTUAL_ACCOUNT_GOODS_NOT_ADD_ERROR = 230146;
    /**
     * @Message("创建订单获取虚拟卡密数据失败")
     */
    const ORDER_CREATOR_KERNEL_CREATE_ORDER_GET_VIRTUAL_ACCOUNT_ERROR = 230147;

}
