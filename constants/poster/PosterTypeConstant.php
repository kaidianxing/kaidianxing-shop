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

namespace shopstar\constants\poster;

use shopstar\bases\constant\BaseConstant;

/**
 * Class PosterTypeConstant
 * @package shopstar\constants\poster
 * @method getText($code) static string
 */
class PosterTypeConstant extends BaseConstant
{

    /**
     * @Message("自定义")
     */
    public const POSTER_TYPE_DIY = 0;

    /**
     * @Text("商品海报")
     */
    public const POSTER_TYPE_GOODS = 10;

    /**
     * @Text("分销海报")
     */
    public const POSTER_TYPE_COMMISSION = 20;

    /**
     * @Text("关注海报")
     */
    public const POSTER_TYPE_ATTENTION = 30;


}