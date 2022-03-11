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

namespace shopstar\models\broadcast;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\order\OrderModel;

/**
 * This is the model class for table "{{%app_broadcast_statistics}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property string $order_pay_price 当天付款总金额
 * @property string $order_pay_count 当天付款订单数
 * @property int $order_member_count 当天付款会员数
 * @property int $room_id 直播间id
 * @property int $order_count 当天订单数
 */
class BroadcastStatisticsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_broadcast_statistics}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_member_count', 'order_pay_count', 'room_id', 'order_count'], 'integer'],
            [['date'], 'string', 'max' => 125],
            [['order_pay_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => '日期',
            'order_pay_price' => '当天付款总金额',
            'order_pay_count' => '当天付款订单数',
            'order_member_count' => '当天付款会员数',
            'room_id' => '直播间id',
            'order_count' => '当天订单数',
        ];
    }

    /**
     * @return bool|string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function createDayStatistic()
    {
        // 查找是否统计过
        $date = date('Y-m-d', strtotime('-1 day'));
        $isCalculate = self::find()->where(['date' => $date])->exists();
        if ($isCalculate) {
            return $date . ' 该日期已统计';
        }

        // 统计
        self::calculate($date);

        return true;
    }

    /**
     * 执行统计
     * @param $date
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculate($date)
    {
        $order = OrderModel::find()->where([
            'and',
            ['scene' => OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST],
            ['between', 'created_at', $date . ' 00:00:00', $date . ' 23:59:59'],
//            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
        ])->select([
            'pay_price',
            'member_id',
            'status',
            'scene',
            'scene_value',
        ])->asArray()->all();

        $payOrder = [];
        foreach ((array)$order as $item) {
            if ($item['status'] >= OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
                $payOrder[] = $item;
            }
        }

        //每个直播间的支付订单
        $payData = [];
        foreach ($payOrder as $payOrderItem) {
            $payData[$payOrderItem['scene_value']][] = $payOrderItem;
        }

        //每个直播间的所有订单
        $allData = [];
        foreach ((array)$order as $orderItem) {
            $allData[$orderItem['scene_value']][] = $orderItem;
        }

        //所有直播间
        $room = BroadcastRoomModel::find()->indexBy('id')->asArray()->all();

        $insertData = [];
        foreach ($room as $roomIndex => $roomItem) {

            //店铺pay order
            $roomPayOrder = isset($payData[$roomIndex]) ? $payData[$roomIndex] : [];

            //店铺order
            $roomAllData = isset($allData[$roomIndex]) ? $allData[$roomIndex] : [];

            //如果不存在订单则添加默认值
            if (empty($roomAllData)) {
                $insertData[] = [
                    'date' => $date,
                    'room_id' => $roomIndex,
                    'order_pay_count' => 0,
                    'order_pay_price' => 0,
                    'order_member_count' => 0,
                    'order_count' => 0
                ];
                continue;
            }

            $insertData[] = [
                'date' => $date,
                'room_id' => $roomIndex,
                'order_pay_count' => count($roomPayOrder),//支付订单数
                'order_pay_price' => array_sum(array_column($roomPayOrder, 'pay_price')),//支付金额
                'order_member_count' => count(array_unique(array_column($roomPayOrder, 'member_id'))),//付款会员
                'order_count' => count($roomAllData),//订单数
            ];
        }

        if (empty($insertData)) {
            return true;
        }

        return self::batchInsert(array_keys($insertData[0]), $insertData);
    }

    /**
     * 获取统计，如果没有直播间id则返回全部
     * @param int $roomId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStatistics(int $roomId = 0)
    {
        $model = BroadcastStatisticsModel::find();

        if ($roomId != 0) {
            $model->andWhere(['room_id' => $roomId]);
        }

        $data = $model->asArray()
            ->orderBy([
                'date' => SORT_DESC
            ])
            ->all();

        $newData = [];
        foreach ($data as $item) {
            $newData['order_pay_count'] += $item['order_pay_count'];
            $newData['order_pay_price'] += $item['order_pay_price'];
            $newData['order_member_count'] += $item['order_member_count'];
            $newData['order_count'] += $item['order_count'];
        }

        return $newData;
    }

}
