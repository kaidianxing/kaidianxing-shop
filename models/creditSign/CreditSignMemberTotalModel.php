<?php

namespace shopstar\models\creditSign;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\DateTimeHelper;


/**
 * This is the model class for table "shopstar_credit_sign_member_total".
 *
 * @property int $id
 * @property int $member_id 会员ID
 * @property string $first_date 首次签到时间
 * @property string $last_date 上次签到时间
 * @property int $sign_day 总签到天数
 * @property int $continuity_day 连续签到天数
 * @property int $longest_day 最长连续签到天数
 * @property string $created_at 添加时间
 * @property int $is_remind 是否开启签到提醒
 */
class CreditSignMemberTotalModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_sign_member_total}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['member_id', 'sign_day', 'continuity_day', 'longest_day', 'is_remind'], 'integer'],
            [['first_date', 'last_date', 'created_at'], 'safe'],
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
            'first_date' => 'First Date',
            'last_date' => 'Last Date',
            'sign_day' => 'Sign Day',
            'continuity_day' => 'Continuity Day',
            'longest_day' => 'Longest Day',
            'created_at' => 'Created At',
            'is_remind' => 'Is Remind',
        ];
    }

    /**
     * 获取用户统计数据
     * @param int $memberId
     * @param string $select
     * @return array
     * @author yuning
     */
    public static function getMemberTotalInfo(int $memberId, string $select = '*'): array
    {
        return self::find()->where([
            'member_id' => $memberId,
        ])->select($select)->first() ?: [];
    }

    /**
     * 保存会员统计数据
     * @param int $memberId
     * @param int $id
     * @param array $params
     * @return bool
     * @author yuning
     */
    public static function saveMemberTotal(int $memberId, int $id = 0, array $params): bool
    {
        $data = [
            'member_id' => $memberId,
            'first_date' => $params['first_date'],
            'last_date' => $params['last_date'],
            'sign_day' => $params['sign_day'],
            'continuity_day' => $params['continuity_day'],
            'longest_day' => $params['longest_day'],
            'is_remind' => $params['is_remind'],
        ];
        if (empty($id)) {
            $data['create_time'] = DateTimeHelper::now();
            $model = new static();
        } else {
            $model = self::findOne([
                'member_id' => $memberId,
                'id' => $id
            ]);
        }

        $model->setAttributes($data);

        return $model->save();
    }

}