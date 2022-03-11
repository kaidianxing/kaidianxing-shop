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
 * 用户注册异常
 * Class UserRegisterException
 * @package modules\account\exceptions
 */
class UserRegisterException extends BaseException
{

    /**
     * @Message("缺少手机号")
     */
    public const REGISTER_SEND_SMS_CODE_EMPTY_MOBILE_ERROR = 111000;

    /**
     * @Message("图形验证码错误")
     */
    public const REGISTER_SEND_SMS_CODE_IMAGE_CODE_ERROR = 111001;

    /**
     * @Message("短信设置错误")
     */
    public const REGISTER_SEND_SMS_CODE_SMS_SETTING_ERROR = 111002;

    /**
     * @Message("缺少用户名")
     */
    public const REGISTER_SUBMIT_EMPTY_USERNAME_ERROR = 111003;

    /**
     * @Message("缺少密码")
     */
    public const REGISTER_SUBMIT_EMPTY_PASSWORD_ERROR = 111004;

    /**
     * @Message("两次密码不一致")
     */
    public const REGISTER_SUBMIT_EMPTY_CONFIRM_PASSWORD_ERROR = 111005;

    /**
     * @Message("验证码错误")
     */
    public const REGISTER_SUBMIT_SMS_CODE_ERROR = 111006;

    /**
     * @Message("缺少短信类型")
     */
    public const REGISTER_SEND_SMS_CODE_TYPE_EMPTY_ERROR = 111007;

    /**
     * @Message("修改用户资料失败")
     */
    public const AUDIT_FORM_ERROR = 111010;

    /**
     * @Message("用户不存在")
     */
    public const AUDIT_USER_NOT_EXIST_ERROR = 111011;

    /**
     * @Message("创建用户失败")
     */
    public const REGISTER_SUBMIT_ERROR = 111012;

    /**
     * @Message("缺少用户名")
     */
    public const REGISTER_FORGET_EMPTY_USERNAME_ERROR = 111020;

    /**
     * @Message("缺少密码")
     */
    public const REGISTER_FORGET_EMPTY_PASSWORD_ERROR = 111021;

    /**
     * @Message("两次密码不一致")
     */
    public const REGISTER_FORGET_EMPTY_CONFIRM_PASSWORD_ERROR = 111022;

    /**
     * @Message("验证码错误")
     */
    public const REGISTER_FORGET_SMS_CODE_ERROR = 111023;

    /**
     * @Message("验证码错误")
     */
    public const REGISTER_FORGET_USER_EMPTY = 111024;
    
    /**
     * @Message("短信未配置，请联系管理进行配置。")
     */
    public const CORE_SETTING_SMS_SETTING_ERROR = 111025;
    
}