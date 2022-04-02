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

namespace shopstar\constants;

use shopstar\bases\constant\BaseConstant;

/**
 * 系统设置
 * Class SyssetType
 * @package shopstar\constants
 * @author 青岛开店星信息技术有限公司
 */
class SyssetTypeConstant extends BaseConstant
{
    /**
     * @Message("不自动关闭")
     */
    public const CUSTOMER_CLOSE_NOT_CLOSE = 1;

    /**
     * @Message("自定义订单关闭时间")
     */
    public const CUSTOMER_CLOSE_ORDER_TIME = 2;

    /**
     * @Message("关闭订单发送通知")
     */
    public const CLOSE_ORDER_NOTICE_SEND = 2;

    /**
     * @Message("自定义自动收货时间")
     */
    public const CUSTOMER_AUTO_RECEIVE_TIME = 2;

    /**
     * @Message("库存预警通知 开启")
     */
    public const STOCK_WARNING_NOTICE_OPEN = 1;

    /**
     * @Message("短信是模板")
     */
    public const SMS_IS_TEMPLATE = 1;

    /**
     * @Message("该类型短信已经配置")
     */
    public const SMS_TYPE_OPEN = 1;

    /**
     * @Message("短信模板状态开启")
     */
    public const SMS_STATE_OPEN = 1;

    /**
     * @Message("自定义售后维权申请时间")
     */
    public const CUSTOMER_REFUND_TIME = 2;

    /**
     * @Message("自定义分享标题")
     */
    public const CUSTOMER_SHARE_TITLE = 2;

    /**
     * @Message("自定义分享logo")
     */
    public const CUSTOMER_SHARE_LOGO = 2;

    /**
     * @Message("自定义分享链接")
     */
    public const CUSTOMER_SHARE_LINK = 2;

    /**
     * @Message("自定义积分上限")
     */
    public const CUSTOMER_CREDIT_LIMIT = 2;

    /**
     * @Message("余额充值开启")
     */
    public const RECHARGE_OPEN = 1;

    /**
     * @Message("自定义提现金额")
     */
    public const CUSTOMER_WITHDRAW_LIMIT = 2;

    /**
     * @Message("自定义提现手续费")
     */
    public const CUSTOMER_WITHDRAW_FEE = 2;

    /**
     * @Message("自定义免手续费区间")
     */
    public const CUSTOMER_FREE_FEE = 2;

    /**
     * @Message("自定义分享描述")
     */
    public const CUSTOMER_SHARE_DESCRIPTION = 2;

    /**
     * @Message("店铺关闭")
     */
    public const SHOP_STATUS_CLOSE = 0;

    /**
     * @Message("店铺开启")
     */
    public const SHOP_STATUS_OPEN = 1;

    /**
     * @Message("自定义关闭时间")
     */
    public const CUSTOMER_TIMEOUT_CANCEL_REFUND_TIME = 1;

}