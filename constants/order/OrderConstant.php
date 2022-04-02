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
 * 订单类型常量
 * Class OrderConst
 * @package shopstar\constants
 * @author 青岛开店星信息技术有限公司
 */
class OrderConstant extends BaseConstant
{
    /**
     * @Message("整单维权")
     */
    public const REFUND_TYPE_ALL = 1;

    /**
     * @Message("单品维权")
     */
    public const REFUND_TYPE_SINGLE = 2;

    /**
     * 是否维权 is_refund
     */

    /**
     * @Message("无维权")
     */
    public const IS_REFUND_NO = 0;

    /**
     * @Message("维权中")
     */
    public const IS_REFUND_YES = 1;


    /**
     * 订单关闭
     */
    /**
     * @Message("买家关闭")
     */
    public const ORDER_CLOSE_TYPE_BUYER_CLOSE = 1;

    /**
     * @Message("卖家关闭")
     */
    public const ORDER_CLOSE_TYPE_SELLER_CLOSE = 2;

    /**
     * @Message("系统自动关闭")
     */
    public const ORDER_CLOSE_TYPE_SYSTEM_AUTO_CLOSE = 3;

    /**
     * @Message("维权完成关闭订单")
     */
    public const ORDER_CLOSE_TYPE_REFUND_SUCCESS_CLOSE = 4;


}
