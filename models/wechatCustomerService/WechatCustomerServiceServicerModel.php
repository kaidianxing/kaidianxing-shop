<?php

namespace shopstar\models\wechatCustomerService;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "wechat_customer_service_servicer".
 *
 * @property int $id
 * @property string $company_id 企业ID
 * @property string $name 客服名称
 * @property string $link 客服链接
 * @property int $is_deleted 是否删除 0:未删除, 1:已删除
 * @property string $created_at
 * @property string $updated_at
 */
class WechatCustomerServiceServicerModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_customer_service_servicer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['company_id'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 50],
            [['link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'name' => 'Name',
            'link' => 'Link',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public function logAttributeLabels()
    {
        return [
            'company_name' => '企业名称',
            'name' => '客服名称',
            'link' => '客服链接',
        ];
    }



    /**
     * 获取微信数
     * @param int $companyId
     * @return int|string
     * @author yuning
     */
    public static function getCustomerServiceCount(int $companyId)
    {
        return self::find()->where([
            'company_id' => $companyId,
            'is_deleted' => 0,
        ])->count();
    }
}
