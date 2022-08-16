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

namespace shopstar\admin\creditShop;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\creditShop\CreditShopStatisticsModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponModel;
use shopstar\services\creditShop\CreditShopOrderService;
use yii\web\Response;

/**
 * 积分商城数据统计控制器
 * Class StatisticsController.
 * @package shopstar\admin\creditShop
 */
class StatisticsController extends KdxAdminApiController
{
    /**
     * 概览
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data['price_sum'] = CreditShopOrderService::getClientPrice();
        $data['credit_sum'] = CreditShopOrderService::getClientPrice(0, 'pay_credit');

        $data['wait_send'] = OrderModel::find()->where(['activity_type' => OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND])->count();
        $data['goods_count'] = CreditShopGoodsModel::find()->where(['is_delete' => 0])->count();
        $data['order_count'] = CreditShopOrderModel::find()->where(['status' => 1])->count();

        return $this->result(['data' => $data]);
    }

    /**
     * 折线图数据
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionStatistics()
    {
        $startDate = RequestHelper::get('start_date', date('Y-m-d', strtotime("-10 days")));
        $endDate = RequestHelper::get('end_date', date('Y-m-d', strtotime("-1 days")));

        $data = [
            'member_count' => 0,
            'view_count' => 0
        ];

        $dateRange = DateTimeHelper::getDateRange($startDate, $endDate);

        $lineChartData = [];
        // x轴
        foreach ($dateRange as $date) {
            $lineChartData[$date] = $data;
        }

        $statistics = CreditShopStatisticsModel::find()
            ->select([
                'date',
                'view_count',
                'member_count',
            ])
            ->where(['between', 'date', $startDate, $endDate])
            ->get();

        foreach ($statistics as $index => $item) {
            $date = $item['date'];
            $key = date('Y-m-d', strtotime($date));
            $lineChartData[$key]['view_count'] += $item['view_count']; // 浏览量
            $lineChartData[$key]['member_count'] += $item['member_count']; // 访客数
        }

        return $this->result(['data' => $lineChartData]);
    }

    /**
     * 渠道数据
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChannel()
    {
        $data['count']['order_price_sum'] = CreditShopOrderService::getClientPrice();
        $data['count']['h5_order_price_sum'] = CreditShopOrderService::getClientPrice(ClientTypeConstant::CLIENT_H5);
        $data['count']['wechat_order_price_sum'] = CreditShopOrderService::getClientPrice(ClientTypeConstant::CLIENT_WECHAT);
        $data['count']['wxapp_order_price_sum'] = CreditShopOrderService::getClientPrice(ClientTypeConstant::CLIENT_WXAPP);
        $data['count']['byte_dance_order_price_sum'] = CreditShopOrderService::getClientPrice([ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO, ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN, ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE]);

        // 累计不为空
        if ($data['count']['order_price_sum'] != 0) {
            $data['channel'][] = [
                'name' => '微信公众号',
                'value' => bcdiv($data['count']['wechat_order_price_sum'], $data['count']['order_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => '微信小程序',
                'value' => bcdiv($data['count']['wxapp_order_price_sum'], $data['count']['order_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => 'H5',
                'value' => bcdiv($data['count']['h5_order_price_sum'], $data['count']['order_price_sum'], 4),
            ];
            $data['channel'][] = [
                'name' => '头条/抖音小程序',
                'value' => bcdiv($data['count']['byte_dance_order_price_sum'], $data['count']['order_price_sum'], 4),
            ];
        }

        return $this->result(['data' => $data]);
    }

    /**
     * 积分商品排行
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsRank()
    {
        $data = CreditShopOrderModel::find()
            ->select([
                'order.goods_id',
                'sum(order.total) total',
                'order.type',
                'shop_goods.title',
                'shop_goods.sub_name',
                'shop_goods.thumb',
                'shop_goods.type as goods_type',
                'shop_coupon.coupon_name',
                'shop_coupon.coupon_name',
                'shop_coupon.coupon_sale_type',
                'shop_coupon.enough',
                'shop_coupon.discount_price',
            ])
            ->alias('order')
            ->leftJoin(GoodsModel::tableName().' shop_goods', 'shop_goods.id=order.shop_goods_id and order.type=0')
            ->leftJoin(CouponModel::tableName().' shop_coupon', 'shop_coupon.id=order.shop_goods_id and order.type=1')
            ->where([
                'and',
                ['>', 'order.status', 0],
            ])
            ->groupBy('order.goods_id')
            ->orderBy([
                'total' => SORT_DESC
            ])
            ->limit(5)
            ->get();

        return $this->result(['data' => $data]);
    }

    /**
     * 会员排行
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMemberRank()
    {
        $data = CreditShopOrderModel::find()
            ->select([
                'order.member_id',
                'member.nickname',
                'member.avatar',
                'member.source',
                'sum(pay_credit) pay_credit',
                'sum(pay_price) pay_price'
            ])
            ->alias('order')
            ->leftJoin(MemberModel::tableName().' member', 'member.id=order.member_id')
            ->where([
                'and',
                ['>', 'order.status', 0],
            ])
            ->groupBy('order.member_id')
            ->orderBy([
                'pay_credit' => SORT_DESC,
                'pay_price' => SORT_DESC
            ])
            ->limit(5)
            ->get();

        return $this->result(['data' => $data]);
    }
}
