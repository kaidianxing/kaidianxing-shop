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

namespace shopstar\services\wxTransactionComponent;

use shopstar\components\wechat\helpers\MiniProgramWxTransactionComponentHelper;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sysset\RefundAddressModel;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * 视频号订单服务
 * Class WxTransactionComponentOrderService.
 * @package shopstar\services\wxTransactionComponent
 */
class WxTransactionComponentOrderService
{
    /**
     * 视频号订单上传 获取支付参数
     * @param string $openId
     * @param $order
     * @param $payParams
     * @return array|mixed
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function callback(string $openId, $order, $payParams)
    {
        // 生成视频号订单 uploadOrder
        $wxTransactionComponentResult = self::uploadOrder($order, $payParams);

        if (is_error($wxTransactionComponentResult)) {
            return error($wxTransactionComponentResult['message'], $wxTransactionComponentResult['error']);
        }

        // 生成支付参数 getPaymentParams
        $wxTransactionComponentPayResult = self::getPaymentParams($openId, $wxTransactionComponentResult['out_order_id'] ?: '', $wxTransactionComponentResult['order_id'] ?: '');

        return is_error($wxTransactionComponentPayResult) ? error($wxTransactionComponentPayResult['message'], $wxTransactionComponentPayResult['error']) : $wxTransactionComponentPayResult;
    }

    /**
     * 上传生成订单
     * @param $order
     * @param $ret
     * @return array|false|mixed
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadOrder($order, $ret)
    {
        $data = WxTransactionComponentService::uploadOrderProcess($order['id'], $ret);
        if (!$data) {
            return false;
        }

        $result = MiniProgramWxTransactionComponentHelper::uploadOrder($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            //更改订单场景值
            OrderModel::updateAll([
                'scene' => OrderSceneConstant::ORDER_SCENE_VIDEO_NUMBER_BROADCAST
            ], [
                'id' => $order['id'],
            ]);

            return $result['data'] ?: [];
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 获取支付参数
     * @param string $openId
     * @param string $orderId
     * @param string $outOrderId
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPaymentParams(string $openId = '', string $orderId = '', string $outOrderId = '')
    {
        $data = [
            'out_order_id' => $orderId,
            'order_id' => $outOrderId,
            'openid' => $openId,
        ];

        $result = MiniProgramWxTransactionComponentHelper::getPaymentParams($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return $result['payment_params'] ?: [];
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 更新地址
     * @param int $memberId
     * @param int $orderId
     * @param array $address
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadOrderAddress(int $memberId, int $orderId, array $address = [])
    {
        $data = [
            'out_order_id' => $orderId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
            'address_info' => [
                'receiver_name' => $address['buyer_name'] ?: '',
                'detailed_address' => $address['address_detail'] ?: '',
                'tel_number' => $address['buyer_mobile'] ?: '',
                'province' => $address['address_province'] ?: '',
                'city' => $address['address_city'] ?: '',
                'town' => $address['address_area'] ?: '',
            ],
        ];

        $result = MiniProgramWxTransactionComponentHelper::uploadOrderAddress($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 订单发货
     * @param $order
     * @param $memberId
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendOrder($order, $memberId, $data)
    {
        // 查询订单是否存在
        $wxAppOrder = self::getOrder($order, $memberId);

        if (!$wxAppOrder) {
            LogHelper::error('["WX_APP_SEND_ORDER_ERROR"]: 未查询到视频号订单', []);
            return true;
        }

        // 获取系统中的物流公司标识
        $express = CoreExpressModel::findOne($data['express_id']);

        // 获取微信小程序端物流公司标识
        $courierCompany = MiniProgramWxTransactionComponentHelper::getCourierCompany([]);
        if ($courierCompany && $express->key) {
            if (!in_array($express->key, array_column($courierCompany['company_list'], 'delivery_id'))) {
                $express->key = 'OTHERS';
            }
        }

        $productInfoList = [];
        if (!empty($order['orderGoods'])) {
            foreach ($order['orderGoods'] as $orderGood) {
                $productInfoList[] = [
                    'out_product_id' => $orderGood['goods_id'],
                    'out_sku_id' => !$orderGood['option_id'] ? $orderGood['goods_id'] : $orderGood['option_id'],
                    'product_cnt' => $orderGood['total'],
                ];
            }
        }

        $data = [
            'out_order_id' => $order['id'],
            'openid' => WxTransactionComponentService::getOpenId($memberId),
            'finish_all_delivery' => !$data['more_package'] ? 1 : 0, // 1是全部发货 0是未全部发货
            'delivery_list' => [
                [
                    'delivery_id' => $express->key,
                    'waybill_id' => $data['express_sn'],
                    'product_info_list' => $productInfoList, // 物流单对应的商品信息
                ]
            ]
        ];

        if ($data['finish_all_delivery'] == 1) {
            $data['ship_done_time'] = DateTimeHelper::now(); // 完成发货时间
        }

        $result = MiniProgramWxTransactionComponentHelper::sendOrder($data);

        // 发货失败
        if (isset($result['errcode']) ? $result['errcode'] != 0 : $result['error'] != 0) {
            LogHelper::error('["WX_APP_SEND_ORDER_ERROR"]:', $result);
            return error('同步小程序订单发货失败');
        }

        return $result;
    }

    /**
     * 获取订单
     * @param $order
     * @param $memberId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrder($order, $memberId): bool
    {
        $params = [
            'out_order_id' => $order['id'],
            'openid' => WxTransactionComponentService::getOpenId($memberId),
        ];

        $wxAppOrder = MiniProgramWxTransactionComponentHelper::getOrder($params);

        if (isset($wxAppOrder['errcode']) ? $wxAppOrder['errcode'] != 0 : $wxAppOrder['error'] != 0) {
            return false;
        }

        return true;
    }

    /**
     * 确认收货
     * @param $orderId
     * @param $memberId
     * @return array|bool
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function confirmOrderStatus($orderId, $memberId)
    {
        $wxAppOrder = self::getOrder(['id' => $orderId], $memberId);

        // 未在微信订单中查到订单
        if (!$wxAppOrder) {
            return true;
        }

        $data = [
            'out_order_id' => $orderId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
        ];

        $result = MiniProgramWxTransactionComponentHelper::confirmOrderStatus($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        LogHelper::error('["WX_APP_CONFIRM_ORDER_STATUS_ERROR"]:', $result);
        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 获取维权单详情
     * @param $afterSaleId
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRefundDetail($afterSaleId)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
        ];

        $wxRefundDetail = MiniProgramWxTransactionComponentHelper::getRefundDetail($data);

        if (isset($wxRefundDetail['errcode']) ? $wxRefundDetail['errcode'] == 0 : $wxRefundDetail['error'] == 0) {
            return $wxRefundDetail;
        }

        return error($wxRefundDetail['errmsg'] ?? $wxRefundDetail['message']);
    }

    /**
     * 关闭订单
     * @param int $orderId
     * @param int $memberId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeOrder(int $orderId, int $memberId)
    {
        $data = [
            'out_order_id' => $orderId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
        ];

        $resultVideo = MiniProgramWxTransactionComponentHelper::closeOrder($data);

        if (isset($resultVideo['errcode']) ? $resultVideo['errcode'] == 0 : $resultVideo['error'] == 0) {
            return true;
        }

        return error($resultVideo['errmsg'] ?? $resultVideo['message']);
    }

    /**
     * 用户取消售后单
     * @param int $afterSaleId
     * @param int $memberId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function cancelRefund(int $afterSaleId, int $memberId)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
        ];

        $resultVideo = MiniProgramWxTransactionComponentHelper::cancelRefund($data);

        if (isset($resultVideo['errcode']) ? $resultVideo['errcode'] == 0 : $resultVideo['error'] == 0) {
            return true;
        }

        return error($resultVideo['errmsg'] ?? $resultVideo['message']);
    }

    /**
     * 提交售后维权
     * @param int $orderId
     * @param int $refundId
     * @param int $memberId
     * @param int $refundType
     * @param int $orderGoodsId
     * @param float $price
     * @param string $content
     * @return array|mixed
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function refundSubmit(int $orderId, int $refundId, int $memberId, int $refundType, int $orderGoodsId, float $price, string $content = '')
    {
        //整单维权
        $where = [
            'order_id' => $orderId,
            'is_deleted' => 0,
        ];

        if (!empty($orderGoodsId)) {
            $where['id'] = $orderGoodsId;
        }

        $data = [
            'out_order_id' => (string)$orderId,
            'out_aftersale_id' => (string)$refundId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
            'type' => (int)$refundType,
            'refund_reason' => $content,
            'refund_reason_type' => 12, // todo 微信的退款原因类型，商城现在没有固定写死12
            'orderamt' => (int)bcmul($price, 100),
        ];

        $orderGoodsList = OrderGoodsModel::find()->where($where)->asArray()->all();
        if ($orderGoodsList) {
            foreach ($orderGoodsList as $value) {
                $data['product_info'] = [
                    'out_product_id' => $value['goods_id'],
                    'out_sku_id' => !$value['option_id'] ? $value['goods_id'] : $value['option_id'],
                    'product_cnt' => (int)$value['total'],
                ];
            }
        }

        $result = MiniProgramWxTransactionComponentHelper::refundOrder($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return $result['aftersale_id'];
        }

        LogHelper::error('["WX_APP_REFUND_SUBMIT_ERROR"]:', $result);
        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 修改申请
     * @param int $afterSaleId
     * @param int $memberId
     * @param int $refundType
     * @param float $price
     * @param string $content
     * @return array|bool
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateRefund(int $afterSaleId, int $memberId, int $refundType, float $price, string $content = '')
    {
        $data = [
            'aftersale_id' => $afterSaleId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
            'type' => $refundType,
            'orderamt' => $price * 100,
            'refund_reason' => $content,
            'refund_reason_type' => 12, // todo 微信的退款原因类型，商城现在没有固定写死12
        ];

        $result = MiniProgramWxTransactionComponentHelper::updateRefund($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        LogHelper::error('["WX_APP_REFUND_UPDATE_ERROR"]:', $result);
        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 用户上传物流信息(维权退货)
     * @param int $afterSaleId 维权单号
     * @param int $memberId
     * @param $deliveryId
     * @param $waybillId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadReturnInfo(int $afterSaleId, int $memberId, $deliveryId, $waybillId)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
            'openid' => WxTransactionComponentService::getOpenId($memberId),
            'delivery_id' => $deliveryId,
            'waybill_id' => $waybillId,
        ];

        $result = MiniProgramWxTransactionComponentHelper::uploadReturnInfo($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 拒绝售后
     * @param int $afterSaleId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function rejectRefund(int $afterSaleId)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
        ];

        $result = MiniProgramWxTransactionComponentHelper::rejectRefund($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 同意退货
     * @param int $afterSaleId
     * @param RefundAddressModel $refundAddress
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function acceptReturn(int $afterSaleId, RefundAddressModel $refundAddress)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
            'address_info' => [
                'receiver_name' => $refundAddress->name,
                'detailed_address' => $refundAddress->address,
                'tel_number' => $refundAddress->mobile,
                'province' => $refundAddress->province,
                'city' => $refundAddress->city,
                'town' => $refundAddress->area,
            ],
        ];

        $result = MiniProgramWxTransactionComponentHelper::acceptReturn($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        return error($result['errmsg'] ?? $result['message']);
    }

    /**
     * 同意退款
     * @param int $afterSaleId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function returnAccept(int $afterSaleId)
    {
        $data = [
            'aftersale_id' => $afterSaleId,
        ];

        $result = MiniProgramWxTransactionComponentHelper::returnAccept($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            return true;
        }

        return error($result['errmsg'] ?? $result['message']);
    }
}
