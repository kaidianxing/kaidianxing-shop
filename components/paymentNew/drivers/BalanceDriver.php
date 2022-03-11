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


namespace shopstar\components\paymentNew\drivers;

use shopstar\components\paymentNew\bases\BasePaymentNewDriver;
use shopstar\components\paymentNew\bases\PaymentNewDriverInterface;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\models\member\MemberModel;
use shopstar\services\tradeOrder\TradeOrderOperation;
use shopstar\services\tradeOrder\TradeOrderPay;

/**
 * 余额支付驱动
 * Class BalanceDriver
 * @package shopstar\components\paymentNew
 * @author likexin
 */
class BalanceDriver extends BasePaymentNewDriver implements PaymentNewDriverInterface
{

    /**
     * 获取客户端映射
     * @return array
     * @author likexin
     * @deprecated
     */
    public function getClientMap(): array
    {
        return [];
    }

    /**
     * 统一下单(此处可根据业务逻辑进行转发)
     * @return array
     * @author likexin
     */
    public function unify(): array
    {
        $options = [];

        if (count((array)TradeOrderPay::$staticOrderId) == 1) {
            $options['order_id'] = current((array)TradeOrderPay::$staticOrderId);
        }

        // 调用会员余额扣除
        $result = MemberModel::updateCredit($this->accountId, $this->orderPrice, 0, 'balance', 2, '余额支付', MemberCreditRecordStatusConstant::BALANCE_STATUS_PAY, $options);
        if (is_error($result)) {
            return $result;
        }

        return success();
    }

    /**
     * 关闭订单
     * @return array|false
     * @deprecated
     * @author likexin
     */
    public function close(): array
    {
        return error('当前支付方式不支持关闭订单');
    }

    /**
     * 退款
     * @param float $orderPrice 订单总金额
     * @param float $refundPrice 退款金额
     * @param string $refundNo 退款编号
     * @return array
     * @author likexin
     */
    public function refund(float $orderPrice, float $refundPrice, string $refundNo): array
    {

        $options = [];

        if (count((array)TradeOrderOperation::$staticOrderId) == 1) {
            $options['order_id'] = current((array)TradeOrderOperation::$staticOrderId);
        }

        // 调用会员余额增加
        $result = MemberModel::updateCredit($this->accountId, $refundPrice, 0, 'balance', 1, '订单退款 refund_no:' . $refundNo, MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND, $options);
        if (is_error($result)) {
            return $result;
        }

        return success();
    }

    /**
     * 验签
     * @param $data
     * @return array
     * @author likexin
     * @deprecated
     */
    public function verifySign($data): array
    {
        return success();
    }
}