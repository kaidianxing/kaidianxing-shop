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

namespace shopstar\constants\creditShop;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分商城日志常量类
 * Class CreditShopLogConstant.
 * @package shopstar\constants\creditShop
 */
class CreditShopLogConstant extends BaseConstant
{
    /**
     * @Text("应用-积分商城-设置")
     */
    public const SETTING = 520000;

    /**
     * @Text("积分商城-商品-添加")
     */
    public const ADD = 520001;

    /**
     * @Text("积分商城-商品-编辑")
     */
    public const EDIT = 520002;

    /**
     * @Text("积分商城-商品-删除")
     */
    public const DELETE = 520003;

    /**
     * @Text("积分商城-商品-操作")
     */
    public const OP = 520004;
}
