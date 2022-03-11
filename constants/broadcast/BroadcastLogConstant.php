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

namespace shopstar\constants\broadcast;

use shopstar\bases\constant\BaseConstant;

/**
 * Class BroadcastLogConstant
 * @package shopstar\constants\broadcast
 */
class BroadcastLogConstant extends BaseConstant
{
    /**
     * @Text("小程序直播-直播间管理-创建直播间")
     */
    public const BROADCAST_ROOM_ADD = 440300;

    /**
     * @Text("小程序直播-直播间管理-同步直播间")
     */
    public const BROADCAST_SYNC_ROOM = 440301;

    /**
     * @Text("小程序直播-直播间管理-操作")
     */
    public const BROADCAST_ROOM_OPERATION = 440302;

    /**
     * @Text("小程序直播-商品库-提交审核")
     */
    public const BROADCAST_GOODS_TO_EXAMINE_SUBMIT = 440303;
}