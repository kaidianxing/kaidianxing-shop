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

namespace shopstar\constants;


use shopstar\bases\constant\BaseConstant;

/**
 * 客户端类型
 * Class ClientTypeConst
 * @package shopstar\constants
 * @method getIdentify($code) static 获取标识
 * @method getAppIdentify($code) static 获取应用标识
 * @method getText($code) static 获取文字
 */
class ClientTypeConstant extends BaseConstant
{

    /**
     * 注意：
     * H5 1X
     * 微信平台 2X
     * 字节跳动 3X
     */

    /**
     * @Text("H5")
     * @Identify("h5")
     * @AppIdentify("wap")
     */
    public const CLIENT_H5 = 10;

    /**
     * @Text("微信公众号")
     * @Identify("wechat")
     * @AppIdentify("wechat")
     */
    public const CLIENT_WECHAT = 20;

    /**
     * @Text("微信小程序")
     * @Identify("wxapp")
     * @AppIdentify("wxapp")
     */
    public const CLIENT_WXAPP = 21;

    /**
     * @Text("头条小程序")
     * @Identify("byte_dance")
     * @AppIdentify("toutiao")
     */
    public const CLIENT_BYTE_DANCE_TOUTIAO = 30;

    /**
     * @Text("抖音小程序")
     * @Identify("byte_dance")
     * @AppIdentify("douyin")
     */
    public const CLIENT_BYTE_DANCE_DOUYIN = 31;

    /**
     * @Text("头条极速版小程序")
     * @Identify("byte_dance")
     * @AppIdentify("douyin")
     */
    public const CLIENT_BYTE_DANCE_TOUTIAO_LITE = 32;

    /**
     * @Text("商家端PC")
     * @Identify("manage_pc")
     */
    public const MANAGE_PC = 50;

    /**
     * @Text("店铺助手")
     * @Identify("assistant")
     */
    public const MANAGE_SHOP_ASSISTANT = 51;

    /**
     * @Text("管理端")
     * @Identify("admin")
     */
    public const ADMIN_PC = 60;

}
