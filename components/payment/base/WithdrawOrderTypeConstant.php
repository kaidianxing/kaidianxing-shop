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
 * 提现订单方式
 * Class WithdrawTypeConstance
 * @package shopstar\components\payment\base
 */
class WithdrawOrderTypeConstant extends BaseConstant
{
    /**
     * @message("余额提现")
     */
    public const WITHDRAW_ORDER_MEMBER_LOG = 10;

    /**
     * @message("佣金提现")
     */
    public const WITHDRAW_ORDER_COMMISSION = 20;

    /**
     * @message("海报奖励")
     */
    public const WITHDRAW_PLUGIN_POSTER_REWARD = 30;

    /**
     * @message("会员红包")
     */
    public const MEMBER_WITHDRAW = 42;
    
    
}