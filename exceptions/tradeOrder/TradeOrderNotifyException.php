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

namespace shopstar\exceptions\tradeOrder;

use shopstar\bases\exception\BaseException;

/**
 * 交易订单回调通知异常类
 * Class TradeOrderNotifyException
 * @package shopstar\exceptions\tradeOrder
 * @author likexin
 */
class TradeOrderNotifyException extends BaseException
{

    /** 1082XX */

    /**
     * @Message("参数错误 raw为空或格式错误")
     */
    public const CHECK_PARAMS_NOTIFY_PARAMS_EMPTY = 108200;

    /**
     * @Message("参数错误 交易订单号为空")
     */
    public const CHECK_PARAMS_TRADE_NO_EMPTY = 108201;

    /**
     * @Message("参数错误 外部交易订单号为空")
     */
    public const CHECK_PARAMS_OUT_TRADE_NO_EMPTY = 108203;

    /**
     * @Message("参数错误 支付金额为空")
     */
    public const CHECK_PARAMS_PAY_PRICE_EMPTY = 108204;

    /**
     * @Message("交易订单未查询到或状态错误")
     */
    public const LOAD_ORDER_NOT_FOUND = 108210;

    /**
     * @Message("支付金额与交易订单金额不符")
     */
    public const LOAD_ORDER_CHECK_PAY_PRICE_FAIL = 108211;

    /**
     * @Message("验签失败")
     */
    public const VERIFY_SIGN_FAIL = 108220;

    /**
     * @Message("更新订单状态，订单数量不匹配")
     */
    public const UPDATE_ORDER_COUNT_NOT_MATCH = 108230;

    /**
     * @Message("回调错误")
     */
    public const ALIPAY_NOTIFY_ERROR = 108231;

}