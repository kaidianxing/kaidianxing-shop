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

namespace shopstar\exceptions\notice;

use shopstar\bases\exception\BaseException;

/**
 * Class NoticeExceptions
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\notice
 */
class NoticeException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_GET_WECHAT_NOTICE_PARAMS_ERROR = 340100;
    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_PARAMS_ERROR = 340101;
    /**
     * @Message("通知人数不能大于三人")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_PEOPLE_NUMBER_ERROR = 340102;
    /**
     * @Message("缺少默认模板")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_LACK_DEFAULT_TEMPLATE_ERROR = 340103;
    /**
     * @Message("添加到微信消息通知模板错误")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_ADD_WECHAT_TEMPLATE_ERROR = 340104;
    /**
     * @Message("请选择消息模板")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_LACK_TEMPLATE_ERROR = 340105;

    /**
     * @Message("设置失败")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_ERROR = 340106;

    /**
     * @Message("微信模板参数错误")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_WECHAT_TEMPLATE_CODE_ERROR = 340107;

    /**
     * @Message("删除失败")
     */
    public const MANAGE_INDEX_WECHAT_NOTICE_DELETE_ERROR = 340108;

    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_GET_WXAPP_NOTICE_PARAMS_ERROR = 340200;

    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_WXAPP_NOTICE_PARAMS_ERROR = 340201;

    /**
     * @Message("没有此场景的小程序订阅消息")
     */
    public const MANAGE_INDEX_WXAPP_NOTICE_LACK_DEFAULT_TEMPLATE_ERROR = 340202;

    /**
     * @Message("添加到小程序消息通知模板错误")
     */
    public const MANAGE_INDEX_WXAPP_NOTICE_ADD_TEMPLATE_ERROR = 340203;

    /**
     * @Message("添加失败")
     */
    public const MANAGE_INDEX_WXAPP_NOTICE_ERROR = 340204;

    /**
     * @Message("删除失败")
     */
    public const MANAGE_INDEX_WXAPP_NOTICE_DELETE_ERROR = 340205;

    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_GET_SMS_NOTICE_PARAMS_ERROR = 340000;

    /**
     * @Message("参数错误")
     */
    public const MANAGE_INDEX_SMS_NOTICE_PARAMS_ERROR = 340001;

    //签名
    /**
     * @Message("参数错误")
     */
    const MANAGE_SMS_SIGNATURE_ENABLED_PARAMS_ERROR = 342001;

    /**
     * @Message("请填写内容")
     */
    const MANAGE_SMS_SIGNATURE_APPLY_PARAMS_ERROR = 342002;
}
