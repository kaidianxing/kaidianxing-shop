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


namespace shopstar\exceptions\expressHelper;

use shopstar\bases\exception\BaseException;

/**
 * 快递助手异常 49
 * Class ExpressHelperException
 * @author 青岛开店星信息技术有限公司
 */
class ExpressHelperException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    public const MANAGE_PRINT_INDEX_PARAMS_ERROR = 490000;

    /**
     * @Message("获取打印列表失败")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_LIST_ERROR = 490001;

    /**
     * @Message("获取实例失败")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_API_INSTANCE_ERROR = 490002;

    /**
     * @Message("获取打印参数失败")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_PARAMS_ERROR = 490003;

    /**
     * @Message("提交打印订单失败")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_SUBMIT_ORDER_ERROR = 490004;

    /**
     * @Message("打印订单返回状态错误")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_SUBMIT_ORDER_RETURN_STATUS_ERROR = 490005;

    /**
     * @Message("请检查快递鸟appid或key")
     */
    public const MANAGE_PRINT_INDEX_GET_PRINT_LACK_PARAMS_ERROR = 490006;


    /**
     * @Message("参数错误")
     */
    public const MANAGE_PRINT_INDEX_CALLBACK_PARAMS_ERROR = 490010;

    /**
     * @Message("面单模板不存在")
     */
    public const MANAGE_PRINT_INDEX_CALLBACK_EXPRESS_TEMPLATE_NOT_FOUND_ERROR = 490011;

    /**
     * @Message("回调失败")
     */
    public const MANAGE_PRINT_INDEX_CALLBACK_ERROR = 490012;

    /**
     * @Message("获取打印参数失败")
     */
    public const MANAGE_EXPRESS_TEMPLATE_TEST_PRINT_PARAMS_ERROR = 490020;

    /**
     * @Message("请检查快递鸟appid或key")
     */
    public const MANAGE_EXPRESS_TEMPLATE_TEST_PRINT_LACK_PARAMS_ERROR = 490021;

    /**
     * @Message("发货单参数错误")
     */
    public const SEND_BILL_PRINT_INDEX_PARAMS_ERROR = 490100;

    /**
     * @Message("发货单记录不存在")
     */
    public const SEND_BILL_PRINT_TEMPLATE_NOT_FOUND_ERROR = 490101;

    /**
     * @Message("包裹数量最大支持300件")
     */
    public const MANAGE_PRINT_INDEX_NUMBER_ERROR = 490102;

    /**
     * @Message("待打印订单信息不存在")
     */
    public const SEND_BILL_PRINT_NO_FOUND_ORDER = 490103;
}