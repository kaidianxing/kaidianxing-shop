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

namespace shopstar\constants\member;

use shopstar\bases\constant\BaseConstant;

/**
 * @author 青岛开店星信息技术有限公司
 */
class MemberLogStatusConstant extends BaseConstant
{

    /**
     * @Message("未支付或待审核")
     */
    public const ORDER_STATUS_NOT = 0;

    /**
     * @Message("成功")
     */
    public const ORDER_STATUS_SUCCESS = 10;

    /**
     * @Message("手动打款")
     */
    public const ORDER_STATUS_MANUAL_SUCCESS = 11;

    /**
     * @Message("失败")
     */
    public const ORDER_STATUS_FAIL = 20;

    /**
     * @Message("已退款")
     */
    public const ORDER_RECHARGE_REFUND = 30;

    /**
     * @Message("已拒绝")
     */
    public const ORDER_WITHDRAW_REFUND = 40;
}