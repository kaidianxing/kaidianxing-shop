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
 * 订单活动类型const
 * Class OrderActivityTypeConst
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\constants\order
 */
class OrderActivityTypeConstant extends BaseConstant
{

    /**
     * @Text("普通订单")
     */
    const ACTIVITY_TYPE_NORMAL = 0;

    /**
     * @Text("预售订单")
     */
    const ACTIVITY_TYPE_PRESELL = 1;

    /**
     * @Text("秒杀订单")
     */
    const ACTIVITY_TYPE_SECKILL = 2;

    /**
     * @Text("拼团订单") TODO likexin
     */
    const ACTIVITY_TYPE_GROUPS = 3;

    /**
     * @Text("积分商城订单")
     */
    const ACTIVITY_TYPE_CREDIT_SHOP = 5;

}
