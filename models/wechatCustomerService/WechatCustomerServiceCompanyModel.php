<?php

namespace shopstar\models\wechatCustomerService;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "wechat_customer_service_company".
 *
 * @property int $id
 * @property string $corp_id 企业ID
 * @property string $name 企业名称
 * @property int $is_deleted 是否删除 0:未删除, 1:已删除
 * @property string $created_at
 * @property string $updated_at
 */
class WechatCustomerServiceCompanyModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_customer_service_company}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['corp_id'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corp_id' => 'Corp ID',
            'name' => 'Name',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'corp_id' => '企业id',
            'name' => '企业名称',
        ];
    }
}
