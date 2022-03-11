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

namespace shopstar\components\byteDance\bases;

use shopstar\bases\constant\BaseConstant;

/**
 * 字节跳动组件渠道常量
 * Class ByteDanceConstant
 * @package shopstar\components\byteDance\bases
 * @method getMessage($code) static
 * @method getClass($code) static
 */
class ByteDanceConstant extends BaseConstant
{
    /**
     * @Message("头条")
     * @Class("shopstar\components\byteDance\channels\ToutiaoChannel")
     */
    public const CHANNEL_TOUTIAO = 'toutiao';
    
    /**
     * @Message("抖音")
     * @Class("shopstar\components\byteDance\channels\DouyinChannel")
     */
    public const CHANNEL_DOUYIN = 'douyin';
    
    /**
     * @Message("头条")
     * @Class("shopstar\components\byteDance\channels\ToutiaoLiteChannel")
     */
    public const CHANNEL_TOUTIAO_LITE = 'toutiao_lite';
    
}