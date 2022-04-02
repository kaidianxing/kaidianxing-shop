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

namespace shopstar\services\tradeOrder;

use shopstar\components\paymentNew\PaymentNewComponent;
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\tradeOrder\TradeOrderCloseTypeConstant;
use shopstar\constants\tradeOrder\TradeOrderRefundStatusConstant;
use shopstar\constants\tradeOrder\TradeOrderStatusConstant;
use shopstar\exceptions\tradeOrder\TradeOrderOperationException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\models\tradeOrder\TradeOrderModel;
use shopstar\models\tradeOrder\TradeOrderRefundModel;
use yii\base\Component;

/**
 * 支付订单-操作实现类
 * Class TradeOrderOperation
 * @package shopstar\services\tradeOrder
 * @author likexin
 */
class TradeOrderOperation extends Component
{

    /**
     * @var int|null 业务订单ID
     */
    public $orderId;

    /**
     * @var int|null 静态业务订单id
     * @author 青岛开店星信息技术有限公司.
     */
    public static $staticOrderId;

    /**
     * @var string|null 业务订单号
     */
    public $orderNo;

    /**
     * @var TradeOrderModel[]|null 交易订单
     */
    private $tradeOrder;

    /**
     * 初始化
     * @throws TradeOrderOperationException
     * @author likexin
     */
    public function init()
    {
        // 检测参数
        $this->checkParams();

        parent::init();
    }

    /**
     * 检测参数
     * @throws TradeOrderOperationException
     * @author likexin
     */
    private function checkParams()
    {
        if (is_null($this->orderNo) && is_null($this->orderId)) {
            throw new TradeOrderOperationException(TradeOrderOperationException::CHECK_PARAMS_ORDER_NO_EMPTY);
        }
    }

    /**
     * 关闭交易
     * @return bool
     * @throws TradeOrderOperationException
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function close(): bool
    {
        // 要关闭交易订单的订单类型(未下单\已下单未支付)
        $closeStatus = [TradeOrderStatusConstant::STATUS_DEFAULT, TradeOrderStatusConstant::STATUS_WAIT_PAY];

        $where = [
            'order_id' => $this->orderId,
        ];

        if (empty($this->orderId)) {
            $where['order_no'] = $this->orderNo;
        }

        // 根据业务订单号查询
        $this->tradeOrder = TradeOrderModel::find()
            ->where($where)
            ->andWhere([
                'status' => $closeStatus,
            ])
            ->all();
        if (empty($this->tradeOrder)) {
            throw new TradeOrderOperationException(TradeOrderOperationException::CLOSE_ORDER_NOT_FOUND);
        }

        /**
         * @var TradeOrderModel $tradeOrder
         */
        foreach ($this->tradeOrder as $tradeOrder) {

            // 更新交易订单状态
            $tradeOrder->setAttributes([
                'status' => TradeOrderStatusConstant::STATUS_CLOSED,
                'close_type' => TradeOrderCloseTypeConstant::TYPE_CREATE_AND_CLOSE_INVALID,
                'close_time' => DateTimeHelper::now(),
            ]);
            $tradeOrder->save();

            // 已经下单未调用支付订单时跳过
            if ($tradeOrder->status == TradeOrderStatusConstant::STATUS_DEFAULT) {
                continue;
            }

            // 获取支付类型
            $payTypeIdentity = PayTypeConstant::getIdentity($tradeOrder->pay_type);

            // 调用支付组件进行关闭(此处不校验组件关闭状态，原因不能影响正常订单支付，即使组件报错只需要更新交易订单状态即可)
            PaymentNewComponent::getInstance($payTypeIdentity, [
                'clientType' => $tradeOrder->client_type,
                'tradeNo' => $tradeOrder->trade_no,
            ])->close();
        }


        return true;
    }

    /**
     * 订单退款
     * @param float $refundPrice 退款金额
     * @param string $refundSubject 退款说明
     * @return bool
     * @throws TradeOrderOperationException
     * @author likexin
     */
    public function refund(float $refundPrice, string $refundSubject = ''): bool
    {
        $where = [
            'order_no' => $this->orderNo,
        ];

        // 根据业务订单号查询
        $this->tradeOrder = TradeOrderModel::find()
            ->where($where)
            ->andWhere([
                'status' => [TradeOrderStatusConstant::STATUS_SUCCESS, TradeOrderStatusConstant::STATUS_SUCCESS_NOTIFY_FAIL],
            ])
            ->all();
        if (empty($this->tradeOrder)) {
            // 交易订单为空
            throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_ORDER_NOT_FOUND);
        } elseif (count($this->tradeOrder) > 1) {
            // 同一业务单号已完成支付的交易订单有多条
            throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_ORDER_TOTAL_INVALID);
        }

        // 取第0个
        $tradeOrder = $this->tradeOrder[0];
        self::$staticOrderId = $tradeOrder->order_id;

        // 当前交易订单已经全部退款
        if ($tradeOrder->refund_status == TradeOrderRefundStatusConstant::STATUS_ALL_REFUNDED) {
            throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_ALREADY_ALL_REFUNDED);
        }

        $addedRefundPrice = (float)bcadd($tradeOrder->refund_price, $refundPrice, 2);

        // 如果退款金额 + 已经退款金额 > 总支付金额
        if ($addedRefundPrice > $tradeOrder->order_price) {
            throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_PRICE_GREATER_PAY_PRICE);
        }

        // 生成退款编号
        $refundNo = OrderNoHelper::getOrderNo('RE', $tradeOrder->client_type);

        // 交易订单退款记录
        $tradeOrderRefund = [
            'refund_no' => $refundNo,
            'order_id' => $tradeOrder->id,
            'order_type' => $tradeOrder->type,
            'order_no' => $tradeOrder->order_no,
            'trade_no' => $tradeOrder->trade_no,
            'out_trade_no' => $tradeOrder->out_trade_no,
            'price' => $refundPrice,
            'created_at' => DateTimeHelper::now(),
            'status' => 1,
            'reason' => $refundSubject,
            'fail_reason' => '',
        ];

        $transaction = \Yii::$app->getDb()->beginTransaction();

        try {
            // 更新交易订单状态
            $update = TradeOrderModel::updateAll([
                'refund_price' => $addedRefundPrice,
                'refund_status' => $addedRefundPrice == $tradeOrder->order_price ? TradeOrderRefundStatusConstant::STATUS_ALL_REFUNDED : TradeOrderRefundStatusConstant::STATUS_PART_REFUNDED,
            ], [
                'id' => $tradeOrder->id,
                'refund_price' => $tradeOrder->refund_price,
            ]);
            if (!$update) {
                throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_UPDATE_STATUS_FAIL);
            }

            // payType从int类型转为string类型
            $payTypeIdentity = PayTypeConstant::getIdentity($tradeOrder->pay_type);

            // 调用支付组件进行退款
            $instance = PaymentNewComponent::getInstance($payTypeIdentity, [
                'clientType' => $tradeOrder->client_type,
                'payType' => $tradeOrder->pay_type,
                'tradeNo' => $tradeOrder->trade_no,
                'accountId' => $tradeOrder->account_id,
                'subject' => $refundSubject,
                'notifyUrl' => '',/** @change likexin 需要传入，命令行获取不到，退款时不需要回调 */
            ]);
            if (is_error($instance)) {
                throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_PAYMENT_COMPONENT_INSTANCE_FAIL);
            }
            $result = $instance->refund($tradeOrder->order_price, $refundPrice, $refundNo);
            if (is_error($result)) {
                throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_PAYMENT_COMPONENT_REFUND_FAIL);
            }

            $transaction->commit();

        } catch (\Exception $exception) {
            $transaction->rollBack();

            // 记录退款失败
            $tradeOrderRefund['status'] = 0;
            $tradeOrderRefund['fail_reason'] = $exception->getMessage();
            TradeOrderRefundModel::write($tradeOrderRefund);


            // 继续抛出异常
            throw new TradeOrderOperationException(TradeOrderOperationException::REFUND_FAIL, $exception->getMessage() . '(' . $exception->getCode() . ')');
        }

        // 退款完成时间
        $tradeOrderRefund['finish_time'] = DateTimeHelper::now();

        // 记录退款成功
        TradeOrderRefundModel::write($tradeOrderRefund);

        return true;
    }

    /**
     * 查询支付状态
     * @author likexin
     * @deprecated 暂时没开发这个功能
     */
    public function query()
    {
    }

}