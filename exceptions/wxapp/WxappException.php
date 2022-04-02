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

namespace shopstar\exceptions\wxapp;

use shopstar\bases\exception\BaseException;

/**
 * 小程序异常类 147
 * Class WxappException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\wxapp
 */
class WxappException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const CHANNEL_MANAGE_WXAPP_SET_PARAMS_ERROR = 147000;

    /**
     * @Message("参数错误")
     */
    const CHANNEL_MANAGE_WXAPP_SET_NOTICE_PARAMS_ERROR = 147001;

    /**
     * @Message("添加模板错误")
     */
    const CHANNEL_MANAGE_WXAPP_SET_NOTICE_ADD_TEMPLATE_ERROR = 147010;

    /**
     * @Message("删除模板错误")
     */
    const CHANNEL_MANAGE_WXAPP_SET_NOTICE_DELETE_TEMPLATE_ERROR = 147011;

    /**
     * @Message("缺少模板id")
     */
    const CHANNEL_MANAGE_WXAPP_SET_NOTICE_EMPTY_TEMPLATE_ID_ERROR = 147012;

    /**
     * @Message("缺少uuid")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_GET_LOGIN_QRCODE_STATUS_EMPTY_UUID_ERROR = 147020;

    /**
     * @Message("请求远端登录状态错误")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_GET_LOGIN_QRCODE_STATUS_FAR_END_ERROR = 147021;

    /**
     * @Message("请求远端登录ticket错误")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_GET_LOGIN_QRCODE_STATUS_TICKET_FAR_END_ERROR = 147022;

    /**
     * @Message("登录二维码请求超时")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_GET_LOGIN_QRCODE_STATUS_TICKET_TIMEOUT_ERROR = 147023;

    /**
     * @Message("请扫码登录微信开发者")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_WECHAT_CODER_LOGIN_ERROR = 147030;

    /**
     * @Message("请填写小程序版本号或小程序简介")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_PARAMS_ERROR = 147031;

    /**
     * @Message("上传出错，请重试")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_ERROR = 147032;

    /**
     * @Message("请先初始化小程序")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_NOT_RUN_INIT_ERROR = 147034;


    /**
     * @Message("参数错误")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_GET_UPLOAD_STATUS_PARAMS_ERROR = 147040;

    /**
     * @Message("查询上传状态出错")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_GET_UPLOAD_STATUS_ERROR = 147041;

    /**
     * @Message("上传记录保存失败")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_GET_UPLOAD_STATUS_ADD_LOG_ERROR = 147042;

    /**
     * @Message("云端通讯失败")
     */
    const CHANNEL_MANAGE_WXAPP_INDEX_UPLOAD_INIT_CLOUDS_ERROR = 147050;

    /**
     * @Message("参数错误")
     */
    const CHANNEL_MANAGE_WXAPP_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR = 147060;

    /**
     * @Message("上传失败")
     */
    const CHANNEL_MANAGE_WXAPP_MEDIA_UPLOAD_IMAGE_ERROR = 147061;
}
