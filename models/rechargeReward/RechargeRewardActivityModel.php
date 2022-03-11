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

namespace shopstar\models\rechargeReward;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;

use yii\helpers\Json;


/**
 * This is the model class for table "{{%app_recharge_reward_activity}}".
 *
 * @property int $id
 * @property string $title 活动名称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $client_type 活动渠道
 * @property int $type 充值类型  0累计 1单次
 * @property string $money 充值金额
 * @property string $reward 奖励 1优惠券 2积分 3 余额
 * @property string $coupon_ids 优惠券id
 * @property int $credit 积分
 * @property string $balance 余额
 * @property int $is_deleted 是否删除
 * @property string $stop_time 停止时间
 * @property int $job_id 任务id
 * @property string $created_at 创建时间
 * @property int $send_count 参与人数
 * @property int $status 状态 0 默认状态  -1停止  -2手动停止
 * @property int $rules 规则
 */
class RechargeRewardActivityModel extends \shopstar\bases\model\BaseActiveRecord
{
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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_recharge_reward_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'credit', 'is_deleted', 'job_id', 'send_count', 'status'], 'integer'],
            [['start_time', 'end_time', 'stop_time', 'created_at'], 'safe'],
            [['money', 'balance'], 'number'],
            [['title'], 'string', 'max' => 100],
            [['client_type', 'coupon_ids'], 'string', 'max' => 50],
            [['reward'], 'string'],
            [['rules'], 'string'],
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
            'client_type' => '活动渠道',
            'type' => '充值类型  0累计 1单次',
            'money' => '充值金额',
            'reward' => '奖励 1优惠券 2积分 3 余额',
            'coupon_ids' => '优惠券id',
            'credit' => '积分',
            'balance' => '余额',
            'is_deleted' => '是否删除',
            'stop_time' => '停止时间',
            'job_id' => '任务id',
            'created_at' => '创建时间',
            'send_count' => '参与人数',
            'status' => '状态 0 默认状态  -1停止  -2手动停止',
            'order_id' => '订单id',
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
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenActivity(int $clientType, int $memberId)
    {
        $activity = RechargeRewardActivityModel::find()
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

        if ($activity['rules']) {
            $activity['rules'] = Json::decode($activity['rules']);
            foreach ($activity['rules']['award'] as &$rule) {
                if (empty($rule['coupon_ids'])) {
                    continue;
                }

                $rule['coupon_info'] = CouponModel::getCouponInfo(explode(',', $rule['coupon_ids']));
            }

        }

        //判断用户权限是否允许参与
        if ($activity['rules']['permission'] != 0) {

            //获取会员信息
            $member = MemberModel::where([
                'id' => $memberId,
            ])->select([
                'id',
                'level_id'
            ])->first();

            if ($activity['rules']['permission'] == 1 && !in_array($member['level_id'], $activity['rules']['permission_value'] ?? [])) {
                return error('没有权限');
            }

            if ($activity['rules']['permission'] == 2) {
                $memberTag = MemberGroupMapModel::where([
                    'member_id' => $memberId,
                ])->select([
                    'group_id'
                ])->column();

                //如果没有会员标签则没有权限
                if (empty($memberTag)) return error('没有权限');

                //如果没有交集则没有权限
                if (!array_intersect($memberTag, $activity['rules']['permission_value'])) return error('没有权限');
            }

        }

        return $activity;
    }


}