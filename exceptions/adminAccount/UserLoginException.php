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

namespace shopstar\exceptions\adminAccount;

use shopstar\bases\exception\BaseException;

/**
 * 用户登录异常
 * Class UserLoginException
 * @package shopstar\exceptions\adminAccount
 * @author 青岛开店星信息技术有限公司
 */
class UserLoginException extends BaseException
{

    /**
     * @Message("暂未开放")
     */
    public const LOGIN_INIT_CLIENT_TYPE_INVALID = 110101;

    /**
     * @Message("参数错误 username不能为空")
     */
    public const LOGIN_SUBMIT_PARAM_USERNAME_EMPTY = 110020;

    /**
     * @Message("参数错误 password不能为空")
     */
    public const LOGIN_SUBMIT_PARAM_PASSWORD_EMPTY = 110021;

    /**
     * @Message("当前已经登录")
     */
    public const LOGIN_SUBMIT_USER_ALREADY_LOGIN = 110022;

    /**
     * @Message("用户名错误或用户不存在")
     */
    public const LOGIN_SUBMIT_USER_NOT_EXIST = 110023;

    /**
     * @Message("当前用户无法登录，请联系管理员")
     */
    public const LOGIN_SUBMIT_USER_STATUS_INVALID = 110024;

    /**
     * @Message("账号或密码不正确")
     */
    public const LOGIN_SUBMIT_USER_PASSWORD_INVALID = 110025;

    /**
     * @Message("参数错误 token不能为空")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_PARAM_TOKEN_EMPTY = 110030;

    /**
     * @Message("token无效")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_PARAM_TOKEN_INVALID = 110031;

    /**
     * @Message("token无效")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_PARAM_INVALID = 110032;

    /**
     * @Message("用户名错误或用户不存在")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_USER_NOT_EXIST = 110033;

    /**
     * @Message("当前用户无法登录，请联系管理员")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_USER_STATUS_INVALID = 110034;

    /**
     * @Message("当前已经登录")
     */
    public const LOGIN_SUBMIT_BY_TOKEN_USER_ALREADY_LOGIN = 110035;

    /**
     * @Message("没有权限")
     */
    public const LOGIN_SUBMIT_USER_PERMISSION_DENIED = 110036;

}