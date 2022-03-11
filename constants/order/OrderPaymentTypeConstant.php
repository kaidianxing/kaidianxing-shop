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
 * 订单支付类型const
 * Class OrderPaymentTypeConst
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\order
 */
class OrderPaymentTypeConstant extends BaseConstant
{
    /**
     * @Text("无需支付")
     */
    public const ORDER_PAYMENT_TYPE_NON = 0;
    /**
     * @Text("后台确认")
     */
    public const ORDER_PAYMENT_TYPE_ADMIN_CONFIRM = 1;

    /**
     * @Text("余额支付")
     */
    public const ORDER_PAYMENT_TYPE_BALANCE = 2;

    /**
     * @Text("货到付款")
     */
    public const ORDER_PAYMENT_TYPE_DELIVERY = 3;

    /**
     * @Text("免支付")
     */
    public const ORDER_PAYMENT_TYPE_NO_PAYMENT = 4;

    /**
     * @Text("微信支付")
     */
    public const ORDER_PAYMENT_TYPE_WECHAT = 20;

    /**
     * @Text("支付宝支付")
     */
    public const ORDER_PAYMENT_TYPE_ALIPAY = 30;
    
    /**
     * @Text("字节跳动支付")
     * 暂未区分是微信还是支付宝
     */
    public const ORDER_PAYMENT_TYPE_BYTEDANCE_WECHAT = 40;
    
    /**
     * @Text("字节跳动支付")
     */
    public const ORDER_PAYMENT_TYPE_BYTEDANCE_ALIPAY = 41;
    
}
