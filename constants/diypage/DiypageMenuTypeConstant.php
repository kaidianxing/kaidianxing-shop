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

namespace shopstar\constants\diypage;

use shopstar\bases\constant\BaseConstant;

/**
 * 装修应用类型常量
 * Class DiypageMenuTypeConstant
 * @method getText($code)
 * @package shopstar\constants\diypage
 * @author 青岛开店星信息技术有限公司
 */
class DiypageMenuTypeConstant extends BaseConstant
{

    /**
     * @Text("自定义")
     * @Icon("icon-tianjia")
     * @thumb("")
     */
    public const TYPE_DIY = 0;

    /**
     * @Text("商城")
     * @Icon("icon-zuocedaohang-shouye1")
     * @thumb("/static/dist/shop/menu/menu_home.png")
     */
    public const TYPE_SHOP = 10;

    /**
     * @Text("分销")
     * @Icon("icon-fenxiao")
     * @thumb("/static/dist/shop/menu/menu_comission.png")
     */
    public const TYPE_APP_COMMISSION = 20;

    /**
     * 获取并且添加后缀
     * @param int $code
     * @param string $suffix
     * @return string
     * @author likexin
     */
    public static function getTextWithSuffix(int $code, string $suffix)
    {
        return self::getText($code) . $suffix;
    }

}