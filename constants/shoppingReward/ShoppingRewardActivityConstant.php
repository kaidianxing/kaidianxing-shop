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

namespace shopstar\constants\shoppingReward;

use shopstar\bases\constant\BaseConstant;

/**
 * 购物常量
 * Class ShoppingRewardActivityConstant
 * @package shopstar\constants\shoppingReward
 */
class ShoppingRewardActivityConstant extends BaseConstant
{
    /**
     * @Text("允许商品使用")
     */
    const GOODS_TYPE_ALLOW_GOODS = 1;
    
    /**
     * @Text("不允许商品使用")
     */
    const GOODS_TYPE_NOT_ALLOW_GOODS = 2;
    
    /**
     * @Text("指定分类")
     */
    const GOODS_TYPE_ALLOW_CATE = 3;
    
    /**
     * @Text("会员等级限制")
     */
    const MEMBER_LEVEL_LIMIT = 1;
    
    /**
     * @Text("会员分组限制")
     */
    const MEMBER_GROUP_LIMIT = 2;
    
    /**
     * @Text("优惠券奖励")
     */
    const REWARD_COUPON = 1;
    
    /**
     * @Text("积分奖励")
     */
    const REWARD_CREDIT = 2;
    
    /**
     * @Text("余额奖励")
     */
    const REWARD_BALANCE = 3;

    /**
     * @Text("红包奖励")
     */
    const REWARD_RED_PACKAGE = 4;

}