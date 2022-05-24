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
use shopstar\constants\tradeOrder\TradeOrderStatusConstant;
use shopstar\constants\tradeOrder\TradeOrderTypeConstant;
use shopstar\exceptions\tradeOrder\TradeOrderNotifyException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\order\BytedanceSettleJob;
use shopstar\models\tradeOrder\TradeOrderModel;
use shopstar\structs\order\OrderPaySuccessStruct;
use yii\helpers\Json;

/**
 * 支付订单-回调实现类
 * Class TradeOrderNotify
 * @package shopstar\services\tradeOrder
 * @author likexin
 */
class TradeOrderNotify
{

    /**
     * @var string|null 回调类型
     */
    public $type;

    /**
     * @var string|array|null 回调原文
     */
    public $raw;

    /**
     * @var string|array|null 回调参数
     */
    public $notifyParams;

    /**
     * @var string|null 当前时间
     */
    private $now;

    /**
     * @var string|null 交易单号
     */
    private $tradeNo;

    /**
     * @var string|null 外部交易单号
     */
    private $outTradeNo;

    /**
     * @var float|null 实际支付金额(元)
     */
    private $payPrice;

    /**
     * @var TradeOrderModel[] 交易订单列表
     */
    private $tradeOrder;

    /**
     * @var int|null 交易订单类型
     */
    private $tradeOrderType;

    /**
     * @var int|null 客户端类型
     */
    private $clientType;

    /**
     * 开始处理
     * @return string
     * @throws \yii\base\Exception
     * @author likexin
     */
    public function handler()
    {
        try {
            // 检测并获取参数
            $this->checkParams();

            // 查询交易订单
            $this->loadOrder();

            // 调用支付组件验签
            $this->verifySign();

            /** 为了防止callback致命错误，先更新回调成功状态，调用callback失败后再次更新状态 */

            // 验签成功修改状态及部分参数
            $this->updateOrder([
                'status' => TradeOrderStatusConstant::STATUS_SUCCESS,
                'out_trade_no' => $this->outTradeNo,
                'notify_time' => $this->now,
                'notify_raw' => is_array($this->raw) ? Json::encode($this->raw) : $this->raw,
            ]);

            // 调用回调方法
            $call = $this->callback();
            if (is_error($call)) {
                $attributes = [
                    'status' => TradeOrderStatusConstant::STATUS_SUCCESS_NOTIFY_FAIL,
                    'fail_reason' => '调用回调方法失败: ' . $call['message'],
                ];
            } else {
                $attributes = [
                    'pay_finish_time' => DateTimeHelper::now(),
                ];
            }

            // 更新订单状态
            $this->updateOrder($attributes);

            return 'SUCCESS';
        } catch (\Exception $exception) {
            LogHelper::error('[WECHAT ALIPAY PAY ERROR]:' . $exception->getMessage(), []);
            return 'FAIL:' . $exception->getCode() . $exception->getMessage();
        }
    }

    /**
     * 检测必要参数
     * @throws TradeOrderNotifyException
     * @author likexin
     */
    private function checkParams()
    {
        switch ($this->type) {
            case 'wechat':
                $this->notifyParams = ArrayHelper::fromXML($this->raw);
                // 交易订单号
                $this->tradeNo = $this->notifyParams['out_trade_no'] ?? '';
                // 外部交易订单号
                $this->outTradeNo = $this->notifyParams['transaction_id'];
                // 实际支付金额(元)
                $this->payPrice = (float)bcdiv($this->notifyParams['total_fee'], 100, 2);
                break;
            case 'alipay':

                $this->notifyParams = $this->raw;

                if (trim($this->notifyParams['trade_status']) !== 'TRADE_SUCCESS') {
                    throw new TradeOrderNotifyException(TradeOrderNotifyException::ALIPAY_NOTIFY_ERROR);
                }

                // 交易订单号
                $this->tradeNo = $this->notifyParams['out_trade_no'] ?? '';
                // 外部交易订单号
                $this->outTradeNo = $this->notifyParams['trade_no'];
                // 实际支付金额(元)
                $this->payPrice = (float)$this->notifyParams['total_amount'];
                break;
            case 'byte_dance':
                $this->notifyParams = Json::decode($this->raw);
                $this->notifyParams['msg'] = Json::decode($this->notifyParams['msg']);
                // 交易订单号
                $this->tradeNo = $this->notifyParams['msg']['cp_orderno'];
                // 外部交易订单号
                $this->outTradeNo = $this->notifyParams['msg']['payment_order_no'];
                // 实际支付金额(元)
                $this->payPrice = (float)bcdiv($this->notifyParams['msg']['total_amount'], 100, 2);
                break;
            case 'balance':
                $this->notifyParams = $this->raw;
                $this->tradeNo = $this->raw['trade_no'];
                $this->outTradeNo = $this->raw['out_trade_no'];
                $this->payPrice = $this->raw['total_amount'];
        }

        if (empty($this->notifyParams)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::CHECK_PARAMS_NOTIFY_PARAMS_EMPTY);
        }

        // 交易订单号
        if (empty($this->tradeNo)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::CHECK_PARAMS_TRADE_NO_EMPTY);
        }

        // 外部交易订单号
        if (empty($this->outTradeNo)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::CHECK_PARAMS_OUT_TRADE_NO_EMPTY);
        }

        if ($this->payPrice <= 0) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::CHECK_PARAMS_PAY_PRICE_EMPTY);
        }

        // 当前时间
        $this->now = DateTimeHelper::now();
    }

    /**
     * 加载交易订单
     * @throws TradeOrderNotifyException
     * @author likexin
     */
    private function loadOrder()
    {
        $this->tradeOrder = TradeOrderModel::find()
            ->where([
                'trade_no' => $this->tradeNo,
                'status' => TradeOrderStatusConstant::STATUS_WAIT_PAY,
            ])
            ->select(['id', 'account_id', 'payment_id', 'order_id', 'type', 'client_type', 'pay_type', 'order_no', 'trade_no', 'order_price'])
            ->all();
        if (empty($this->tradeOrder)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::LOAD_ORDER_NOT_FOUND);
        }

        // 检测订单金额与实际支付金额
        $orderPriceTotal = array_sum(array_column($this->tradeOrder, 'order_price'));
        if ($orderPriceTotal != $this->payPrice) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::LOAD_ORDER_CHECK_PAY_PRICE_FAIL);
        }

        // 以下参数多单支付肯定是一致的
        $this->clientType = $this->tradeOrder[0]->client_type;
        $this->tradeOrderType = $this->tradeOrder[0]->type;
    }

    /**
     * 更新订单状态
     * @param array $attributes
     * @throws TradeOrderNotifyException
     * @author likexin
     */
    private function updateOrder(array $attributes)
    {
        $update = TradeOrderModel::updateAll($attributes, [
            'id' => array_column($this->tradeOrder, 'id'),
        ]);

        if ($update != count($this->tradeOrder)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::UPDATE_ORDER_COUNT_NOT_MATCH);
        }
    }

    /**
     * 调用支付组件进行验签
     * @throws \yii\base\InvalidConfigException
     * @throws TradeOrderNotifyException
     * @author likexin
     */
    private function verifySign()
    {
        // 余额支付进来无需验证签名
        if ($this->type == 'balance' || (isset($this->notifyParams['is_verify_sign']) && $this->notifyParams['is_verify_sign'])) {
            return;
        }

        // 调用支付组件验签
        $check = PaymentNewComponent::getInstance($this->type, [
            'payType' => $this->tradeOrder[0]['pay_type'],
            'tradeNo' => $this->tradeNo,
            'clientType' => $this->clientType,
        ])
            ->verifySign($this->raw);
        if (is_error($check)) {
            throw new TradeOrderNotifyException(TradeOrderNotifyException::VERIFY_SIGN_FAIL);
        }
    }

    /**
     * 业务场景支付回调
     * @return array|void
     * @author likexin
     */
    private function callback()
    {
        // 根据订单类型获取回调方法
        $function = TradeOrderTypeConstant::getNotifyFunction($this->tradeOrderType);
        if (empty($function)) {
            return success();
        }

        // 执行调用
        try {

            // 遍历所有交易订单，调用订单支付回调方法
            foreach ($this->tradeOrder as $tradeOrder) {
                // 组成回调方法结构
                $params = new OrderPaySuccessStruct([
                    'accountId' => $tradeOrder->account_id,           // 支付用户账号ID
                    'orderId' => $tradeOrder->order_id,                    // 业务订单ID
                    'orderNo' => $tradeOrder->order_no,                 // 业务订单号
                    'orderPrice' => $tradeOrder->order_price,          // 业务订单支付金额
                    'payPrice' => $this->payPrice,                            // 实际支付金额
                    'payType' => $tradeOrder->pay_type,                 // 支付类型
                    'paymentId' => $tradeOrder->payment_id,         // 支付模板ID
                    'tradeNo' => $tradeOrder->trade_no,                 // 内部交易订单号
                    'outTradeNo' => $this->outTradeNo,                  // 外部交易订单号
                ]);

                // 调用回调方法
                $call = call_user_func($function, $params);
                if (is_error($call)) {
                    return $call;
                }

                // 字节跳动支付 创建结算任务
                if ($tradeOrder->pay_type == PayTypeConstant::PAY_TYPE_BYTEDANCE) {
                    QueueHelper::push(new BytedanceSettleJob([
                        'outTradeNo' => $this->outTradeNo,
                    ]), 1296000); // 15 天结算
                }
            }

        } catch (\Throwable $exception) {
            return error($exception->getMessage());
        }
    }

}
