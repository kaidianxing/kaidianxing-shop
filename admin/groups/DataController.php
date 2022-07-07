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

namespace shopstar\admin\groups;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\activity\MarketingStatisticsModel;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\activity\ShopMarketingModel;
use yii\web\Response;

/**
 * 拼团活动数据控制器
 * Class DataController
 * @package shopstar\admin\groups
 * @author likexin
 */
class DataController extends KdxAdminApiController
{

    /**
     * 渠道统计
     * @return Response
     * @author likexin
     */
    public function actionIndex(): Response
    {
        $data['count'] = MarketingStatisticsModel::find()
            ->select([
                'coalesce(sum(pay_price_sum),0) as pay_price_sum',
                'coalesce(sum(order_count),0) as order_count',
                'coalesce(sum(goods_view_count),0) as goods_view_count',
                'coalesce(sum(sales_goods_total),0) as sales_goods_total',
                'coalesce(sum(wechat_order_price_sum),0) as wechat_order_price_sum',
                'coalesce(sum(wxapp_order_price_sum),0) as wxapp_order_price_sum',
                'coalesce(sum(h5_order_price_sum),0) as h5_order_price_sum',
                'coalesce(sum(byte_dance_order_price_sum),0) as byte_dance_order_price_sum',
            ])
            ->where([
                'activity_type' => 'groups',
            ])
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

        return $this->result([
            'data' => $data,
        ]);
    }

    /**
     * 活动曝光量
     * @return Response
     * @author likexin
     */
    public function actionView(): Response
    {
        $startDate = RequestHelper::get('start_date', date('Y-m-d', strtotime("-10 days")));
        $endDate = RequestHelper::get('end_date', date('Y-m-d', strtotime("-1 days")));
        $activityId = RequestHelper::get('activity_id', 0);

        $lineChartData = MarketingStatisticsModel::getView($startDate, $endDate, $activityId, 'groups');

        return $this->result([
            'data' => $lineChartData,
        ]);
    }

    /**
     * 活动概览数据
     * @return Response
     * @author likexin
     */
    public function actionActivity(): Response
    {
        $params = [
            'select' => [
                'activity.id',
                'activity.title',
                'activity.start_time',
                'activity.end_time',
                'activity.stop_time',
                'if(activity.stop_time=0, 1, 2) as level',
                'coalesce(sum(order_count),0) as order_count',
                'coalesce(sum(pay_price_sum),0) as pay_price_sum',
                'coalesce(sum(refund_price_sum),0) as refund_price_sum',
                'coalesce(sum(sales_goods_total),0) as sales_goods_total',
                'coalesce(sum(pay_member_count),0) as pay_member_count',
            ],
            'alias' => 'activity',
            'leftJoin' => [MarketingStatisticsModel::tableName() . ' statistics', 'statistics.activity_id=activity.id and statistics.activity_type=\'groups\''],
            'where' => [
                'and',
                ['activity.is_deleted' => 0],
                ['<', 'activity.start_time', DateTimeHelper::now(false)],
                ['activity.type' => 'groups'],
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
     * 统计拼团商品总销量等情况
     * @return Response
     * @author likexin
     */
    public function actionGoods(): Response
    {
        $list = ShopMarketingGoodsMapModel::statisticsGoods('groups', []);

        return $this->result($list);
    }

    /**
     * 获取统计更新时间
     * @return Response
     * @author likexin
     */
    public function actionGetUpdateTime(): Response
    {
        $statistics = MarketingStatisticsModel::find()
            ->select('created_at')
            ->where([
                'activity_type' => 'groups',
            ])
            ->orderBy([
                'date' => SORT_DESC,
            ])
            ->first();

        if (empty($statistics)) {
            $updateTime = '0000-00-00 00:00:00';
        } else {
            $updateTime = $statistics['created_at'];
        }

        return $this->result([
            'time' => $updateTime,
        ]);
    }

}