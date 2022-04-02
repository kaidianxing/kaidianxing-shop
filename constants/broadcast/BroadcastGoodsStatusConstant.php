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
 * Class BroadcastGoodsStatusConstant
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\broadcast
 */
class BroadcastGoodsStatusConstant extends BaseConstant
{
    /**
     * @Text("未审核")
     */
    const BROADCAST_GOODS_STATUS_NOT_AUDIT = 0;

    /**
     * @Text("审核中")
     */
    const BROADCAST_GOODS_STATUS_UNDER = 1;

    /**
     * @Text("审核通过")
     */
    const BROADCAST_GOODS_STATUS_PASS = 2;

    /**
     * @Text("审核驳回")
     */
    const BROADCAST_GOODS_STATUS_REJECT = 3;
}
