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

class wxAppOrderStatusConstant extends BaseConstant
{
    /**
     * @Text("待付款")
     */
    public const WX_APP_ORDER_STATUS_WAIT_PAY = 10;

    /**
     * @Text("收银台支付完成（自动流转，对商家来说和10同等对待即可")
     */
    public const WX_APP_ORDER_STATUS_CASHIER_WAIT_PAY = 11;

    /**
     * @Text("待发货(即支付完成)")
     */
    public const WX_APP_ORDER_STATUS_WAIT_SEND = 20;

    /**
     * @Text("部分发货")
     */
    public const WX_APP_ORDER_STATUS_WAIT_PART_SEND = 21;

    /**
     * @Text("待收货")
     */
    public const WX_APP_ORDER_STATUS_WAIT_PICK = 30;

    /**
     * @Text("完成")
     */
    public const WX_APP_ORDER_STATUS_SUCCESS = 100;

    /**
     * @Text("超时未支付取消")
     */
    public const WX_APP_ORDER_STATUS_ = 181;

    /**
     * @Text("全部商品售后之后取消")
     */
    public const WX_APP_ORDER_STATUS_REFUND_CLOSE = 200;

    /**
     * @Text("用户取消")
     */
    public const WX_APP_ORDER_STATUS_CLOSE = 250;
}
