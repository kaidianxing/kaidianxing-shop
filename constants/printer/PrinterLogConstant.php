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

namespace shopstar\constants\printer;

use shopstar\bases\constant\BaseConstant;

/**
 * 小票日志
 * Class PrinterLogConstant
 * @package shopstar\constants\printer
 * @author 青岛开店星信息技术有限公司
 */
class PrinterLogConstant extends BaseConstant
{

    /************** 打印机 **************/

    /**
     * @Text("打印机-新增")
     */
    public const PRINTER_ADD = 480100;

    /**
     * @Text("打印机-保存")
     */
    public const PRINTER_SAVE = 480101;

    /**
     * @Text("打印机-取消任务")
     */
    public const PRINTER_CANCEL = 480102;

    /**
     * @Text("打印机-删除")
     */
    public const PRINTER_DELETE = 480103;

    /**
     * @Text("打印机-启用")
     */
    public const PRINTER_ACTIVE = 480104;

    /**
     * @Text("打印机-禁用")
     */
    public const PRINTER_FORBIDDEN = 480105;

    /************** 模板 **************/

    /**
     * @Text("打印模板-新增")
     */
    public const PRINTER_TEMPLATE_ADD = 480200;

    /**
     * @Text("打印模板-保存")
     */
    public const PRINTER_TEMPLATE_SAVE = 480201;

    /**
     * @Text("打印模板-删除")
     */
    public const PRINTER_TEMPLATE_DELETE = 480202;


    /************** 打印任务 **************/

    /**
     * @Text("打印任务-新增")
     */
    public const PRINTER_TASK_ADD = 480300;

    /**
     * @Text("打印任务-保存")
     */
    public const PRINTER_TASK_SAVE = 480301;

    /**
     * @Text("打印任务-删除")
     */
    public const PRINTER_TASK_DELETE = 480302;
}