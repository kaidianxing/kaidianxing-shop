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

namespace shopstar\models\tradeOrder;

use shopstar\bases\model\BaseActiveRecord;


/**
 * 交易订单退款记录实体类
 * This is the model class for table "{{%trade_order_refund}}".
 *
 * @property int $id
 * @property int $order_id 支付订单ID
 * @property int $order_type 支付订单类型
 * @property string $order_no 业务订单编号(商城\充值\系统订单号)
 * @property string $trade_no 内部交易单号
 * @property string $out_trade_no 外部交易单号(微信\支付宝交易单号)
 * @property string $price 退款金额
 * @property string $created_at 创建退款记录时间
 * @property string $finish_time 退款完成时间
 * @property int $status 退款状态 0:未退款 1:退款完成 2:退款完成
 * @property string $reason 退款原因
 * @property string $fail_reason 退款失败原因
 */
class TradeOrderRefundModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trade_order_refund}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'order_type', 'status'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'finish_time'], 'safe'],
            [['order_no', 'trade_no', 'out_trade_no'], 'string', 'max' => 50],
            [['reason', 'fail_reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '支付订单ID',
            'order_type' => '支付订单类型',
            'order_no' => '业务订单编号(商城\充值\系统订单号)',
            'trade_no' => '内部交易单号',
            'out_trade_no' => '外部交易单号(微信\\支付宝交易单号)',
            'price' => '退款金额',
            'created_at' => '创建退款记录时间',
            'finish_time' => '退款完成时间',
            'status' => '退款状态 0:未退款 1:退款完成 2:退款完成',
            'reason' => '退款原因',
            'fail_reason' => '退款失败原因',
        ];
    }

    /**
     * 写入记录
     * @param array $attributes
     * @return array|bool
     * @author likexin
     */
    public static function write(array $attributes)
    {
        $model = new self();
        $model->setAttributes($attributes);
        if (!$model->save()) {
            return error($model->getErrorMessage());
        }

        return true;
    }

}