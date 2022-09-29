<?php

namespace shopstar\models\creditSign;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\constants\creditSign\CreditSignRewardRecordConstant;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "shopstar_credit_sign".
 *
 * @property int $id 主键
 * @property int $job_id 队列任务ID
 * @property string $activity_name 活动名称
 * @property string $client_type 客户端类型
 * @property string $ext_field 活动规则等备用字段
 * @property int $status 活动状态：0未开始；1进行中；-1停止；-2手动停止；
 * @property string $start_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property string $stop_time 停止时间
 * @property string $created_at 新增时间
 * @property string $updated_at 更改时间
 * @property int $is_deleted 0未删除 1已删除
 */
class CreditSignModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_sign}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['job_id', 'status', 'is_deleted'], 'integer'],
            [['ext_field'], 'required'],
            [['ext_field'], 'string'],
            [['start_time', 'end_time', 'stop_time', 'created_at', 'updated_at'], 'safe'],
            [['activity_name'], 'string', 'max' => 25],
            [['client_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'activity_name' => 'Activity Name',
            'client_type' => 'Client Type',
            'ext_field' => 'Ext Field',
            'status' => 'Status',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'stop_time' => 'Stop Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * 获取签到次数
     * @return ActiveQuery
     * @author yuning
     */
    public function getSignCount(): ActiveQuery
    {
        return $this->hasMany(CreditSignRecordModel::class, [
            'activity_id' => 'id',
        ])->where([
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ]);
    }

    /**
     * 获取活动人数
     * @return ActiveQuery
     * @author yuning
     */
    public function getSignPersonCountList(): ActiveQuery
    {
        return $this->hasMany(CreditSignRecordModel::class, [
            'activity_id' => 'id',
        ])->where([
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ])->groupBy('member_id')->select('count(id) as count, activity_id');
    }

    /**
     * 获取签到奖励
     * @return ActiveQuery
     * @author yuning
     */
    public function getSignTotalNum(): ActiveQuery
    {
        return $this->hasMany(CreditSignRewardRecordModel::class, [
            'activity_id' => 'id',
        ])->where([
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
            'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_YES,
        ])->select('credit_num, coupon_num, activity_id');
    }
    
}