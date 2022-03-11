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
 * 交易订单实体类
 * This is the model class for table "{{%trade_order}}".
 *
 * @property int $id
 * @property int $type 支付订单类型
 * @property int $account_id 支付账号ID(member_id\user_id)
 * @property int $is_multi 是否合并支付(合并支付时同一个order_no有多条记录)
 * @property int $order_id 业务订单ID
 * @property string $order_no 业务订单编号(商城\充值\系统订单号)
 * @property string $trade_no 内部交易单号(商城\充值\系统订单单号)
 * @property string $out_trade_no 外部交易单号(微信\支付宝交易单号)
 * @property int $client_type 客户端类型
 * @property string $order_price 订单支付金额
 * @property string $refund_price 已退款金额
 * @property int $pay_type 支付方式
 * @property int $payment_id 支付方式ID(payment表主键ID)
 * @property string $created_at 下单时间
 * @property string $close_time 订单关闭时间
 * @property string $notify_time 支付回调通知时间
 * @property string $pay_finish_time 支付完成时间(执行完成业务回调)
 * @property int $status 订单状态 0:待支付 10:调用支付(等待支付) 20:调用支付失败 21:支付回调失败 30:支付成功 31:支付成功(内部回调失败) -1:取消订单
 * @property string $fail_reason 支付失败原因
 * @property int $close_type 关闭订单类型
 * @property int $refund_status 退款状态 0:未退款 1:部分退款 2:全部退款
 * @property string $notify_raw 回调原文数据
 */
class TradeOrderModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trade_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'account_id', 'is_multi', 'order_id', 'client_type', 'pay_type', 'payment_id', 'status', 'refund_status', 'close_type'], 'integer'],
            [['order_price', 'refund_price'], 'number'],
            [['created_at', 'close_time', 'notify_time', 'pay_finish_time'], 'safe'],
            [['notify_raw'], 'string'],
            [['order_no', 'trade_no', 'out_trade_no'], 'string', 'max' => 50],
            [['fail_reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '支付订单类型',
            'account_id' => '支付账号ID(member_id\\user_id)',
            'is_multi' => '是否合并支付(合并支付时同一个order_no有多条记录)',
            'order_id' => '业务订单ID',
            'order_no' => '业务订单编号(商城\充值\系统订单号)',
            'trade_no' => '内部交易单号',
            'out_trade_no' => '外部交易单号(微信\\支付宝交易单号)',
            'client_type' => '客户端类型',
            'order_price' => '订单支付金额',
            'refund_price' => '已退款金额',
            'pay_type' => '支付方式',
            'payment_id' => '支付方式ID(payment表主键ID)',
            'created_at' => '下单时间',
            'close_time' => '订单关闭时间',
            'notify_time' => '支付回调通知时间',
            'pay_finish_time' => '支付完成时间(执行完成业务回调)',
            'status' => '订单状态 0:待支付 10:调用支付(等待支付) 20:调用支付失败 21:支付回调失败 30:支付成功 31:支付成功(内部回调失败) -1:取消订单',
            'fail_reason' => '支付失败原因',
            'close_type' => '关闭订单类型',
            'refund_status' => '退款状态 0:未退款 1:部分退款 2:全部退款',
            'notify_raw' => '回调原文数据',
        ];
    }

}