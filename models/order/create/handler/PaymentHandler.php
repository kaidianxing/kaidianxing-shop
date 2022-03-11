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

namespace shopstar\models\order\create\handler;

use shopstar\constants\ClientTypeConstant;
use shopstar\constants\PaymentConstant;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\helpers\RequestHelper;
use shopstar\models\order\create\OrderCreatorKernel;

class PaymentHandler
{
    /**
     * @author 青岛开店星信息技术有限公司
     * @var OrderCreatorKernel
     */
    public $orderCreatorKernel;

    /**
     * PaymentHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel $orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function check()
    {
        // 判断支付设置不为空
        $payment = $this->orderCreatorKernel->shopOrderSettings['payment']['typeset'][ClientTypeConstant::getIdentify(RequestHelper::header('Client-Type'))] ?? [];
        if (empty($payment)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_PAYMENT_EMPTY_ERROR);
        }

        // 已启用的支付方式
        $this->orderCreatorKernel->payment = [];

        foreach ($payment as $type => $item) {
            if (empty($item['enable'])) {
                continue;
            } elseif ($type == 'delivery') {
                // 货到付款与商品有关，直接跳出，定义creator变量，loadGoods继续处理
                $this->orderCreatorKernel->deliveryPay = (int)$item['enable'];
                continue;
            }

            // 塞入支付方式
            $this->orderCreatorKernel->payment[] = [
                'name' => PaymentConstant::getText($type),
                'type' => $type,
            ];
        }

        // 在线支付时，如果没有启用的支付方式则报错
        if (!$this->orderCreatorKernel->isConfirm) {

            //这里判断如果所有的支付方式都未开启则报错，如果只开了货到付款则到 goodsHandel 继续处理
            if (empty($this->orderCreatorKernel->payment) && $this->orderCreatorKernel->deliveryPay == 0) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_USABLE_PAYMENT_EMPTY_ERROR);
            }
        }


        return $this->orderCreatorKernel->payment;
    }
}