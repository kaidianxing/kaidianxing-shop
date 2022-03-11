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

namespace shopstar\config\apps\commission;

use shopstar\components\notice\bases\BaseMake;

class NoticeMake extends BaseMake
{
    /**
     * 预留字段 ，用于字段名转化
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $reserveField = [
        'member_nickname' => '[用户昵称]',
        'mobile' => '[手机号]',
        'change_time' => '[变动时间]',
        'down_line_nickname' => '[下级昵称]',
        'down_line_commission_level' => '[下级等级]',
        'old_commission_level' => '[旧等级]',
        'new_commission_level' => '[新等级]',
        'order_no' => '[订单编号]',
        'order_price' => '[订单金额]',
        'commission' => '[佣金金额]',
        'goods_title' => '[商品名称]',
        'pay_time' => '[付款时间]',
        'withdraw_time' => '[提现时间]',
        'withdraw_money' => '[提现金额]',
        'audit_result' => '[审核结果]',
        'shop_name' => '[商城名称]',
        'apply_price' => '[提现金额]',
        'name' => '[用户昵称]',
        'junior_level' => '[下级层级]',
    ];
}