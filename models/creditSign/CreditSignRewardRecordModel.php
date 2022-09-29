<?php

namespace shopstar\models\creditSign;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "shopstar_credit_sign_reward_record".
 *
 * @property int $id
 * @property int $member_id 用户ID
 * @property int $activity_id 活动ID
 * @property int $sign_id 签到记录ID
 * @property int $type 奖励类型 0日常 1连续 2递增
 * @property int $status 领取状态 0未领取 1已领取
 * @property int $credit_num 奖励积分
 * @property int $coupon_num 优惠券领取数量
 * @property string $content 奖励内容
 * @property string $created_at 领取时间
 * @property int $is_deleted 重置状态 0未重置 1已重置
 * @property int $continuity_day 连续签到天数(冗余字段)
 */
class CreditSignRewardRecordModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_sign_reward_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['member_id', 'activity_id', 'sign_id', 'type', 'status', 'credit_num', 'coupon_num', 'is_deleted', 'continuity_day'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'activity_id' => 'Activity ID',
            'sign_id' => 'Sign ID',
            'type' => 'Type',
            'status' => 'Status',
            'credit_num' => 'Credit Num',
            'coupon_num' => 'Coupon Num',
            'content' => 'Content',
            'created_at' => 'Created At',
            'is_deleted' => 'Is Deleted',
            'continuity_day' => 'Continuity Day',
        ];
    }

    /**
     * 添加奖励记录
     * @param int $memberId
     * @param array $options
     * @return bool
     * @author yuning
     */
    public static function saveRewardRecord(int $memberId, array $options): bool
    {
        $data = [
            'member_id' => $memberId,
            'activity_id' => $options['activity_id'],
            'sign_id' => $options['sign_id'],
            'type' => $options['type'],
            'status' => $options['status'],
            'credit_num' => $options['credit_num'],
            'coupon_num' => $options['coupon_num'],
            'content' => $options['content'],
            'create_time' => DateTimeHelper::now(),
            'is_deleted' => 0,
        ];

        $result = new static;
        $result->setAttributes($data);

        return $result->save();
    }

}