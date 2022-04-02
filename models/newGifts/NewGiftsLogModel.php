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

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\newGifts\ActivityConstant;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%new_gifts_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $pick_type 领取类型 0 无消费记录  1 新用户
 * @property int $client_type 领取来源 渠道
 * @property string $gifts 奖励
 * @property int $activity_id 活动id
 * @property string $created_at 领取时间
 */
class NewGiftsLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%new_gifts_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'pick_type', 'client_type', 'activity_id'], 'integer'],
            [['created_at'], 'safe'],
            [['gifts'], 'string', 'max' => 100],
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
            'pick_type' => '领取类型 0 无消费记录  1 新用户',
            'client_type' => '领取来源 渠道',
            'gifts' => '奖励',
            'activity_id' => '活动id',
            'created_at' => '领取时间',
        ];
    }

    /**
     * 发送新人礼
     * @param int $memberId
     * @param array $activity
     * @param int $clientType
     * @return bool|array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendGifts(int $memberId, array &$activity, int $clientType)
    {
        $sendGifts = [];
        // 发送奖励
        foreach ($activity['gifts_array'] as $gift) {
            // 优惠券
            if ($gift == ActivityConstant::ACTIVITY_SEND_COUPON) {
                $res = CouponModel::activitySendCoupon($memberId, $activity['coupon_ids_array']);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['gifts_array'][ActivityConstant::ACTIVITY_SEND_COUPON]);
                }
                $sendGifts['coupon_ids'] = implode(',', $activity['coupon_ids_array']);
            } else if ($gift == ActivityConstant::ACTIVITY_SEND_CREDIT) {
                // 积分
                $res = MemberModel::updateCredit($memberId, $activity['credit'], 0, 'credit', 1, '新人送礼', MemberCreditRecordStatusConstant::NEW_MEMBER_SEND_CREDIT);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['gifts_array'][ActivityConstant::ACTIVITY_SEND_CREDIT]);
                }
                $sendGifts['credit'] = $activity['credit'];
            } else if ($gift == ActivityConstant::ACTIVITY_SEND_BALANCE) {
                // 余额
                $res = MemberModel::updateCredit($memberId, $activity['balance'], 0, 'balance', 1, '新人送礼', MemberCreditRecordStatusConstant::NEW_MEMBER_SEND_BALANCE);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['gifts_array'][ActivityConstant::ACTIVITY_SEND_BALANCE]);
                }
                $sendGifts['balance'] = $activity['balance'];
            }
        }
        // 如果活动为空
        if (empty($activity['gifts_array'])) {
            return error('发送失败');
        }

        // 记录log
        $log = new self();
        $log->member_id = $memberId;
        $log->pick_type = $activity['pick_type'];
        $log->client_type = $clientType;
        $log->gifts = Json::encode($sendGifts);
        $log->activity_id = $activity['id'];
        if (!$log->save()) {
            return error('记录保存失败');
        }
        // 发送记录 +1
        NewGiftsActivityModel::updateAllCounters(['send_count' => 1], ['id' => $activity['id']]);


        return $activity;
    }
}