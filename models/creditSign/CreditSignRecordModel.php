<?php

namespace shopstar\models\creditSign;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "shopstar_credit_sign_record".
 *
 * @property int $id
 * @property int $member_id 用户ID
 * @property int $activity_id 签到活动ID
 * @property string $sign_time 签到时间
 * @property int $status 签到类型：0正常 1补签
 * @property int $is_deleted 记录状态：0未重置 1已重置
 */
class CreditSignRecordModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_sign_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['member_id', 'activity_id', 'status', 'is_deleted'], 'integer'],
            [['sign_time'], 'safe'],
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
            'sign_time' => 'Sign Time',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
        ];
    }
}