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

namespace shopstar\constants\core;

use shopstar\bases\constant\BaseConstant;

/**
 * 系统附件类型常量
 * Class CoreAttachmentTypeConstant
 * @method getMessage($code) static 获取标题
 * @method getIdentify($code) static 获取标识
 * @package shopstar\constants\core
 * @author 青岛开店星信息技术有限公司
 */
class CoreAttachmentTypeConstant extends BaseConstant
{

    /**
     * @Message("图片")
     * @Identify("image")
     */
    public const TYPE_IMAGE = 10;

    /**
     * @Message("视频")
     * @Identify("video")
     */
    public const TYPE_VIDEO = 20;

    /**
     * @Message("音频")
     * @Identify("audio")
     */
    public const TYPE_AUDIO = 30;

}