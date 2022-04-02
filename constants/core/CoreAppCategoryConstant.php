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

namespace shopstar\constants\core;

use shopstar\bases\constant\BaseConstant;

/**
 * 系统应用分类常量
 * Class CoreAppCategoryConstant
 * @method getTitle($code) static 获取标题
 * @package shopstar\constants\core
 * @author 青岛开店星信息技术有限公司
 */
class CoreAppCategoryConstant extends BaseConstant
{
    /**
     * @Title("业务渠道")
     */
    public const CATEGORY_CHANNEL = 0;
    /**
     * @Title("业务插件")
     */
    public const CATEGORY_BUSINESS = 10;

    /**
     * @Title("分销管理")
     */
    public const CATEGORY_COMMISSION = 11;
    /**
     * @Title("营销玩法")
     */
    public const CATEGORY_MARKET = 20;

    /**
     * @Title("辅助工具")
     */
    public const CATEGORY_TOOL = 30;


}