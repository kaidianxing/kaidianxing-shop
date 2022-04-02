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

namespace shopstar\mobile\pay;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\OrderConstant;
use shopstar\constants\tradeOrder\TradeOrderTypeConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\order\OrderService;
use shopstar\services\tradeOrder\TradeOrderService;
use shopstar\structs\order\OrderPaySuccessStruct;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{
    /**
     * 获取支付方式
     * @action index
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $get = RequestHelper::get();
        $setting = ShopSettings::get('sysset.payment.typeset');
        $config = $setting[ClientTypeConstant::getIdentify(RequestHelper::header('Client-Type'))];

        $orderId = $get['order_id'];
        //检测是否可用货到付款
        if (!empty($orderId)) {
            $orderId = StringHelper::explode($orderId);
            $isDeliveryPay = OrderGoodsModel::isDeliveryPay($orderId, $this->clientType);
            if (!$isDeliveryPay) {
                unset($config['delivery']);
            }

            // 核销不支持货到付款
            $order = OrderModel::find()->select('dispatch_type')->where(['id' => $orderId])->get();
            $dispatchType = array_column($order, 'dispatch_type');
            if (in_array('20', $dispatchType)) {
                unset($config['delivery']);
            }
        }

        $type = [
            OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL,
            OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS,
            OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP,
        ];
        if (in_array($get['activity_type'], $type)) {
            unset($config['delivery']);
        }

        return $this->result(['payList' => $config]);
    }

    /**
     * 支付
     * @return array|\yii\web\Response
     * @throws OrderException
     * @throws PaymentException
     * @throws \shopstar\exceptions\tradeOrder\TradeOrderPayException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay()
    {
        $orderId = RequestHelper::post('order_id');

        //下面是支付
        if (empty($orderId)) {
            return $this->error('订单id不能为空');
        }

        // 支付类型
        $payType = RequestHelper::post('pay_type');
        if (empty($payType)) {
            return $this->error('支付方式不能为空');
        }
        $payTypeCode = PayTypeConstant::getPayTypeCodeByIdentity($payType);
        if (empty($payTypeCode)) {
            return $this->error('不支持的支付方式');
        }

        /**
         * 查询订单
         * @var OrderModel[] $order
         */
        $order = OrderModel::find()
            ->where([
                'id' => $orderId,
                'status' => OrderConstant::ORDER_STATUS_WAIT_PAY,
            ])
            ->select(['id', 'order_no', 'pay_price', 'activity_type', 'extra_discount_rules_package', 'goods_info', 'create_from', 'order_type', 'scene'])
            ->all();
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_NOT_FOUND_ERROR);
        }

        /** 解析活动规则，处理预售订单 **/
        // // TODO 青岛开店星信息技术有限公司 拿出去
        $extraPricePackage = Json::decode($order[0]->extra_discount_rules_package);
        // 商品预售 定金预售
        $presellPayType = 0; // 预售支付类型 0不是预售订单 1 定金  2 尾款 3 全款
        $presellOrder = [];

        /** 货到付款处理逻辑块 **/
        if ($payTypeCode == PayTypeConstant::PAY_TYPE_DELIVERY) {
            if (!empty($presellOrder)) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_PRESELL_ORDER_PAY_NOT_DELIVERY);
            }

            if ($order[0]['create_from'] >= 30 && $order[0]['create_from'] <= 32) {
                throw new PaymentException(PaymentException::PAY_CHANNEL_ERROR);
            }
            //货到付款直接付款成功
            foreach ($order as $orderIndex => $orderItem) {
                // paysuccess结构体
                $orderPaySuccessStruct = \Yii::createObject([
                    'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                    'accountId' => $this->memberId,
                    'orderId' => $orderItem->id,
                    'payType' => PayTypeConstant::PAY_TYPE_DELIVERY,
                ]);

                /**
                 * @var OrderPaySuccessStruct $orderPaySuccessStruct
                 */
                $ret = OrderService::paySuccess($orderPaySuccessStruct);
                if (is_error($ret)) {
                    return $this->result($ret);
                }
            }

            return $this->success();
        }


        // 根据渠道获取会员openid
        $openid = '';
        if ($this->clientType == ClientTypeConstant::CLIENT_WECHAT) {
            $openid = MemberWechatModel::getOpenId($this->memberId);
        } elseif ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            $openid = MemberWxappModel::getOpenId($this->memberId);
        }

        // 组装多单支付结构
        $payMultiOrder = [];
        foreach ($order as $orderItem) {
            $payMultiOrder[] = [
                'orderId' => $orderItem->id,
                'orderNo' => $presellPayType == 1 ? $presellOrder['order_no'] : $orderItem->order_no,   // 预售支付定金时，使用预售的订单号以及支付金额
                'orderPrice' => $presellPayType == 1 ? $presellOrder['front_price'] : $orderItem->pay_price,
            ];
        }

        /** @change likexin 调用交易订单服务获取支付参数 * */
        $result = TradeOrderService::pay([
            'type' => TradeOrderTypeConstant::TYPE_SHOP_ORDER,     // 交易订单类型(交易类型)
            'payType' => $payTypeCode,                  // 支付类型code
            'payTypeIdentity' => $payType,              // 支付类型string
            'clientType' => $this->clientType,          // 客户端类型
            'accountId' => $this->memberId,          // 充值账号ID(会员ID)
            'openid' => $openid,                             // 会员OPENID
            'multiOrder' => $payMultiOrder,           // 多单合并支付数据
            'callbackUrl' => RequestHelper::post('return_url'), // 回调URL
        ])->unify();

        return $this->result([
            'data' => $result['pay_params']['pay_url'] ?? $result['pay_params'],
        ]);
    }

    /**
     * 支付校验
     * @return array|\yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheck()
    {
        //下面是支付
        $orderId = RequestHelper::post('order_id');
        $payType = RequestHelper::post('pay_type');
        if (empty($payType) || empty($orderId)) {
            return $this->error('参数错误');
        }

        // 查询预售状态(预售订单有定金、尾款，需要使用额外参数来查询支付状态)，1: 定金 2: 尾款
        $presellStatus = RequestHelper::postInt('presell_status');

        // string转为int
        $payTypeCode = PayTypeConstant::getPayTypeCodeByIdentity($payType);
        if (empty($payTypeCode)) {
            return $this->error('不支持的支付类型');
        }

        // 查询订单是否是预售订单
        $isPresellOrder = OrderModel::find()->where([
            'id' => $orderId,
            'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY,
            'activity_type' => OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL,
        ])->count();

        if (!empty($isPresellOrder)) {

            if (!in_array($presellStatus, [1, 2])) {
                return $this->error('参数presell_status错误');
            }
        } else {

            // 此处直接查询业务订单
            $payOrderData = OrderModel::find()
                ->where([
                    'id' => $orderId,
//                    'pay_type' => $payTypeCode,
                ])
                ->select(['status', 'pay_type'])
                ->andWhere(['!=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY])
                ->get();

            $payOrder = array_column($payOrderData, 'status');
            $payTypeOrder = array_column($payOrderData, 'pay_type');

            // 判断订单状态与支付类型
            if (in_array(OrderStatusConstant::ORDER_STATUS_CLOSE, $payOrder)) {
                throw new OrderException(OrderException::ORDER_STATUS_CLOSE_ERROR);
            } else if (!in_array($payTypeCode, $payTypeOrder)) {
                throw new OrderException(OrderException::ORDER_PAY_TYPE_ERROR);
            }

            $payOrder = count($payOrderData);
        }

        //判断数量是否相等
        if ($payOrder !== count((array)$orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR);
        }

        return $this->result('支付成功');
    }
}
