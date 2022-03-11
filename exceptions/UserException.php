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

namespace shopstar\exceptions;

use shopstar\bases\exception\BaseException;

/**
 * @note:用户异常类
 * Class UserException
 * @package shopstar\exceptions
 */
class UserException extends BaseException
{

    /**
     * @Message("用户未登录")
     */
    public const CHECK_USER_NOT_LOGIN = 100200;

    /**
     * @Message("用户未找到")
     */
    public const CHECK_USER_USER_NOT_EXITS = 100201;

    /**
     * @Message("用户Session-Id无效")
     */
    public const CHECK_USER_SESSION_INVALID = 100202;

    /**
     * @Message("创建失败,请重试")
     */
    const CREATE_FAILED = 100300;
    /**
     * @Message("创建失败,请重试")
     */
    const PARAMS_ERROR = 100301;
    /**
     * @Message("记录不存在")
     */
    const RECORD_NOT_FOUND = 100302;
    /**
     * @Message("系统默认不可操作")
     */
    const DEFAULT_IS_CANT_EDIT = 100303;
    /**
     * @Message("更新失败")
     */
    const SAVE_FAILED = 100304;
    /**
     * @Message("删除失败")
     */
    const DELETE_FAILED = 100305;
    /**
     * @Message("禁用失败")
     */
    const FORBIDDEN_FAILED = 100306;
    /**
     * @Message("启用失败")
     */
    const ACTIVE_FAILED = 100307;
    /**
     * @Message("用户不存在")
     */
    const USER_NOT_EXITS = 100308;
    /**
     * @Message("店铺操作员不存在")
     */
    const SHOP_MANAGE_NOT_EXITS = 100309;

    /**
     * @Message("用户权限不足")
     */
    const USER_NOT_IS_ADMIN_ROLE = 100400;

    /**
     * @Message("审核成功")
     */
    const USER_AUDIT_PASS = 100500;

    /**
     * @Message("审核未通过")
     */
    const USER_AUDIT_NOT_PASS = 100501;


    /********** 系统管理员相关 **********/

    /**
     * @Message("操作员用户名不能为空")
     */
    const MANAGE_USER_INDEX_CREATE_USERNAME_NOT_EMPTY = 110000;

    /**
     * @Message("操作员角色不能为空")
     */
    const MANAGE_USER_INDEX_CREATE_ROLE_NOT_EMPTY = 110001;

    /**
     * @Message("操作员姓名不能为空")
     */
    const MANAGE_USER_INDEX_CREATE_NAME_NOT_EMPTY = 110002;

    /**
     * @Message("操作员手机号不能为空")
     */
    const MANAGE_USER_INDEX_CREATE_CONTACT_NOT_EMPTY = 110003;

    /**
     * @Message("操作员密码不能为空")
     */
    const MANAGE_USER_INDEX_CREATE_PASSWORD_NOT_EMPTY = 110004;

    /**
     * @Message("用户创建失败")
     */
    const MANAGE_USER_INDEX_CREATE_USER_FAILED = 110006;

    /**
     * @Message("用户已经存在")
     */
    const MANAGE_USER_INDEX_CREATE_USER_EXISTS = 110007;

    /**
     * @Message("用户已经分配给本店铺!")
     */
    const MANAGE_USER_INDEX_CREATE_MANAGE_EXISTS = 110008;

    /**
     * @Message("管理员创建失败")
     */
    const MANAGE_USER_INDEX_CREATE_MANAGE_FAILED = 110009;

    /**
     * @Message("不存在该管理员")
     */
    const MANAGE_USER_INDEX_MANAGE_NOT_EXISTS = 110010;

    /**
     * @Message("管理员修改失败")
     */
    const MANAGE_USER_INDEX_UPDATE_MANAGE_FAILED = 110011;

    /**
     * @Message("操作员ID不能为空")
     */
    const MANAGE_USER_INDEX_SAVE_ID_NOT_EMPTY = 110012;

    /**
     * @Message("操作员角色不能为空")
     */
    const MANAGE_USER_INDEX_SAVE_ROLE_NOT_EMPTY = 110013;

    /**
     * @Message("操作员名称不能为空")
     */
    const MANAGE_USER_INDEX_SAVE_NAME_NOT_EMPTY = 110014;

    /**
     * @Message("操作员联系方式不能为空")
     */
    const MANAGE_USER_INDEX_SAVE_CONTACT_NOT_EMPTY = 110015;

    /**
     * @Message("操作员禁用失败")
     */
    const MANAGE_USER_INDEX_FORBIDDEN_FAILED = 110016;

    /**
     * @Message("操作员启用失败")
     */
    const MANAGE_USER_INDEX_ACTIVE_FAILED = 110017;

    /**
     * @Message("操作员删除失败")
     */
    const MANAGE_USER_INDEX_DELETE_FAILED = 110018;


    /********** 用户错误提示 **********/

    /**
     * @Message("创建默认用户UID不能为空")
     */
    const CREATE_DEFAULT_USER_UID_NOT_EMPTY = 114001;

    /**
     * @Message("创建默认用户姓名不能为空")
     */
    const CREATE_DEFAULT_USER_USERNAME_NOT_EMPTY = 114002;

    /**
     * @Message("创建默认用户失败")
     */
    const CREATE_DEFAULT_USER_FAILED = 114003;

    /********** 通用错误提示 **********/

    /**
     * @Message("密码错误，密码格式需要包含数字和字母组合")
     */
    const MANAGE_USER_INDEX_PASSWORD_TYPE_INVALID = 115000;

    /**
     * @Message("密码长度不能少于8个字符")
     */
    const MANAGE_USER_INDEX_PASSWORD_LENGTH_INVALID = 115001;

    /**
     * @Message("请传入正确的联系方式")
     */
    const MANAGE_USER_INDEX_CONTACT_INVALID = 115002;

    /**
     * @Message("账号状态异常!")
     */
    const MANAGE_ACCOUNT_ISNOT_ABNORMAL = 115003;

    /**
     * @Message("帐号错误: 您的账号已绑定过系统操作员，请联系管理员授权核销权限")
     */
    const MANAGE_ACCOUNT_USER_ERROR = 115004;

}