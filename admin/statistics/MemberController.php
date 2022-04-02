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

use shopstar\exceptions\statistics\TradeException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\statistics\StatisticsModel;
use shopstar\models\statistics\StatisticsUniqueViewModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 会员统计
 * Class MemberController
 * @package shopstar\admin\statistics
 * @author 青岛开店星信息技术有限公司
 */
class MemberController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $allowHeaderActions = [
        'money-rank',
    ];

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'index'
        ]
    ];

    /**
     * 会员数据
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        // 会员累计数据
        $count = [];

        // 累计会员数
        $count['member_count'] = MemberModel::find()
            ->where(['is_deleted' => 0])
            ->count();

        // 访问会员数
        $count['member_pv_count'] = StatisticsUniqueViewModel::find()
            ->count();

        // 付款会员数
        $count['order_pay_member_count'] = OrderModel::find()
            ->where(['>', 'status', 0])->count('distinct(member_id)');

        // 30天内访问会员数
        $view = StatisticsUniqueViewModel::find()
            ->where(['>', 'create_date', date('Y-m-d', strtotime('-30 days'))])
            ->count('distinct(member_id)');
        // 流失率 （30天内未访问商城的会员数/累计会员）*100%
        if ($count['member_count'] != 0) {
            if ($view > $count['member_count']) {
                $view = $count['member_count'];
            }
            $unViewScale = bcdiv(($count['member_count'] - $view), $count['member_count'], 4);
        } else {
            $unViewScale = 0;
        }
        $count['un_view_scale'] = bcmul($unViewScale, 100, 2) . '%';

        return $this->result($count);
    }

    /**
     * 会员增长趋势
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTrend()
    {
        // 下方折线图统计
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

        // 时间范围
        $dateRange = DateTimeHelper::getDateRange($startDate, $endDate);
        // 折线图数据
        $lineChartData = [];
        $data = [
            'member_count' => '0', // 新增会员数
            'member_pv_count' => '0' // 访客数
        ];

        // 会员增长趋势数据
        $statistics = StatisticsModel::find()
            ->where(['between', 'statistic_date', $startDate, $endDate])
            ->select([
                'member_new_count',
                'uv',
                'statistic_date'
            ])
            ->asArray()
            ->all();

        //  会员增长趋势 折线图数据 x 轴
        foreach ($dateRange as $date) {
            if ($interval == 'month') {
                $lineChartData[date('Y-m', strtotime($date))] = $data;
            } else if ($interval == 'year') {
                $lineChartData[date('Y', strtotime($date))] = $data;
            } else {
                $lineChartData[$date] = $data;
            }
        }

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

            $lineChartData[$key]['member_count'] += $item['member_new_count']; // 新增会员
            $lineChartData[$key]['member_pv_count'] += $item['uv']; // 访客
        }

        return $this->result($lineChartData);
    }

    /**
     * 消费排行榜
     * 包含维权订单
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMoneyRank()
    {
        $field = RequestHelper::get('field', 'order_money');
        $sort = RequestHelper::get('sort', 'desc');
        $export = RequestHelper::get('export', '0');

        $orderBy[$field] = $sort == 'asc' ? SORT_ASC : SORT_DESC;


        $params = [
            'searchs' => [
                ['member.level_id', 'int', 'level_id'],
                [['member.nickname', 'member.realname', 'member.mobile'], 'like', 'keyword']
            ],
            'select' => [
                'member.id',
                'member.nickname',
                'member.level_id',
                'member.avatar',
                'member.realname',
                'member.mobile',
                'member.source',
                'level.level_name',
                'ifnull(sum(order.pay_price),0) as order_money',
                'count(order.id) as order_count ',
                'member.is_black'
            ],
            'andWhere' => [
                ['member.is_deleted' => 0]
            ],
            'leftJoins' => [
                [MemberLevelModel::tableName() . ' level', 'member.level_id=level.id'],
                [OrderModel::tableName() . ' order', 'order.member_id=member.id and order.pay_type<>0']
            ],
            'alias' => 'member',
            'orderBy' => $orderBy,
            'groupBy' => 'member.id'
        ];

        // 获取默认等级
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();

        $list = MemberModel::getColl($params, [
            'callable' => function (&$row) use ($defaultLevelId) {
                if ($row['level_id'] == $defaultLevelId) {
                    $row['is_default_level'] = 1;
                }
            },
            'pager' => $export != 1,
            'onlyList' => $export == 1,
        ]);

        // 执行导出
        if ($export == 1) {
            $this->export($list);
        }

        return $this->result($list);
    }

    /**
     * 导出
     * @param array $list
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function export(array $list)
    {
        ExcelHelper::export($list, [
            [
                'field' => 'nickname',
                'title' => '用户昵称',
                'width' => 18,
            ],
            [
                'field' => 'realname',
                'title' => '真实姓名',
                'width' => 18,
            ],
            [
                'field' => 'mobile',
                'title' => '手机号',
                'width' => 18,
            ],
            [
                'field' => 'level_name',
                'title' => '等级',
                'width' => 18,
            ],
            [
                'field' => 'order_money',
                'title' => '订单金额',
                'width' => 18,
            ],
            [
                'field' => 'order_count',
                'title' => '订单数量',
                'width' => 12,
            ],
        ], '会员排行数据导出');
        die;
    }

}