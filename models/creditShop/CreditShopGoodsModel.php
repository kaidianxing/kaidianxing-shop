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

namespace shopstar\models\creditShop;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%credit_shop_goods}}".
 *
 * @property int $id id
 * @property int $goods_id 商品或优惠券id
 * @property int $type 类型  0 商品  1优惠券
 * @property int $has_option 是否多规格
 * @property int $credit_shop_credit 积分
 * @property float $credit_shop_price 价格
 * @property int $credit_shop_stock 库存
 * @property int $original_stock 库存
 * @property int $sale 销量
 * @property int $min_price_credit 最小价格对应积分
 * @property float $min_price 最小价格
 * @property int $dispatch_type 运费设置 0读取系统  1包邮
 * @property int $member_level_limit_type 会员等级限制  0不限制  1指定可购买  2指定不可购买
 * @property string $member_level_id 会员等级id
 * @property int $member_group_limit_type 会员标签限制  0不限制  1指定可购买  2指定不可购买
 * @property string $member_group_id 会员标签id
 * @property int $goods_limit_type 商品限购类型 0 不限购  1每人限购  2  每人每n天限购
 * @property int $goods_limit_num 每人限购
 * @property int $goods_limit_day 每天限购
 * @property int $status 状态  0下架  1上架 -1 原商品修改规格导致下架
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_delete 是否已删除
 */
class CreditShopGoodsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_shop_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['goods_id', 'type', 'has_option', 'credit_shop_credit', 'credit_shop_stock', 'original_stock', 'sale', 'min_price_credit', 'dispatch_type', 'member_level_limit_type', 'member_group_limit_type', 'goods_limit_type', 'goods_limit_num', 'goods_limit_day', 'status', 'is_delete'], 'integer'],
            [['credit_shop_price', 'min_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['member_level_id', 'member_group_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'id',
            'goods_id' => '商品或优惠券id',
            'type' => '类型  0 商品  1优惠券',
            'has_option' => '是否多规格',
            'credit_shop_credit' => '积分',
            'credit_shop_price' => '价格',
            'credit_shop_stock' => '库存',
            'original_stock' => '库存',
            'sale' => '销量',
            'min_price_credit' => '最小价格对应积分',
            'min_price' => '最小价格',
            'dispatch_type' => '运费设置 0读取系统  1包邮',
            'member_level_limit_type' => '会员等级限制  0不限制  1指定可购买  2指定不可购买',
            'member_level_id' => '会员等级id',
            'member_group_limit_type' => '会员标签限制  0不限制  1指定可购买  2指定不可购买',
            'member_group_id' => '会员标签id',
            'goods_limit_type' => '商品限购类型 0 不限购  1每人限购  2  每人每n天限购',
            'goods_limit_num' => '每人限购',
            'goods_limit_day' => '每天限购',
            'status' => '状态  0下架  1上架 -1 原商品修改规格导致下架',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_delete' => '是否已删除',
        ];
    }

    /**
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function logAttributeLabels(): array
    {
        return [
            'id' => 'id',
            'type' => '类型',
            'title' => '商品/优惠券名称',
            'option' => [
                'title' => '规格',
                'item' => [
                    'title' => '标题',
                    'is_join' => '是否参与',
                    'credit_shop_credit' => '积分',
                    'credit_shop_price' => '金额',
                    'credit_shop_stock' => '库存',
                ]
            ],
            'credit_shop_credit' => '积分价',
            'credit_shop_price' => '金额',
            'credit_shop_stock' => '库存',
            'dispatch_type' => '运费设置',
            'member_level_limit_type' => '会员等级限制',
            'member_level_id' => '会员等级id',
            'member_group_limit_type' => '会员标签限制',
            'member_group_id' => '会员标签id',
            'goods_limit_type' => '商品限购类型',
            'goods_limit_num' => '每人限购',
            'goods_limit_day' => '每天限购',
            'status' => '状态',
        ];
    }
}
