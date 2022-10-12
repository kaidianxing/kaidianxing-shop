<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "shopstar_article_group".
 *
 * @property int $id
 * @property string $name 分组名称
 * @property int $display_order 排序
 * @property int $status 状态 0:隐藏 1:显示
 * @property string $created_at
 * @property string $updated_at
 */
class ArticleGroupModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['display_order', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'display_order' => 'Display Order',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * @return array
     * @author yuning
     */
    public function logAttributeLabels()
    {
        return [
            'id' => '分组id',
            'name' => '分组名称',
            'display_order' => '排序',
            'status' => '状态',
        ];
    }
}
