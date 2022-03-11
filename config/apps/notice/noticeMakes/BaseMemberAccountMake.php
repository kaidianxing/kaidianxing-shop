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

namespace shopstar\config\apps\notice\noticeMakes;


use shopstar\components\notice\bases\BaseMake;

class BaseMemberAccountMake extends BaseMake
{
    /**
     * 预留字段 ，用于字段名转化
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $reserveField = [
        'recharge_pay_method' => '[充值支付方式]',
        'member_nickname' => '[会员昵称]',
        'nickname' => '[变动账户]',
        'recharge_price' => '[充值金额]',
        'recharge_time' => '[充值时间]',
        'recharge_method' => '[充值方式]',
        'withdraw_price' => '[提现金额]',
        'withdraw_time' => '[提现时间]',
        'balance_change_method' => '[余额变动方式]',
        'balance_change_reason' => '[余额变动原因]',
        'member_balance' => '[账户余额]',
        'member_credit' => '[账户积分]',
        'coupon_type' => '[优惠券类型]',
        'coupon_send_status' => '[发放状态]',
        'coupon_send_time' => '[发放时间]',
        'change_time' => '[变动时间]',
        'change_reason' => '[变动类型]',

    ];
}
