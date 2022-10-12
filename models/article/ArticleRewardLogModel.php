<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "shopstar_article_reward_log".
 *
 * @property int $id
 * @property int $article_id 文章id
 * @property int $to_member_id 发放给用户id
 * @property int $from_member_id 来源自用户id
 * @property int $reward_type 类型 1: 积分 2: 余额
 * @property float $number 发放数量
 * @property string $created_at
 * @property string $updated_at
 */
class ArticleRewardLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'to_member_id', 'from_member_id', 'reward_type'], 'integer'],
            [['number'], 'number'],
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
            'to_member_id' => 'To Member ID',
            'from_member_id' => 'From Member ID',
            'reward_type' => 'Reward Type',
            'number' => 'Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
