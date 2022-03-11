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

namespace shopstar\admin\statistics;

use shopstar\constants\RefundConstant;
use shopstar\exceptions\statistics\TradeException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberBrowseFootprintModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\statistics\StatisticsModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 数据统计首页
 * Class IndexController
 * @package shop\manage\statistics
 */
class TradeController extends KdxAdminApiController
{
    /**
     * 成交信息
     * @return array|\yii\web\Response
     * @throws TradeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        //
        $startDate = RequestHelper::get('start_date', date('Y-m-d', strtotime("-7 days")));
        $endDate = RequestHelper::get('end_date', date('Y-m-d', strtotime("-1 days")));
        $interval = RequestHelper::get('interval', 'day');
        if ($startDate > $endDate) {
            throw new TradeException(TradeException::TRADE_DATE_START_MORE_THAN_END);
        }
        // 时间大于180天 禁用天数粒度 默认月
        if ($interval == 'day' && DateTimeHelper::days($endDate, $startDate) > 180) {
            $interval = 'month';
        }

        $dateRange = DateTimeHelper::getDateRange($startDate, $endDate);

        // 成交信息合计
        $count = [
            'order_pay_count' => '0', // 支付笔数
            'order_pay_member_count' => '0', // 成交用户
            'order_pay_price_sum' => '0.00', // 支付金额
            'order_refund_price_sum' => '0.00' // 退款金额
        ];
        // 折线图数据
        $lineChartData = [];

        // 成交信息 折线图数据 x 轴
        foreach ($dateRange as $date) {
            if ($interval == 'month') {
                $lineChartData[date('Y-m', strtotime($date))] = $count;
            } else if ($interval == 'year') {
                $lineChartData[date('Y', strtotime($date))] = $count;
            } else {
                $lineChartData[$date] = $count;
            }
        }

        // 成交信息数据
        $statistics = StatisticsModel::find()
            ->where(['between', 'statistic_date', $startDate, $endDate])
            ->select([
                'statistic_date',
                'order_pay_count',
                'order_pay_member_count',
                'order_pay_price_sum',
                'order_refund_price_sum'
            ])
            ->asArray()
            ->all();

        // 遍历数据
        foreach ($statistics as $index => $item) {
            $date = $item['statistic_date'];
            if ($interval == 'month') {
                $key = date('Y-m', strtotime($date));
            } else if ($interval == 'year') {
                $key = date('Y', strtotime($date));
            } else {
                $key = date('Y-m-d', strtotime($date));
            }

            // 折线图
            $lineChartData[$key]['order_pay_count'] += $item['order_pay_count']; // 支付笔数
            $lineChartData[$key]['order_pay_member_count'] += $item['order_pay_member_count']; // 支付笔数
            $lineChartData[$key]['order_pay_price_sum'] = bcadd($lineChartData[$key]['order_pay_price_sum'], $item['order_pay_price_sum'], 2); // 支付笔数
            $lineChartData[$key]['order_refund_price_sum'] = bcadd($lineChartData[$key]['order_refund_price_sum'], $item['order_refund_price_sum'], 2); // 支付笔数

            // 合计
            $count['order_pay_count'] += $item['order_pay_count']; // 支付笔数
            $count['order_pay_member_count'] += $item['order_pay_member_count']; // 成交用户
            $count['order_pay_price_sum'] = bcadd($item['order_pay_price_sum'], $count['order_pay_price_sum'], 2); // 支付金额
            $count['order_refund_price_sum'] = bcadd($item['order_refund_price_sum'], $count['order_refund_price_sum'], 2); // 退款金额
        }

        return $this->result([
            'count' => $count,
            'line_chart_data' => $lineChartData,
        ]);
    }

    /**
     * 核心销售指标
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCoreIndicator()
    {
        // 核心销售指标
        $coreIndicator = [];
        // 商品浏览次数
        $coreIndicator['goods_view_times'] = MemberBrowseFootprintModel::find()
            ->count();

        // 所有支付订单数
        $coreIndicator['order_pay_count'] = OrderModel::find()
            ->where(['>', 'status', 0])->count();

        // 所有支付订单金额总和 不管维权 只要已支付就算
        $coreIndicator['order_pay_price_sum'] = OrderModel::find()
            ->where(['<>', 'pay_type', 0])->sum('pay_price');

        // 完成支付订单的用户数
        $coreIndicator['order_pay_member_count'] = OrderModel::find()
            ->where(['>', 'status', 0])->count('distinct(member_id)');

        // 总会员数
        $coreIndicator['member_count'] = MemberModel::find()
            ->count();

        // 退款订单数
        $coreIndicator['order_refund_count'] = OrderRefundModel::find()
            ->where([
                'and',
                ['is_history' => 0],
                [
                    'or',
                    ['refund_type' => RefundConstant::TYPE_REFUND],
                    ['refund_type' => RefundConstant::TYPE_RETURN]
                ]
            ])->count('distinct(order_id)');

        // 成交转化率
        if ($coreIndicator['goods_view_times'] != 0) {
            $payConversion = bcdiv($coreIndicator['order_pay_member_count'], $coreIndicator['goods_view_times'], 4);
        } else {
            $payConversion = 0;
        }
        $coreIndicator['pay_conversion'] = bcmul($payConversion, 100, 2) . '%';

        // 客单价
        if ($coreIndicator['order_pay_member_count'] != 0) {
            $coreIndicator['member_unit_price'] = bcdiv($coreIndicator['order_pay_price_sum'], $coreIndicator['order_pay_member_count'], 2);
        } else {
            $coreIndicator['member_unit_price'] = 0;
        }

        // 付费会员占比
        if ($coreIndicator['member_count'] != 0) {
            $payMemberScale = bcdiv($coreIndicator['order_pay_member_count'], $coreIndicator['member_count'], 4);
        } else {
            $payMemberScale = 0;
        }
        $coreIndicator['pay_member_scale'] = bcmul($payMemberScale, 100, 2) . '%';

        // 退款率
        if ($coreIndicator['order_pay_count'] != 0) {
            $refundScale = bcdiv($coreIndicator['order_refund_count'], $coreIndicator['order_pay_count'], 4);
        } else {
            $refundScale = 0;
        }
        $coreIndicator['refund_scale'] = bcmul($refundScale, 100, 2) . '%';

        return $this->result(['core_indicator' => $coreIndicator]);
    }
}