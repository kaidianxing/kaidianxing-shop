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

namespace shopstar\constants\form;


use shopstar\bases\constant\BaseConstant;

/**
 * 系统表单日志
 * Class FormLogConstant
 * @package shopstar\constants\form
 */
class FormLogConstant extends BaseConstant
{
    /**
     * @Text("系统表单-新增")
     */
    public const FORM_ADD = 420100;

    /**
     * @Text("系统表单-删除")
     */
    public const FORM_DELETE = 420101;

    /**
     * @Text("系统表单-启用")
     */
    public const FORM_ACTIVE = 420102;

    /**
     * @Text("系统表单-禁用")
     */
    public const FORM_FORBIDDEN = 420103;

    /**
     * @Text("系统表单删除状态-未删除")
     */
    public const FORM_IS_NO_DELETE = 0;

    /**
     * @Text("系统表单删除状态-删除")
     */
    public const FORM_IS_DELETE = 1;

    /**
     * @Text("下单商品")
     */
    public const FORM_SOURCE_ORDER = 1;

    /**
     * @Test("价格面议商品")
     */
    public const FORM_SOURCE_BUY_BUTTON_GOODS = 2;

    /**
     * @Text("系统表单-修改")
     */
    public const FORM_EDIT = 420104;

    /**
     * @Text("系统表单-导出")
     */
    public const FORM_EXPORT = 420105;
}