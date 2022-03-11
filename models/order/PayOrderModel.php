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

use shopstar\exceptions\order\OrderException;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\DateTimeHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%pay_order}}".
 *
 * @property int $id
 * @property int $account_id 账户ID type==10时为uid type==20时为member_id
 * @property int $type 10: 系统订单 20: 商城订单 30: 会员充值 40: 会员卡购买 50 优惠券购买
 * @property int $order_id 订单ID
 * @property string $order_no 商户单号
 * @property string $trade_no 交易单号
 * @property string $out_trade_no 外部交易单号
 * @property int $pay_type 10: 微信支付 20: 支付宝支付
 * @property int $client_type 10:H5 20:微信公众号 21 微信小程序 30 抖音小程序
 * @property float $pay_price 支付金额(分)
 * @property string $created_at 创建支付时间
 * @property string $pay_time 支付时间
 * @property string $finish_time 处理完成时间 成功失败均有此时间
 * @property string $close_time 订单关闭时间
 * @property string|null $error_info 错误信息
 * @property string|null $raw_data 通知参数
 * @property string|null $back_data 通知参数
 * @property string|null $refund_data 通知参数
 * @property int $status 0未处理 10成功 20支付成功未同步订单状态 -1失败 -10取消支付
 */
class PayOrderModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pay_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_id', 'type', 'order_id', 'pay_type', 'status', 'client_type'], 'integer'],
            [['account_id'], 'required'],
            [['pay_price'], 'number'],
            [['created_at', 'pay_time', 'finish_time', 'close_time'], 'safe'],
            [['error_info', 'raw_data'], 'string'],
            [['order_no'], 'string', 'max' => 50],
            [['trade_no', 'out_trade_no'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => '账户ID type==10时为uid type==20时为member_id',
            'type' => '10: 系统订单 20: 商城订单 30: 会员充值 40: 会员卡购买 50 优惠券购买',
            'order_id' => '订单ID',
            'order_no' => '商户单号',
            'trade_no' => '交易单号',
            'out_trade_no' => '外部交易单号',
            'pay_type' => '10: 微信支付 20: 支付宝支付',
            'client_type' => '10:H5 20:微信公众号 21 微信小程序 30 抖音小程序',
            'pay_price' => '支付金额(分)',
            'created_at' => '创建支付时间',
            'pay_time' => '支付时间',
            'finish_time' => '处理完成时间 成功失败均有此时间',
            'close_time' => '订单关闭时间',
            'error_info' => '错误信息',
            'raw_data' => '通知参数',
            'back_data' => '吊起的存储数据',
            'refund_data' => '退款数据',
            'status' => '0未处理 10成功 20支付成功未同步订单状态 -1失败 -10取消支付',
        ];
    }

    /**
     * @param array $order
     * @param $payType
     * @param $clientType
     * @param int $payOrderType 支付订单类型
     * @param string $orderNo
     * @return array
     * @throws OrderException
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司.
     */
    public static function write(array $order, $payType, $clientType, int $payOrderType)
    {
        $payOrder = [];

        //循环入库
        foreach ($order as $index => $item) {

            /**
             * @var $item OrderModel
             */

            $model = self::find()
                ->where(['order_id' => $item->id, 'order_no' => $item->order_no])
                ->one();

            if (empty($model)) {
                $model = new self();
                $model->client_type = $clientType;
                $model->account_id = $item->member_id;
                $model->type = $payOrderType;
                $model->order_id = $item->id;
                $model->order_no = $item->order_no;
                $model->created_at = DateTimeHelper::now();
                $model->status = 0;
            } else {
                if ($model->status != 0) {
                    throw new PaymentException(PaymentException::ORDER_IS_BE_PAYED);
                }
            }

            $model->pay_type = $payType;
            $model->pay_price = $item->pay_price;

            //计算全部订单金额
            if ($model->save() === false) {
                throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_PARAMS_ERROR);
            }

            $payOrder[] = $model;
        }

        return $payOrder;
    }

    /**
     * 记录pay_order
     * @param $memberId
     * @param $orderId
     * @param $orderNo
     * @param $orderType
     * @param $payType
     * @param $clientType
     * @param $payPrice
     * @return mixed
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public static function write2($memberId, $orderId, $orderNo, $orderType, $payType, $clientType, $payPrice)
    {
        $model = self::find()
            ->where(['order_id' => $orderId, 'order_no' => $orderNo])
            ->one();
        if ($model == null) {
            $model = new self();
            $model->account_id = $memberId;
            $model->type = $orderType;
            $model->order_id = $orderId;
            $model->order_no = $orderNo;
            $model->pay_type = $payType;
            $model->client_type = $clientType;
            $model->pay_price = $payPrice;
            $model->created_at = DateTimeHelper::now();
            $model->status = 0;
        } else {
            // 改价
            $model->pay_price = $payPrice;
            // 修改支付方式
            $model->pay_type = $payType;
        }

        if ($model->save() === false) {
            throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_PARAMS_ERROR);
        }

        return [$model];
    }

    /**
     * 写入退款记录
     * @param $id
     * @param $data
     * @return bool
     * @author
     * 伟霆
     */
    public static function writeRefund($id, $data)
    {
        $model = self::findOne(['id' => $id]);
        if ($model === false) {
            return false;
        }
        $model->refund_data = Json::encode($data);
        if ($model->save() === false) {
            return false;
        }
        return true;
    }

}
