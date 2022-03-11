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

namespace shopstar\constants;

use shopstar\bases\constant\BaseConstant;

/**
 * 订单类型常量
 * Class OrderConst
 * @package shopstar\constants
 */
class OrderConstant extends BaseConstant
{

    /**
     * 订单类型
     */
    /**
     * 普通订单
     */
    public const ORDER_TYPE_ORDINARY = 10;

    /**
     * 虚拟订单
     */
    public const ORDER_TYPE_VIRTUAL = 20;

    /**
     * 活动类型
     */


    /**
     * 订单状态
     */
    /**
     * @Message("关闭订单")
     */
    public const ORDER_STATUS_CLOSE = -1;

    /**
     * @Message("待支付")
     */
    public const ORDER_STATUS_WAIT_PAY = 0;

    /**
     * @Message("待发货")
     */
    public const ORDER_STATUS_WAIT_SEND = 10;

    /**
     * @Message("部分发货")
     */
    public const ORDER_STATUS_WAIT_PART_SEND = 11;

    /**
     * @Message("待收货")
     */
    public const ORDER_STATUS_WAIT_PICK = 20;

    /**
     * @Message("已完成")
     */
    public const ORDER_STATUS_SUCCESS = 30;

    /**
     * @Message("未支付")
     */
    public const ORDER_PAYMENT_TYPE_NON = 0;

    /**
     * 支付方式
     */
    /**
     * @Message("后台确认")
     */
    public const ORDER_PAYMENT_TYPE_ADMIN_CONFIRM = 1;

    /**
     * @Message("余额支付")
     */
    public const ORDER_PAYMENT_TYPE_BALANCE = 2;

    /**
     * @Message("货到付款")
     */
    public const ORDER_PAYMENT_TYPE_DELIVERY = 3;

    /**
     * @Message("微信支付")
     */
    public const ORDER_PAYMENT_TYPE_WECHAT = 10;

    /**
     * @Message("支付宝支付")
     */
    public const ORDER_PAYMENT_TYPE_ALIPAY = 20;

    /**
     * @Message("整单维权")
     */
    public const REFUND_TYPE_ALL = 1;

    /**
     * @Message("单品维权")
     */
    public const REFUND_TYPE_SINGLE = 2;

    /**
     * 是否维权 is_refund
     */

    /**
     * @Message("无维权")
     */
    public const IS_REFUND_NO = 0;

    /**
     * @Message("维权中")
     */
    public const IS_REFUND_YES = 1;

    /**
     * 配送方式
     */
    /**
     * @Message("快递配送")
     */
    public const ORDER_DISPATCH_EXPRESS = 10;


    /**
     * 订单关闭
     */
    /**
     * @Message("买家关闭")
     */
    public const ORDER_CLOSE_TYPE_BUYER_CLOSE = 1;

    /**
     * @Message("卖家关闭")
     */
    public const ORDER_CLOSE_TYPE_SELLER_CLOSE = 2;

    /**
     * @Message("系统自动关闭")
     */
    public const ORDER_CLOSE_TYPE_SYSTEM_AUTO_CLOSE = 3;

    /**
     * @Message("维权完成关闭订单")
     */
    public const ORDER_CLOSE_TYPE_REFUND_SUCCESS_CLOSE = 4;


}
