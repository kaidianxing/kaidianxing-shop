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

namespace shopstar\mobile\order;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\RequestHelper;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\services\order\OrderService;
use yii\web\Response;

/**
 * Class OpController
 * @author 青岛开店星信息技术有限公司
 * @package shop\client\order
 */
class OpController extends BaseMobileApiController
{
    /**
     * 取消订单
     * @return array|Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCancel()
    {
        $id = RequestHelper::postInt('id');
//        $cancelReason = Request::post('cancel_reason');
        if (empty($id)) {
            throw new OrderException(OrderException::ORDER_OP_CANCEL_PARAMS_ERROR);
        }

        // 查询订单
        $order = OrderModel::findOne(['id' => $id]);

        // 订单已支付 不能取消
        if ($order->status > OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            throw new OrderException(OrderException::ORDER_OP_CANCEL_ORDER_STATUS_SEND_ERROR);
        }

        //订单已取消
        if ($order->status < OrderStatusConstant::ORDER_STATUS_CLOSE) {
            throw new OrderException(OrderException::ORDER_OP_CANCEL_ORDER_STATUS_CLOSE_ERROR);
        }

        //关闭订单
        $result = OrderService::closeOrder($order, OrderConstant::ORDER_CLOSE_TYPE_BUYER_CLOSE, 0, [
            'is_refund_front' => 0
        ]);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_OP_CANCEL_ORDER_ERROR, $result['message']);
        }

        return $this->result($result);
    }

    /**
     * 确认收货
     * @return array|Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionFinish()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new OrderException(OrderException::ORDER_OP_FINISH_ORDER_PARAMS_ERROR);
        }

        $order = OrderModel::getOrderAndOrderGoods($id);
        if ($order['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
            throw new OrderException(OrderException::ORDER_OP_FINISH_ORDER_STATUS_ERROR);
        }

        $result = OrderService::complete($order);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_OP_FINISH_ORDER_ERROR, $result['message']);
        }

        return $this->result($result);
    }

    /**
     * 删除
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new OrderException(OrderException::ORDER_OP_DELETE_PARAMS_ERROR);
        }

        $order = OrderModel::findOne(['id' => $id]);
        if ($order['status'] != OrderStatusConstant::ORDER_STATUS_SUCCESS && $order['status'] != OrderStatusConstant::ORDER_STATUS_CLOSE) {
            throw new OrderException(OrderException::ORDER_OP_DELETE_ORDER_STATUS_ERROR);
        }

        $refundInfo = OrderRefundModel::find()->where([
            'order_id' => $id,
            'is_history' => 0])->asArray()->all();
        if (!empty($refundInfo)) {
            //取交集
            $intersect = array_intersect(array_column($refundInfo, 'status'), [
                RefundConstant::REFUND_STATUS_CANCEL,
                RefundConstant::REFUND_STATUS_REJECT,
                RefundConstant::REFUND_STATUS_SUCCESS,
            ]);

            if (empty($intersect)) {
                throw new OrderException(OrderException::ORDER_OP_DELETE_ORDER_EXIST_REFUND_ERROR);
            }
        }

        $result = OrderModel::deleteOrder($id);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_OP_DELETE_ERROR);
        }

        return $this->result($result);
    }
}
