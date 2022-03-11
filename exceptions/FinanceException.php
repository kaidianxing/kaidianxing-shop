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

namespace shopstar\exceptions;

use shopstar\bases\exception\BaseException;

/**
 * 财务
 * Class FinanceException
 * @package shopstar\exceptions
 */
class FinanceException extends BaseException
{
    /*************** 业务端异常开始 ******************/
    /**
     * 26 财务
     * 01 业务端
     * 00 错误码
     */

    /**
     * @Message("修改状态失败")
     */
    const LOG_UPDATE_STATUS_FAIL = 260100;

    /**
     * @Message("参数错误")
     */
    const LOG_UPDATE_STATUS_PARAMS_ERROR = 260101;

    /**
     * @Message("参数错误")
     */
    const MEMBER_LOG_REFUND_PARAMS_ERROR = 260102;

    /**
     * @Message("记录不存在")
     */
    const MEMBER_LOG_NOT_EXISTS = 260103;

    /**
     * @Message("充值退款失败")
     */
    const MEMBER_LOG_RECHARGE_REFUND_FAILED = 260104;

    /**
     * @Message("参数错误")
     */
    const MEMBER_LOG_WITHDRAW_APPLY_PARAMS_ERROR = 260105;

    /**
     * @Message("记录不存在")
     */
    const MEMBER_LOG_WITHDRAW_APPLY_NOT_EXISTS = 260106;

    /**
     * @Message("提现申请失败")
     */
    const MEMBER_LOG_WITHDRAW_APPLY_FAILED = 260107;

    /**
     * @Message("参数错误");
     */
    const MANAGE_ORDER_DETAIL_PARAMS_ERROR = 260108;

    /**
     * @Message("订单不存在");
     */
    const MANAGE_ORDER_DETAIL_ORDER_EMPTY_ERROR = 260109;

    /**
     * @Message("订单商品不存在");
     */
    const MANAGE_ORDER_DETAIL_ORDER_APP_EMPTY_ERROR = 260110;


    /*************** 业务端异常开始 ******************/

    /*************** 客户端异常开始 ******************/
    /**
     * 26 财务
     * 02 客户端
     * 00错误码
     */

    /**
     * @Message("充值关闭")
     */
    const RECHARGE_CLOSE = 260200;

    /**
     * @Message("提现关闭")
     */
    const WITHDRAW_CLOSE = 260201;

    /**
     * @Message("充值金额不能为空或负数")
     */
    const RECHARGE_SUBMIT_MONEY_ERROR = 260202;

    /**
     * @Message("充值金额小于最低充值金额")
     */
    const RECHARGE_SUBMIT_MONEY_LOW_ERROR = 260203;

    /**
     * @Message("参数错误")
     */
    const WITHDRAW_SUBMIT_PARAM_ERROR = 260204;

    /**
     * @Message("提现方式未开启")
     */
    const WITHDRAW_PAY_TYPE_ERROR = 260205;

    /**
     * @Message("参数错误 pay_type不能为空")
     */
    const RECHARGE_SUBMIT_PARAM_PAY_TYPE_EMPTY = 260210;

    /**
     * @Message("参数错误 不支持的pay_type")
     */
    const RECHARGE_SUBMIT_PARAM_PAY_TYPE_INVALID = 260211;


    /*************** 客户端异常结束 ******************/

}
