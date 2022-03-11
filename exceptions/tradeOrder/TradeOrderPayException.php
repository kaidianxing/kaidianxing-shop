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


namespace shopstar\exceptions\tradeOrder;

use shopstar\bases\exception\BaseException;

/**
 * 交易订单支付异常类
 * Class TradeOrderPayException
 * @package shopstar\exceptions\tradeOrder
 * @author likexin
 */
class TradeOrderPayException extends BaseException
{

    /** 1080XX */

    /**
     * @Message("参数错误 type不能为空")
     */
    public const CHECK_PARAMS_TYPE_EMPTY = 108000;

    /**
     * @Message("参数错误 payType不能为空")
     */
    public const CHECK_PARAMS_PAY_TYPE_EMPTY = 108001;

    /**
     * @Message("参数错误 payType无效")
     */
    public const CHECK_PARAMS_PAY_TYPE_INVALID = 108002;

    /**
     * @Message("参数错误 clientType不能为空")
     */
    public const CHECK_PARAMS_CLIENT_TYPE_EMPTY = 108003;

    /**
     * @Message("参数错误 accountId不能为空")
     */
    public const CHECK_PARAMS_ACCOUNT_ID_EMPTY = 108005;

    /**
     * @Message("参数错误 openid不能为空")
     */
    public const CHECK_PARAMS_OPENID_EMPTY = 108006;

    /**
     * @Message("参数错误 multiOrder与orderNo重复")
     */
    public const CHECK_PARAMS_MULTI_ORDER_ORDER_NO_REPEAT = 108007;

    /**
     * @Message("参数错误 multiOrder[][orderId]不能为空")
     */
    public const CHECK_PARAMS_MULTI_ORDER_ORDER_ID_EMPTY = 108008;

    /**
     * @Message("参数错误 multiOrder[][orderNo]不能为空")
     */
    public const CHECK_PARAMS_MULTI_ORDER_ORDER_NO_EMPTY = 108009;

    /**
     * @Message("参数错误 multiOrder[][orderPrice]重复")
     */
    public const CHECK_PARAMS_MULTI_ORDER_ORDER_PRICE_EMPTY = 108010;

    /**
     * @Message("参数错误 orderId不能为空")
     */
    public const CHECK_PARAMS_ORDER_ID_EMPTY = 108011;

    /**
     * @Message("参数错误 orderNo不能为空")
     */
    public const CHECK_PARAMS_ORDER_NO_EMPTY = 108012;

    /**
     * @Message("参数错误 orderPrice不能为空")
     */
    public const CHECK_PARAMS_ORDER_PRICE_EMPTY = 108013;

    /**
     * @Message("参数错误 payType不支持")
     */
    public const CHECK_PARAMS_PAY_TYPE_NOT_SUPPORT = 108014;

    /**
     * @Message("参数错误 payTypeIdentity不支持")
     */
    public const CHECK_PARAMS_PAY_TYPE_IDENTITY_NOT_SUPPORT = 108015;

    /**
     * @Message("创建交易订单失败")
     */
    public const GET_OR_CREATE_ORDER_CREATE_FAIL = 108030;

    /**
     * @Message("当前订单不可支付(订单数量错误)")
     */
    public const GET_OR_CREATE_ORDER_TRADE_COUNT_MISS = 108031;

    /**
     * @Message("当前订单不可支付(业务订单的内部交易单号不一致)")
     */
    public const GET_OR_CREATE_ORDER_TRADE_NO_INVALID = 108032;

    /**
     * @Message("交易订单状态无效，可能已经支付")
     */
    public const GET_OR_CREATE_ORDER_STATUS_INVALID = 108033;

    /**
     * @Message("不支持的支付类型")
     */
    public const INVOKE_COMPONENT_GET_INSTANCE_FAIL = 108040;

    /**
     * @Message("支付失败")
     */
    public const INVOKE_COMPONENT_PAY_FAIL = 108041;


}