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

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\form\FormTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\OrderConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\form\FormLogModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;

/**
 * 订单详情
 * Class DetailController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\order
 */
class DetailController extends KdxAdminApiController
{

    /**
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $orderId = RequestHelper::getInt('order_id');
        $isRefund = RequestHelper::getInt('is_refund', 0);
        $orderGoodsId = RequestHelper::getInt('order_goods_id', 0);
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_PARAMS_ERROR);
        }

        //查找订单
        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId, $orderGoodsId);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_ORDER_NOT_FOUND_ERROR);
        }

        //获取其他包裹
        if ($orderInfo['status'] > OrderStatusConstant::ORDER_STATUS_WAIT_PAY) {
            $qitaPackage = OrderPackageModel::where([
                'order_id' => $orderId,
            ])->select([
                'count(*) as total',
                "count(if(express_com='qita',true,null)) qita_total",
            ])->groupBy('order_id')->first();

            if ($qitaPackage['total'] <= $qitaPackage['qita_total']) $orderInfo['not_show_express'] = 1;
        }

        //初始化icon
        $orderInfo['icon'] = [
            'electronic_sheet' => 0
        ];

        //如果订单状态大于等于付款 并且没有维权
        if ($orderInfo['status'] >= OrderStatusConstant::ORDER_STATUS_WAIT_SEND && $orderInfo['is_refund'] == 0) {
            $orderInfo['icon']['electronic_sheet'] = 1;
        }

        if ($isRefund == 1) {

            // 非维权订单
            if ($orderInfo['is_refund'] != 1) {
                throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_IS_NOT_REFUND_ORDER);
            }

            // 非单品维权订单
            if (!empty($orderGoodsId) && $orderInfo['refund_type'] != 2) {
                throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_IS_NOT_SINGLE_REFUND_ORDER);
            }

            // 获取维权信息
            $orderInfo['refund_info'] = OrderRefundModel::getOrderRefundInfo($orderId, $orderGoodsId);
        }

        // 分销信息
        // 获取单个商品的
        if ($isRefund == 1 && !empty($orderGoodsId)) {
            $orderInfo['commission_info'] = CommissionOrderGoodsModel::getOrderGoodsCommissionInfo($orderId, $orderGoodsId);
        } else {
            // 整个订单的
            $orderInfo['commission_info'] = CommissionOrderDataModel::getOrderCommissionInfo($orderId);
        }

        // 无分销信息 删除该元素
        if (empty($orderInfo['commission_info'])) {
            unset($orderInfo['commission_info']);
        }


        $orderInfo['form'] = FormLogModel::get(FormTypeConstant::FORM_TYPE_ORDER, $orderInfo['member_id'], $orderInfo['id']);

        // 虚拟卡密详情展示
        if ($orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT && $orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_NOT_DELIVERY) {
            // 待支付订单
            $orderVirtualMap = VirtualAccountOrderMapModel::getInfo($orderInfo['id']);

            $orderInfo['virtual_account_data'] = [];
            $orderInfo['to_mailer'] = $orderVirtualMap->to_mailer ?? '';
            // 已完成订单
            if ($orderInfo['status'] == OrderConstant::ORDER_STATUS_SUCCESS) {
                $orderVirtualMap = VirtualAccountOrderMapModel::getDetails($orderInfo['id']);

                $orderInfo['to_mailer'] = $orderVirtualMap[0]['to_mailer'];
                $orderInfo['virtual_account_data'] = $orderVirtualMap;
            }
        }

        return $this->success($orderInfo);
    }

}