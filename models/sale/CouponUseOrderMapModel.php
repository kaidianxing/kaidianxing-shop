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

namespace shopstar\models\sale;

use shopstar\bases\model\BaseActiveRecord;


/**
 * This is the model class for table "{{%coupon_use_order_map}}".
 *
 * @property string $id
 * @property int $coupon_member_id 优惠券会员使用记录id
 * @property int $order_id 订单id
 * @property string $order_no 订单编号
 */
class CouponUseOrderMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_use_order_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_member_id', 'order_id'], 'integer'],
            [['order_no'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_member_id' => '优惠券会员使用记录id',
            'order_id' => '订单id',
            'order_no' => '订单编号',
        ];
    }
}