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

namespace shopstar\constants\commission;

use shopstar\bases\constant\BaseConstant;

/**
 * 分销商品
 * Class CommissionGoodsConstant
 * @package shopstar\constants\commission
 */
class CommissionGoodsConstant extends BaseConstant
{
    /**
     * @Message("系统默认设置")
     */
    public const TYPE_SYSTEM = 0;
    
    /**
     * @Message("商品设置")
     */
    public const TYPE_GOODS = 1;
    
    /**
     * @Message("规格设置")
     */
    public const TYPE_GOODS_OPTION = 2;
    
    /**
     * @Message("佣金类型 比例")
     */
    public const COMMISSION_TYPE_SCALE = 1;
    
    /**
     * @Message("佣金类型 金额")
     */
    public const COMMISSION_TYPE_MONEY = 2;
    
    
}