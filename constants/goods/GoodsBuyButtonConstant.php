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

namespace shopstar\constants\goods;

use shopstar\bases\constant\BaseConstant;

/**
 * 价格面议商品constant
 * Class GoodsConst
 * @author niznegchao
 * @package shopstar\constants
 * @author 青岛开店星信息技术有限公司
 */
class GoodsBuyButtonConstant extends BaseConstant
{

    /**
     * @Text("走装修")
     */
    public const GOODS_BUY_BUTTON_TYPE_DEFAULT = 0;

    /**
     * @Text("自定义(价格面议)")
     */
    public const GOODS_BUY_BUTTON_TYPE_CUSTOM = 1;

    /**
     * @Text("默认(立即下单)")
     */
    public const GOODS_BUY_BUTTON_CLICK_TYPE_DEFAULT = 1;

    /**
     * @Text("价格面议")
     */
    public const GOODS_BUY_BUTTON_CLICK_TYPE_CUSTOM = 2;

    /**
     * @Text("弹窗")
     */
    public const GOODS_BUY_BUTTON_CLICK_STYLE_POP = 1;

    /**
     * @Text("跳转链接")
     */
    public const GOODS_BUY_BUTTON_CLICK_STYLE_JUMP = 2;

    /**
     * @Text("拨打电话")
     */
    public const GOODS_BUY_BUTTON_CLICK_STYLE_PHONE = 3;

    /**
     * @Text("使用商品默认电话")
     */
    public const GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_DEFAULT = 1;

    /**
     * @Text("自定义电话")
     */
    public const GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_CUSTOM = 2;

}
