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

namespace shopstar\exceptions\byteDance;

use shopstar\bases\exception\BaseException;

/**
 * 字节跳动异常
 * Class ByteDanceException
 * @package shopstar\exceptions\byteDance
 */
class ByteDanceException extends BaseException
{
    /**
     * @Message("登录参数错误")
     */
    const EMAIL_LOGIN_PARAMS_ERROR = 148000;

    /**
     * @Message("参数错误")
     */
    const SEND_SMS_PARAMS_ERROR = 148001;

    /**
     * @Message("参数错误")
     */
    const SMS_LOGIN_PARAMS_ERROR = 148002;

    /**
     * @Message("获取验证码失败")
     */
    const GET_CAPTCHA_FAIL = 148003;

    /**
     * @Message("登录失败")
     */
    const SMS_LOGIN_FAIL = 148004;

    /**
     * @Message("发送验证码失败")
     */
    const SEND_SMS_FAIL = 148005;

    /**
     * @Message("登录失败")
     */
    const EMAIL_LOGIN_FAIL = 148006;

    /**
     * @Message("请重新登录")
     */
    const UPLOAD_UN_LOGIN = 148007;

    /**
     * @Message("参数错误")
     */
    const UPLOAD_PARAMS_ERROR = 148008;

    /**
     * @Message("请重新初始化")
     */
    const UPLOAD_REFRESH_PAGE = 148009;

    /**
     * @Message("参数错误")
     */
    const GET_UPLOAD_STATUS_PARAMS_ERROR = 148010;

    /**
     * @Message("查询上传状态出错")
     */
    const GET_UPLOAD_STATUS_ERROR = 148011;

    /**
     * @Message("保存上传记录失败")
     */
    const GET_UPLOAD_STATUS_SAVE_LOG_FAIL = 148012;

    /**
     * @Message("云端通信失败")
     */
    const CLOUD_INIT_ERROR = 148013;

    /**
     * @Message("未登录")
     */
    const GET_LOGIN_STATUS_UN_LOGIN = 148014;

    /**
     * @Message("云端通信失败")
     */
    const UPLOAD_CLOUD_ERROR = 148015;

    /**
     * @Message("版本描述过长")
     */
    const UPLOAD_DESC_TO_LONG = 148016;

    /**
     * @Message("未配置")
     */
    const UPLOAD_GET_CHANNEL_STATUS = 148016;

}