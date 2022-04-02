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

namespace shopstar\constants\expressHelper;

use shopstar\bases\constant\BaseConstant;

/**
 * Class ExpressHelperLogConstant
 * @package shopstar\constants\expressHelper
 * @author 青岛开店星信息技术有限公司
 */
class ExpressHelperLogConstant extends BaseConstant
{
    /**
     * @Text("应用-快递助手-电子面单-面单模板-添加")
     */
    public const EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_ADD = 490000;

    /**
     * @Text("应用-快递助手-电子面单-面单模板-修改")
     */
    public const EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_EDIT = 490001;

    /**
     * @Text("应用-快递助手-电子面单-面单模板-删除")
     */
    public const EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_DELETE = 490002;

    /**
     * @Text("应用-快递助手-电子面单-面单模板-设置/取消默认")
     */
    public const EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_SWITCH = 490003;

    /**
     * @Text("应用-快递助手-电子面单-发货人模板-添加")
     */
    public const EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_ADD = 490010;

    /**
     * @Text("应用-快递助手-电子面单-发货人模板-修改")
     */
    public const EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_EDIT = 490011;

    /**
     * @Text("应用-快递助手-电子面单-发货人模板-删除")
     */
    public const EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_DELETE = 490012;

    /**
     * @Text("应用-快递助手-电子面单-发货人模板-设置/取消默认")
     */
    public const EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_SWITCH = 490013;


    /**
     * @Text("应用-快递助手-电子面单-基础设置-设置")
     */
    public const EXPRESS_HELPER_LOG_BASE_SET = 490015;

    /**
     * @Text("开始打印")
     */
    public const EXPRESS_HELPER_STATUS_START = 10;

    /**
     * @Text("打印成功")
     */
    public const EXPRESS_HELPER_STATUS_SUCCESS = 20;
}