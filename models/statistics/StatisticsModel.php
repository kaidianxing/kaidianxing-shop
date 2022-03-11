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

namespace shopstar\models\statistics;

use shopstar\bases\model\BaseActiveRecord;


/**
 * This is the model class for table "{{%statistics}}".
 *
 * @property int $id
 * @property string $statistic_date 统计日期
 * @property int $member_count 会员总数
 * @property int $member_new_count 当天新注册用户数
 * @property int $order_new_count 当天下单数
 * @property string $order_new_price_sum 当天下单总额
 * @property int $order_pay_count 当天付款订单数
 * @property string $order_pay_price_sum 当天付款订单总额
 * @property int $order_pay_member_count 当天支付订单用户数
 * @property int $order_member_count 当天下单会员数
 * @property int $order_new_member_count 当天新会员下单数
 * @property int $member_source_wechat_count 当天来自微信的新会员数
 * @property int $member_source_wxapp_count 当天来自微信小程序的新会员数
 * @property int $member_source_h5_count 当天来自h5的新会员数
 * @property int $member_source_alipay_count 当天来自支付宝小程序的新会员数
 * @property int $member_source_byte_count 当天来自抖音小程序的新会员数
 * @property int $order_refund_count 当天完成退款订单数
 * @property string $order_refund_price_sum 当天退款订单总金额
 * @property int $cart_goods_count 当天加入购物车商品数
 * @property int $shelves_goods_count 当天在架商品数
 * @property int $pay_goods_count 当天付款的商品件数
 * @property int $goods_pv_count 商品浏览次数
 * @property int $uv 当日uv
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class StatisticsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statistics}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_count', 'member_new_count', 'order_new_count', 'order_pay_count', 'order_pay_member_count', 'order_member_count', 'order_new_member_count', 'member_source_wechat_count', 'member_source_wxapp_count', 'member_source_h5_count', 'member_source_alipay_count', 'member_source_byte_count', 'order_refund_count', 'cart_goods_count', 'shelves_goods_count', 'pay_goods_count', 'goods_pv_count', 'uv'], 'integer'],
            [['statistic_date', 'created_at', 'updated_at'], 'safe'],
            [['order_new_price_sum', 'order_pay_price_sum', 'order_refund_price_sum'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'statistic_date' => '统计日期',
            'member_count' => '会员总数',
            'member_new_count' => '当天新注册用户数',
            'order_new_count' => '当天下单数',
            'order_new_price_sum' => '当天下单总额',
            'order_pay_count' => '当天付款订单数',
            'order_pay_price_sum' => '当天付款订单总额',
            'order_pay_member_count' => '当天支付订单用户数',
            'order_member_count' => '当天下单会员数',
            'order_new_member_count' => '当天新会员下单数',
            'member_source_wechat_count' => '当天来自微信的新会员数',
            'member_source_wxapp_count' => '当天来自微信小程序的新会员数',
            'member_source_h5_count' => '当天来自h5的新会员数',
            'member_source_alipay_count' => '当天来自支付宝小程序的新会员数',
            'member_source_byte_count' => '当天来自抖音小程序的新会员数',
            'order_refund_count' => '当天完成退款订单数',
            'order_refund_price_sum' => '当天退款订单总金额',
            'cart_goods_count' => '当天加入购物车商品数',
            'shelves_goods_count' => '当天在架商品数',
            'pay_goods_count' => '当天付款的商品件数',
            'goods_pv_count' => '商品浏览次数',
            'uv' => '当日uv',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
