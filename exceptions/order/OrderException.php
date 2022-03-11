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
 * 订单异常 22
 * Class OrderException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\order
 */
class OrderException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_DETAIL_PARAMS_ERROR = 220002;

    /********************业务端订单操作*****************/
    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_PAY_PARAMS_ERROR = 220200;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_NOT_FOUND_ERROR = 220201;

    /**
     * @Message("订单状态修改失败")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR = 220202;

    /**
     * @Message("订单状态错误")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_STATUS_ERROR = 220203;

    /**
     * @Message("订单修改失败")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_ERROR = 220204;

    /**
     * @Message("商品库存修改失败")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_GOODS_STOCK_ERROR = 220205;
    
    /**
     * @Message("修改预售订单状态失败")
     */
    const ORDER_MANAGE_OP_PAY_EDIT_PRESELL_ORDER_ERROR = 220206;

    /**
     * @Message("预售订单不存在")
     */
    const ORDER_MANAGE_OP_PAY_PRESELL_ORDER_NOT_EXISTS = 220207;

    /**
     * @Message("付尾款时间错误")
     */
    const ORDER_MANAGE_OP_PAY_PRESELL_ORDER_PAY_FINAL_TIME_ERROR = 220208;

    /**
     * @Message("预售订单不支持货到付款")
     */
    const ORDER_MANAGE_OP_PAY_PRESELL_ORDER_PAY_NOT_DELIVERY = 220209;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CLOSE_PARAMS_ERROR = 220210;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_OP_CLOSE_ORDER_NOT_FOUND_ERROR = 220211;

    /**
     * @Message("订单状态不正确")
     */
    const ORDER_MANAGE_OP_CLOSE_ORDER_STATUS_ERROR = 220212;

    /**
     * @Message("关闭订单错误")
     */
    const ORDER_MANAGE_OP_CLOSE_ORDER_ERROR = 220213;
    
    /**
     * @Message("不在定金支付时间内")
     */
    const ORDER_MANAGE_OP_PAY_PRESELL_ORDER_PAY_FRONT_TIME_ERROR = 220214;
    
    /**
     * @Message("订单商品不存在")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_GOODS_INFO_NOT_EXISTS = 220215;
    
    /**
     * @Message("虚拟商品发货失败")
     */
    const ORDER_MANAGE_OP_PAY_ORDER_VIRTUAL_SEND_FAIL = 220216;
    
    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_PARAMS_ERROR = 220220;

    /**
     * @Message("订单未找到")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_ORDER_NOT_FOUND_ERROR = 220221;

    /**
     * @Message("订单状态不正确")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_ORDER_STATUS_ERROR = 220222;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_PARAMS_ERROR = 220230;

    /**
     * @Message("订单未找到")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_NOT_FOUND_ERROR = 220231;

    /**
     * @Message("订单状态不正确")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_STATUS_ERROR = 220232;

    /**
     * @Message("没有改变")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_NO_CHANGE_ERROR = 220233;

    /**
     * @Message("价格不能小于0元")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_PRICE_TOO_SMALL_ERROR = 220234;

    /**
     * @Message("价格错误")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_PRICE_ERROR = 220235;

    /**
     * @Message("改价失败")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_ERROR = 220236;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_LOG_PARAMS_ERROR = 220237;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_SEND_PACKAGE_PARAMS_ERROR = 220238;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_OP_SEND_PACKAGE_ORDER_NOT_FOUND_ERROR = 220239;

    /**
     * @Message("订单商品不存在")
     */
    const ORDER_MANAGE_OP_SEND_PACKAGE_ORDER_GOODS_NOT_FOUND_ERROR = 220240;

    /**
     * @Message("订单状态不正确")
     */
    const ORDER_MANAGE_OP_SEND_PACKAGE_STATUS_ERROR = 220241;

    /**
     * @Message("配送类型不正确")
     */
    const ORDER_MANAGE_OP_SEND_PACKAGE_DISPATCH_TYPE_ERROR = 220242;

    /**
     * @Message("订单发货失败")
     */
    const ORDER_MANAGE_OP_SEND_ERROR = 220243;

    /**
     * @Message("改价次数过多")
     */
    const ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_CHANGE_NUMBER_ERROR = 220244;

    /**
     * @Message("该订单类型不支持更改运费")
     */
    const ORDER_MANAGE_OP_ORDER_TYPE_DISABLE_CHANGE_DISPATCH_PRICE = 220245;

    /**
     * @Message("该物流类型不支持更改运费")
     */
    const ORDER_MANAGE_OP_DISPATCH_TYPE_DISABLE_CHANGE_DISPATCH_PRICE = 220246;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_BATCH_SEND_ORDER_ID_EMPTY_ERROR = 220250;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_BATCH_SEND_EXPRESS_ID_EMPTY_ERROR = 220251;

    /**
     * @Message("错误的物流公司")
     */
    const ORDER_MANAGE_OP_BATCH_SEND_EXPRESS_ERROR = 220252;

    /**
     * @Message("没有可以发货的订单")
     */
    const ORDER_MANAGE_OP_BATCH_SEND_NO_SEND_ORDER_ERROR = 220253;

    /**
     * @Message("失败的订单号")
     */
    const ORDER_MANAGE_OP_BATCH_SEND_FAIL_ORDER_NO_ERROR = 220254;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_GET_PACKAGE_LIST_GET_PARAMS_ERROR = 220260;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_OP_GET_PACKAGE_LIST_ORDER_NOT_FOUND_ERROR = 220261;

    /**
     * @Message("包裹内没有已发货的商品")
     */
    const ORDER_MANAGE_OP_GET_PACKAGE_LIST_NOT_SEND_GOODS_ERROR = 220262;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CHANGE_SEND_POST_PARAMS_ERROR = 220263;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CHANGE_SEND_POST_PACKAGE_ID_EMPTY_PARAMS_ERROR = 220264;

    /**
     * @Message("请选择包裹中的物流公司")
     */
    const ORDER_MANAGE_OP_CHANGE_SEND_POST_PLACE_SELECT_EXPRESS_COMPANY_ERROR = 220265;

    /**
     * @Message("请选择包裹中的物流单号")
     */
    const ORDER_MANAGE_OP_CHANGE_SEND_POST_PLACE_SELECT_EXPRESS_NUMBER_ERROR = 220266;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_CANCEL_SEND_PARAMS_ERROR = 220270;

    /**
     * @Message("取消发货失败")
     */
    const ORDER_MANAGE_OP_CANCEL_SEND_ERROR = 220271;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_OP_FINISH_PARAMS_ERROR = 220280;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_OP_FINISH_ORDER_NOT_FOUND_PARAMS_ERROR = 220281;

    /**
     * @Message("订单状态错误，无法进行收货确认")
     */
    const ORDER_MANAGE_OP_FINISH_ORDER_STATUS_ERROR = 220282;

    /**
     * @Message("收货失败")
     */
    const ORDER_MANAGE_OP_FINISH_ERROR = 220283;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_DETAIL_ORDER_NOT_FOUND_ERROR = 220300;

    /**
     * @Message("批量发货模板数据错误")
     */
    const ORDER_MANAGE_BATCH_SEND_INDEX_PARAMS_ERROR = 220310;

    /**
     * @Message("每次最多可处理")
     */
    const ORDER_MANAGE_BATCH_SEND_NUMBER_MAX_ERROR = 220311;

    /**
     * @Message("没有需要发货的订单")
     */
    const ORDER_MANAGE_BATCH_SEND_NOT_NEED_SEND_ORDER_ERROR = 220312;

    /**
     * @Message("没有可以发货的订单")
     */
    const ORDER_MANAGE_BATCH_SEND_NOT_CAN_SEND_ORDER_ERROR = 220313;

    /**
     * @Message("批量发货失败")
     */
    const ORDER_MANAGE_BATCH_SEND_DATA_INSERT_ERROR = 220314;

    /**
     * @Message("该订单非维权订单")
     */
    const ORDER_MANAGE_DETAIL_IS_NOT_REFUND_ORDER = 220400;

    /**
     * @Message("该订单非单品维权订单")
     */
    const ORDER_MANAGE_DETAIL_IS_NOT_SINGLE_REFUND_ORDER = 220401;

    /**
     * @Message("退款失败")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_ORDER = 220500;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_ORDER_NOT_FOUND_ORDER = 220501;

    /**
     * @Message("用户密码不正确")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_PASSWORD_ORDER = 220502;

    /**
     * @Message("货到付款无法退款")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_DELIVERY_NOT_CLOSE_REFUND_ORDER = 220503;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_PARAMS_ERROR = 220504;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_EDIT_ADDRESS_PARAMS_ORDER = 220510;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_EDIT_ADDRESS_ORDER_NOT_FOUND_ORDER = 220511;

    /**
     * @Message("缺少关键参数")
     */
    const ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER = 220512;

    /**
     * @Message("修改失败")
     */
    const ORDER_MANAGE_EDIT_ADDRESS_ORDER = 220513;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_GET_EXPRESS_PARAMS_ORDER = 220520;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_GET_EXPRESS_ORDER_NOT_FOUND_ORDER = 220521;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_CHANGE_INVOICE_STATUS_PARAMS_ERROR = 220531;

    /**
     * @Message("订单不存在")
     */
    const ORDER_MANAGE_CHANGE_INVOICE_STATUS_ORDER_NOT_FOUND_ERROR = 220532;

    /**
     * @Message("发票信息不存在")
     */
    const ORDER_MANAGE_CHANGE_INVOICE_STATUS_INVOICE_INFO_ERROR = 220533;

    /**
     * @Message("保存失败")
     */
    const ORDER_MANAGE_CHANGE_INVOICE_STATUS_ERROR = 220534;

    /**
     * @Message("维权信息不存在")
     */
    const ORDER_MANAGE_DETAIL_ORDER_INFO_NOT_EXISTS = 220535;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_DIY_EXPORT_ADD_TEMPLATE_PARAMS_ERROR = 220540;

    /**
     * @Message("模板添加失败")
     */
    const ORDER_MANAGE_DIY_EXPORT_ADD_TEMPLATE_ERROR = 220541;

    /**
     * @Message("参数为空")
     */
    const ORDER_MANAGE_DIY_EXPORT_GET_TEMPLATE_PARAMS_ERROR = 220550;

    /**
     * @Message("没有模板")
     */
    const ORDER_MANAGE_DIY_EXPORT_GET_TEMPLATE_NOT_EXISTS_ERROR = 220551;

    /**
     * @Message("参数错误")
     */
    const ORDER_MANAGE_DIY_EXPORT_DEL_TEMPLATE_PARAMS_ERROR = 220555;

    /**
     * @Message("虚拟卡密不支持退款")
     */
    const ORDER_MANAGE_CLOSE_AND_REFUND_ORDER_NOT_FOUND_ORDER_VIRTUAL_ACCOUNT = 220556;


    /******************************手机端**********************************/
    /**
     * @Message("缺少订单id")
     */
    const ORDER_DETAIL_INDEX_PARAMS_ERROR = 221000;

    /**
     * @Message("订单未找到")
     */
    const ORDER_DETAIL_INDEX_ORDER_NOT_FOUND_ERROR = 221001;

    /**
     * @Message("参数错误")
     */
    const ORDER_OP_CANCEL_PARAMS_ERROR = 221010;

    /**
     * @Message("订单已支付，不能取消")
     */
    const ORDER_OP_CANCEL_ORDER_STATUS_SEND_ERROR = 221011;

    /**
     * @Message("订单已取消")
     */
    const ORDER_OP_CANCEL_ORDER_STATUS_CLOSE_ERROR = 221012;

    /**
     * @Message("订单取消失败")
     */
    const ORDER_OP_CANCEL_ORDER_ERROR = 221013;

    /**
     * @Message("订单状态错误")
     */
    const ORDER_OP_FINISH_ORDER_STATUS_ERROR = 221020;

    /**
     * @Message("参数错误")
     */
    const ORDER_OP_FINISH_ORDER_PARAMS_ERROR = 221021;

    /**
     * @Message("完成订单失败")
     */
    const ORDER_OP_FINISH_ORDER_ERROR = 221022;

    /**
     * @Message("订单状态不正确，无法删除订单")
     */
    const ORDER_OP_DELETE_ORDER_STATUS_ERROR = 221030;

    /**
     * @Message("订单存在维权，无法删除订单")
     */
    const ORDER_OP_DELETE_ORDER_EXIST_REFUND_ERROR = 221031;

    /**
     * @Message("参数错误")
     */
    const ORDER_OP_DELETE_PARAMS_ERROR = 221032;

    /**
     * @Message("删除订单错误")
     */
    const ORDER_OP_DELETE_ERROR = 221033;

    /**
     * @Message("拼团商品库存不足")
     */
    const ORDER_PAY_GROUPS_STOCK_ERROR = 221034;

    /**
     * @Message("积分商品库存不足")
     */
    const ORDER_PAY_CREDIT_SHOP_STOCK_ERROR = 221036;
    /**
     * @Message("订单已关闭")
     */
    const ORDER_STATUS_CLOSE_ERROR = 221038;
    /**
     * @Message("订单支付类型错误")
     */
    const ORDER_PAY_TYPE_ERROR = 221039;



}
