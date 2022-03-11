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

namespace shopstar\models\activity;

use shopstar\constants\goods\GoodsReductionTypeConstant;

use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\services\goods\GoodsActivityService;


/**
 * This is the model class for table "{{%activity_goods_map}}".
 *
 * @property string $id
 * @property int $activity_id 活动id
 * @property int $goods_id 商品id
 * @property int $option_id 规格id
 * @property int $original_stock 原始库存
 * @property int $activity_stock 活动库存
 * @property int $activity_sales 销量
 * @property string $activity_price 活动价格
 * @property int $is_join 是否参与
 */
class ShopMarketingGoodsMapModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%marketing_goods_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'goods_id', 'option_id', 'activity_stock', 'activity_sales', 'is_join', 'original_stock'], 'integer'],
            [['activity_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动id',
            'goods_id' => '商品id',
            'option_id' => '规格id',
            'original_stock' => '原始库存',
            'activity_stock' => '活动库存',
            'activity_sales' => '销量',
            'activity_price' => '活动价格',
            'is_join' => '是否参与',
        ];
    }

    /**
     * 保存商品
     * @param array $goodsInfo
     * @param ShopMarketingModel $activity
     * @param array $options
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveGoodsMap(array $goodsInfo, ShopMarketingModel $activity, array $options = [])
    {
        $options = array_merge([
            'type' => '',
        ], $options);

        // 保存商品信息
        $fields = ['activity_id', 'goods_id', 'option_id', 'original_stock', 'activity_stock', 'activity_price', 'is_join'];
        $insertGoodsInfo = [];

        // 活动商品
        $goodsActivityData = [];
        foreach ($goodsInfo as $item) {

            // 活动商品数据
            $goodsActivityData[$item['goods_id']] = [
                $item['goods_id'],
                $activity->id,
                $options['type'],
                $activity->start_time,
                $activity->end_time,
                $activity->client_type,
                (int)$activity->is_preheat,
                $activity->preheat_time ?: '0000-00-00 00:00:00',
            ];

            if ($item['has_option'] == 0) {
                $insertGoodsInfo[] = [
                    $activity->id,
                    $item['goods_id'],
                    0,
                    $item['activity_stock'],
                    $item['activity_stock'],
                    $item['activity_price'] ?: 0.00,
                    1,
                ];
            } else {
                foreach ($item['rules'] as $value) {
                    // 规格不参与  跳过
                    if ($value['is_join'] == 0) {
                        continue;
                    }
                    $insertGoodsInfo[] = [
                        $activity->id,
                        $item['goods_id'],
                        $value['option_id'],
                        $value['activity_stock'],
                        $value['activity_stock'],
                        $value['activity_price'] ?: 0.00,
                        $value['is_join'],
                    ];
                }
            }
        }
        // 保存
        self::batchInsert($fields, $insertGoodsInfo);
        // 插入商品活动表
        GoodsActivityModel::insertData(array_values($goodsActivityData));
    }

    /**
     * 校验商品信息
     * @param array $goodsInfo
     * @param string $startTime
     * @param string $endTime
     * @param array $options
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkGoodsInfo(array $goodsInfo, string $startTime, string $endTime, array $options = [])
    {
        $options = array_merge([
            'is_seckill' => false, //是秒杀
            'is_groups' => false,   //是拼团
        ], $options);

        foreach ($goodsInfo as $item) {
            // 判断商品是否可用
            $isAvailable = GoodsActivityService::isAvailable($item['goods_id'], $startTime, $endTime);
            if ($isAvailable) {
                return error('存在已参加其他活动的商品');
            }

            // 查找商品
            $goods = GoodsModel::find()->select('id, title, price, stock, reduction_type')->where(['id' => $item['goods_id']])->first();

            if (empty($goods)) {
                return error('商品未找到');
            }

            // 秒杀的减库存方式必须是下单减库存
            if ($options['is_seckill'] && $goods['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_ORDER) {
                return error('商品[' . $goods['title'] . ']减库存方式不是下单减库存');
            }

            // 拼团的减库存方式必须是付款减库存
            if ($options['is_groups'] && $goods['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT) {
                return error('商品[' . $goods['title'] . ']减库存方式不是付款减库存');
            }

            // 不是多规格
            if ($item['has_option'] == 0) {
                if ($item['activity_stock'] == '') {
                    return error('活动库存不能为空');
                }

                if ($goods['stock'] < $item['activity_stock']) {
                    return error('活动库存不能大于商品库存');
                }

                if ($item['activity_price'] == '') {
                    return error('活动价格不能为空');
                }

                if ($item['activity_price'] > $goods['price']) {
                    return error('活动价格不能大于商品价格');
                }
            } else {
                // 多规格
                if (empty($item['rules'])) {
                    return error('请确认多规格商品是否设置活动信息');
                }
                // 获取商品规格
                $goodsOption = GoodsOptionModel::find()->where(['goods_id' => $item['goods_id']])->get();
                foreach ($item['rules'] as $value) {
                    // 不参与直接跳出
                    if ($value['is_join'] == 0) {
                        continue;
                    }
                    if ($value['activity_stock'] == '') {
                        return error('活动库存不能为空');
                    }
                    if ($value['activity_price'] == '') {
                        return error('活动价格不能为空');
                    }
                    // 校验库存
                    foreach ($goodsOption as $option) {
                        if ($option['id'] == $value['option_id']) {
                            if ($value['activity_stock'] > $option['stock']) {
                                return error('活动库存不能大于商品库存');
                            }
                            if ($item['activity_price'] > $option['price']) {
                                return error('活动价格不能大于商品价格');
                            }
                            break;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 关闭订单返回库存
     * @param int $orderId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeSeckillUpdateStock(int $orderId)
    {
        $orderGoods = OrderGoodsModel::find()
            ->select(['goods_id', 'option_id', 'total'])
            ->where(['order_id' => $orderId])
            ->get();
        if (empty($orderGoods)) {
            return error('订单信息错误');
        }
        $orderActivity = OrderActivityModel::find()->where(['order_id' => $orderId])->first();
        if (empty($orderActivity)) {
            return error('订单活动信息错误');
        }
        $redis = \Yii::$app->redisPermanent;
        foreach ($orderGoods as $item) {
            // 更新库存
            self::updateAllCounters(
                ['activity_stock' => $item['total']],
                ['activity_id' => $orderActivity['activity_id'], 'goods_id' => $item['goods_id'], 'option_id' => $item['option_id']]
            );
            $key = 'seckill_' . '_' . $orderActivity['activity_id'] . '_' . $item['goods_id'] . '_' . $item['option_id'];
            $redis->incrby($key, -$item['total']);
        }

        return true;
    }


    /**
     * 获取某活动下每个商品总销量
     * @param string $activityType
     * @param array $activityId
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function statisticsGoods(string $activityType, array $activityId = [])
    {
        if (empty($activityId)) {

            $allActivity = ShopMarketingModel::find()
                ->select([
                    'id',
                    'title',
                ])
                ->where([
                    'type' => $activityType,
                ])
                ->get();

            $activityId = array_column($allActivity, 'id');
        }

        $select = [
            'activity_goods.goods_id',
            'count(if(order.pay_type<>0, order.id, null)) as order_count',
            'sum(if(order.pay_type<>0, order.pay_price, 0)) as pay_price',
            'COALESCE(sum(order.refund_price), 0) as refund_price',
            'sum(if(order.pay_type<>0, order_goods.total , 0)) as total',
            'count(distinct(if(order.pay_type<>0, order.member_id, null))) member_count',
        ];
        $params = [
            'select' => $select,
            'alias' => 'activity_goods',
            'leftJoins' => [
                [OrderActivityModel::tableName() . 'order_activity', "order_activity.activity_id=activity_goods.activity_id and activity_type='$activityType'"],
                [OrderGoodsModel::tableName() . ' order_goods', 'order_goods.order_id=order_activity.order_id and order_goods.goods_id=activity_goods.goods_id and order_goods.option_id=activity_goods.option_id'],
                [OrderModel::tableName() . ' order', 'order.id=order_goods.order_id']
            ],
            'where' => [
                'and',
                ['activity_goods.activity_id' => $activityId],
                ['activity_goods.is_join' => 1],
            ],
            'groupBy' => [
                'activity_goods.goods_id',
            ]
        ];
        $list = self::getColl($params);

        $goodsIds = array_unique(array_column($list['list'], 'goods_id'));

        $goodsList = GoodsModel::find()->with('options')->where(['id' => $goodsIds])->indexBy('id')->get();
        foreach ($list['list'] as &$item) {
            $item['title'] = $goodsList[$item['goods_id']]['title'];
            $item['thumb'] = $goodsList[$item['goods_id']]['thumb'];
            $item['type'] = $goodsList[$item['goods_id']]['type'];
            if (!empty($item['option_id'])) {

                foreach ($goodsList[$item['goods_id']]['options'] as $option) {
                    if ($option['id'] == $item['option_id']) {
                        $item['option_title'] = $option['title'];
                        $item['price'] = $option['price'];
                    }
                }
            } else {
                $item['price'] = $goodsList[$item['goods_id']]['price'];
            }
        }
        unset($item);

        return $list;
    }

}
