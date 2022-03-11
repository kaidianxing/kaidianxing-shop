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

namespace shopstar\exceptions\sysset;

use shopstar\bases\exception\BaseException;

/**
 * 支付设置异常
 * Class CreditException
 * @package shopstar\bases\exception
 */
class PaymentException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置
     * 50 支付设置
     * 01 错误码
     */

    /**
     * @Message("支付设置保存失败")
     */
    const PAY_SET_SAVE_FAIL = 135101;

    /**
     * @Message("支付方式设置保存失败")
     */
    const TYPE_SET_SAVE_FAIL = 135102;

    /**
     * @Message("参数错误")
     */
    const TEMPLATE_SET_DETAIL_PARAMS_ERROR = 135103;

    /**
     * @Message("模板不存在")
     */
    const TEMPLATE_SET_DETAIL_TEMPLATE_NOT_EXISTS = 135104;

    /**
     * @Message("模板保存失败")
     */
    const TEMPLATE_ADD_SAVE_FAIL = 135105;

    /**
     * @Message("参数错误")
     */
    const TEMPLATE_SET_UPDATE_PARAMS_ERROR = 135106;

    /**
     * @Message("支付模板保存失败")
     */
    const TEMPLATE_UPDATE_SAVE_FAIL = 135107;

    /**
     * @Message("支付模板保存失败")
     */
    const TEMPLATE_SET_DELETE_PARAMS_ERROR = 135108;

    /**
     * @Message("请选择微信支付模板")
     */
    const TYPE_SET_WECHAT_TEMPLATE_PARAMS_ERROR = 135109;

    /**
     * @Message("请选择支付宝支付模板")
     */
    const TYPE_SET_ALIPAY_TEMPLATE_PARAMS_ERROR = 135110;

    /**
     * @Message("支付宝提现错误")
     */
    const WITHDRAW_ALIPAY_ERROR = 135111;

    /**
     * @Message("支付宝支付错误")
     */
    const PAY_ALIPAY_ERROR = 135112;

    /**
     * @Message("支付宝退款错误")
     */
    const REFUND_ALIPAY_ERROR = 135113;

    /**
     * @Message("微信提现错误")
     */
    const WITHDRAW_WECHAT_ERROR = 135114;

    /**
     * @Message("微信支付错误")
     */
    const PAY_WECHAT_ERROR = 135115;

    /**
     * @Message("微信退款错误")
     */
    const REFUND_WECHAT_ERROR = 135116;

    /**
     * @Message("删除失败")
     */
    const PAYMENT_DELETE_FAIL = 135117;

    /**
     * @Message("支付类型暂不支持")
     */
    const PAYMENT_TYPE_INVALID = 135118;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 13 设置
     * 52 支付设置 客户端
     * 01 错误码
     */
    /**
     * @Message("code不能为空,参数错误")
     */
    const CODE_IS_MUST_BE_NOT_EMPTY = 135201;

    /**
     * @Message("支付类型错误")
     */
    const PAY_TYPE_IS_NOT_ALLOWED = 135202;

    /**
     * @Message("支付方式未开启")
     */
    const PAY_TYPE_IS_CLOSED = 135203;

    /**
     * @Message("订单已经支付")
     */
    const ORDER_IS_BE_PAYED = 135204;

    /**
     * @Message("提现方式未开启")
     */
    const PAYSET_IS_CLOSED = 135205;

    /**
     * @Message("提现类型错误")
     */
    const PAYSET_IS_NOT_ALLOWED = 135206;

    /**
     * @Message("支付方式错误")
     */
    const NOTIFY_PAYSET_IS_NOT_ALLOWED = 135207;

    /**
     * @Message("支付方式错误")
     */
    const MANAGE_WECHAT_NOTIFY_PAYSET_IS_NOT_ALLOWED = 135208;

    /**
     * @Message("支付方式错误")
     */
    const MANAGE_ALIPAY_NOTIFY_PAYSET_IS_NOT_ALLOWED = 135209;
    
    /**
     * @Message("支付配置错误")
     */
    const PAY_CONFIG_ERROR = 135210;

    /**
     * @Message("不支持切换渠道支付")
     */
    const PAY_CHANNEL_ERROR = 135211;

    /**
     * @Message("未配置支付方式对应的支付证书")
     */
    const PAY_CONFIG_NEED_CERT = 135212;


    /*************客户端异常结束*************/

}
