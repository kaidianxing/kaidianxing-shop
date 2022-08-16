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
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\OrderConstant;
use shopstar\constants\tradeOrder\TradeOrderTypeConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\exceptions\tradeOrder\TradeOrderPayException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\groups\GroupsGoodsService;
use shopstar\services\order\OrderService;
use shopstar\services\tradeOrder\TradeOrderService;
use shopstar\services\wxTransactionComponent\WxTransactionComponentOrderService;
use shopstar\structs\order\OrderPaySuccessStruct;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\Response;

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

            // 检测是否是视频号订单 仅支持微信支付
            $scene = array_column($order, 'scene');
            if ($scene == OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST) {
                $config = [$config['wechat']];
            }
        }

        $type = [
            OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL,
        ];
        if (in_array($get['activity_type'], $type)) {
            unset($config['delivery']);
        }

        return $this->result(['payList' => $config]);
    }

    /**
     * 支付
     * @return array|Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws OrderException
     * @throws PaymentException
     * @throws TradeOrderPayException
     * @throws \yii\base\Exception
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

        /** 拼团检测活动库存 **/
        if ($order[0]->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS) {
            // 查询订单活动表
            $orderActivity = OrderActivityModel::find()
                ->where([
                    'order_id' => $orderId,
                    'activity_type' => 'groups',
                ])
                ->select([
                    'order_id',
                    'activity_id'
                ])
                ->first();

            // 获取第一个订单商品
            $orderGoods = current($order)['goods_info'];
            $orderGoods = Json::decode($orderGoods);
            $orderGoods = current($orderGoods);

            // 判断拼团库存是否充足
            $result = GroupsGoodsService::orderPayCheckGroupsGoodsStock($orderActivity['activity_id'], $orderGoods['goods_id'], $orderGoods['option_id'], $orderGoods['total']);
            if (is_error($result)) {
                throw new OrderException(OrderException::ORDER_PAY_GROUPS_STOCK_ERROR);
            }
        }

        /** 货到付款处理逻辑块 **/
        if ($payTypeCode == PayTypeConstant::PAY_TYPE_DELIVERY) {

            if ($order[0]['create_from'] >= 30 && $order[0]['create_from'] <= 32) {
                throw new PaymentException(PaymentException::PAY_CHANNEL_ERROR);
            }
            //货到付款直接付款成功
            foreach ($order as $orderIndex => $orderItem) {
                // paysuccess结构体
                $orderPaySuccessStruct = Yii::createObject([
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
                'orderNo' => $orderItem->order_no,
                'orderPrice' => $orderItem->pay_price,
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

        $data = $result['pay_params']['pay_url'] ?? $result['pay_params'];

        // 如果是视频号直播产生的订单需要上传给微信小程序端
        if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            // 获取开发人员设置
            $developmentMemberId = ShopSettings::get('wxTransactionComponent.development.member_id');

            // 1. 判断场景值或者是开发会员
            if ($order[0]['scene'] == OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST || $developmentMemberId == $this->memberId) {
                $wxTransactionPayResult = WxTransactionComponentOrderService::callback($openid, $order[0], $result['pay_params']);

                if (is_error($wxTransactionPayResult)) {
                    return $this->error($wxTransactionPayResult['message']);
                }

                // 判断是否请求视频号订单成功(如果成功将返回视频号支付参数)
                if ($wxTransactionPayResult) {
                    $data = $wxTransactionPayResult;
                }
            }
        }

        return $this->result([
            'data' => $data,
        ]);
    }

    /**
     * 支付校验
     * @return array|Response
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

        // string转为int
        $payTypeCode = PayTypeConstant::getPayTypeCodeByIdentity($payType);
        if (empty($payTypeCode)) {
            return $this->error('不支持的支付类型');
        }

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

        //判断数量是否相等
        if ($payOrder !== count((array)$orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR);
        }

        return $this->result('支付成功');
    }
}
