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

namespace shopstar\admin\seckill;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\activity\MarketingStatisticsModel;
use shopstar\models\activity\ShopMarketingModel;

/**
 * 数据
 * Class StatisticsController
 * @package apps\seckill\manage
 */
class StatisticsController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'get-merchant-statistics',
            'view',
            'activity',
            'get-update-time',
            'index'
        ]
    ];

    /**
     * 数据
     * 后续活动如果统计数据跟秒杀一样的话 把这个方法改为公共方法
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data['count'] = MarketingStatisticsModel::find()
            ->select([
                'sum(pay_price_sum) pay_price_sum',
                'sum(order_count) order_count',
                'sum(goods_pv_count) goods_pv_count',
                'sum(sales_goods_total) sales_goods_total',
                'sum(wechat_order_price_sum) wechat_order_price_sum',
                'sum(wxapp_order_price_sum) wxapp_order_price_sum',
                'sum(h5_order_price_sum) h5_order_price_sum',
                'sum(byte_dance_order_price_sum) byte_dance_order_price_sum',
            ])
            ->where(['activity_type' => 'seckill'])
            ->first();
        // 累计不为空
        if ($data['count']['pay_price_sum'] != 0) {
            $data['channel'][] = [
                'name' => '微信公众号',
                'value' => bcdiv($data['count']['wechat_order_price_sum'], $data['count']['pay_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => '微信小程序',
                'value' => bcdiv($data['count']['wxapp_order_price_sum'], $data['count']['pay_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => 'H5',
                'value' => bcdiv($data['count']['h5_order_price_sum'], $data['count']['pay_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => '头条/抖音小程序',
                'value' => bcdiv($data['count']['byte_dance_order_price_sum'], $data['count']['pay_price_sum'], 4),
            ];
        }

        return $this->result(['data' => $data]);
    }

    /**
     * 活动曝光量
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionView()
    {
        $startDate = RequestHelper::get('start_date', date('Y-m-d', strtotime("-10 days")));
        $endDate = RequestHelper::get('end_date', date('Y-m-d', strtotime("-1 days")));
        $activityId = RequestHelper::get('activity_id', 0);

        $lineChartData = MarketingStatisticsModel::getView($startDate, $endDate, $activityId, 'seckill');

        return $this->result(['data' => $lineChartData]);
    }

    /**
     * 活动数据
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActivity()
    {
        $params = [
            'select' => [
                'activity.id',
                'activity.title',
                'activity.start_time',
                'activity.end_time',
                'activity.stop_time',
                'if(activity.stop_time=0, 1, 2) as level',
                'sum(order_count) order_count',
                'sum(pay_price_sum) pay_price_sum',
                'sum(refund_price_sum) refund_price_sum',
                'sum(sales_goods_total) sales_goods_total',
                'sum(pay_member_count) pay_member_count',
            ],
            'alias' => 'activity',
            'leftJoin' => [MarketingStatisticsModel::tableName() . ' statistics', 'statistics.activity_id=activity.id and statistics.activity_type=\'seckill\''],
            'where' => [
                'and',
                ['activity.is_deleted' => 0],
                ['<', 'activity.start_time', DateTimeHelper::now(false)],
                ['activity.type' => 'seckill'],
            ],
            'groupBy' => 'activity.id',
            'orderBy' => [
                'level' => SORT_ASC,
                'activity.stop_time' => SORT_DESC,
                'activity.status' => SORT_DESC,
                'activity.id' => SORT_DESC,
            ]
        ];

        $list = ShopMarketingModel::getColl($params);

        return $this->result($list);

    }

    /**
     * 获取统计更新时间
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetUpdateTime()
    {
        $statistics = MarketingStatisticsModel::find()
            ->select('created_at')
            ->where(['activity_type' => 'seckill'])
            ->orderBy(['date' => SORT_DESC])
            ->first();
        if (empty($statistics)) {
            $updateTime = '0000-00-00 00:00:00';
        } else {
            $updateTime = $statistics['created_at'];
        }
        return $this->result(['time' => $updateTime]);
    }

}