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
 * Class BroadcastRoomStatusConstant
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\broadcast
 */
class BroadcastRoomStatusConstant extends BaseConstant
{
    /**
     * @Text("直播中")
     */
    const BROADCAST_ROOM_STATUS_UNDERWAY = 101;

    /**
     * @Text("未开始")
     */
    const BROADCAST_ROOM_STATUS_NOTSTARTED = 102;

    /**
     * @Text("已结束")
     */
    const BROADCAST_ROOM_STATUS_END = 103;

    /**
     * @Text("禁播")
     */
    const BROADCAST_ROOM_STATUS_NO = 104;

    /**
     * @Text("暂停")
     */
    const BROADCAST_ROOM_STATUS_SUSPEND = 105;

    /**
     * @Text("异常")
     */
    const BROADCAST_ROOM_STATUS_ABNORMAL = 106;

    /**
     * @Text("已过期")
     */
    const BROADCAST_ROOM_STATUS_PAST_DUE = 107;
}
