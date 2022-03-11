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


namespace shopstar\exceptions\virtualAccount;

use shopstar\bases\exception\BaseException;

/**
 * 卡密库
 * Class VirtualAccountException
 * @package shopstar\exceptions\virtualAccount
 */
class VirtualAccountException extends BaseException
{

    /**
     * @Message("参数错误")
     */
    public const PARAMS_ERROR = 590100;

    /**
     * @Message("卡密库名称重复")
     */
    public const VIRTUAL_ACCOUNT_PARAMS_NAME_ERROR = 590101;

    /**
     * @Message("数据结构字段数量不正确")
     */
    public const VIRTUAL_ACCOUNT_PARAMS_CONFIG_ERROR = 590102;

    /**
     * @Message("更新失败")
     */
    public const VIRTUAL_ACCOUNT_UPDATE_ERROR = 590201;

    /**
     * @Message("卡密库不存在")
     */
    public const VIRTUAL_ACCOUNT_NOT_NULL = 590202;

    /**
     * @Message("excel解析错误，请检查excel")
     */
    public const VIRTUAL_ACCOUNT_EXCEL_PARSING_ERROR = 590203;

    /**
     * @Message("一次性最大导入1000条")
     */
    public const VIRTUAL_ACCOUNT_EXCEL_IMPORT_COUNT_MAX = 590204;

    /**
     * @Message("卡密库数据不存在")
     */
    public const VIRTUAL_ACCOUNT_DATA_NOT_NULL = 590205;

    /**
     * @Message("卡密库数据重复")
     */
    public const VIRTUAL_ACCOUNT_DATA_ERROR = 590206;

    /**
     * @Message("卡密库库存为0")
     */
    public const VIRTUAL_ACCOUNT_STOCK_NOT_NULL = 590207;


    // 判断权限等
    /**
     * @Message("未拥有消息通知应用")
     */
    public const NOT_NOTICE_PLUGINS_PREM = 590501;

    /**
     * @Message("导出失败")
     */
    public const MEMBER_EXPORT_FAIL = 590601;


}