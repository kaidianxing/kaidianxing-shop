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



namespace shopstar\bases\exception;

/**
 * 基础接口异常
 * Class BaseApiException
 * @package shopstar\bases\exception
 */
class BaseApiException extends BaseException
{

    /**
     * @Message("系统已经安装完成")
     */
    public const SYSTEM_INSTALLED = -11000;

    /**
     * @Message("系统未安装")
     */
    public const SYSTEM_NOT_INSTALL = -11001;

    /**
     * @Message("错误的请求")
     */
    public const REQUEST_MUST_POST = -10000;

    /**
     * @Message("请求头参数错误(Client-Type)")
     */
    public const REQUEST_CLIENT_TYPE_EMPTY = -10001;

    /**
     * @Message("请求头参数错误(Client-Type)")
     */
    public const REQUEST_PLUGIN_CLIENT_TYPE_INVALID = -10006;

    /**
     * @Message("请求头参数无效(Client-Type)")
     */
    public const REQUEST_CLIENT_TYPE_INVALID = -10002;

    /**
     * @Message("请求头参数错误(Session-Id)")
     */
    public const REQUEST_SESSION_ID_EMPTY = -10003;

    /**
     * @Message("应用暂无权限")
     */
    public const REQUEST_APP_NOT_PERM = -10004;

}