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
 * Class AppsException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions
 */
class AppsException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const MANAGE_BUY_PARAMS_ERROR = 140000;

    /**
     * @Message("支付方式错误")
     */
    const MANAGE_BUY_PAY_METHOD_ERROR = 140001;

    /**
     * @Message("应用选择错误")
     */
    const MANAGE_BUY_APP_OR_OPTION_ERROR = 140002;

    /**
     * @Message("您可免费使用此营销活动，无需购买")
     */
    const MANAGE_BUY_APP_IS_FREE_ERROR = 140003;

    /**
     * @Message("您可永久使用此营销活动，无需购买")
     */
    const MANAGE_BUY_APP_IS_FOREVER_ERROR = 140004;

    /**
     * @Message("未开启支付方式，请联系管理员进行购买")
     */
    const MANAGE_BUY_PAY_METHOD_NOT_OPEN_ERROR = 140005;

    /**
     * @Message("创建订单失败")
     */
    const MANAGE_BUY_CREATE_ORDER_ERROR = 140006;

    /**
     * @Message("创建订单应用失败")
     */
    const MANAGE_BUY_CREATE_ORDER_APP_ERROR = 140007;

    /**
     * @Message("购买失败(send fail)")
     */
    const MANAGE_BUY_FREE_ORDER_ERROR = 140008;

    /**
     * @Message("保存支付回调参数失败")
     */
    const MANAGE_BUY_SAVE_PAY_BODY_ERROR = 140009;

    /**
     * @Message("支付参数配置错误")
     */
    const MANAGE_BUY_PAY_PARAMS_ERROR = 1400010;

    /**
     * @Message("订单不存在")
     */
    const MANAGE_INDEX_GET_PAY_STATUS_ORDER_NOT_FOUND_ERROR = 140020;

    /**
     * @Message("标识为空")
     */
    const MANAGE_INDEX_GET_APP_OVERDUE_PARAMS_ERROR = 140025;

    /**
     * @Message("应用未找到")
     */
    const MANAGE_INDEX_GET_APP_OVERDUE_APP_NOT_FOUND_ERROR = 140026;
}
