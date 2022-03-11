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

use shopstar\constants\ClientTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%activity_statistics}}".
 *
 * @property int $id
 * @property int $activity_id 活动id
 * @property string $activity_type 活动标识
 * @property string $date 日期
 * @property string $created_at 创建时间
 * @property string $pay_price_sum 累计成交额
 * @property int $order_count 累计订单数量
 * @property int $goods_pv_count 活动浏览量
 * @property int $sales_goods_total 商品销售数量
 * @property int $member_pv_count 访客量
 * @property string $wechat_order_price_sum 微信渠道订单金额
 * @property string $wxapp_order_price_sum 微信小程序渠道订单金额
 * @property string $h5_order_price_sum h5渠道订单金额
 * @property string $byte_dance_order_price_sum 字节跳动小程序渠道订单金额
 * @property string $refund_price_sum 累计退款数量
 * @property string $pay_member_count 累计支付人数
 */
class MarketingStatisticsModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%marketring_statistics}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'activity_id', 'order_count', 'goods_pv_count', 'sales_goods_total', 'member_pv_count', 'pay_member_count'], 'integer'],
            [['date', 'created_at'], 'safe'],
            [['pay_price_sum', 'wechat_order_price_sum', 'wxapp_order_price_sum', 'h5_order_price_sum', 'byte_dance_order_price_sum', 'refund_price_sum'], 'number'],
            [['activity_type'], 'string', 'max' => 20],
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
            'activity_type' => '活动标识',
            'date' => '日期',
            'created_at' => '创建时间',
            'pay_price_sum' => '累计成交额',
            'order_count' => '累计订单数量',
            'goods_pv_count' => '活动浏览量',
            'sales_goods_total' => '商品销售数量',
            'member_pv_count' => '访客量',
            'wechat_order_price_sum' => '微信渠道订单金额',
            'wxapp_order_price_sum' => '微信小程序渠道订单金额',
            'h5_order_price_sum' => 'h5渠道订单金额',
            'byte_dance_order_price_sum' => '字节跳动小程序渠道订单金额',
            'refund_price_sum' => '累计退款数量',
            'pay_member_count' => '累计支付人数',
        ];
    }

    /**
     * 订单上的activity_type 和 活动的type对应
     * @var int[]
     */
    public static $typeMap = [
        'seckill' => OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL,
        'groups' => OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS,
    ];

    /**
     * 每日统计
     * 定时任务调用
     * @param string $activityType
     * @return bool|string
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function createDayStatistic(string $activityType)
    {
        // 查找是否统计过
        $date = date('Y-m-d', strtotime('-1 day'));
        $isCalculate = self::find()->where(['date' => $date, 'activity_type' => $activityType])->exists();
        if ($isCalculate) {
            return $date . ' 该日期已统计';
        }
        // 统计
        self::calculate($date, $activityType);

        return true;
    }

    /**
     * 统计
     * @param string $date
     * @param string $activityType 活动表示 seckill 秒杀
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculate(string $date, string $activityType)
    {
        // 订单活动类型
        $orderActivityType = self::$typeMap[$activityType];
        $order = OrderModel::find()
            ->select([
                'order.id',
                'order.pay_price', // 支付金额
                'order.status', // 订单状态
                'order.pay_type', // 支付方式
                'order.member_id', // 用户id
                'order.create_from', // 订单来源
                'order_activity.activity_id', // 活动id
                'order.refund_price', // 维权金额
                'order.goods_info', // 维权金额
            ])
            ->alias('order')
            ->innerJoin(OrderActivityModel::tableName() . ' order_activity', 'order.id=order_activity.order_id')
            ->where(['order.activity_type' => $orderActivityType])
            ->andWhere(['between', 'order.created_at', $date . ' 00:00:00', $date . ' 23:59:59']) // 这样过滤有问题,隔天支付或维权的统计不到 TODO 青岛开店星信息技术有限公司 预售统计也是
            ->get();
        // 每个活动的订单分组
        $activityOrder = [];
        foreach ($order as $item) {
            $activityOrder[$item['activity_id']][] = $item;
        }
        // 查找所有昨天正在进行中的活动
        $activityList = ShopMarketingModel::find()
            ->where([
                'and',
                ['type' => $activityType],
                ['<', 'start_time', $date . ' 23:59:59'],
                [
                    'or',
                    ['status' => 0], // 正在进行中
                    ['>', 'stop_time', $date . ' 00:00:00'] // 结束时间在当天
                ]
            ])
            ->indexBy('id')
            ->get();
        // 插入字段
        $insertFields = [
            'date', // 统计日期
            'activity_id', // 活动id
            'activity_type', // 活动类型
            'pay_price_sum', // 支付金额
            'order_count', // 支付订单数量
            'goods_pv_count', // 浏览量
            'sales_goods_total', // 销售量
            'member_pv_count', // 访客量
            'wechat_order_price_sum', // 公众号订单金额
            'wxapp_order_price_sum', // 微信小程序订单金额
            'h5_order_price_sum', // h5 订单金额
            'byte_dance_order_price_sum', // 字节跳动金额
            'refund_price_sum', // 维权金额
            'pay_member_count', // 支付人数
        ];
        foreach ($activityList as $activityId => $activity) {
            // 每个活动的数据
            $data = [
                1 => $date,
                2 => $activityId, // 活动id
                3 => $activityType, // 活动类型
                4 => 0, // 支付金额
                5 => 0, // 支付订单数量
                6 => 0, // 浏览量
                7 => 0, // 销售量
                8 => 0, // 访客量
                9 => 0, // 公众号订单金额
                10 => 0, // 微信小程序订单金额
                11 => 0, // h5 订单金额
                12 => 0, // 字节跳动金额
                13 => 0, // 维权金额
                14 => 0, // 支付人数
            ];
            // 遍历订单
            if (!empty($activityOrder[$activityId])) {
                $payMember = []; // 支付会员id
                $channelPrice = []; // 各个渠道的支付金额
                foreach ($activityOrder[$activityId] as $orderItem) {
                    if ($orderItem['pay_type'] != 0) {
                        $payMember[] = $orderItem['member_id'];
                        $data[4] += $orderItem['pay_price']; // 支付金额
                        $data[5] += 1; // 支付订单
                        $goodsInfo = Json::decode($orderItem['goods_info']); // 商品信息
                        $data[7] += $goodsInfo[0]['total']; // 销量
                        $data[13] += $orderItem['refund_price']; // 维权金额
                        $channelPrice[$orderItem['create_from']] += $orderItem['pay_price']; // 渠道价格
                    }
                }
                $data[9] = $channelPrice[ClientTypeConstant::CLIENT_WECHAT] ?? 0; // 微信渠道支付金额
                $data[10] = $channelPrice[ClientTypeConstant::CLIENT_WXAPP] ?? 0; // 小程序渠道支付金额
                $data[11] = $channelPrice[ClientTypeConstant::CLIENT_H5] ?? 0; // h5 渠道支付金额
                $data[12] = ($channelPrice[ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO] + $channelPrice[ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN] + $channelPrice[ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE]) ?? 0; // 字节跳动渠道支付金额
                $data[14] = count(array_unique($payMember)); // 支付人数
            }
            // 预售商品总浏览量
            $data[6] = MarketingViewLogModel::find()
                ->where(['activity_id' => $activityId, 'activity_type' => $activityType])
                ->andWhere(['between', 'created_at', $date . ' 00:00:00', $date . ' 23:59:59'])
                ->count();

            // 用户浏览量统计
            $data[8] = MarketingViewLogModel::find()
                ->where(['activity_id' => $activityId, 'activity_type' => $activityType])
                ->andWhere(['between', 'created_at', $date . ' 00:00:00', $date . ' 23:59:59'])
                ->count('distinct(member_id)');
            $insertData[] = $data;
        }
        if (!empty($insertData)) {
            self::batchInsert($insertFields, $insertData);
        }

        return true;
    }

    /**
     * 获取曝光量
     * @param string $startDate
     * @param string $endDate
     * @param int $activityId 活动id
     * @param string $activityType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getView(string $startDate, string $endDate, int $activityId, string $activityType)
    {
        // 时间范围
        $dateRange = DateTimeHelper::getDateRange($startDate, $endDate);
        // 折线图数据
        $lineChartData = [];
        $data = [
            'goods_pv_count' => 0, // 浏览量
            'member_pv_count' => 0 // 访客数
        ];

        $query = MarketingStatisticsModel::find()
            ->select([
                'date',
                'goods_pv_count',
                'member_pv_count',
            ])
            ->where(['between', 'date', $startDate, $endDate])
            ->andWhere(['activity_type' => $activityType]);

        // 活动
        if (!empty($activityId)) {
            $query->andWhere(['activity_id' => $activityId]);
        }
        // 查询
        $statistics = $query->get();
        // x轴
        foreach ($dateRange as $date) {
            $lineChartData[$date] = $data;
        }

        foreach ($statistics as $index => $item) {
            $date = $item['date'];
            $key = date('Y-m-d', strtotime($date));
            $lineChartData[$key]['goods_pv_count'] += $item['goods_pv_count']; // 浏览量
            $lineChartData[$key]['member_pv_count'] += $item['member_pv_count']; // 访客数
        }

        return $lineChartData;
    }

}