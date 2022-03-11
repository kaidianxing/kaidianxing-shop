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

namespace shopstar\exceptions\member;

use shopstar\bases\exception\BaseException;

/**
 * 用户异常类
 * Class MemberException
 * @package shopstar\exceptions\member
 */
class MemberException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 21 会员相关
     * 11 列表业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const CHANGE_LEVEL_PARAM_ERROR = 211101;

    /**
     * @Message("修改等级失败")
     */
    const CHANGE_LEVEL_FAIL = 211102;

    /**
     * @Message("参数错误")
     */
    const CHANGE_GROUP_PARAM_ERROR = 211103;

    /**
     * @Message("修改标签组失败")
     */
    const CHANGE_GROUP_FAIL = 211104;

    /**
     * @Message("参数错误")
     */
    const SET_BLACK_PARAM_ERROR = 211105;

    /**
     * @Message("修改黑名单失败")
     */
    const CHANGE_BLACK_FAIL = 211106;

    /**
     * @Message("变化类型不能为空")
     */
    const RECHARGE_CHANGE_TYPE_NOT_EMPTY = 211107;

    /**
     * @Message("充值类型不能为空")
     */
    const RECHARGE_TYPE_NOT_EMPTY = 211108;

    /**
     * @Message("充值金额不能为空")
     */
    const RECHARGE_NUM_NOT_EMPTY = 211109;

    /**
     * @Message("充值失败")
     */
    const UPDATE_CREDIT_FAIL = 211110;

    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAM_ERROR = 211111;

    /**
     * @Message("用户不存在")
     */
    const DETAIL_MEMBER_NOT_EXISTS = 211112;

    /**
     * @Message("参数错误")
     */
    const DETAIL_CHANGE_PASSWORD_PARAM_ERROR = 211113;

    /**
     * @Message("修改密码失败")
     */
    const DETAIL_CHANGE_PASSWORD_FAIL = 211114;

    /**
     * @Message("参数错误")
     */
    const DETAIL_CHANGE_MOBILE_PARAM_ERROR = 211115;

    /**
     * @Message("修改手机失败")
     */
    const DETAIL_CHANGE_MOBILE_FAIL = 211116;

    /**
     * @Message("修改备注失败")
     */
    const DETAIL_CHANGE_MOBILE_REMARK = 211120;

    /**
     * @Message("参数错误")
     */
    const DETAIL_DELETE_MEMBER_PARAM_ERROR = 211117;

    /**
     * @Message("管理员密码不正确")
     */
    const DELETE_MEMBER_MANAGE_PASSWORD_ERROR = 211118;

    /**
     * @Message("删除会员失败")
     */
    const DETAIL_DELETE_MEMBER_ERROR = 211119;
    
    /**
     * @Message("导出失败")
     */
    const MEMBER_EXPORT_FAIL = 211126;

    /**
     * @Message("备注不能为空")
     */
    const RECHARGE_REMARK_NOT_EMPTY = 211127;

    /**
     * @Message("密码不能为空")
     */
    const MEMBER_MANAGE_PASSWORD_NOT_EMPTY = 211128;

    /**
     * @Message("手机号错误")
     */
    const MEMBER_CHANGE_MOBILE_ERROR = 211129;

    /**
     * @Message("会员id不能为空")
     */
    const MEMBER_ID_ERROR = 211130;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 21 会员相关
     * 12 列表客户端端
     * 01 错误码
     */
    
    /**
     * @Message("地址保存失败")
     */
    const MEMBER_ADDRESS_CREATE_FAIL = 211204;
    
    /**
     * @Message("地址保存失败")
     */
    const MEMBER_ADDRESS_SAVE_FAIL = 211205;
    
    /**
     * @Message("参数错误")
     */
    const MEMBER_ADDRESS_SAVE_PARAM_ERROR = 211206;

    /**
     * @Message("参数错误")
     */
    const MEMBER_ADDRESS_DELETE_PARAM_ERROR = 211207;

    /**
     * @Message("地址删除失败")
     */
    const MEMBER_ADDRESS_DELETE_FAIL = 211208;

    /**
     * @Message("参数错误")
     */
    const MEMBER_ADDRESS_SET_DEFAULT_PARAM_ERROR = 211209;
    
    /**
     * @Message("默认地址设置失败")
     */
    const MEMBER_ADDRESS_SET_DEFAULT_FAIL = 211210;

    /**
     * @Message("地址不存在")
     */
    const MEMBER_ADDRESS_DETAIL_NOT_EXISTS = 211211;

    /**
     * @Message("参数错误")
     */
    const MEMBER_ADDRESS_DETAIL_PARAMS_ERROR = 211212;

    /**
     * @Message("操作失败")
     */
    const MEMBER_FAVORITE_CHANGE_FAIL = 211213;

    /**
     * @Message("参数错误")
     */
    const FAVORITE_DELETE_PARAM_ERROR = 211214;

    
    /**
     * @Message("您暂时没有访问权限，请联系管理员")
     */
    const MEMBER_WAP_LOGIN_IS_BLACK_ERROR = 211216;
    
    /**
     * @Message("您暂时没有访问权限，请联系管理员")
     */
    const MEMBER_WECHAT_LOGIN_IS_BLACK_ERROR = 211217;
    
    /**
     * @Message("您暂时没有访问权限，请联系管理员")
     */
    const MEMBER_WXAPP_LOGIN_IS_BLACK_ERROR = 211218;
    
    /**
     * @Message("原始密码错误")
     */
    const MEMBER_CHANGE_PASSWORD_OLD_PASSWORD_ERROR = 211219;
    
    /**
     * @Message("授权组件不存在")
     */
    const AUTHORIZATION_AUTH_COMPONENTS_NOT_FOUND = 211220;
    
    /**
     * @Message("登录组件不存在")
     */
    const AUTHORIZATION_LOGIN_COMPONENTS_NOT_FOUND = 211221;
    
    /**
     * @Message("请绑定手机号")
     */
    const MEMBER_MOBILE_NOT_EXIST = 211222;
    
    /**
     * @Message("请登录")
     */
    const MEMBER_NOT_LOGIN = 211223;
    
    /**
     * @Message("参数错误")
     */
    const MEMBER_CHANGE_PASSWORD_PARAMS_ERROR = 211224;
    
    /**
     * @Message("修改密码失败")
     */
    const MEMBER_CHANGE_PASSWORD_ERROR = 211225;
    
    /**
     * @Message("验证码错误")
     */
    const MEMBER_CHECK_SMS_CODE_ERROR = 211226;
    
    /**
     * @Message("参数错误")
     */
    const MEMBER_MERGE_PARAMS_ERROR = 211227;
    
    /**
     * @Message("废弃会员保存失败")
     */
    const MEMBER_MERGE_DISCARD_SAVE_ERROR = 211228;
    
    /**
     * @Message("迁移废弃会员的账号附属信息失败")
     */
    const MEMBER_MERGE_CHANGE_SUBJECT_ERROR = 211229;
    
    
    /**
     * @Message("微信小程序主体账号创建失败")
     */
    const MEMBER_WXAPP_SUBJECT_ACCOUNT_CREATE_ERROR = 211230;
    
    /**
     * @Message("微信小程序附属账号创建失败")
     */
    const MEMBER_WXAPP_ACCOUNT_CREATE_ERROR = 211231;
    
    /**
     * @Message("用户不存在")
     */
    const MEMBER_INDEX_CHANGE_USER_INFO_USER_NOT_EXIST_ERROR = 211232;
    
    /**
     * @Message("修改资料失败")
     */
    const MEMBER_INDEX_CHANGE_USER_INFO_ERROR = 211233;
    
    /**
     * @Message("手机号已存在")
     */
    const MEMBER_INDEX_CHECK_SMS_CODE_MOBILE_EXIST_ERROR = 211234;
    
    /**
     * @Message("手机号不存在")
     */
    const MEMBER_INDEX_FORGET_PASSWORD_MOBILE_EXIST_ERROR = 211235;
    
    /**
     * @Message("会员错误")
     */
    const MEMBER_BIND_MOBILE_MEMBER_ERROR = 211236;
    
    /**
     * @Message("用户不存在")
     */
    const MEMBER_DELETED = 211237;
    
    /**
     * @Message("店铺已打烊")
     */
    const MEMBER_BLACK = 211238;
    
    /**
     * @Message("店铺已打烊")
     */
    const WXAPP_MAINTAIN_OPEN = 211239;
    
    /**
     * @Message("用户已被删除，无法操作")
     */
    const MEMBER_DELETED_NO_GET_MEMBER_COUPON = 211240;
    
    /**
     * @Message("用户已被删除，无法操作")
     */
    const MEMBER_DELETED_NO_RECHARGE = 211241;
    
    /**
     * @Message("用户已被删除，无法操作")
     */
    const MEMBER_DELETED_NO_CHANGE_LEVEL = 211242;
    
    /**
     * @Message("用户已被删除，无法操作")
     */
    const MEMBER_DELETED_NO_CHANGE_PASSWORD = 211243;
    
    /**
     * @Message("用户已被删除，无法操作")
     */
    const MEMBER_DELETED_NO_CHANGE_GROUP = 211244;
    
    /**
     * @Message("用户不存在")
     */
    const WAP_LOGIN_MEMBER_NOT_EXISTS = 211245;
    
    /**
     * @Message("用户不存在")
     */
    const LOGIN_BY_CODE_MEMBER_NOT_EXISTS = 211246;
    
    /**
     * @Message("用户不存在")
     */
    const USER_INFO_MEMBER_NOT_EXISTS = 211247;
    
    /**
     * @Message("手机号格式不正确")
     */
    const SEND_SMS_MOBILE_ERROR = 211250;
    
    /**
     * @Message("手机号格式不正确")
     */
    const LOGIN_BY_CODE_MOBILE_ERROR = 211251;
    
    /**
     * @Message("修改密码失败")
     */
    const FORGET_PASSWORD_FAIL = 211252;
    
    /**
     * @Message("注册失败")
     */
    const CHANGE_REGISTER_FAIL = 211253;
    
    /**
     * @Message("用户日志写入失败")
     */
    const MEMBER_LOG_WRITE_FAIL = 211254;
    
    /**
     * @Message("余额扣除失败")
     */
    const MEMBER_DEDUCTION_FAILED = 211255;
    
    /**
     * @Message("修改订单状态失败")
     */
    const CHANGE_ORDER_STATUS_FAILED = 211256;
    
    /**
     * @Message("积分排行关闭")
     */
    const CREDIT_RANK_CLOSED = 211257;
    
    /**
     * @Message("用户余额不足")
     */
    const MEMBER_BALANCE_IS_NOT_ENOUGH = 211258;
    
    /**
     * @Message("消费排行关闭")
     */
    const MONEY_RANK_CLOSED = 211259;
    
    /**
     * @Message("合并会员失败")
     */
    const MERGE_MEMBER_ERROR = 211260;
    
    /**
     * @Message("绑定失败")
     */
    const BIND_MOBILE_ERROR = 211261;
    
    /**
     * @Message("两次密码不一致")
     */
    const PASSWORD_CHECK_ERROR = 211262;
    
    /**
     * @Message("用户密码不正确")
     */
    const PASSWORD_ERROR = 211263;
    
    /**
     * @Message("修改手机失败")
     */
    const CHANGE_MOBILE_FAIL = 211264;
    
    /**
     * @Message("手机号已存在")
     */
    const BIND_MOBILE_MOBILE_EXISTS_ERROR = 211265;
    
    /**
     * @Message("手机号已存在")
     */
    const CHANGE_BIND_MOBILE_MOBILE_EXISTS_ERROR = 211266;
    
    /**
     * @Message("手机号已存在")
     */
    const REGISTER_MOBILE_EXISTS_ERROR = 211267;
    
    /**
     * @Message("用户不存在")
     */
    const MERGE_MEMBER_NOT_EXISTS = 211268;
    
    /**
     * @Message("用户不存在")
     */
    const MERGE_MEMBER_SELECT_NOT_EXISTS = 211269;
    
    /**
     * @Message("用户不存在")
     */
    const BIND_MOBILE_MEMBER_NOT_EXISTS = 211270;
    
    /**
     * @Message("用户不存在")
     */
    const CHANGE_BIND_MOBILE_MEMBER_NOT_EXISTS = 211271;
    
    /**
     * @Message("创建会员失败")
     */
    const MEMBER_WECHAT_CREATE_MEMBER_ERROR = 211272;
    
    /**
     * @Message("创建会员失败")
     */
    const MEMBER_WECHAT_CREATE_WECHAT_MEMBER_ERROR = 211273;
    
    /**
     * @Message("短信验证码无效")
     */
    const LOGIN_BY_CODE_SMSCODE_ERROR = 211274;
    
    /**
     * @Message("短信验证码无效")
     */
    const FORGET_PASSWORD_SMSCODE_ERROR = 211275;
    
    /**
     * @Message("短信验证码无效")
     */
    const BIND_MOBILE_SMSCODE_ERROR = 211276;
    
    /**
     * @Message("短信验证码无效")
     */
    const CHANGE_BIND_MOBILE_SMSCODE_ERROR = 211277;
    
    /**
     * @Message("短信验证码无效")
     */
    const CHANGE_BIND_MOBILE_NOW_SMSCODE_ERROR = 211278;
    
    /**
     * @Message("短信验证码无效")
     */
    const REGISTER_SMSCODE_ERROR = 211279;
    
    /**
     * @Message("图形验证码无效")
     */
    const LOGIN_BY_CODE_VERIFY_CODE_ERROR = 211280;
    
    /**
     * @Message("图形验证码无效")
     */
    const FORGET_PASSWORD_VERIFY_CODE_ERROR = 211281;
    
    /**
     * @Message("图形验证码无效")
     */
    const BIND_MOBILE_VERIFY_CODE_ERROR = 211282;
    
    /**
     * @Message("图形验证码无效")
     */
    const CHANGE_BIND_MOBILE_VERIFY_CODE_ERROR = 211283;
    
    /**
     * @Message("图形验证码无效")
     */
    const REGISTER_VERIFY_CODE_ERROR = 211284;
    
    /**
     * @Message("图形验证码无效")
     */
    const SEND_SMS_VERIFY_CODE_ERROR = 211285;
    
    /**
     * @Message("您暂时没有访问权限，请联系管理员")
     */
    const MEMBER_BYTE_DANCE_LOGIN_IS_BLACK_ERROR = 211286;
    
    /**
     * @Message("字节跳动小程序主体账号创建失败")
     */
    const MEMBER_BYTE_DANCE_SUBJECT_ACCOUNT_CREATE_ERROR = 211287;
    
    /**
     * @Message("字节跳动小程序附属账号创建失败")
     */
    const MEMBER_BYTE_DANCE_ACCOUNT_CREATE_ERROR = 211288;

    /**
     * @Message("已是核销员无需绑定")
     */
    const ALREADY_VALIDATOR_ERROR = 211289;

    /**
     * @Message("当前手机号已是操作员账号请更换手机号")
     */
    const MOBILE_ALREADY_MANAGER_ERROR = 211290;

    /**
     * @Message("二维码或链接已失效")
     */
    const QRCODE_URL_INVALI_ERROR = 211291;

    /**
     * @Message("已绑定手机号，不是核销员")
     */
    const BIND_MOBILE_NOT_INVALI_ERROR = 211292;
    
    /**
     * @Message("帐号错误")
     */
    const BIND_USER_ERROR = 211293;

    /**
     * @Message("会员不存在")
     */
    const MEMBER_IS_NO_EXISTS = 211294;

    /**
     * @Message("授权组件授权失败")
     * @MessageWithCode("true")
     */
    const AUTHORIZATION_AUTH_IS_DEFEATED = 211295;
    /**
     * @Message("授权组件登陆失败")
     * @MessageWithCode("true")
     */
    const AUTHORIZATION_LOGIN_IS_DEFEATED = 211296;

    /**
     * @Message("open_id不存在")
     */
    const WXAPP_AUTH_OPEN_ID_EMPTY = 211297;
    
    
    /*************客户端异常结束*************/

    // 会员PC微信授权登录
    /**
     * @Message("PC授权登录失败-参数错误")
     */
    public const MEMBER_WECHAT_PC_CHECK_PARAMS_INVALID = 211500;

    /**
     * @Message("会员信息创建失败")
     */
    public const MEMBER_WECHAT_PC_CHECK_CREATE_FAIL = 211501;

    /**
     * @Message("会员信息创建失败")
     */
    public const MEMBER_WECHAT_PC_CHECK_CREATE_CHANNEL_FAIL = 211502;

    /**
     * @Message("当前会员无法登录")
     */
    public const MEMBER_WECHAT_PC_CHECK_IN_BLACK_LIST = 211503;


}
