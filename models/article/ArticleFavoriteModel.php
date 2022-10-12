<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "shopstar_article_favorite".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $article_id 文章id
 * @property string $created_at 创建时间
 */
class ArticleFavoriteModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_favorite}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'article_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'article_id' => 'Article ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 收藏关系
     * @return ActiveQuery
     * @author yuning
     */
    public function getArticle()
    {
        return $this->hasOne(ArticleModel::class, ['id' => 'article_id']);
    }
}
