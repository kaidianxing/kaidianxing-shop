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


namespace shopstar\exceptions\wechat;

use shopstar\bases\exception\BaseException;

/**
 * 公众号异常类 1471
 * Class WechatException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\wechat
 */
class WechatException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR = 147100;

    /**
     * @Message("请选择正确的文件")
     */
    const FILE_FORMAT_ERROR = 147101;

    /**
     * @Message("文件上传失败")
     */
    const FILE_UPLOAD_ERROR = 147102;

    /**
     * @Message("保存失败")
     */
    const SAVE_FAILURE_ERROR = 147103;

    /**
     * @Message("删除失败")
     */
    const DELETE_FAILURE_ERROR = 147104;

    /**
     * @Message("关键词重复")
     */
    const KEYWORD_REPEAT = 147105;

    /**
     * @Message("更新失败")
     */
    const UPDATE_FAILURE_ERROR = 147106;

    /**
     * @Message("数据不存在")
     */
    const DATA_NOT_EXISTS_ERROR = 147107;

    /**
     * @Message("图片上传失败")
     */
    const IMAGES_UPLOAD_ERROR = 147108;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_FANS_BLACK_PARAMS_ERROR = 147109;

    /**
     * @Message("添加错误")
     */
    const WECHAT_FANS_TAG_ADD_WECHAT_ERROR = 147110;

    /**
     * @Message("添加失败")
     */
    const WECHAT_FANS_TAG_ADD_ERROR = 147111;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_FANS_TAG_ADD_PARAMS_ERROR = 147112;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_FANS_TAG_DELETE_PARAMS_ERROR = 147113;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_FANS_TAG_DELETE_WECHAT_ERROR = 147114;

    /**
     * @Message("标签未找到")
     */
    const WECHAT_FANS_TAG_SAVE_TAG_NOT_FOUND_ERROR = 147115;

    /**
     * @Message("微信通讯失败")
     */
    const WECHAT_FANS_TAG_SAVE_TAG_EDIT_WECHAT_ERROR = 147116;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_MENU_ENABLE_PARAMS_ERROR = 147117;

    /**
     * @Message("菜单未找到")
     */
    const WECHAT_MENU_ENABLE_NOT_FOUND_ERROR = 147118;

    /**
     * @Message("标签名称重复")
     */
    const WECHAT_FANS_TAG_ADD_NAME_EXIST_ERROR = 147119;

    /**
     * @Message("上传失败")
     */
    const WECHAT_FANS_MEDIA_UPLOAD_ERROR = 147120;

    /**
     * @Message("缺少参数")
     */
    const WECHAT_MEDIA_DELETE_PARAMS_ERROR = 147121;
}
