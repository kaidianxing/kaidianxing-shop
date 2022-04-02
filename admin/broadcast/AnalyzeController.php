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

namespace shopstar\admin\broadcast;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\broadcast\BroadcastGoodsStatusConstant;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\broadcast\BroadcastGoodsModel;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\broadcast\BroadcastStatisticsModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\statistics\StatisticsModel;

/**
 * Class AnalyzeController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\broadcast
 */
class AnalyzeController extends KdxAdminApiController
{

    /**
     * 全部统计
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAll()
    {
        $data = BroadcastStatisticsModel::find()
            ->asArray()
            ->groupBy('date')
            ->orderBy([
                'date' => SORT_DESC
            ])
            ->select([
                'date',
                'room_id',
                'sum(order_pay_price) as order_pay_price',
                'sum(order_pay_count) as order_pay_count',
                'sum(order_member_count) as order_member_count',
                'sum(order_count) as order_count',
            ])
            ->limit(7)
            ->all();


        //全部订单
        $broadcastOrder = OrderModel::find()
            ->where([
                'and',
                ['scene' => OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST],
                ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND]
            ])->asArray()->select(['id', 'member_id', 'pay_price', 'scene', 'scene_value'])->all();

        //商城订单统计
        $shopStatistics = StatisticsModel::find()->select([
            'order_pay_price_sum', //当天付款订单总额
        ])->asArray()->all();


        //商品销量排行
        $goodsRank = OrderGoodsModel::find()->where([
            'and',
            ['order_id' => array_column($broadcastOrder, 'id')],
            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND]
        ])->groupBy('goods_id')->select([
            'goods_id',
            'option_id',
            'title',
            'thumb',
            'price',
            'sum(total) as total'
        ])->asArray()->orderBy(['total' => SORT_DESC])->limit(5)->all();


        //会员排行
        $memberRank = OrderModel::find()
            ->alias('order')
            ->leftJoin(MemberModel::tableName() . ' member', 'member.id = order.member_id')
            ->where(['order.id' => array_unique(array_column($broadcastOrder, 'id'))])
            ->groupBy('order.member_id')
            ->select([
                'member.id',
                'member.avatar',
                'member.source',
                'member.nickname',
                'order.member_id',
                'sum(order.pay_price) pay_price',
            ])
            ->orderBy(['pay_price' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();

        //获取昨日数据
        $yesterday = date('Y-m-d', strtotime('- 1 day'));

        $yesterdayData = !empty($data[0]) && $data[0]['date'] == $yesterday ? $data[0] : [
            'order_pay_price' => 0.00,
            'order_pay_count' => 0,
            'order_member_count' => 0,
            'pay_percent' => 0
        ];

        //算已支付的直播间订单比例
        if (!empty($yesterdayData['order_count']) && !empty($yesterdayData['order_pay_count'])) {
            $yesterdayData['pay_percent'] = round2(((int)$yesterdayData['order_pay_count'] / (int)$yesterdayData['order_count']) * 100, 2);
        }

        return $this->result([
            'recent_data' => $data,
            'yesterday_data' => $yesterdayData,
            'goods_rank' => $goodsRank,
            'member_rank' => $memberRank,
            'order_total_pay_price' => round2(array_sum(array_column($shopStatistics, 'order_pay_price_sum'))), //全部订单的支付金额
            'broadcast_order_total_pay_price' => round2(array_sum(array_column($broadcastOrder, 'pay_price'))) //直播间订单的支付金额
        ]);
    }

    /**
     * 商品统计
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoods()
    {
        //商品库列表
        $goodsList = BroadcastGoodsModel::getColl([
            'alias' => 'broadcast_goods',
            'leftJoins' => [
                [GoodsModel::tableName() . ' goods', 'goods.id = broadcast_goods.goods_id'],
                [BroadcastRoomGoodsMapModel::tableName() . ' room_goods', 'room_goods.goods_id = goods.id']
            ],
            'groupBy' => 'broadcast_goods.goods_id',
            'where' => [
                'broadcast_goods.status' => BroadcastGoodsStatusConstant::BROADCAST_GOODS_STATUS_PASS
            ],
            'select' => [
                'goods.title',
                'goods.thumb',
                'goods.price',
                'goods.has_option',
                'goods.type',
                'count(room_goods.room_id) as room_quantity',
                'sum(room_goods.pv_count) as pv_count',
                'sum(room_goods.sales) as sales',
            ]
        ], [
            'callable' => function (&$result) {
                //计算转化率
                if ($result['sales'] != 0 && $result['pv_count'] != 0) {
                    $result['conversion_rate'] = round2($result['sales'] / $result['pv_count'] * 100, 2);
                } else {
                    $result['conversion_rate'] = 0;
                }
            }
        ]);

        return $this->result($goodsList);
    }

}
