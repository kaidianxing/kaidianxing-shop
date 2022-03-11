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

class ChannelException extends BaseException
{
    /**
     * @Message("渠道未开启")
     */
    const SHOP_CHANNEL_NOT_OPEN = 350000;


    /**
     * @Message("手机浏览器H5渠道未开启")
     * @MessageWithCode("true")
     */
    const SHOP_CHANNEL_H5_NOT_OPEN = 350001;


    /**
     * @Message("小程序维护已开启")
     * @MessageWithCode("true")
     */
    const SHOP_CHANNEL_WXAPP_NOT_OPEN = 350002;


}
