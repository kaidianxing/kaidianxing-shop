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

namespace shopstar\admin;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\goods\GoodsDeleteConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\statistics\TradeException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\statistics\StatisticsModel;
use shopstar\models\statistics\StatisticsPageViewModel;
use shopstar\models\statistics\StatisticsUniqueViewModel;

/**
 * 首页类
 * Class IndexController
 * @package shopstar\admin
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'order',
            'manage-overview',
            'manage',
            'commission',
            'goods-rank',
            'money-rank'
        ]
    ];

    /**
     * 订单数据
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionOrder()
    {
        $sendCount = OrderModel::find()
            ->where([
                'status' => [OrderStatusConstant::ORDER_STATUS_WAIT_SEND, OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND]
            ])
            ->count();

        // 待付款
        $waitPay = OrderModel::find()
            ->where([
//                'status' => [OrderStatusConstant::ORDER_STATUS_WAIT_PAY, OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND]
                'status' => [OrderStatusConstant::ORDER_STATUS_WAIT_PAY]
            ])
            ->count();

        // 待收货 (已发货)
        $waitPick = OrderModel::find()
            ->where([
                'status' => [OrderStatusConstant::ORDER_STATUS_WAIT_PICK],
            ])
            ->count();

        // 待处理维权
        $refundCount = OrderRefundModel::find()
            ->where([
                'is_history' => 0,
            ])
            ->andWhere(['between', 'status', 0, 9])
            ->count();

        // 总用户数
        $memberCount = MemberModel::find()
            ->where([
                'is_deleted' => 0
            ])
            ->count();
        // 消费会员数
        $consumeMemberCount = OrderModel::find()
            ->where(['>', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY])
            ->count('distinct(member_id)');
        // 商品总数
        $goodsCount = GoodsModel::find()
            ->where([
                'status' => GoodsStatusConstant::GOODS_STATUS_PUTAWAY,
                'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO
            ])
            ->andWhere(['>', 'stock', 0])
            ->count();

        // 商品出售中
        $goodsInSale = GoodsModel::find()
            ->where([
                'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY],
                'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO
            ])
            ->andWhere(['>', 'stock', 0])
            ->count();

        // 商品已售罄
        $goodsSaleOut = GoodsModel::find()
            ->where([
                'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY],
                'stock' => 0,
                'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO
            ])
            ->count();

        // 商品仓库中
        $goodsInStock = GoodsModel::find()
            ->where([
                'status' => [GoodsStatusConstant::GOODS_STATUS_UNSHELVE],
                'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO
            ])
            ->count();

        // 商品库存预警
        $goodsWarning = GoodsModel::find()
            ->where([
                'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY],
                'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO
            ])
            ->andWhere(['<', 'stock', 5])
            ->count();

        // 订单总数
        $orderCount = OrderModel::find()
            ->count();

        //

        $final = [
            'send' => $sendCount,
            'refund' => $refundCount,
            'member' => $memberCount,
            'consume_member' => $consumeMemberCount,
            'goods_count' => $goodsCount,
            'goods_in_sale' => $goodsInSale,
            'goods_sale_out' => $goodsSaleOut,
            'goods_in_stock' => $goodsInStock,
            'goods_warning' => $goodsWarning,
            'order_count' => $orderCount,
            'wait_pay' => $waitPay,
            'wait_pick' => $waitPick,
        ];

        return $this->result(['data' => $final]);
    }

    /**
     * 经营状况总览
     * @return array|\yii\web\Response
     * @throws TradeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManageOverview()
    {
        // 获取参数
        $startDate = RequestHelper::get('start_date');
        $endDate = RequestHelper::get('end_date');

        if (empty($startDate) || empty($endDate)) {
            throw new TradeException(TradeException::INDEX_MANAGE_OVERVIEW_PARAMS_ERROR);
        }
        $endDate = date('Y-m-d', strtotime($endDate) + 86400);

        // 成交金额 order_pay_price_sum

        // 订单数 order_pay_count

        // 支付人数 order_pay_member_count

        $statisticData = StatisticsModel::find()
            ->select(['order_pay_price_sum', 'order_pay_count', 'order_pay_member_count', 'order_refund_price_sum'])
            ->where(['between', 'statistic_date', $startDate, $endDate])
            ->asArray()
            ->all();

        $orderPayPriceCount = array_sum(array_column($statisticData, 'order_pay_price_sum')) - array_sum(array_column($statisticData, 'order_refund_price_sum'));
        $orderPayCount = array_sum(array_column($statisticData, 'order_pay_count'));
        $orderPayMemberCount = array_sum(array_column($statisticData, 'order_pay_member_count'));

        // 笔单价 成交金额/订单数
        $unitPrice = empty($orderPayCount) ? 0 :
            round2($orderPayPriceCount / $orderPayCount, 2);

        // 客单价 成交金额/支付人数
        $guestUnitPrice = empty($orderPayMemberCount) ? 0 :
            round2($orderPayPriceCount / $orderPayMemberCount, 2);

        // PV
        $pv = StatisticsPageViewModel::find()
            ->where(['between', 'created_at', $startDate, $endDate])
            ->count();

        // UV
        $uv = StatisticsUniqueViewModel::find()
            ->where(['between', 'create_date', $startDate, $endDate])
            ->count();


        // 新增会员数
        $newMember = MemberModel::find()
            ->where(['between', 'created_at', $startDate, $endDate])
            ->count();


        $final = [
            'order_pay_price' => round2($orderPayPriceCount, 2),
            'order_pay' => $orderPayCount,
            'unit_price' => $unitPrice,
            'guest_unit_price' => round2($guestUnitPrice, 2),
            'order_pay_member' => $orderPayMemberCount,
            'pv' => $pv,
            'uv' => $uv,
            'new_member' => $newMember
        ];

        return $this->result(['data' => $final]);
    }

    /**
     * 经营状况折线图
     * @return array|\yii\web\Response
     * @throws TradeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManage()
    {
        // 获取参数
        $startDate = RequestHelper::get('start_date');
        $endDate = RequestHelper::get('end_date');
        $period = RequestHelper::get('period', 'day');
        $type = RequestHelper::get('type', 'order_pay_price');

        if (empty($startDate) || empty($endDate)) {
            throw new TradeException(TradeException::INDEX_MANAGE_PARAMS_ERROR);
        }
        $endDateForSearch = date('Y-m-d', strtotime($endDate) + 86400);

        // 成交金额 order_pay_price_sum

        // 订单数 order_pay_count

        // 支付人数 order_pay_member_count

        switch ($type) {
            case 'order_pay_price':
            case 'order_pay':
            case 'order_pay_member':
            case 'unit_price':
            case 'guest_unit_price':
                if ($period == 'month') {
                    $dateFormatForDb = '%Y-%m';
                } elseif ($period == 'year') {
                    $dateFormatForDb = '%Y';
                } else {
                    $dateFormatForDb = '%Y-%m-%d';
                }
                $statisticData = StatisticsModel::find()
                    ->select(["DATE_FORMAT(statistic_date, '{$dateFormatForDb}') as date", 'sum(order_pay_price_sum)-sum(order_refund_price_sum) as order_pay_price', 'sum(order_pay_count) as order_pay',
                        'sum(order_pay_member_count) as order_pay_member'])
                    ->where(['between', 'statistic_date', $startDate, $endDateForSearch])
                    ->groupBy('date')
                    ->asArray()
                    ->all();

                foreach ($statisticData as $key => $statisticDatum) {
                    $statisticData[$key]['unit_price'] = empty($statisticDatum['order_pay']) ? 0 :
                        round2($statisticDatum['order_pay_price'] / $statisticDatum['order_pay'], 2);
                    $statisticData[$key]['guest_unit_price'] = empty($statisticDatum['order_pay_member']) ? 0 :
                        round2($statisticDatum['order_pay_price'] / $statisticDatum['order_pay_member'], 2);
                }
                break;
            case 'pv':
                $statisticData = StatisticsPageViewModel::find()
                    ->select(['date(created_at) as date', 'count(1) as pv'])
                    ->where(['between', 'created_at', $startDate, $endDateForSearch])
                    ->groupBy('date')
                    ->asArray()
                    ->all();
                break;
            case 'uv':
                $statisticData = StatisticsUniqueViewModel::find()
                    ->select(['create_date as date', 'count(1) as uv'])
                    ->where(['between', 'create_date', $startDate, $endDateForSearch])
                    ->groupBy('date')
                    ->asArray()
                    ->all();
                break;
            case 'new_member':
                $statisticData = MemberModel::find()
                    ->select(['date(created_at) as date', 'count(1) as new_member'])
                    ->where(['between', 'created_at', $startDate, $endDateForSearch])
                    ->groupBy('date')
                    ->asArray()
                    ->all();
                break;
            default:
                throw new TradeException(TradeException::INDEX_TYPE_ERROR);

        }

        // 趋势图
        // 时间范围
        $dateRange = DateTimeHelper::getDateRange($startDate, $endDate);
        if ($period == 'month') {
            $dateFormat = 'Y-m';
        } elseif ($period == 'year') {
            $dateFormat = 'Y';
        } else {
            $dateFormat = 'Y-m-d';
        }

        $final = [];

        // 循环遍历展示数据
        foreach ($dateRange as $dateItem) {
            $datePeriod = date($dateFormat, strtotime($dateItem));

            if (isset($final[$datePeriod])) {
                continue;
            }

            $final[$datePeriod] = 0;
            foreach ($statisticData as $statisticDatum) {
                $statisticPeriod = date($dateFormat, strtotime($statisticDatum['date']));

                if ($datePeriod == $statisticPeriod) {
                    $final[$datePeriod] += $statisticDatum[$type];
                }
            }
        }

        $returnData = [];
        foreach ($final as $periodKey => $countVal) {
            $returnData[] = [
                'period' => $periodKey,
                'count' => round2($countVal, 2)
            ];
        }

        return $this->result(['data' => $returnData]);
    }

    /**
     * 分销数据
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCommission()
    {
        $final = [
            'agent_count' => 0,
            'wait_agent_count' => 0,
            'pre_check' => 0,
            'check_agree' => 0,
        ];

        // 分销
        $agent = CommissionAgentTotalModel::getTotalInfo();

        $final['agent_count'] = intval(ArrayHelper::arrayGet($agent, 'agent_count', 0));
        $final['wait_agent_count'] = intval(ArrayHelper::arrayGet($agent, 'wait_agent_count', 0));

        // 佣金
        $commission = CommissionApplyModel::getCommissionInfo();

        $final['pre_check'] = floatval(ArrayHelper::arrayGet($commission, 'pre_check', 0));
        $final['check_agree'] = floatval(ArrayHelper::arrayGet($commission, 'check_agree', 0));

        return $this->result(['data' => $final]);
    }

    /**
     * 首页商品销量TOP排行榜
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsRank()
    {
        $params = [
            'select' => [
                'goods.id',
                'goods.title',
                'goods.thumb',
                'goods.status',
                'goods.stock',
                'goods.is_deleted',
                'goods.has_option',
                'real_sales as total'
            ],
            'groupBy' => 'goods.id',
            'alias' => 'goods',
            'orderBy' => 'real_sales desc, goods.created_at desc'
        ];
        $list = GoodsModel::getColl($params, [
            'callable' => function (&$row) {
                if ($row['status'] == 1 && $row['stock'] > 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 1; // 上架
                } else if ($row['status'] == 1 && $row['stock'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 2; // 售罄
                } else if ($row['status'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 3; // 下架
                } else if ($row['is_deleted'] == 1) {
                    $row['status'] = 4; // 回收站
                }
                $row['total'] = is_null($row['total']) ? 0 : $row['total'];
            },
            'pageSize' => 5,
            'onlyList' => true,
            'total' => false
        ]);

        return $this->result(['data' => $list]);
    }

    /**
     * 首页购买力TOP排行榜
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMoneyRank()
    {
        $params = [
            'select' => [
                'member.id',
                'member.nickname',
                'member.avatar',
                'member.realname',
                'member.mobile',
                'member.source',
                'sum(o.pay_price) as order_money',
            ],
            'where' => [
                'and',
                ['member.is_deleted' => 0],
                ['>', 'o.status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY]
            ],
            'alias' => 'member',
            'leftJoin' => [OrderModel::tableName() . ' o', 'o.member_id = member.id'],
            'orderBy' => 'order_money desc, member.created_at asc',
            'groupBy' => 'member.id'
        ];
        $list = MemberModel::getColl($params, [
            'callable' => function (&$row) {
                $row['order_money'] = is_null($row['order_money']) ? 0 : round2($row['order_money'], 2);
            },
            'pageSize' => 5,
            'onlyList' => true,
            'total' => false
        ]);

        return $this->result(['data' => $list]);
    }

}
