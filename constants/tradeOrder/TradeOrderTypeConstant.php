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
 * 交易订单类型常量类
 * Class TradeOrderTypeConstant
 * @method static getNotifyFunction(int $code) 获取回调函数
 * @package shopstar\constants\tradeOrder
 * @author likexin
 */
class TradeOrderTypeConstant extends BaseConstant
{

    /**
     * @Message("商城订单")
     * @NotifyFunction("shopstar\services\order\OrderService::paySuccess")
     */
    public const TYPE_SHOP_ORDER = 20;

    /**
     * @Message("会员充值")
     * @NotifyFunction("shopstar\models\member\MemberLogModel::paySuccess")
     */
    public const TYPE_MEMBER_RECHARGE = 30;

    /**
     * @Message("购买优惠券")
     * @NotifyFunction("shopstar\services\sale\CouponLogService::paySuccess")
     */
    public const TYPE_MEMBER_COUPON_ORDER = 40;

}