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
 * 打印任务异常
 * Class PrinterTaskException
 * @package shopstar\exceptions\printer
 */
class PrinterTaskException extends BaseException
{
    /******************** 后台 ********************/

    /**
     * @Message("打印机参数校验异常")
     */
    public const PRINTER_TASK_ADD_PARAMS_INVALID = 483000;

    /**
     * @Message("添加打印任务失败")
     */
    public const PRINTER_TASK_ADD_RESULT_INVALID = 483001;

    /**
     * @Message("打印机参数校验异常")
     */
    public const PRINTER_TASK_SAVE_PARAMS_INVALID = 483002;

    /**
     * @Message("保存打印任务失败")
     */
    public const PRINTER_TASK_SAVE_RESULT_INVALID = 483003;

    /**
     * @Message("打印任务不存在")
     */
    public const PRINTER_TASK_EDIT_RECORD_INVALID = 483004;

    /**
     *  @Message("打印任务不存在")
     */
    public const PRINTER_TASK_DELETE_RECORD_INVALID = 483005;

    /**
     *  @Message("打印任务参数错误")
     */
    public const PRINTER_TASK_EXECUTE_PARAMS_INVALID = 483006;
}