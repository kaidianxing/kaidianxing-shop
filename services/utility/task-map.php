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

return [
    // 数据统计
    'statistics' => [
        'class' => ['\shopstar\services\statistics\StatisticsService', 'createDayStatistic'],
        'settings_key' => 'statistics',
    ],

    // 小程序直播数据统计
    'plugin_broadcast_statistics' => [
        'class' => ['\shopstar\models\broadcast\BroadcastStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_broadcast_statistics',
    ],

    // 小程序直播间状态同步
    'plugin_broadcast_room_sync_status' => [
        'class' => ['\shopstar\models\broadcast\BroadcastRoomModel', 'syncStatus'],
        'settings_key' => 'plugin_broadcast_room_sync_status',
    ],

    // 小程序直播商品状态同步
    'plugin_broadcast_goods_sync_status' => [
        'class' => ['\shopstar\models\broadcast\BroadcastGoodsModel', 'syncStatus'],
        'settings_key' => 'plugin_broadcast_goods_sync_status',
    ],

    // 商品预售数据统计
    'plugin_presell_statistics' => [
        'class' => ['\apps\presell\models\PresellStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_presell_statistics',
    ],

    // 秒杀数据统计
    'plugin_seckill_statistics' => [
        'class' => ['\shopstar\models\activity\MarketingStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_seckill_statistics',
        'options' => ['activity_type' => 'seckill'], // 扩展参数
    ],

    // 核销数据统计
    'plugin_verify_statistics' => [
        'class' => ['\apps\verify\models\VerifyStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_verify_statistics',
    ],

    // 拼团数据统计
    'plugin_groups_statistics' => [
        'class' => ['\shopstar\models\activity\MarkdoetingStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_groups_statistics',
        'options' => ['activity_type' => 'groups'], // 扩展参数
    ],

    // 商品预售数据统计
    'plugin_full_reduce_statistics' => [
        'class' => ['\apps\fullReduce\models\FullReduceStatisticsModel', 'createDayStatistic'],
        'settings_key' => 'plugin_full_reduce_statistics',
    ],
    // 积分商城数据统计
    'plugin_credit_shop_statistics' => [
        'class' => ['\shopstar\services\creditShopCreditShopStatisticsService', 'createDayStatistic'],
        'settings_key' => 'plugin_credit_shop_statistics',
    ],
];
