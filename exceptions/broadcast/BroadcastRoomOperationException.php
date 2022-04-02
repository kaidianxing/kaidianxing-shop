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
 * 4402
 * Class BroadcastRoomOperationException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\broadcast
 */
class BroadcastRoomOperationException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_ROOM_OPERATION_GOODS_LIST_PARAMS_ERROR = 440200;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_ROOM_OPERATION_ADD_ROOM_GOODS_PARAMS_ERROR = 440205;

    /**
     * @Message("添加直播间商品失败")
     */
    const BROADCAST_MANAGE_ROOM_OPERATION_ADD_ROOM_GOODS_ERROR = 440206;

}
