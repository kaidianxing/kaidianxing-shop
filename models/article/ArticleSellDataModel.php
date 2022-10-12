<?php

namespace shopstar\models\article;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "shopstar_article_sell_data".
 *
 * @property int $id
 * @property int $article_id 文章id
 * @property int $member_id 用户id
 * @property int $type 类型 1: 商品 2: 优惠券
 * @property int $order_id 商品为 订单id 优惠券 为coupon_log_id
 * @property int $goods_id 商品id
 * @property float $money 引导金额
 * @property int $coupon_member_id 会员优惠券id
 * @property string $created_at
 * @property string $updated_at
 */
class ArticleSellDataModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_sell_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'member_id', 'type', 'order_id', 'goods_id', 'coupon_member_id'], 'integer'],
            [['money'], 'number'],
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
            'type' => 'Type',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'money' => 'Money',
            'coupon_member_id' => 'Coupon Member ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * 商品类型字段
     */
    public static array $goodsField = [
        'sell.goods_id',
        'goods.title',
    ];

    /**
     * 优惠券类型字段
     */
    public static array $couponField = [
        'sell.id',
        'sell.coupon_member_id',
        'member.nickname',
        'member.mobile',
        'coupon_member.title as coupon_title',
        'coupon_member.coupon_sale_type',
        'coupon_member.discount_price',
        'coupon_member.enough',
        'coupon_member.status',
        'coupon_member.created_at',
    ];
}
