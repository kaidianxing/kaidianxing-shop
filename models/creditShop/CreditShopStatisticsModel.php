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
 * This is the model class for table "{{%credit_shop_statistics}}".
 *
 * @property int $id
 * @property string $date 统计日期
 * @property string $created_at 创建日期
 * @property int $goods_num 当前
 * @property int $order_count 订单数量
 * @property int $order_credit_sum 累计积分
 * @property float $order_price_sum 累计金额
 * @property int $view_count 访问量
 * @property int $member_count 访客量
 * @property float $wechat_order_price_sum 微信渠道订单金额
 * @property float $wxapp_order_price_sum 微信小程序渠道订单金额
 * @property float $h5_order_price_sum h5渠道订单金额
 * @property float $byte_dance_order_price_sum 字节跳动小程序渠道订单金额
 */
class CreditShopStatisticsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%credit_shop_statistics}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['date', 'created_at'], 'safe'],
            [['goods_num', 'order_count', 'order_credit_sum', 'view_count', 'member_count'], 'integer'],
            [['order_price_sum', 'wechat_order_price_sum', 'wxapp_order_price_sum', 'h5_order_price_sum', 'byte_dance_order_price_sum'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'date' => '统计日期',
            'created_at' => '创建日期',
            'goods_num' => '当前',
            'order_count' => '订单数量',
            'order_credit_sum' => '累计积分',
            'order_price_sum' => '累计金额',
            'view_count' => '访问量',
            'member_count' => '访客量',
            'wechat_order_price_sum' => '微信渠道订单金额',
            'wxapp_order_price_sum' => '微信小程序渠道订单金额',
            'h5_order_price_sum' => 'h5渠道订单金额',
            'byte_dance_order_price_sum' => '字节跳动小程序渠道订单金额',
        ];
    }
}
