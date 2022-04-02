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

namespace shopstar\components\payment\base;

use shopstar\bases\constant\BaseConstant;

/**
 * 获取订单类型
 * Class PayTypeConstance
 * @package shopstar\constants
 * @method getMessage($code) static 获取文案
 * @method getModel($code) static 获取模型
 * @author 青岛开店星信息技术有限公司
 */
class PayOrderTypeConstant extends BaseConstant
{

    /**
     * @message("订单下单")
     * @model("\shopstar\models\order\OrderModel")
     *
     */
    public const ORDER_TYPE_ORDER = 20;
    
    /**
     * @message("会员充值")
     * @model("\shopstar\models\member\MemberLogModel")
     */
    public const ORDER_TYPE_MEMBER_LOG = 30;

    /**
     * @message("优惠券支付")
     * @model("\shopstar\models\sale\CouponLogModel")
     */
    public const ORDER_TYPE_COUPON_LOG = 40;


}
