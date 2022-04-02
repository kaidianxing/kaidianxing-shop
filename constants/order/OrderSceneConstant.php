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

namespace shopstar\constants\order;

use shopstar\bases\constant\BaseConstant;

/**
 * Class OrderSceneConstant
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\order
 * @method getText($code) string
 */
class OrderSceneConstant extends BaseConstant
{
    /**
     * @Text("小程序直播")
     */
    public const ORDER_SCENE_MINIPROGRAM_BROADCAST = 10;

    /**
     * @Text("视频号直播")
     */
    public const ORDER_SCENE_VIDEO_NUMBER_BROADCAST = 20;
}
