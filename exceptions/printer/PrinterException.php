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
 * 打印小票异常
 * Class PrinterException
 * @package shopstar\exceptions\printer
 * @author 青岛开店星信息技术有限公司
 */
class PrinterException extends BaseException
{
    /******************** 后台 ********************/

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_ACTIVE_RECORD_NOT_EXISTS = 481000;

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_FORBIDDEN_RECORD_NOT_EXISTS = 481001;

    /**
     * @Message("打印机参数校验异常")
     */
    public const PRINTER_INDEX_ADD_PARAMS_INVALID = 481002;

    /**
     * @Message("添加打印机失败")
     */
    public const PRINTER_INDEX_ADD_RESULT_FAILED = 481003;

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_EDIT_RECORD_NOT_EXISTS = 481004;

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_SAVE_RECORD_NOT_EXISTS = 481005;

    /**
     * @Message("打印机参数校验异常")
     */
    public const PRINTER_INDEX_SAVE_PARAMS_INVALID = 481006;

    /**
     * @Message("保存打印机失败")
     */
    public const PRINTER_INDEX_SAVE_RESULT_FAILED = 481006;

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_DELETE_RECORD_NOT_EXISTS = 481007;

    /**
     * @Message("取消所有未打印订单参数错误")
     */
    public const PRINTER_INDEX_CANCEL_PARAMS_INVALID = 481008;

    /**
     * @Message("打印机不存在")
     */
    public const PRINTER_INDEX_CANCEL_RECORD_NOT_EXISTS = 481009;

    /**
     * @Message("打印机参数校验异常")
     */
    public const PRINTER_INDEX_TEST_PARAMS_INVALID = 481010;
}