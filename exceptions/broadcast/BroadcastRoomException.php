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


namespace shopstar\exceptions\broadcast;
use shopstar\bases\exception\BaseException;

/**
 * 直播间错误码4401
 * Class RoomException
 * @author 青岛开店星信息技术有限公司
 */
class BroadcastRoomException extends BaseException
{
    /**
     * @Message("添加失败")
     */
    const BROADCAST_MANAGE_ROOM_ADD_ERROR = 440100;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_CLIENT_ROOM_GET_GOODS_ROOM_PARAMS_ERROR = 440101;

    /**
     * @Message("获取直播间失败")
     */
    const BROADCAST_CLIENT_ROOM_GET_GOODS_ROOM_ERROR = 440102;

    /**
     * @Message("直播间未找到")
     */
    const BROADCAST_MANAGE_ROOM_DETAILS_ROOM_NOT_FOUND_ERROR = 440120;

    /**
     * @Message("直播间未找到")
     */
    const BROADCAST_MANAGE_ROOM_DETAILS_GOODS_ROOM_NOT_FOUND_ERROR = 440121;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_ROOM_HIDE_PARAMS_ERROR = 440122;
}
