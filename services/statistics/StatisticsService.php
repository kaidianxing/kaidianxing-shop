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


namespace shopstar\services\statistics;

use shopstar\bases\service\BaseService;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\RefundConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberBrowseFootprintModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\statistics\StatisticsModel;
use shopstar\models\statistics\StatisticsUniqueViewModel;

class StatisticsService extends BaseService
{

    /**
     * 每日统计
     * 定时任务调用
     * @return bool|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function createDayStatistic()
    {
        // 查找是否统计过
        $date = date('Y-m-d', strtotime('-1 day'));
        $isCalculate = StatisticsModel::find()->where(['statistic_date' => $date])->exists();
        if ($isCalculate) {
            return $date . ' 该日期已统计';
        }

        // 统计
        self::calculate($date);

        return true;

    }

    /**
     * 统计
     * @param string $date 统计日期
     * @param array $countFields
     * @param bool $isReturn 是否返回
     * @param bool $isUpdate 是否更新数据库
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculate(string $date, array $countFields = [], bool $isReturn = false, bool $isUpdate = false)
    {
        $fields = [
            'member_count', // 用户总数
            'member_new_count', // 新用户数
            'order_new_count', // 新订单数
            'order_new_price_sum', // 新订单总额
            'order_pay_count', // 支付订单数
            'order_pay_price_sum', // 支付订单总额
            'order_pay_member_count', // 支付用户数
            'order_member_count', // 下单用户数
            'order_new_member_count', // 新下单用户数
            'member_source_wechat_count', // 来自微信的用户数
            'member_source_wxapp_count', // 来自微信小程序的用户数
            'member_source_h5_count', // 来自 h5 的用户数
            'member_source_alipay_count', // 来自支付宝的用户数
            'member_source_byte_count', // 来自字节跳动的用户数
            'order_refund_count', // 维权订单数
            'order_refund_price_sum', // 维权订单总额
            'cart_goods_count', // 购物车商品数量
            'shelves_goods_count', // 在架商品数
            'pay_goods_count', // 当天付款商品数
            'uv', // UV
            'goods_pv_count', // 商品浏览次数

        ];
        if (!empty($countFields)) {
            $fields = array_intersect($fields, $countFields);
        }

        if (empty($date)) {
            $date = date('Y-m-d', strtotime('-1 days'));
        }
        $startTime = $date . ' 00:00:00';
        $endTime = $date . ' 23:59:59';

        // 如果日期是今天 则返回，不插入数据库
        if ($date == DateTimeHelper::now(false)) {
            $isReturn = true;
        }

        $data = [];

        // 截止到当天 总用户数
        if (in_array('member_count', $fields)) {
            $data['member_count'] = MemberModel::find()
                ->where(['is_deleted' => 0])
                ->count();
        }

        // 当天新用户数
        if (in_array('member_new_count', $fields)) {
            $data['member_new_count'] = MemberModel::find()
                ->where(['is_deleted' => 0])
                ->andWhere(['between', 'created_at', $startTime, $endTime])
                ->count();
        }

        // 当天下单总额
        if (in_array('order_new_price_sum', $fields)) {
            $data['order_new_price_sum'] = OrderModel::find()
                ->where(['between', 'created_at', $startTime, $endTime])
                ->andWhere(['>=', 'status', 0])
                ->sum('pay_price') ?: 0;

        }

        // 当天下单总数
        if (in_array('order_new_count', $fields)) {
            $data['order_new_count'] = OrderModel::find()
                ->where(['between', 'created_at', $startTime, $endTime])
                ->andWhere(['>=', 'status', 0])
                ->count();
        }

        // 支付笔数：商城店铺内完成支付的订单数。
        if (in_array('order_pay_count', $fields)) {
            $data['order_pay_count'] = OrderModel::find()
                ->where([
                    'and',
                    ['>', 'status', 0],
                    ['between', 'pay_time', $startTime, $endTime]
                ])->count();
        }

        // 支付金额：商城店铺内完成支付的订单金额总和。
        if (in_array('order_pay_price_sum', $fields)) {
            $data['order_pay_price_sum'] = OrderModel::find()
                ->where([
                    'and',
                    ['<>', 'pay_type', 0],
                    ['between', 'pay_time', $startTime, $endTime]
                ])->sum('pay_price') ?: 0;

        }

        // 成交用户：成功完成订单支付的用户数，同一用户   多次成功支付不重复计
        if (in_array('order_pay_member_count', $fields)) {
            $data['order_pay_member_count'] = OrderModel::find()
                ->where([
                    'and',
                    ['<>', 'pay_type', 0],
                    ['between', 'pay_time', $startTime, $endTime]
                ])
                ->count('distinct(member_id)');

        }

        // 当天下单用户数
        if (in_array('order_member_count', $fields)) {
            $data['order_member_count'] = OrderModel::find()
                ->where(['between', 'created_at', $startTime, $endTime])
                ->andWhere(['>=', 'status', 0])
                ->count('distinct(member_id)');
        }

        // 新下单用户数
        if (in_array('order_new_member_count', $fields)) {
            $data['order_new_member_count'] = OrderModel::find()
                ->alias('order')
                ->leftJoin(MemberModel::tableName() . ' member', 'member.id=order.member_id')
                ->where([
                    'and',
                    ['>=', 'order.status', 0],
                    ['between', 'order.created_at', $startTime, $endTime],
                    ['between', 'member.created_at', $startTime, $endTime],
                ])
                ->count('distinct(member_id)');
        }

        // 来自微信的新用户数
        if (in_array('member_source_wechat_count', $fields)) {
            $data['member_source_wechat_count'] = MemberModel::find()
                ->where(['is_deleted' => 0, 'source' => ClientTypeConstant::CLIENT_WECHAT])
                ->andWhere(['between', 'created_at', $startTime, $endTime])
                ->count();
        }

        // 来自微信的新用户数
        if (in_array('member_source_wxapp_count', $fields)) {
            $data['member_source_wxapp_count'] = MemberModel::find()
                ->where(['is_deleted' => 0, 'source' => ClientTypeConstant::CLIENT_WXAPP])
                ->andWhere(['between', 'created_at', $startTime, $endTime])
                ->count();
        }

        // 来自 h5 的新用户数
        if (in_array('member_source_h5_count', $fields)) {
            $data['member_source_h5_count'] = MemberModel::find()
                ->where(['is_deleted' => 0, 'source' => ClientTypeConstant::CLIENT_H5])
                ->andWhere(['between', 'created_at', $startTime, $endTime])
                ->count();
        }

//        // 来自支付宝的用户数
//        if (in_array('member_source_alipay_count', $fields)) {
//            $data['member_source_alipay_count'] = 0;
//        }

        // 来自字节跳动的用户数
//        if (in_array('member_source_byte_count', $fields)) {
//            $data['member_source_byte_count'] = 0;
//        }

        // 来自微信的订单数
        if (in_array('order_from_wechat_count', $fields)) {
            $data['order_from_wechat'] = 0;
        }

        // 来自微信小程序的订单数
        if (in_array('order_from_wxapp', $fields)) {
            $data['order_form_wxapp'] = 0;
        }

        // 来自 h5 的订单数
        if (in_array('order_from_h5', $fields)) {
            $data['order_from_h5'] = 0;
        }

        // 查询维权订单数量
        if (in_array('order_refund_count', $fields)) {
            $data['order_refund_count'] = OrderRefundModel::find()
                ->where([
                    'and',
                    ['between', 'finish_time', $startTime, $endTime],
                    ['is_history' => 0],
                    [
                        'or',
                        ['status' => RefundConstant::REFUND_STATUS_SUCCESS],
                        ['status' => RefundConstant::REFUND_STATUS_MANUAL]
                    ]

                ])->count();
        }

        // 退款金额：商城店铺内成功完成退款的退款金额总和。
        if (in_array('order_refund_price_sum', $fields)) {
            $data['order_refund_price_sum'] = OrderRefundModel::find()
                ->where([
                    'and',
                    ['between', 'finish_time', $startTime, $endTime],
                    [
                        'or',
                        ['status' => RefundConstant::REFUND_STATUS_SUCCESS],
                        ['status' => RefundConstant::REFUND_STATUS_MANUAL]
                    ],
                    [
                        'or',
                        ['refund_type' => RefundConstant::TYPE_REFUND],
                        ['refund_type' => RefundConstant::TYPE_RETURN]
                    ]
                ])->sum('price') ?: 0;
        }

        // 加入购物车商品数量
        if (in_array('cart_goods_count', $fields)) {
            $data['cart_goods_count'] = GoodsCartModel::find()
                ->where(['between', 'created_at', $startTime, $endTime])->count();
        }

        // 在架商品数
        if (in_array('shelves_goods_count', $fields)) {
            $data['shelves_goods_count'] = GoodsModel::find()
                ->where([
                    'or',
                    ['status' => 1],
                    ['status' => 2]
                ])->count();
        }

        // 购买商品数量/件：统计时间内，成功付款的商品件数之和（包含退款订单）
        if (in_array('pay_goods_count', $fields)) {
            $data['pay_goods_count'] = OrderGoodsModel::find()
                ->alias('og')
                ->leftJoin(OrderModel::tableName() . ' o', 'o.id=og.order_id')
                ->where([
                    'and',
                    ['>', 'o.status', 0],
                    ['between', 'o.pay_time', $startTime, $endTime],
                    ['og.shop_goods_id' => 0], // 过滤积分订单
                ])->sum('og.total') ?: 0;
        }

        // uv
        if (in_array('uv', $fields)) {
            $data['uv'] = StatisticsUniqueViewModel::find()
                ->where([
                    'and',
                    ['create_date' => $date]
                ])->count();
        }

        // 商品浏览件数
        if (in_array('goods_pv_count', $fields)) {
            $data['goods_pv_count'] = MemberBrowseFootprintModel::find()
                ->where(['between', 'created_at', $startTime, $endTime])
                ->count('distinct(goods_id)');
        }

        // 只返回
        if ($isReturn == true) {
            return $data;
        }
        $data['statistic_date'] = $date;

        // 先查找有没有该日期的记录
        $statistics = StatisticsModel::findOne(['statistic_date' => $date]);
        // 如果不存在 则 新建
        if (empty($statistics)) {
            $statistics = new StatisticsModel();
        } else {
            // 如果存在 且 不更新的话 返回错误
            if (!$isUpdate) {
                return error('该日期已统计');
            }
        }
        $statistics->setAttributes($data);
        if ($statistics->save() === false) {
            return error('数据保存失败,' . $statistics->getErrorMessage());
        }

        return true;
    }


}