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
 * 订单状态const
 * Class OrderStatusConst
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\order
 */
class OrderStatusConstant extends BaseConstant
{
    /**
     * @Text("关闭订单")
     */
    public const ORDER_STATUS_CLOSE = -1;

    /**
     * @Text("待支付")
     */
    public const ORDER_STATUS_WAIT_PAY = 0;

    /**
     * @Text("待发货")
     */
    public const ORDER_STATUS_WAIT_SEND = 10;

    /**
     * @Text("部分发货")
     */
    public const ORDER_STATUS_WAIT_PART_SEND = 11;

    /**
     * @Text("待收货")
     */
    public const ORDER_STATUS_WAIT_PICK = 20;

    /**
     * @Text("已完成")
     */
    public const ORDER_STATUS_SUCCESS = 30;
}
