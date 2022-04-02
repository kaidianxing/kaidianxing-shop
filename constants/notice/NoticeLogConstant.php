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

namespace shopstar\constants\notice;

use shopstar\bases\constant\BaseConstant;

/**
 * 消息通知日志
 * Class NoticeLogConstants
 * @package shopstar\constants\notice
 * @author 青岛开店星信息技术有限公司
 */
class NoticeLogConstant extends BaseConstant
{
    /**
     * @Text("消息通知-邮箱设置")
     */
    public const NOTICE_MAILER = 340200;

    /**
     * @Text("消息通知-短信签名-申请自定义短信签名")
     */
    public const NOTICE_SMS_SIGNATURE_APPLY = 340201;

    /**
     * @Text("消息通知-模板设置-短信模板-申请新模板")
     */
    public const NOTICE_TEMPLATE_SMS_ADD = 340202;

    /**
     * @Text("消息通知-模板设置-短信模板-编辑模板")
     */
    public const NOTICE_TEMPLATE_SMS_EDIT = 340203;

    /**
     * @Text("消息通知-模板设置-短信模板-删除模板")
     */
    public const NOTICE_TEMPLATE_SMS_DEL = 340204;

    /**
     * @Text("消息通知-模板设置-微信模板-添加微信模板")
     */
    public const NOTICE_TEMPLATE_WECHAT_ADD = 340205;

    /**
     * @Text("消息通知-模板设置-微信模板-删除模板")
     */
    public const NOTICE_TEMPLATE_WECHAT_DEL = 340206;

    /**
     * @Text("消息通知-模板设置-微信模板-批量删除模板")
     */
    public const NOTICE_TEMPLATE_WECHAT_BATCH_DEL = 340207;
}