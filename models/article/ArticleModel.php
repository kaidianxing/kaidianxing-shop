<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\article\ArticleConstant;
use shopstar\exceptions\article\ArticleException;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shopstar_article".
 *
 * @property int $id
 * @property int $group_id 分组id
 * @property int $group_id_origin 原分组id, 分组隐藏后,显示, 需切回到原分组
 * @property string $title 标题
 * @property string $cover 封面图片地址
 * @property string $digest 文章简介
 * @property string $author 作者
 * @property int $display_order 排序(0-9999 数字越大越靠前)
 * @property string|null $content 详情
 * @property string|null $content_origin 详情原始数据-后台编辑时熏染用
 * @property string|null $goods_ids 文章包含的商品ids
 * @property string|null $coupon_ids 文章中包含的优惠券ids
 * @property int $read_number_status 阅读数状态 0:隐藏 1:显示
 * @property int $read_number_init 初始阅读数
 * @property int $read_number 阅读数
 * @property int $read_number_step 阅读数增长步长,取随机 1-n内随机
 * @property int $read_number_real 真实阅读数
 * @property int $thumps_up_status 点赞数状态 0:隐藏 1:显示
 * @property int $thumps_up_number_init 初始点赞数
 * @property int $thumps_up_number 点赞数
 * @property int $thumps_up_number_real 真实点赞数
 * @property int $share_number 分享数
 * @property int $share_number_real 真实分享数
 * @property int $member_level_limit_type 会员等级限制类型 0: 不限制 1:指定等级
 * @property string|null $member_level_limit_ids 会员等级限制-等级
 * @property int $commission_level_limit_type 分销商等级限制类型 0: 不限制 1:指定等级
 * @property string|null $commission_level_limit_ids 分销商等级限制-等级
 * @property int $reward_type 奖励类型 0: 无奖励 1:积分 2:余额
 * @property string|null $reward_rule 奖励规则
credit 积分
once: 每次获得的积分
max: 最多获得的积分
balance 余额
first: 第一次获得的余额
max: 最多获得的余额


 * @property int $status 状态 0: 隐藏(未发布) 1:显示(发布)
 * @property int $is_top 置顶 0: 不置顶 1: 置顶
 * @property int $is_deleted 1: 已删除
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $top_thumb 文章头图
 * @property int $top_thumb_type 头图类型 0 单图 1轮播
 * @property string|null $top_thumb_all 轮播图
 */
class ArticleModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'group_id_origin', 'display_order', 'read_number_status', 'read_number_init', 'read_number', 'read_number_step', 'read_number_real', 'thumps_up_status', 'thumps_up_number_init', 'thumps_up_number', 'thumps_up_number_real', 'share_number', 'share_number_real', 'member_level_limit_type', 'commission_level_limit_type', 'reward_type', 'status', 'is_top', 'is_deleted', 'top_thumb_type'], 'integer'],
            [['content', 'content_origin', 'goods_ids', 'coupon_ids', 'member_level_limit_ids', 'commission_level_limit_ids', 'reward_rule', 'top_thumb_all'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 64],
            [['cover', 'top_thumb'], 'string', 'max' => 191],
            [['digest'], 'string', 'max' => 120],
            [['author'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'group_id_origin' => 'Group Id Origin',
            'title' => 'Title',
            'cover' => 'Cover',
            'digest' => 'Digest',
            'author' => 'Author',
            'display_order' => 'Display Order',
            'content' => 'Content',
            'content_origin' => 'Content Origin',
            'goods_ids' => 'Goods Ids',
            'coupon_ids' => 'Coupon Ids',
            'read_number_status' => 'Read Number Status',
            'read_number_init' => 'Read Number Init',
            'read_number' => 'Read Number',
            'read_number_step' => 'Read Number Step',
            'read_number_real' => 'Read Number Real',
            'thumps_up_status' => 'Thumps Up Status',
            'thumps_up_number_init' => 'Thumps Up Number Init',
            'thumps_up_number' => 'Thumps Up Number',
            'thumps_up_number_real' => 'Thumps Up Number Real',
            'share_number' => 'Share Number',
            'share_number_real' => 'Share Number Real',
            'member_level_limit_type' => 'Member Level Limit Type',
            'member_level_limit_ids' => 'Member Level Limit Ids',
            'commission_level_limit_type' => 'Commission Level Limit Type',
            'commission_level_limit_ids' => 'Commission Level Limit Ids',
            'reward_type' => 'Reward Type',
            'reward_rule' => 'Reward Rule',
            'status' => 'Status',
            'is_top' => 'Is Top',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'top_thumb' => 'Top Thumb',
            'top_thumb_type' => 'Top Thumb Type',
            'top_thumb_all' => 'Top Thumb All',
        ];
    }

    /**
     * @return array
     * @author yuning
     */
    public function logAttributeLabels(): array
    {
        return [
            'id' => '文章id',
            'title' => '文章标题',
            'cover' => '文章封面',
            'group' => '文章分组',
            'digest' => '文章简介',
            'author' => '作者名称',
            'content' => '文章详情',
            'goods' => [
                'title' => '商品',
                'item' => [
                    'id' => 'id',
                    'title' => '商品标题',
                ],
            ],
            'coupons' => [
                'title' => '优惠券',
                'item' => [
                    'id' => 'id',
                    'name' => '优惠券名称',
                ],
            ],
            'is_top' => '文章置顶',
            'reward' => [
                'title' => '奖励设置',
                'item' => [
                    'type' => '类型',
                    'once' => '单次奖励',
                    'max' => '最多奖励',
                ],
            ],
            'status' => '文章状态',
            'member_limit' => [
                'title' => '会员查看权限',
                'item' => [
                    'status' => '是否限制',
                    'level' => '限制等级',

                ]
            ],
            'commission_limit' => [
                'title' => '分销商查看权限',
                'item' => [
                    'status' => '是否限制',
                    'level' => '限制等级',

                ]
            ],
        ];
    }



    /**
     * @var mixed|null
     */
    public static $articleField = [
        'id',
        'group_id',
        'title',
        'cover',
        'digest',
        'author',
        'display_order',
        'content',
        'content_origin',
        'goods_ids',
        'coupon_ids',
        'read_number_status',
        'read_number',
        'read_number_init',
        'read_number_step',
        'read_number_real',
        'thumps_up_status',
        'thumps_up_number_init',
        'thumps_up_number',
        'thumps_up_number_real',
        'share_number',
        'share_number_real',
        'member_level_limit_type',
        'member_level_limit_ids',
        'commission_level_limit_type',
        'commission_level_limit_ids',
        'reward_type',
        'reward_rule',
        'status',
        'is_top',
        'created_at',
        'top_thumb',
        'top_thumb_all',
        'top_thumb_type',
    ];

    /**
     * @var mixed|null
     */
    public static $ListField = [
        'article.id',
        'article.group_id',
        'group.name as group_name',
        'article.title',
        'article.cover',
        'article.digest',
        'article.author',
        'article.display_order',
        'article.goods_ids',
        'article.read_number_status',
        'article.read_number',
        'article.read_number_real',
        'article.thumps_up_status',
        'article.thumps_up_number',
        'article.thumps_up_number_real',
        'article.share_number',
        'article.share_number_real',
        'article.status',
        'article.is_top',
        'article.created_at',

    ];

    /**
     * 获取文章model
     * @param int $id
     * @param $field
     * @param bool $throw
     * @return array|ActiveRecord|null
     * @throws ArticleException
     * @author yuning
     */
    public static function getModel(int $id = 0, $field = '*', bool $throw = true)
    {
        $where = [
            'id' => $id,
            'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
        ];
        $article = self::find()->select($field)->where($where)->one();
        if (!$article && $throw) {
            throw new ArticleException(ArticleException::ARTICLE_GET_ERROR);
        }

        return $article;
    }

    /**
     * 保存时检测标题是否唯一
     * @param int $articleId 文章id
     * @param string $title 文章标题
     * @return bool
     * @throws ArticleException
     * @author yuning
     */
    public static function saveCheckTitleExist(int $articleId = 0, string $title = ''): bool
    {
        $exist = ArticleModel::find()->select('id')->where(['title' => $title, 'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE])->first();
        if ($exist) {
            // 是否是同一篇文章
            if ($articleId > 0 && $exist['id'] == $articleId) {
                return true;
            }
            throw new ArticleException(ArticleException::SAVE_PARAMS_TITLE_EXIST);
        }
        return true;
    }
}
