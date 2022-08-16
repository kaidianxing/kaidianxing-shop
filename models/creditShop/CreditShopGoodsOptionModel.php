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
 * This is the model class for table "{{%credit_shop_goods_option}}".
 *
 * @property int $id id
 * @property int $goods_id 商品id
 * @property int $credit_shop_goods_id 积分商品id
 * @property int $option_id 规格id
 * @property int $credit_shop_credit 积分
 * @property float $credit_shop_price 价格
 * @property int $credit_shop_stock 库存
 * @property int $original_stock 原始库存
 * @property int $sale 销量
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_join 是否参与
 */
class CreditShopGoodsOptionModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_shop_goods_option}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['goods_id', 'credit_shop_goods_id', 'option_id', 'credit_shop_credit', 'credit_shop_stock', 'original_stock', 'sale', 'is_join'], 'integer'],
            [['credit_shop_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'id',
            'goods_id' => '商品id',
            'credit_shop_goods_id' => '积分商品id',
            'option_id' => '规格id',
            'credit_shop_credit' => '积分',
            'credit_shop_price' => '价格',
            'credit_shop_stock' => '库存',
            'original_stock' => '原始库存',
            'sale' => '销量',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_join' => '是否参与',
        ];
    }
}
