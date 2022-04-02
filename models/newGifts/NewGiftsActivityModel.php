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

namespace shopstar\models\newGifts;

use shopstar\constants\newGifts\ActivityConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\sale\CouponModel;

/**
 * 新人送礼活动
 * Class NewGiftsModel
 * @package apps\newGifts\model
 */
class NewGiftsActivityModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * 领取类型文字
     * @var string[]
     */
    public static $pickTypeText = [
        '0' => '无消费记录',
        '1' => '新注册用户',
    ];

    public static $giftsText = [
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
        return '{{%new_gifts_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'pick_type', 'credit', 'job_id', 'send_count', 'is_deleted', 'popup_type'], 'integer'],
            [['start_time', 'end_time', 'stop_time', 'created_at'], 'safe'],
            [['balance'], 'number'],
            [['title', 'gifts', 'coupon_ids', 'client_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'title' => '活动名称',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'stop_time' => '活动停止时间',
            'status' => '活动状态 0默认状态  未开始或进行中   -1 自动停止 -2 手动停止',
            'pick_type' => '领取条件  0 无消费记录 1 新注册',
            'gifts' => '优惠奖励  1 优惠券 2 积分 3 余额',
            'coupon_ids' => '优惠券',
            'credit' => '积分奖励',
            'balance' => '余额奖励',
            'job_id' => '任务id 手动停止时用',
            'created_at' => '创建时间',
            'send_count' => '已发放人数',
            'client_type' => '渠道设置 10 h5  20公众号  21微信小程序',
            'is_deleted' => '是否删除',
            'popup_type' => '弹窗样式 0样式一（默认样式）1样式二'
        ];
    }

    /**
     * 检查时间段内是否有任务存在
     * @param string $startTime 任务开始时间
     * @param string $endTime 任务结束时间
     * @param int $exceptId 排除的任务id
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
     * 获取当前开启的活动
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenActivity(int $clientType)
    {
        $activity = self::find()
            ->select('id, title, gifts, coupon_ids, credit, balance, pick_type, client_type, start_time, end_time, popup_type')
            ->where(['status' => 0, 'is_deleted' => 0])
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

        // 判断活动是否可用
        $activity['gifts_array'] = explode(',', $activity['gifts']);
        // 如果送 优惠券  检查优惠券是否存在
        if (in_array(ActivityConstant::ACTIVITY_SEND_COUPON, $activity['gifts_array'])) {
            $activity['coupon_ids_array'] = explode(',', $activity['coupon_ids']);
            $coupons = CouponModel::getCouponInfo($activity['coupon_ids_array']);
            // 重置
            $activity['coupon_ids_array'] = [];
            foreach ($coupons as $index => $coupon) {
                if ($coupon['stock_type'] == 1 && $coupon['stock'] - $coupon['get_total'] <= 0) {
                    unset($coupons[$index]);
                } else {
                    $activity['coupon_ids_array'][] = $coupon['id'];
                }
            }
            if (!empty($coupons)) {
                $activity['coupon_info'] = array_values($coupons);
            } else {
                // 如果只有优惠券奖励 且 优惠券为空 返回无活动
                if (count($activity['gifts_array']) == 1) {
                    return error('无活动');
                }
            }
        }

        return $activity;

    }
}