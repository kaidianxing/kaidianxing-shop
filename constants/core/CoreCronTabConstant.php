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

namespace shopstar\constants\core;

use shopstar\bases\constant\BaseConstant;

/**
 * 系统定时任务常量
 * Class CoreCrontabConstant
 * @package shopstar\constants\core
 * @author 青岛开店星信息技术有限公司
 */
class CoreCronTabConstant extends BaseConstant
{
    /**
     * @Message("数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_STATISTICS = 'statistics';

    /**
     * @Message("小程序直播间同步状态")
     * @Tips("执行自动同步小程序直播间状态")
     */
    public const TASK_PLUGIN_BROADCAST_ROOM_SYNC_STATUS = 'plugin_broadcast_room_sync_status';

    /**
     * @Message("小程序直播同步商品状态")
     * @Tips("执行自动同步小程序直播商品状态")
     */
    public const TASK_PLUGIN_BROADCAST_GOODS_SYNC_STATUS = 'plugin_broadcast_goods_sync_status';

    /**
     * @Message("小程序直播数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_BROADCAST_STATISTICS = 'plugin_broadcast_statistics';

    /**
     * @Message("商品预售数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_PRESELL_STATISTICS = 'plugin_presell_statistics';

    /**
     * @Message("核销数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_VERIFY_STATISTICS = 'plugin_verify_statistics';


    /**
     * @Message("拼团数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_GROUPS_STATISTICS = 'plugin_groups_statistics';

    /**
     * @Message("拼团返利数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_GROUPS_REBATE_STATISTICS = 'plugin_groups_rebate_statistics';

    /**
     * @Message("礼品卡数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_GIFT_CARD_STATISTICS = 'plugin_gift_card_statistics';

    /**
     * @Message("满减折数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_FULL_REDUCE_STATISTICS = 'plugin_full_reduce_statistics';

    /**
     * @Message("积分商城数据统计")
     * @Tips("每天统计前一天的数据")
     */
    public const TASK_PLUGIN_CREDIT_SHOP_STATISTICS = 'plugin_credit_shop_statistics';


}
