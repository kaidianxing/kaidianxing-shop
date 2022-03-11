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
 * 交易订单操作异常类
 * Class TradeOrderOperationException
 * @package shopstar\exceptions\tradeOrder
 * @author likexin
 */
class TradeOrderOperationException extends BaseException
{

    /** 1081XX */

    /**
     * @Message("参数错误 orderNo不能为空")
     */
    public const CHECK_PARAMS_ORDER_NO_EMPTY = 108100;

    /**
     * @Message("未查询到交易订单或交易订单状态无效")
     */
    public const CLOSE_ORDER_NOT_FOUND = 108120;

    /**
     * @Message("关闭订单失败")
     */
    public const CLOSE_FAIL = 108121;

    /**
     * @Message("未查询到交易订单或交易订单状态无效")
     */
    public const REFUND_ORDER_NOT_FOUND = 108130;

    /**
     * @Message("同一业务单号已完成支付的交易订单有多条")
     */
    public const REFUND_ORDER_TOTAL_INVALID = 108131;

    /**
     * @Message("当前交易订单已经全部退款")
     */
    public const REFUND_ALREADY_ALL_REFUNDED = 108132;

    /**
     * @Message("退款金额大于支付金额")
     */
    public const REFUND_PRICE_GREATER_PAY_PRICE = 108133;

    /**
     * @Message("更新交易订单状态失败")
     */
    public const REFUND_UPDATE_STATUS_FAIL = 108134;

    /**
     * @Message("调用支付组件失败")
     */
    public const REFUND_PAYMENT_COMPONENT_INSTANCE_FAIL = 108135;

    /**
     * @Message("调用支付组件退款失败")
     */
    public const REFUND_PAYMENT_COMPONENT_REFUND_FAIL = 108136;

    /**
     * @Message("退款失败")
     */
    public const REFUND_FAIL = 108137;

}