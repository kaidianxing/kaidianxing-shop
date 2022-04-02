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

namespace shopstar\constants\activity;

use shopstar\bases\constant\BaseConstant;

/**
 * 活动
 * Class ActivityConstant
 * @package shopstar\constants\activity
 * @author 青岛开店星信息技术有限公司
 */
class ActivityConstant extends BaseConstant
{
    /**
     * @Text("限购次数 不限制")
     */
    const LIMIT_TYPE_NOT_LIMIT = 0;

    /**
     * @Text("限购次数 活动期内每人最多购买")
     */
    const LIMIT_TYPE_MORE_BUY = 1;

    /**
     * @Text("限购次数 活动期内每人每天最多购买")
     */
    const LIMIT_TYPE_DAY_MORE_BUY = 2;

    /**
     * @Text("活动预热")
     */
    const IS_PREHEAT = 1;

    /**
     * @Text("未开始或进行中")
     */
    const ACTIVITY_STATUS_NORMAL = 0;

    /**
     * @Text("自动停止")
     */
    const ACTIVITY_STATUS_AUTO_STOP = -1;

    /**
     * @Text("手动停止")
     */
    const ACTIVITY_STATUS_MANUAL_STOP = -2;

    /**
     * @Text("限购次数 不限制")
     */
    const ACTIVITY_LIMIT_TYPE_NOT_LIMIT = 0;

    /**
     * @Text("限购次数 活动期内每人最多购买")
     */
    const ACTIVITY_LIMIT_TYPE_MORE_BUY = 1;

    /**
     * @Text("限购次数 活动期内每人最多购买")
     */
    const ACTIVITY_LIMIT_TYPE_DAY_MORE_BUY = 2;


}