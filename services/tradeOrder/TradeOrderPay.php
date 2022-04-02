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
use shopstar\constants\tradeOrder\TradeOrderStatusConstant;
use shopstar\exceptions\tradeOrder\TradeOrderPayException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\models\tradeOrder\TradeOrderModel;

/**
 * 交易订单-支付实现类
 * Class TradeOrderPay
 * @package shopstar\services\tradeOrder
 * @author likexin
 */
class TradeOrderPay
{

    /**
     * @var int|null 交易订单类型
     */
    public $type;

    /**
     * @var string|null 支付类型 (payType与payTypeIdentity必须二传一，checkParams会做转换)
     */
    public $payType;

    /**
     * @var int|null 支付类型标识 (payType与payTypeIdentity必须二传一，checkParams会做转换)
     */
    public $payTypeIdentity;

    /**
     * @var int|null 支付客户端类型
     */
    public $clientType;

    /**
     * @var int|null 支付账号ID
     */
    public $accountId;

    /**
     * @var string|null 支付账号微信openid
     */
    public $openid;

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
     * @var string|array|null 业务订单号
     */
    public $orderNo;

    /**
     * @var array|null 多个业务订单
     */
    public $multiOrder;

    /**
     * @var int|null 订单金额
     */
    public $orderPrice;

    /**
     * @var string|null 回调地址
     */
    public $callbackUrl;

    /**
     * @var bool 多单合并支付
     */
    private $isMulti = false;

    /**
     * @var int 支付业务订单梳理
     */
    private $orderCount = 1;

    /**
     * @var TradeOrderModel[]|null 交易订单列表
     */
    private $tradeOrder;

    /**
     * @var string|null 内部交易订单号
     */
    private $tradeNo;

    /**
     * @var array|null 返回值
     */
    private $response;

    /**
     * @var string 当前时间
     */
    private $now;

    /**
     * 统一支付
     * @throws TradeOrderPayException
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function unify()
    {
        // 检测参数
        $this->checkParams();

        // 获取\创建交易订单
        $this->getOrCreateOrder();

        // 关闭未支付的(无效)交易订单
        $this->closeInvalidOrder();

        // 调用支付组件
        $this->invokeComponent();

        // 更新订单信息 payment_id、status等
        $this->updateOrder();

        // 支付自动回调
        $this->notify();

        // 返回结果
        return [
            'pay_params' => $this->response['pay_params'] ?? []
        ];
    }

    /**
     * 检测参数
     * @throws TradeOrderPayException
     * @author likexin
     */
    private function checkParams()
    {
        if (empty($this->type)) {
            // 交易订单类型
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_TYPE_EMPTY);
        } elseif (empty($this->payType) && empty($this->payTypeIdentity)) {
            // 支付类型
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_PAY_TYPE_EMPTY);
        } elseif (empty($this->clientType)) {
            // 客户端类型
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_CLIENT_TYPE_EMPTY);
        } elseif (empty($this->accountId)) {
            // 账号ID
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_ACCOUNT_ID_EMPTY);
        }

        // 如果多单参数不为空，进行解析处理
        if (!empty($this->multiOrder)) {
            // 传入多单支付，不能传入单个
            if (!is_null($this->orderNo) || !is_null($this->orderId)) {
                throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_MULTI_ORDER_ORDER_NO_REPEAT);
            }

            $this->orderId = [];
            $this->orderNo = [];
            $this->orderPrice = 0;

            // 遍历多单判断参数是否合法
            foreach ($this->multiOrder as $index => $order) {
                if (!isset($order['orderId']) || empty($order['orderId'])) {
                    throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_MULTI_ORDER_ORDER_ID_EMPTY, "参数错误 multiOrder[{$index}]['orderId']不能为空");
                } elseif (!isset($order['orderNo']) || empty($order['orderNo'])) {
                    throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_MULTI_ORDER_ORDER_NO_EMPTY, "参数错误 multiOrder[{$index}]['orderNo']不能为空");
                }
                if (!isset($order['orderPrice']) || is_null($order['orderPrice']) || floatval($order['orderPrice']) < 0) {
                    throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_MULTI_ORDER_ORDER_PRICE_EMPTY, "参数错误 multiOrder[{$index}]['orderPrice']不能为空");
                }

                // 追加数据
                $this->orderId[] = $order['orderId'];
                $this->orderNo[] = $order['orderNo'];
                $this->orderPrice = (float)bcadd($this->orderPrice, $order['orderPrice'], 2);
            }

            // 定义是多单合并支付
            $this->isMulti = true;
            $this->orderCount = count($this->orderNo);
        }

        if (empty($this->orderNo)) {
            // 业务订单号
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_ORDER_NO_EMPTY);
        } elseif (is_null($this->orderId)) {
            // 业务订单ID
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_ORDER_ID_EMPTY);
        } elseif (is_null($this->orderPrice) || (float)$this->orderPrice < 0) {
            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_ORDER_PRICE_EMPTY);
        }

        // 不是多单合并支付时塞入订单数组
        if (!$this->isMulti) {
            $this->multiOrder = [
                [
                    'orderId' => $this->orderId,
                    'orderNo' => $this->orderNo,
                    'orderPrice' => $this->orderPrice,
                ]
            ];
        }

        // 上方已经检测了payType与payTypeIdentity必须传入一个，此处将为传入的进行转换
        if (empty($this->payType)) {
            $this->payType = PayTypeConstant::getPayTypeCodeByIdentity($this->payTypeIdentity);
            if (empty($this->payType)) {
                throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_PAY_TYPE_IDENTITY_NOT_SUPPORT);
            }
        } elseif (empty($this->payTypeIdentity)) {
            $this->payTypeIdentity = PayTypeConstant::getIdentity($this->payType);
            if (empty($this->payTypeIdentity)) {
                throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_PAY_TYPE_NOT_SUPPORT);
            }
        }

        // 微信支付判断OPENID必须传入 TODO 青岛开店星信息技术有限公司 后台支付的不需要传
//        if ($this->payType == PayTypeConstant::PAY_TYPE_WECHAT && empty($this->openid)) {
//            throw new TradeOrderPayException(TradeOrderPayException::CHECK_PARAMS_OPENID_EMPTY);
//        }

        // 定义当前操作时间
        $this->now = DateTimeHelper::now();

        //赋值静态订单id
        self::$staticOrderId = array_column($this->multiOrder, 'orderId');
    }

    /**
     * 获取或创建交易订单
     * @throws TradeOrderPayException
     * @author likexin
     */
    private function getOrCreateOrder()
    {
        // 查询订单是否存在(当前业务单号orderNo可能多个)
        $this->tradeOrder = TradeOrderModel::find()
            ->where([
                'order_no' => $this->orderNo,
                'type' => $this->type,
                'pay_type' => $this->payType,
                'client_type' => $this->clientType,
                'order_price' => $this->orderPrice,
                'is_multi' => (int)$this->isMulti,
            ])
            ->andWhere(['>', 'status', TradeOrderStatusConstant::STATUS_CLOSED])
            ->all();

        if (empty($this->tradeOrder)) {
            // 不存在时创建(判断是否合单支付)
            $this->tradeNo = OrderNoHelper::getOrderNo('TR', $this->clientType);

            // 插入数据
            $this->tradeOrder = [];

            // 遍历多单进行创建支付订单
            foreach ($this->multiOrder as $order) {
                $model = new TradeOrderModel();
                $model->setAttributes([
                    'type' => $this->type,
                    'account_id' => $this->accountId,
                    'is_multi' => (int)$this->isMulti,
                    'order_id' => (int)$order['orderId'],
                    'order_no' => $order['orderNo'],
                    'trade_no' => $this->tradeNo,
                    'client_type' => $this->clientType,
                    'order_price' => (float)$order['orderPrice'],
                    'pay_type' => $this->payType,
                    'created_at' => $this->now,
                ]);
                if (!$model->save()) {
                    throw new TradeOrderPayException(TradeOrderPayException::GET_OR_CREATE_ORDER_CREATE_FAIL, "创建交易订单失败 业务订单号:{$order['orderNo']}");
                }
                $this->tradeOrder[] = $model;
                $model = null;
            }
            return;
        }

        // 判断查询出的订单数量与传入的不一致
        if (count($this->tradeOrder) !== $this->orderCount) {
            throw new TradeOrderPayException(TradeOrderPayException::GET_OR_CREATE_ORDER_TRADE_COUNT_MISS);
        }

        // 判断多个交易订单的交易单号是否一致
        $orderTradeNo = array_unique(array_column($this->tradeOrder, 'trade_no'));
        if (count($orderTradeNo) > 1) {
            throw new TradeOrderPayException(TradeOrderPayException::GET_OR_CREATE_ORDER_TRADE_NO_INVALID);
        }
        $this->tradeNo = $orderTradeNo[0];

        // 判断多个交易订单的支付状态是否都是待支付
        $orderTradeStatus = array_unique(array_column($this->tradeOrder, 'status'));
        if (count($orderTradeStatus) > 1 || !in_array($orderTradeStatus[0], [TradeOrderStatusConstant::STATUS_DEFAULT, TradeOrderStatusConstant::STATUS_WAIT_PAY])) {
            throw new TradeOrderPayException(TradeOrderPayException::GET_OR_CREATE_ORDER_STATUS_INVALID);
        }
    }

    /**
     * 关闭未支付的(无效)交易订单
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    private function closeInvalidOrder()
    {
        // 要关闭交易订单的订单类型(未下单\已下单未支付)
        $closeStatus = [TradeOrderStatusConstant::STATUS_DEFAULT, TradeOrderStatusConstant::STATUS_WAIT_PAY];

        // 查询无效订单
        $invalidTradeOrder = TradeOrderModel::find()
            ->where([
                'order_no' => $this->orderNo,
                'status' => $closeStatus,
            ])
            ->all();
        if (empty($invalidTradeOrder)) {
            return;
        }

        /**
         * @var TradeOrderModel $tradeOrder
         */
        foreach ($invalidTradeOrder as $tradeOrder) {
            // 如果查询出的交易订单号与刚插入的一致则跳过
            if ($tradeOrder->trade_no == $this->tradeNo) {
                continue;
            }

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

            // 调用支付组件进行关闭(此处不校验组件关闭状态，原因不能影响正常订单支付，即使组件报错只需要更新交易订单状态即可)
            PaymentNewComponent::getInstance(PayTypeConstant::getIdentity($tradeOrder->pay_type), [
                'clientType' => $tradeOrder->client_type,
                'tradeNo' => $tradeOrder->trade_no,
                'payType' => $tradeOrder->pay_type
            ])->close();
        }
    }

    /**
     * 调用支付组件
     * @throws \yii\base\InvalidConfigException
     * @throws TradeOrderPayException
     * @author likexin
     */
    private function invokeComponent()
    {
        $params = [
            'payType' => $this->payType,
            'clientType' => $this->clientType,
            'accountId' => $this->accountId,
            'tradeNo' => $this->tradeNo,
            'orderPrice' => $this->orderPrice,
            'openid' => $this->openid,

            'callbackUrl' => $this->callbackUrl,
        ];

        // 非微信支付，卸载openid
        if ($this->payType != PayTypeConstant::PAY_TYPE_WECHAT) {
            unset($params['openid']);
        }

        // 获取支付组件实例
        $instance = PaymentNewComponent::getInstance($this->payTypeIdentity, $params);
        if (is_error($instance)) {
            throw new TradeOrderPayException(TradeOrderPayException::INVOKE_COMPONENT_GET_INSTANCE_FAIL);
        }

        // 执行统一支付
        $result = $instance->unify();
        if (is_error($result)) {
            throw new TradeOrderPayException(TradeOrderPayException::INVOKE_COMPONENT_PAY_FAIL, $result['message']);
        }

        // 返回支付参数
        $this->response = $result;
    }

    /**
     * 更新订单信息
     * @author likexin
     */
    private function updateOrder()
    {
        TradeOrderModel::updateAll([
            'status' => TradeOrderStatusConstant::STATUS_WAIT_PAY,
            'payment_id' => $this->response['payment_id'] ?? 0,
        ], [
            'id' => array_column($this->tradeOrder, 'id'),
            'status' => TradeOrderStatusConstant::STATUS_DEFAULT,
        ]);
    }

    /**
     * 支付自动回调
     * @author likexin
     */
    private function notify()
    {
        if ($this->payType != PayTypeConstant::TYPE_BALANCE) {

            return;
        }

        // 调用回调
        TradeOrderService::notify([
            'type' => $this->payTypeIdentity,
            'raw' => [
                'trade_no' => $this->tradeNo,                                       // 交易单号
                'out_trade_no' => '-1',           // 外部交易单号(回调的外部交易单号实际是商城交易单号，正好相反)
                'total_amount' => $this->orderPrice,        // 支付金额
            ],
        ])->handler();
    }

}