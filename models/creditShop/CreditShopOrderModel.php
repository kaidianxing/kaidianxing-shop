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
 * This is the model class for table "{{%credit_shop_order}}".
 *
 * @property int $id 发送的优惠券id  为了回收
 * @property int $order_id 订单id
 * @property int $status 订单状态
 * @property int $pay_credit 支付积分
 * @property float $pay_price 支付金额
 * @property int $goods_id 商品id
 * @property int $option_id 规格id
 * @property int $shop_goods_id 商城商品id
 * @property int $shop_option_id 商城规格id
 * @property int $total 购买数量
 * @property int $member_id 会员id
 * @property string $created_at 创建时间
 * @property int $type 商品类型  0商品  1优惠券
 * @property string|null $member_coupon_id 发送的优惠券id  为了回收
 * @property int $credit_unit 单价
 * @property int $client_type 客户端类型
 */
class CreditShopOrderModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_shop_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['order_id', 'status', 'pay_credit', 'goods_id', 'option_id', 'shop_goods_id', 'shop_option_id', 'total', 'member_id', 'type', 'credit_unit', 'client_type'], 'integer'],
            [['pay_price'], 'number'],
            [['created_at'], 'safe'],
            [['member_coupon_id'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => '发送的优惠券id  为了回收',
            'order_id' => '订单id',
            'status' => '订单状态',
            'pay_credit' => '支付积分',
            'pay_price' => '支付金额',
            'goods_id' => '商品id',
            'option_id' => '规格id',
            'shop_goods_id' => '商城商品id',
            'shop_option_id' => '商城规格id',
            'total' => '购买数量',
            'member_id' => '会员id',
            'created_at' => '创建时间',
            'type' => '商品类型  0商品  1优惠券',
            'member_coupon_id' => '发送的优惠券id  为了回收',
            'credit_unit' => '单价',
            'client_type' => '客户端类型',
        ];
    }
}
