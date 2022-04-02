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

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;

use yii\helpers\Json;


/**
 * This is the model class for table "{{%recharge_reward_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $activity_id 活动id
 * @property int $type 充值类型  0累计  1单次
 * @property int $client_type 渠道
 * @property string $reward 奖励内容
 * @property string $recharge_money 充值金额
 * @property string $created_at 充值时间
 * @property int $log_id 充值记录id
 */
class RechargeRewardLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'activity_id', 'type', 'client_type', 'log_id'], 'integer'],
            [['recharge_money'], 'number'],
            [['created_at'], 'safe'],
            [['reward'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'activity_id' => '活动id',
            'type' => '充值类型  0累计  1单次',
            'client_type' => '渠道',
            'reward' => '奖励内容',
            'recharge_money' => '充值金额',
            'created_at' => '充值时间',
            'log_id' => '充值记录id',
        ];
    }

    /**
     * 充值送礼
     * @param int $memberId
     * @param int $orderId
     * @param int $clientType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendReward(int $memberId, int $orderId, int $clientType)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }

        $activity = RechargeRewardActivityModel::getOpenActivity($clientType, $memberId);
        if (is_error($activity)) {
            return $activity;
        }

        $activity['rules']['award'] = array_column($activity['rules']['award'], null, 'money');
        krsort($activity['rules']['award']);

        // 查找订单
        $log = MemberLogModel::find()
            ->where(['member_id' => $memberId, 'id' => $orderId])
            ->andWhere(['<>', 'pay_type', 10])
            ->first();

        // 累计
        if ($activity['type'] == 0) {

            // 查找记录 只能参加一次
            $isExists = RechargeRewardLogModel::find()
                ->where(['activity_id' => $activity['id'], 'member_id' => $memberId])
                ->exists();

            if ($isExists) {
                return error('已参与过活动');
            }

            $rechargeBalance = MemberLogModel::find()
                ->where(['member_id' => $memberId, 'type' => 1, 'status' => 10])
                ->andWhere(['<>', 'pay_type', 10])
                ->andWhere(['>=', 'created_at', $activity['start_time']])
                ->sum('money');

            $rewardArray = [];
            foreach ($activity['rules']['award'] as $item) {
                if ($rechargeBalance >= $item['money']) {
                    $rewardArray = $item;
                    break;
                }
            }

            if (empty($rewardArray)) {
                return error('不满足条件');
            }

        } else {

            // 单次
            // 检查记录  一个订单只送一次
            $isExists = RechargeRewardLogModel::find()
                ->where(['activity_id' => $activity['id'], 'member_id' => $memberId, 'log_id' => $orderId])
                ->exists();
            if ($isExists) {
                return error('已参与过活动');
            }

            if (empty($log)) {
                return error('订单不存在');
            }
            // 状态错误
            if ($log['status'] != 10) {
                return error('充值状态错误');
            }

            $rewardArray = [];
            foreach ($activity['rules']['award'] as $item) {
                if ($log['money'] >= $item['money']) {
                    $rewardArray = $item;
                    break;
                }
            }

            if (empty($rewardArray)) {
                return error('不满足条件');
            }
        }


        $sendReward = [];
        // 发送奖励
        foreach ($rewardArray['reward'] as $gift) {
            // 优惠券
            if ($gift == 1) {

                if (!is_array($rewardArray['coupon_ids_array'])) {
                    $rewardArray['coupon_ids_array'] = explode(',', $rewardArray['coupon_ids']);
                }

                $res = CouponModel::activitySendCoupon($memberId, $rewardArray['coupon_ids_array']);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward'][1]);
                }
                $sendReward['coupon_ids'] = $rewardArray['coupon_ids'];
            } else if ($gift == 2) {
                // 积分
                $res = MemberModel::updateCredit($memberId, $rewardArray['credit'], 0, 'credit', 1, '充值奖励', MemberCreditRecordStatusConstant::RECHARGE_REWARD_SEND_CREDIT);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward'][2]);
                }
                $sendReward['credit'] = $rewardArray['credit'];
            } else if ($gift == 3) {
                // 余额
                $res = MemberModel::updateCredit($memberId, $rewardArray['balance'], 0, 'balance', 1, '充值奖励', MemberCreditRecordStatusConstant::RECHARGE_REWARD_SEND_BALANCE);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward'][2]);
                }
                $sendReward['balance'] = $rewardArray['balance'];
            }
        }

        // 如果活动为空
        if (empty($rewardArray['reward'])) {
            return error('发送失败');
        }

        // 记录log
        $rewardLog = new self();
        $rewardLog->member_id = $memberId;
        $rewardLog->type = $activity['type'];
        $rewardLog->client_type = $clientType;
        $rewardLog->reward = Json::encode($sendReward);
        $rewardLog->activity_id = $activity['id'];
        $rewardLog->log_id = $log['id'];
        if (!$rewardLog->save()) {
            return error('记录保存失败');
        }
        // 发送记录 +1
        RechargeRewardActivityModel::updateAllCounters(['send_count' => 1], ['id' => $activity['id']]);

        return $activity;
    }
}