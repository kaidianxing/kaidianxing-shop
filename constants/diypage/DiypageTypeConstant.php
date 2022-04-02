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
 * 装修页面类型
 * Class DiypageTypeConstant
 * @method static getMessage($code)
 * @package shopstar\constants\diypage
 * @author 青岛开店星信息技术有限公司
 */
class DiypageTypeConstant extends BaseConstant
{

    /**
     * @var array 商城页面映射
     */
    public static $pageShopMap= [
        self::TYPE_HOME,
        self::TYPE_GOODS_DETAIL,
        self::TYPE_MEMBER,
    ];

    /**
     * @var array 应用页面映射
     */
    public static $pageAppMap = [
        self::TYPE_APP_COMMISSION,
    ];

    /**
     * @Message("商城首页")
     */
    public const TYPE_HOME = 10;

    /**
     * @Message("商品详情")
     */
    public const TYPE_GOODS_DETAIL = 11;

    /**
     * @Message("会员中心")
     */
    public const TYPE_MEMBER = 12;

    /**
     * @Message("分销中心")
     */
    public const TYPE_APP_COMMISSION = 20;

}