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

/**
 * 会员
 * Class MemberType
 * @package shopstar\constants
 */
class MemberTypeConstant
{
    /**
     * @Message("金额充值类型 固定")
     */
    public const RECHARGE_CHANGE_TYPE_FIXED = 0;
    
    /**
     * @Message("金额充值类型 充值")
     */
    public const RECHARGE_CHANGE_TYPE_ADD = 1;
    
    /**
     * @Message("金额充值类型 减少")
     */
    public const RECHARGE_CHANGE_TYPE_SUB = 2;
    
    /**
     * @Message("用户未关注公众号")
     */
    public const MEMBER_NOT_FOLLOW = 0;
    
    /**
     * @Message("用户已关注公众号")
     */
    public const MEMBER_HAVE_FOLLOW = 1;
    
    /**
     * @Message("用户取消关注公众号")
     */
    public const MEMBER_CANCEL_FOLLOW = 2;
    
    
}