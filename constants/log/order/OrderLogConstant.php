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

namespace shopstar\constants\log\order;

use shopstar\bases\constant\BaseConstant;

class OrderLogConstant extends BaseConstant
{
    /**
     * @Text("订单-操作-修改收货地址")
     */
    public const ORDER_OP_EDIT_ADDRESS = 220000;

    /**
     * @Text("订单-操作-发货")
     */
    public const ORDER_OP_SEND_PACKAGE = 220001;

    /**
     * @Text("订单-操作-批量发货")
     */
    public const ORDER_OP_BATCH_SEND_PACKAGE = 220002;

    /**
     * @Text("订单-操作-修改发货")
     */
    public const ORDER_OP_CHANGE_SEND = 220003;

    /**
     * @Text("订单-操作-确认收货")
     */
    public const ORDER_OP_FINISH = 220004;

    /**
     * @Text("订单-操作-确认支付")
     */
    public const ORDER_OP_PAY = 220005;

    /**
     * @Text("订单-操作-关闭订单")
     */
    public const ORDER_OP_CLOSE = 220006;

    /**
     * @Text("订单-操作-退款")
     */
    public const ORDER_OP_CLOSE_AND_REFUND = 220007;

    /**
     * @Text("订单-操作-改价")
     */
    public const ORDER_OP_CHANGE_PRICE = 220008;

    /**
     * @Text("订单-操作-确认开发票")
     */
    public const ORDER_OP_CHANGE_INVOICE_STATUS = 220009;

    /**
     * @Text("订单-操作-导入式批量发货")
     */
    public const ORDER_OP_IMPORT_BATCH_SEND = 220010;

    /**
     * @Text("订单-自定义导出-添加导出模板")
     */
    public const ORDER_DIY_EXPORT_ADD_TEMPLATE = 220011;

    /**
     * @Text("订单-自定义导出-删除导出模板")
     */
    public const ORDER_DIY_EXPORT_DELETE_TEMPLATE = 220012;


    /**
     * @Text("订单-维权-驳回申请")
     */
    public const ORDER_REFUND_REJECT = 220013;

    /**
     * @Text("订单-维权-通过申请")
     */
    public const ORDER_REFUND_ACCEPT = 220014;

    /**
     * @Text("订单-维权-确认发货")
     */
    public const ORDER_REFUND_EXCHANGE_SEND = 220015;

    /**
     * @Text("订单-维权-关闭申请")
     */
    public const ORDER_REFUND_EXCHANGE_CLOSE = 220016;

    /**
     * @Text("订单-维权-手动退款")
     */
    public const ORDER_REFUND_MANUAL = 220017;

    /**
     * @Text("订单-维权-自动退款")
     */
    public const ORDER_REFUND_REFUND_ACCEPT = 220018;

    /**
     * @Text("订单-操作-核销")
     */
    public const ORDER_OP_CHANGE_VERIFY = 220019;

    /**
     * @Text("订单-操作-确认核销")
     */
    public const ORDER_OP_SHOP_CHANGE_VERIFY = 220020;

}
