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

namespace shopstar\exceptions\printer;


use shopstar\bases\exception\BaseException;

/**
 * 模板异常
 * Class PrinterTemplateException
 * @package shopstar\exceptions\printer
 */
class PrinterTemplateException extends BaseException
{
    /******************** 后台 ********************/

    /**
     * @Message("打印机模板参数校验异常")
     */
    public const PRINTER_TEMPLATE_ADD_PARAMS_INVALID = 482000;

    /**
     * @Message("打印机模板添加失败")
     */
    public const PRINTER_TEMPLATE_ADD_RESULT_FAILED = 482001;

    /**
     * @Message("打印机模板不存在")
     */
    public const PRINTER_TEMPLATE_EDIT_RECORD_INVALID = 482002;

    /**
     * @Message("打印机模板参数校验异常")
     */
    public const PRINTER_TEMPLATE_SAVE_PARAMS_INVALID = 482003;

    /**
     * @Message("打印机模板不存在")
     */
    public const PRINTER_TEMPLATE_DELETE_RECORD_INVALID = 482004;

    /**
     * @Message("打印机模板保存失败")
     */
    public const PRINTER_TEMPLATE_SAVE_RESULT_FAILED = 482005;
}