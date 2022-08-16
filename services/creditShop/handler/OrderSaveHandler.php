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

namespace shopstar\services\creditShop\handler;

use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\virtualAccount\VirtualAccountDataConstant;
use shopstar\exceptions\creditShop\CreditShopOrderException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;
use yii\helpers\Json;

class OrderSaveHandler implements HandlerInterface
{
    /**
     * 订单实体类
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public OrderCreatorKernel $orderCreatorKernel;

    /**
     * GoodsHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 保存积分商城订单
     * @return array
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor(): array
    {
        $order = new OrderModel();
        // 保存 order

        // 保存前再处理下字段类型，及保存前才可装载的数据
        $orderData = array_merge($this->orderCreatorKernel->orderData, [
            'member_id' => $this->orderCreatorKernel->memberId,

            'order_no' => $this->orderCreatorKernel->createOrderNo(),

            'create_time' => $this->orderCreatorKernel->createTime,

            //订单来源
            'create_from' => $this->orderCreatorKernel->clientType,

            // 自动关闭时间
            'auto_close_time' => $this->orderCreatorKernel->autoCloseTime ?: DateTimeHelper::DEFAULT_DATE_TIME,

            // 商品信息
            'goods_info' => Json::encode($this->orderCreatorKernel->orderData['goods_info']),

            // 地址信息
            'address_info' => Json::encode($this->orderCreatorKernel->orderData['address_info']) ?: '',

            // 配送信息
            'dispatch_info' => Json::encode($this->orderCreatorKernel->orderData['dispatch_info']),

            // 发票信息
            'invoice_info' => Json::encode($this->orderCreatorKernel->orderData['invoice_info']),

            // 活动规则
            'extra_discount_rules_package' => Json::encode($this->orderCreatorKernel->orderData['extra_discount_rules_package'] ?: []),
            'extra_price_package' => Json::encode($this->orderCreatorKernel->orderData['extra_price_package'] ?: []),

            // 订单额外的支付价格
            'extra_pay_price' => !empty($this->orderCreatorKernel->orderData['extra_pay_price']) ? Json::encode($this->orderCreatorKernel->orderData['extra_pay_price']) : '',

            // 额外的数据包(表单数据\自动发货信息\会员卡信息等下单后不会修改的额外数据)
            'extra_package' => !empty($this->orderCreatorKernel->orderData['extra_package']) ? Json::encode($this->orderCreatorKernel->orderData['extra_package']) : '',
        ]);

        $order->setAttributes($orderData);
        if (!$order->save()) {
            //重新抛出异常
            throw new CreditShopOrderException(CreditShopOrderException::ORDER_SAVE_HANDLER_CREATE_ORDER_ERROR, $order->getErrorMessage());
        }

        $orderGoodsData = $this->orderCreatorKernel->orderGoodsData;
        array_walk($orderGoodsData, function (&$row) use ($order) {
            // 合并数据
            $row = array_merge($row, [
                'order_id' => $order->id,
                'member_id' => $this->orderCreatorKernel->memberId,
                'create_time' => $this->orderCreatorKernel->createTime,
            ]);

            $row['activity_package'] = Json::encode((array)$row['activity_package']);
            $row['ext_field'] = Json::encode((array)$row['ext_field']);
            $row['dispatch_info'] = Json::encode((array)$row['dispatch_info']);
            $row['plugin_identification'] = Json::encode((array)$row['plugin_identification']);
        });

        //订单商品id
        $orderGoodsIds = [];
        foreach ($orderGoodsData as $orderGoodsDataItem) {
            $model = new OrderGoodsModel();
            $model->setAttributes($orderGoodsDataItem);
            if (!$model->save()) {
                //订单商品添加失败
                throw new CreditShopOrderException(CreditShopOrderException::ORDER_SAVE_HANDLER_CREATE_ORDER_GOODS_ERROR, $model->getErrorMessage());
            }

            //处理订单商品id 保存到订单上
            $orderGoodsIds[$orderGoodsDataItem['goods_id'] . '_' . $orderGoodsDataItem['option_id']] = $model->id;

            // 处理虚拟卡密订单的订单关联卡密表
            if ($orderGoodsDataItem['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                // 购买多个数量,需要锁定多个卡密数据信息
                for ($i = 1; $i <= $orderGoodsDataItem['total']; $i++) {
                    $virModel = new VirtualAccountOrderMapModel();
                    $virAccInfo = VirtualAccountModel::getInfo($orderGoodsDataItem['virtual_account_id']);
                    $virtualAccountDataInfo = VirtualAccountDataModel::getId($virAccInfo);

                    $virModel->setAttributes([
                        'order_id' => $order->id,
                        'virtual_account_id' => $orderGoodsDataItem['virtual_account_id'],
                        'virtual_account_data_id' => $virtualAccountDataInfo['id'],
                        'use_description' => $virAccInfo['use_description'],
                        'use_description_title' => $virAccInfo['use_description_title'],
                        'use_description_remark' => $virAccInfo['use_description_remark'],
                        'use_address' => $virAccInfo['use_address'],
                        'use_address_title' => $virAccInfo['use_address_title'],
                        'use_address_address' => $virAccInfo['use_address_address'],
                        'data' => $virtualAccountDataInfo['data'],
                        'config' => $virAccInfo['config'],
                        'to_mailer' => $this->orderCreatorKernel->inputData['virtual_email'],
                    ]);

                    if (!$virModel->save()) {
                        //订单虚拟卡密数据添加失败
                        throw new CreditShopOrderException(CreditShopOrderException::ORDER_SAVE_HANDLER_CREATE_ORDER_VIRTUAL_ACCOUNT_ERROR, $model->getErrorMessage());
                    }

                    VirtualAccountDataModel::updateStatus($virModel->virtual_account_data_id, VirtualAccountDataConstant::ORDER_VIRTUAL_ACCOUNT_DATA_WAIT_PAY);
                }
            }
        }

        foreach ($this->orderCreatorKernel->orderData['goods_info'] as &$goodsItem) {
            if (isset($orderGoodsIds[$goodsItem['goods_id'] . '_' . $goodsItem['option_id']])) {
                $goodsItem['order_goods_id'] = $orderGoodsIds[$goodsItem['goods_id'] . '_' . $goodsItem['option_id']];
            }
        }

        //重新保存订单商品
        $order->goods_info = Json::encode($this->orderCreatorKernel->orderData['goods_info']);
        if (!$order->save()) {
            throw new CreditShopOrderException(CreditShopOrderException::ORDER_SAVE_HANDLER_ORDER_GOODS_ORDER_SAVE_ERROR);
        }

        // 保存积分商城订单
        $creditShopOrder = new CreditShopOrderModel();
        $creditShopOrder->setAttributes([
            'order_id' => $order->id,
            'member_id' => $order->member_id,
            'pay_credit' => $this->orderCreatorKernel->orderData['goods_info'][0]['credit'],
            'pay_price' => $order->pay_price,
            'goods_id' => $this->orderCreatorKernel->orderGoodsData[0]['goods_id'],
            'option_id' => $this->orderCreatorKernel->orderGoodsData[0]['option_id'],
            'shop_goods_id' => $this->orderCreatorKernel->orderGoodsData[0]['shop_goods_id'],
            'shop_option_id' => $this->orderCreatorKernel->orderGoodsData[0]['shop_option_id'],
            'total' => $this->orderCreatorKernel->orderGoodsData[0]['total'],
            'type' => $this->orderCreatorKernel->orderData['order_type'] == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON ? 1 : 0,
            'credit_unit' => $this->orderCreatorKernel->orderGoodsData[0]['credit_unit'],
            'client_type' => $this->orderCreatorKernel->clientType,
        ]);

        if (!$creditShopOrder->save()) {
            throw new CreditShopOrderException(CreditShopOrderException::ORDER_SAVE_HANDLER_CREDIT_SHOP_ORDER_SAVE_ERROR);
        }

        // 提交就要扣积分
        MemberModel::updateCredit($this->orderCreatorKernel->memberId,
            $this->orderCreatorKernel->orderData['pay_credit'],
            0,
            'credit',
            2,
            '积分商城支付',
            MemberCreditRecordStatusConstant::CREDIT_SHOP_PAY, ['order_id' => $order->id]);

        return $order->toArray();
    }
}
