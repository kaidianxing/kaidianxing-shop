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


namespace shopstar\services\sale;

use apps\article\constants\ArticleSellDataConstant;
use shopstar\bases\service\BaseService;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponLogModel;
use shopstar\models\sale\CouponModel;
use shopstar\structs\order\OrderPaySuccessStruct;

class CouponLogService extends BaseService
{

    /**
     * 支付成功
     * @param OrderPaySuccessStruct $orderPaySuccessStruct
     * @return array|bool|MemberModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function paySuccess(OrderPaySuccessStruct $orderPaySuccessStruct)
    {
        //订单是否存在
        // 获取支付日志
        $order = CouponLogModel::findOne([
            'id' => $orderPaySuccessStruct->orderId,
            'member_id' => $orderPaySuccessStruct->accountId,
        ]);
        if (empty($order)) {
            return error('订单不存在');
        } elseif ($order->status == 1) {
            return error('订单已支付');
        }

        //更改订单的金额支付状态
        $order->pay_status = 1;

        //修改订单状态
        $order->status = 1;

        // 订单支付类型
        $order->pay_type = $orderPaySuccessStruct->payType;

        // 查询优惠券是否存在
        $coupon = CouponModel::find()
            ->where([
                'id' => $order->coupon_id,
            ])
            ->first();
        if (empty($coupon)) {
            return error('优惠券不存在');
        }

        // 发送优惠券
        $memberCouponSendRes = CouponMemberService::sendCoupon($orderPaySuccessStruct->accountId, $coupon, 12, ['get_id' => true]);
        if (is_error($memberCouponSendRes)) {
            return $memberCouponSendRes;
        }

        if (!$order->save()) {
            return error('订单状态修改失败');
        }

        return true;
    }
}