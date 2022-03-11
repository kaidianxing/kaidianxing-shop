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

namespace shopstar\exceptions\sale;

use shopstar\bases\exception\BaseException;

/**
 * 优惠券异常
 * Class CouponException
 * @package shopstar\exceptions\sale
 */
class CouponException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 24 营销
     * 31 优惠券
     * 01 错误码
     */
    
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 243101;
    
    /**
     * @Message("优惠券新增失败")
     */
    const ADD_COUPON_SAVE_FAIL = 243102;
    
    /**
     * @Message("参数错误")
     */
    const EDIT_PARAMS_ERROR = 243103;
    
    /**
     * @Message("优惠券修改失败")
     */
    const EDIT_COUPON_SAVE_FAIL = 243104;
    
    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 243105;
    
    /**
     * @Message("删除优惠券失败")
     */
    const COUPON_DELETE_FAIL = 243106;
    
    /**
     * @Message("参数错误")
     */
    const CHANGE_STATE_PARAMS_ERROR = 243107;
    
    /**
     * @Message("修改发放状态失败")
     */
    const CHANGE_COUPON_STATE_FAIL = 243108;
    
    /**
     * @Message("优惠券不能为空")
     */
    const BATCH_SEND_COUPON_NOT_EMPTY = 243109;
    
    /**
     * @Message("发送数量错误")
     */
    const BATCH_SEND_TOTAL_ERROR = 243110;
    
    /**
     * @Message("优惠券不存在")
     */
    const BATCH_SEND_COUPON_NOT_EXISTS = 243111;
    
    /**
     * @Message("需要发送的会员不能为空")
     */
    const BATCH_SEND_MEMBER_ID_NOT_EMPTY = 243112;
    
    /**
     * @Message("需要发送的会员等级不能为空")
     */
    const BATCH_SEND_MEMBER_LEVEL_NOT_EMPTY = 243113;
    
    /**
     * @Message("需要发送的用户分组不能为空")
     */
    const BATCH_SEND_MEMBER_GROUP_NOT_EMPTY = 243114;
    
    /**
     * @Message("需要发送的分销商等级不能为空")
     */
    const BATCH_SEND_COMMISSION_LEVEL_NOT_EMPTY = 243115;
    
    /**
     * @Message("会员不存在")
     */
    const BATCH_SEND_MEMBER_EMPTY = 243116;
    
    /**
     * @Message("优惠券库存不够")
     */
    const BATCH_SEND_COUPON_STOCK_NOT_ENOUGH = 243117;
    
    /**
     * @Message("发送失败")
     */
    const BATCH_SEND_COUPON_FAIL = 243118;
    
    /**
     * @Message("优惠券设置保存失败")
     */
    const COUPON_SET_SAVE_FAIL = 243119;
    
    /**
     * @Message("参数错误")
     */
    const MEMBER_COUPON_PARAMS_ERROR = 243120;

    /**
     * @Message("优惠券不能为空")
     */
    const BATCH_SEND_COUPON_ID_NOT_EMPTY = 243121;
    
    
    /*************业务端异常结束*************/
    
    /*************客户端异常开始*************/
    /**
     * 24 营销
     * 32 优惠券
     * 01 错误码
     */
    /**
     * @Message("每人最多可领取错误")
     */
    const ENOUGH_SEND_COUPON_SEND_TOTAL_ERROR = 243201;
    
    /**
     * @Message("任务不存在")
     */
    const SEND_COUPON_NOT_EXISTS = 243202;
    
    /**
     * @Message("不在可领取等级之内")
     */
    const NOT_IN_THE_RECEIVING_LEVEl = 243203;
    
    /**
     * @Message("优惠券无需支付")
     */
    const COUPON_ONT_NEED_PAY = 243204;
    
    /**
     * @Message("没有符合条件的优惠券")
     */
    const COUPON_NO_CONDITIONS = 243205;
    
    /**
     * @Message("您已经领取过了")
     */
    const COUPON_EXISTS = 243206;
    
    /**
     * @Message("参数错误,订单/支付方式错误")
     */
    const PARAMS_OR_PAY_TYPE_ERROR = 243207;
    
    /**
     * @Message("优惠券不存在")
     */
    const COUPON_NOT_EXISTS = 243208;
    
    /**
     * @Message("构建订单失败")
     */
    const PAY_COUPON_BUILD_LOG_ERROR = 243209;
    
    /**
     * @Message("构建订单失败")
     */
    const PAY_BUILD_LOG_ERROR = 243210;
    
    /**
     * @Message("积分不足")
     */
    const COUPON_PAY_CREDIT_NOT_ENOUGH = 243211;
    
    /**
     * @Message("参数错误")
     */
    const COUPON_DETAIL_PARAMS_ERROR = 243212;
    
    /**
     * @Message("优惠券已失效")
     */
    const COUPON_DETAIL_STATE_ERROR = 243213;
    
    /**
     * @Message("参数错误")
     */
    const COUPON_GET_PARAMS_ERROR = 243214;
    
    /**
     * @Message("优惠券领取检查未通过")
     */
    const COUPON_GET_CHECK_ERROR = 243215;
    
    /**
     * @Message("优惠券需付费领取")
     */
    const COUPON_IS_NOT_FREE = 243216;
    
    /**
     * @Message("积分扣除失败")
     */
    const COUPON_PAY_CREDIT_FAIL = 243217;
    
    
    /*************客户端异常结束*************/
    
    

    /**
     * @Message("领取失败")
     */
    const FAIL_TO_RECEIVE = 700139;
    
}