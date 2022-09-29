<?php

namespace shopstar\models\creditSign;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "shopstar_credit_sign_total".
 *
 * @property int $id
 * @property int $member_id 会员ID
 * @property int $activity_id 活动ID
 * @property int $sign_days 签到天数
 * @property int $continuity_days 连签天数
 * @property int $increasing_days 递增签到天数
 * @property int $longest_days 最长连续签到天数
 * @property string $current_date 最近签到时间
 * @property string $last_date 上次签到时间
 * @property string $created_at 添加时间
 */
class CreditSignTotalModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_sign_total}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['member_id', 'activity_id', 'sign_days', 'continuity_days', 'increasing_days', 'longest_days'], 'integer'],
            [['current_date', 'last_date', 'created_at'], 'safe'],
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
            'sign_days' => 'Sign Days',
            'continuity_days' => 'Continuity Days',
            'increasing_days' => 'Increasing Days',
            'longest_days' => 'Longest Days',
            'current_date' => 'Current Date',
            'last_date' => 'Last Date',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 保存统计数据
     * @param int $memberId
     * @param int $activityId
     * @param int $id
     * @param array $data
     * @return bool
     * @author yuning
     */
    public static function saveTotal(int $memberId, int $activityId, int $id = 0, array $data): bool
    {
        $data = [
            'member_id' => $memberId,
            'activity_id' => $activityId,
            'sign_days' => $data['sign_days'],
            'continuity_days' => $data['continuity_days'],
            'increasing_days' => $data['increasing_days'],
            'longest_days' => $data['longest_days'],
            'current_date' => $data['current_date'],
            'last_date' => $data['last_date'],
        ];

        if (empty($id)) {
            $data['create_time'] = DateTimeHelper::now(false);
            $model = new static();
        } else {
            $model = self::findOne([
                'member_id' => $memberId,
                'activity_id' => $activityId,
                'id' => $id
            ]);
        }

        $model->setAttributes($data);

        return $model->save();
    }

    /**
     * 获取用户在活动中的数据
     * @param int $memberId
     * @param int $activityId
     * @param string $select
     * @return array
     * @author yuning
     */
    public static function getActivityMemberTotal(int $memberId, int $activityId, string $select = '*'): array
    {
        return CreditSignTotalModel::find()->where([
            'member_id' => $memberId,
            'activity_id' => $activityId,
        ])->select($select)->first() ?: [];
    }

}