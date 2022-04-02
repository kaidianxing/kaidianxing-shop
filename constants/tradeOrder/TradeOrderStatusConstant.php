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

namespace shopstar\constants\tradeOrder;

use shopstar\bases\constant\BaseConstant;

/**
 * 交易订单状态常量类
 * Class TradeOrderStatusConstant
 * @package shopstar\constants\tradeOrder
 * @author likexin
 */
class TradeOrderStatusConstant extends BaseConstant
{

    /**
     * @Text("交易订单关闭")
     */
    public const STATUS_CLOSED = -1;

    /**
     * @Text("待支付")
     */
    public const STATUS_DEFAULT = 0;

    /**
     * @Text("等待支付")
     */
    public const STATUS_WAIT_PAY = 10;

    /**
     * @Text("调用支付失败")
     */
    public const STATUS_INVOKE_PAY_FAIL = 20;

    /**
     * @Text("支付回调失败")
     */
    public const STATUS_NOTIFY_FAIL = 21;

    /**
     * @Text("支付成功")
     */
    public const STATUS_SUCCESS = 30;

    /**
     * @Text("支付成功(内部回调失败)")
     */
    public const STATUS_SUCCESS_NOTIFY_FAIL = 31;

}