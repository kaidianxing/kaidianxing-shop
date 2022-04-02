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
 * 分销海报访问页面
 * Class PosterVisitPageConstant
 * @package shopstar\constants\poster
 * @method getText($code) static string
 * @author 青岛开店星信息技术有限公司
 */
class PosterVisitPageConstant extends BaseConstant
{
    //访问页面 0默认 1商城主页 2分销首页

    /**
     * @Message("默认")
     */
    public const POSTER_VISIT_PAGE_DEFAULT = 0;

    /**
     * @Message("商城首页")
     */
    public const POSTER_VISIT_PAGE_SHOP = 1;

    /**
     * @Message("分销首页")
     */
    public const POSTER_VISIT_PAGE_COMMISSION = 2;
}