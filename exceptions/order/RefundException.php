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
 * Class RefundException
 * @package shopstar\exceptions\order
 * @author 青岛开店星信息技术有限公司
 */
class RefundException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 22 订单
     * 61 维权业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const REJECT_PARAMS_ERROR = 226101;

    /**
     * @Message("该订单不符合维权条件")
     */
    const REJECT_ORDER_NOT_ALLOW_REFUND = 226102;

    /**
     * @Message("用户已寄出,不能驳回申请")
     */
    const REJECT_MEMBER_IS_SEND_REJECT_FAIL = 226103;

    /**
     * @Message("驳回失败")
     */
    const ORDER_REFUND_SAVE_REJECT_FAIL = 226104;

    /**
     * @Message("参数错误")
     */
    const ORDER_REFUND_MANUAL_PARAMS_ERROR = 226104;

    /**
     * @Message("维权信息不存在")
     */
    const REJECT_ORDER_REFUND_NOT_EXISTS = 226105;

    /**
     * @Message("维权信息不存在")
     */
    const MANUAL_ORDER_REFUND_NOT_EXISTS = 226106;

    /**
     * @Message("换货不需要退款")
     */
    const MANUAL_EXCHANGE_TYPE_NOT_REFUND = 226107;

    /**
     * @Message("手动退款失败")
     */
    const MANUAL_REFUND_FAIL = 226108;

    /**
     * @Message("修改订单维权金额失败")
     */
    const MANUAL_ORDER_CHANGE_REFUND_PRICE_FAIL = 226109;

    /**
     * @Message("退回积分抵扣出错")
     */
    const MANUAL_ORDER_BACK_CREDIT_FAIL = 226110;

    /**
     * @Message("退回余额抵扣出错")
     */
    const MANUAL_ORDER_BACK_BALANCE_FAIL = 226111;

    /**
     * @Message("库存返回错误")
     */
    const MANUAL_ORDER_STOCK_BACK_FAIL = 226112;

    /**
     * @Message("参数错误")
     */
    const RETURN_ACCEPT_PARAMS_ERROR = 226113;

    /**
     * @Message("该订单不符合维权条件")
     */
    const MANUAL_ORDER_NOT_ALLOW_REFUND = 226114;

    /**
     * @Message("该订单不符合维权条件")
     */
    const RETURN_ACCEPT_ORDER_NOT_ALLOW_REFUND = 226115;

    /**
     * @Message("退货地址不存在，请重新选择")
     */
    const RETURN_ACCEPT_REFUND_ADDRESS_NOT_EXISTS = 226116;

    /**
     * @Message("维权信息不存在")
     */
    const RETURN_ACCEPT_ORDER_REFUND_NOT_EXISTS = 226117;

    /**
     * @Message("维权状态非申请中")
     */
    const RETURN_ACCEPT_REFUND_STATUS_NOT_APPLY = 226118;

    /**
     * @Message("通过申请失败")
     */
    const RETURN_ACCEPT_REFUND_ACCEPT_FAIL = 226119;

    /**
     * @Message("参数错误")
     */
    const EXCHANGE_SEND_PARAMS_ERROR = 226120;

    /**
     * @Message("该订单不符合维权条件")
     */
    const EXCHANGE_SEND_ORDER_NOT_ALLOW_REFUND = 226121;

    /**
     * @Message("维权信息不存在")
     */
    const EXCHANGE_SEND_ORDER_REFUND_NOT_EXISTS = 226122;

    /**
     * @Message("该状态写下不允许发货")
     */
    const EXCHANGE_SEND_ORDER_STATUS_NOT_ALLOW = 226123;

    /**
     * @Message("发货失败")
     */
    const EXCHANGE_SEND_REFUND_SELLER_SEND_FAIL = 226124;

    /**
     * @Message("参数错误")
     */
    const EXCHANGE_CLOSE_PARAMS_ERROR = 226125;

    /**
     * @Message("该订单不符合维权条件")
     */
    const EXCHANGE_CLOSE_ORDER_NOT_ALLOW_REFUND = 226125;

    /**
     * @Message("维权信息不存在")
     */
    const EXCHANGE_CLOSE_ORDER_REFUND_NOT_EXISTS = 226126;

    /**
     * @Message("该状态下不允许关闭申请")
     */
    const EXCHANGE_CLOSE_REFUND_NOT_ALLOW_CLOSE = 226127;

    /**
     * @Message("维权关闭失败")
     */
    const EXCHANGE_CLOSE_REFUND_CLOSE_FAIL = 226128;

    /**
     * @Message("参数错误")
     */
    const REFUND_ACCEPT_PARAMS_ERROR = 226129;

    /**
     * @Message("该订单不符合维权条件")
     */
    const REFUND_ACCEPT_ORDER_NOT_ALLOW_REFUND = 226130;

    /**
     * @Message("维权信息不存在")
     */
    const REFUND_ACCEPT_ORDER_REFUND_NOT_EXISTS = 226131;

    /**
     * @Message("同意退款失败")
     */
    const REFUND_ACCEPT_ORDER_ACCEPT_REFUND_FAIL = 226132;

    /**
     * @Message("修改订单维权金额失败")
     */
    const REFUND_ACCEPT_ORDER_CHANGE_REFUND_PRICE_FAIL = 226133;

    /**
     * @Message("退回积分抵扣出错")
     */
    const REFUND_ACCEPT_ORDER_BACK_CREDIT_FAIL = 226134;

    /**
     * @Message("退回余额抵扣出错")
     */
    const REFUND_ACCEPT_ORDER_BACK_BALANCE_FAIL = 226135;

    /**
     * @Message("返还库存错误")
     */
    const REFUND_ACCEPT_ORDER_STOCK_BACK_FAIL = 226136;

    /**
     * @Message("同意退款失败")
     */
    const REFUND_ACCEPT_ORDER_ACCEPT_REFUND_BALANCE_FAIL = 226137;

    /**
     * @Message("同意退款失败")
     */
    const REFUND_ACCEPT_ORDER_ACCEPT_REFUND_OTHER_FAIL = 226138;

    /**
     * @Message("同意退款失败")
     */
    const REFUND_ACCEPT_FAIL = 226139;

    /**
     * @Message("订单不存在")
     */
    const QUERY_EXPRESS_ORDER_IS_NOT_EXISTS = 226140;

    /**
     * @Message("订单商品不存在")
     */
    const REFUND_ORDER_GOODS_NOT_EXISTS = 226141;

    /**
     * @Message("订单关闭失败")
     */
    const REFUND_MANUAL_ORDER_CLOSE_FAIL = 226142;

    /**
     * @Message("订单关闭失败")
     */
    const REFUND_MANUAL_ORDER_GOODS_CLOSE_FAIL = 226143;

    /**
     * @Message("订单关闭失败")
     */
    const REFUND_ACCEPT_ORDER_CLOSE_FAIL = 226144;

    /**
     * @Message("积分退款失败")
     */
    const CREDIT_STATUS_CREDIT_SHOP_REFUND = 226145;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 22 订单
     * 62 维权客户端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const REFUND_APPLY_PARAMS_ERROR = 226201;

    /**
     * @Message("订单不存在")
     */
    const REFUND_APPLY_ORDER_NOT_EXISTS = 226202;

    /**
     * @Message("订单检查未通过")
     */
    const REFUND_APPLY_CHECK_ERROR = 226203;

    /**
     * @Message("获取订单维权信息错误")
     */
    const REFUND_APPLY_GET_REFUND_INFO_ERROR = 226204;

    /**
     * @Message("该订单已进行单品维权，不允许整单维权")
     */
    const REFUND_APPLY_ORDER_IS_SINGLE_REFUND = 226205;

    /**
     * @Message("订单检查未通过 已拒绝维权订单")
     */
    const REFUND_APPLY_REJECT_CHECK_ERROR = 226206;

    /**
     * @Message("该订单不允许修改维权信息")
     */
    const REFUND_APPLY_ORDER_NOT_CHANG_REFUND_INFO = 226207;

    /**
     * @Message("该订单不允许整单维权")
     */
    const REFUND_APPLY_ORDER_NOT_ALLOW_REFUND = 226208;

    /**
     * @Message("参数错误")
     */
    const REFUND_SUBMIT_PARAMS_ERROR = 226209;

    /**
     * @Message("订单不存在")
     */
    const REFUND_SUBMIT_ORDER_NOT_EXISTS = 226210;

    /**
     * @Message("订单检查未通过")
     */
    const REFUND_SUBMIT_CHECK_ERROR = 226211;

    /**
     * @Message("不支持该维权方式")
     */
    const REFUND_SUBMIT_ORDER_REFUND_TYPE_ERROR = 226212;

    /**
     * @Message("维权提交失败")
     */
    const REFUND_SUBMIT_CHANGE_HISTORY_FAIL = 226213;

    /**
     * @Message("维权提交失败")
     */
    const REFUND_SUBMIT_ORDER_REFUND_FAIL = 226214;

    /**
     * @Message("参数错误")
     */
    const REFUND_DETAIL_PARAMS_ERROR = 226215;

    /**
     * @Message("维权信息不存在")
     */
    const REFUND_DETAIL_REFUND_INFO_NOT_EXISTS = 226216;

    /**
     * @Message("参数错误")
     */
    const REFUND_EXPRESS_PARAMS_ERROR = 226217;

    /**
     * @Message("物流信息保存失败")
     */
    const REFUND_EXPRESS_SAVE_FAIL = 226218;

    /**
     * @Message("参数错误")
     */
    const REFUND_CANCEL_PARAMS_ERROR = 226219;

    /**
     * @Message("商品不存在,请联系商家进行处理")
     */
    const SINGLE_REFUND_APPLY_ORDER_GOODS_NOT_EXISTS = 226220;

    /**
     * @Message("取消维权失败")
     */
    const REFUND_ORDER_CANCEL_FAIL = 226221;

    /**
     * @Message("取消维权失败")
     */
    const REFUND_QUERY_EXPRESS_ORDER_IS_NOT_EXISTS = 226222;

    /**
     * @Message("订单已整单维权，不允许单品维权")
     */
    const REFUND_APPLY_ORDER_IS_ALL_REFUND = 226223;

    /**
     * @Message("订单不存在")
     */
    const REFUND_DETAIL_ORDER_NOT_EXISTS = 226224;

    /**
     * @Message("参数错误")
     */
    const MEMBER_EXCHANGE_CLOSE_PARAMS_ERROR = 226225;

    /**
     * @Message("该订单不符合维权条件")
     */
    const MEMBER_EXCHANGE_CLOSE_ORDER_NOT_ALLOW_REFUND = 226226;

    /**
     * @Message("该订单不符合维权条件")
     */
    const MEMBER_EXCHANGE_CLOSE_ORDER_REFUND_NOT_EXISTS = 226227;

    /**
     * @Message("该状态下不允许关闭申请")
     */
    const MEMBER_EXCHANGE_CLOSE_REFUND_NOT_ALLOW_CLOSE = 226228;

    /**
     * @Message("维权关闭失败")
     */
    const MEMBER_EXCHANGE_CLOSE_REFUND_CLOSE_FAIL = 226229;

    /**
     * @Message("申请金额不能大于最多可维权金额")
     */
    const MEMBER_REFUND_SUBMIT_PRICE_BIG = 226230;

    /**
     * @Message("维权金额错误")
     */
    const MEMBER_REFUND_SUBMIT_PRICE_ERROR = 226231;

    /**
     * @Message("商品不存在,请联系商家进行处理")
     */
    const SINGLE_REFUND_SUBMIT_ORDER_GOODS_NOT_EXISTS = 226232;

    /**
     * @Message("订单检查未通过")
     */
    const REFUND_APPLY_OTHER_SINGLE_CHECK_ERROR = 226233;

    /**
     * @Message("维权已完成，请勿重复维权")
     */
    const REFUND_MANUAL_REFUND_IS_FINISH = 226234;

    /**
     * @Message("维权状态错误")
     */
    const REFUND_NOT_NEED_PLATFORM = 226235;

    /**
     * @Message("该状态无法提交快递单号")
     */
    const REFUND_TIMEOUT_CANCEL_ALREADY = 226236;

    /**
     * @Message("优惠券已使用")
     */
    const REFUND_MANUAL_COUPON_IS_USE = 226237;


    /*************客户端异常结束*************/

}