<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\models\order;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\exceptions\order\CommentException;
use shopstar\helpers\RequestHelper;

/**
 * This is the model class for table "{{%order_goods_comment}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $goods_id 商品id
 * @property int $order_goods_id 订单商品id
 * @property int $member_id 用户id
 * @property string $nickname 用户昵称
 * @property string $avatar 头像
 * @property int $client_type 客户端来源
 * @property int $level 评价等级
 * @property string $content 评价内容
 * @property int $is_have_image 是否有图评价   0否  1是
 * @property string $images 图片
 * @property int $is_delete 是否删除
 * @property string $append_content 追加内容
 * @property string $append_images 追加图片
 * @property string $reply_content 回复内容
 * @property string $reply_images 回复图片
 * @property string $append_reply_content 追加回复内容
 * @property string $append_reply_images 追加回复图片
 * @property int $is_top 是否置顶  0否 1 置顶
 * @property int $status 审核状态 0 审核中  1通过  -1 拒绝
 * @property int $append_status 追加内容审核状态
 * @property int $type 类型 1真实评论 2手动创建 3助手抓取 0默认评价
 * @property string $append_time 追评时间
 * @property string $reply_time 回复时间
 * @property string $reply_append_time 回复追评时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $option_title 规格标题
 * @property string $member_level_name 会员等级名称
 * @property int $is_new 是否新评论 兼容规格名称,等级名称
 * @property int $sort_by 排序
 * @property int $is_reward 是否发放奖励
 * @property int $is_choice 是否精选
 * @property string $reward_content 奖励内容
 * @property string $grab_url 抓取链接
 * @property int $level_id 等级id
 */
class OrderGoodsCommentModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_goods_comment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'level_id', 'order_goods_id', 'member_id', 'client_type', 'level', 'is_have_image', 'is_delete', 'is_top', 'status', 'append_status', 'type', 'is_new', 'sort_by', 'is_reward', 'is_choice'], 'integer'],
            [['content', 'images', 'append_images', 'reply_images', 'append_reply_images', 'option_title'], 'string'],
            [['append_time', 'reply_time', 'reply_append_time', 'created_at', 'updated_at'], 'safe'],
            [['nickname', 'option_title', 'member_level_name'], 'string', 'max' => 50],
            [['avatar', 'append_content', 'reply_content', 'append_reply_content', 'reward_content', 'grab_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单id',
            'goods_id' => '商品id',
            'order_goods_id' => '订单商品id',
            'member_id' => '用户id',
            'nickname' => '用户昵称',
            'avatar' => '头像',
            'client_type' => '客户端来源',
            'level' => '评价等级',
            'content' => '评价内容',
            'is_have_image' => '是否有图评价   0否  1是',
            'images' => '图片',
            'is_delete' => '是否删除',
            'append_content' => '追加内容',
            'append_images' => '追加图片',
            'reply_content' => '回复内容',
            'reply_images' => '回复图片',
            'append_reply_content' => '追加回复内容',
            'append_reply_images' => '追加回复图片',
            'is_top' => '是否置顶  0否 1 置顶',
            'status' => '审核状态 0 审核中  1通过  -1 拒绝',
            'append_status' => '追加内容审核状态',
            'type' => '类型 1真实评论 2手动创建 3助手抓取 0默认评价',
            'append_time' => '追评时间',
            'reply_time' => '回复时间',
            'reply_append_time' => '回复追评时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'option_title' => '规格标题',
            'member_level_name' => '会员等级名称',
            'is_new' => '是否新评论 兼容规格名称,等级名称',
            'sort_by' => '排序',
            'is_reward' => '是否发放奖励',
            'is_choice' => '是否精选',
            'reward_content' => '奖励内容',
            'grab_url' => '抓取链接',
            'level_id' => '等级id',
        ];
    }
}