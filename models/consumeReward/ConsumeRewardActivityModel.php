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

namespace shopstar\models\consumeReward;


use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%consume_reward_activity}}".
 *
 * @property int $id
 * @property string $title 活动名称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $status 活动状态 0 未开始或开始  -1 自动停止  -2 手动停止
 * @property string $pay_type 支付方式 20 微信支付  30 支付宝
 * @property int $type 消费类型 0累计消费 1 单次消费
 * @property string $money 消费金额
 * @property string $reward 奖励设置
 * @property string $coupon_ids 优惠券id
 * @property int $credit 积分
 * @property string $balance 余额
 * @property string $red_package 红包
 * @property int $send_type 赠送时间  0 订单完成后  1订单付款后
 * @property string $activity_limit 活动限制 1使用优惠券不能参与  2参与满额立减不能参与
 * @property string $goods_limit 不参与活动的商品
 * @property int $job_id 任务队列id
 * @property string $created_at 创建时间
 * @property int $send_count 领取人数
 * @property int $is_deleted 是否删除
 * @property int $is_repeat 是否可重复参加(单次消费) 0 否 1可以
 * @property string $stop_time 停止时间
 * @property string $client_type 渠道限制
 * @property string $rules 规则
 * @property int $popup_type 弹窗样式 0样式一（默认样式）1样式二
 */
class ConsumeRewardActivityModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * 活动限制
     * var string[]
     */
    public static $activityLimit = [
        '1' => '使用优惠券不能参与',
        '2' => '参与满额立减不能参与',
    ];

    /**
     * 支付类型
     * @var string[]
     */
    public static $payType = [
        '20' => '微信支付',
        '2' => '余额支付',
        '30' => '支付宝'
    ];

    /**
     * 客户端类型
     * @var string[]
     */
    public static $clientType = [
        '10' => 'H5',
        '20' => '微信公众号',
        '21' => '微信小程序',
        '30' => '头条/抖音小程序',
    ];

    /**
     * 奖励类型
     * @var string[]
     */
    public static $rewardText = [
        '1' => '优惠券',
        '2' => '积分',
        '3' => '余额'
    ];

    /**
     * 弹框样式
     * @var string[]
     */
    public static $popupType = [
        '0' => '样式一',
        '1' => '样式二'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%consume_reward_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'type', 'credit', 'send_type', 'job_id', 'send_count', 'is_deleted', 'is_repeat', 'popup_type'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'stop_time'], 'safe'],
            [['money', 'balance'], 'number'],
            [['goods_limit', 'rules'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['red_package'], 'string', 'max' => 255],
            [['reward'], 'string'],
            [['pay_type', 'coupon_ids', 'activity_limit', 'client_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '活动名称',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'status' => '活动状态 0 未开始或开始  -1 自动停止  -2 手动停止',
            'pay_type' => '支付方式 10 微信支付  20 支付宝',
            'type' => '消费类型 0累计消费 1 单次消费',
            'money' => '消费金额',
            'reward' => '奖励设置',
            'coupon_ids' => '优惠券id',
            'credit' => '积分',
            'balance' => '余额',
            'red_package' => '红包',
            'send_type' => '赠送时间  0 订单完成后  1订单付款后',
            'activity_limit' => '活动限制 1使用优惠券不能参与  2参与满额立减不能参与',
            'goods_limit' => '不参与活动的商品',
            'job_id' => '任务队列id',
            'created_at' => '创建时间',
            'send_count' => '领取人数',
            'is_deleted' => '是否删除',
            'is_repeat' => '是否可重复参加(单次消费) 0 否 1可以',
            'stop_time' => '停止时间',
            'client_type' => '渠道限制',
            'rules' => '规则',
            'popup_type' => '弹窗样式 0样式一（默认样式）1样式二'
        ];
    }

    /**
     * 检查时间段内是否有活动
     * @param string $startTime
     * @param string $endTime
     * @param int $exceptId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkExistsByTime(string $startTime, string $endTime, int $exceptId = 0)
    {
        $where = [
            'and',
            ['is_deleted' => 0],
            ['stop_time' => 0],
            [
                'or', // 进行中/未开始/自动停止 或 手动停止
                [
                    'and', // 进行中/未开始/自动停止
                    [
                        'or',
                        ['status' => 0],
                        ['status' => -1],
                    ],
                    [
                        'or',
                        [ // 开始时间不能在时间段内
                            'and',
                            ['<=', 'start_time', $startTime],
                            ['>=', 'end_time', $startTime],
                        ],
                        [ // 结束时间不能在时间段内
                            'and',
                            ['<=', 'start_time', $endTime],
                            ['>=', 'end_time', $endTime],
                        ],
                        [ // 开始时间比现有小  结束时间比现有大
                            'and',
                            ['>=', 'start_time', $startTime],
                            ['<=', 'end_time', $endTime]
                        ]
                    ]
                ],
                [
                    'and',
                    ['status' => -2], // 手动停止
                    [
                        'or',
                        [ // 开始时间不能在 已创建的开始时间 和停止时间内
                            'and',
                            ['<=', 'start_time', $startTime],
                            ['>=', 'stop_time', $startTime],
                        ],
                        [ // 结束时间不能在 已创建的开始时间 和停止时间内
                            'and',
                            ['<=', 'start_time', $endTime],
                            ['>=', 'stop_time', $endTime],
                        ],
                        [ // 开始时间比现有小  结束时间比现有大
                            'and',
                            ['>=', 'start_time', $startTime],
                            ['<=', 'stop_time', $endTime],
                        ]
                    ]
                ]
            ]
        ];
        if (!empty($exceptId)) {
            $where[] = ['<>', 'id', $exceptId];
        }

        return self::find()->where($where)->exists();
    }

    /**
     * 获取开启的活动
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenActivity(int $clientType)
    {
        $activity = self::find()->where(['status' => 0, 'is_deleted' => 0])
            ->andWhere(['<', 'start_time', DateTimeHelper::now()])
            ->andWhere(['>', 'end_time', DateTimeHelper::now()])
            ->first();

        if (empty($activity)) {
            return error('无活动');
        }
        $activityClientType = explode(',', $activity['client_type']);
        if (!in_array($clientType, $activityClientType)) {
            return error('当前渠道不支持');
        }

        return $activity;
    }

}