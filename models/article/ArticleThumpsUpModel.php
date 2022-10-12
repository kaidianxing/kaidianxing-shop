<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shopstar_article_thumps_up".
 *
 * @property int $id
 * @property int $article_id 文章id
 * @property int $member_id 用户id
 * @property int $status 状态 0:未点赞 1:已点赞
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class ArticleThumpsUpModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_thumps_up}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'status'], 'required'],
            [['article_id', 'member_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => 'Article ID',
            'member_id' => 'Member ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 获取点赞model
     * @param int $articleId
     * @param int $memberId
     * @param array $options
     * @return array|ActiveRecord|null
     * @author yuning
     */
    public static function getModel(int $articleId = 0, int $memberId = 0, array $options = [])
    {
        $options = array_merge([
            'andWhere' => [],
            'select' => '*',
        ], $options);

        $where = [
            'article_id' => $articleId,
            'member_id' => $memberId,
        ];
        return self::find()->select($options['select'])->where($where)->andWhere($options['andWhere'])->one();
    }
}
