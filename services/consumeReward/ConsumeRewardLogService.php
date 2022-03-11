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


namespace shopstar\services\consumeReward;

use shopstar\bases\service\BaseService;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\consumeReward\ConsumeRewardActivityModel;
use shopstar\models\consumeReward\ConsumeRewardLogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberRedPackageModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

class ConsumeRewardLogService extends BaseService
{
    /**
     * 发送奖励
     * @param int $memberId 会员id
     * @param int $orderId 订单id
     * @param int $type 0 订单完成后  1 订单付款后
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendReward(int $memberId, int $orderId, int $type)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }

        // 查找订单 （该订单触发）
        $order = OrderModel::find()->where(['id' => $orderId])->first();

        // 获取记录
        $log = ConsumeRewardLogModel::findOne(['member_id' => $memberId, 'order_id' => $orderId]);

        if (empty($log)) {
            return error('记录不存在');
        }

        // 获取活动
        $activity = ConsumeRewardActivityModel::find()->where(['id' => $log->activity_id])->first();
        if (is_error($activity)) {
            return error('活动不存在');
        }

        // 发送结点
        if ($activity['send_type'] != $type) {
            return error('发送结点不匹配');
        }

        //排序奖励，获取满足最高的奖励
        if ($activity['rules']) {
            $activity['rules'] = Json::decode($activity['rules']);
            $activity['rules']['award'] = array_column($activity['rules']['award'], null, 'money');
            krsort($activity['rules']['award']);
        }

        //判断用户权限是否允许参与
        if ($activity['rules']['permission'] != 0) {

            //获取会员信息
            $member = MemberModel::where([
                'id' => $memberId,
            ])->select([
                'id',
                'level_id'
            ])->first();

            if ($activity['rules']['permission'] == 1 && !in_array($member['level_id'], $activity['rules']['permission_value'] ?? [])) {
                return error('没有权限');
            }

            if ($activity['rules']['permission'] == 2) {
                $memberTag = MemberGroupMapModel::where([
                    'member_id' => $memberId,
                ])->select([
                    'group_id'
                ])->column();

                //如果没有会员标签则没有权限
                if (empty($memberTag)) return error('没有权限');

                //如果没有交集则没有权限
                if (!array_intersect($memberTag, $activity['rules']['permission_value'])) return error('没有权限');
            }
        }

        // 发送结点 订单完成后
        if ($activity['send_type'] == 0) {
            $orderStatus = OrderStatusConstant::ORDER_STATUS_SUCCESS;
        } else {
            // 订单付款后
            $orderStatus = OrderStatusConstant::ORDER_STATUS_WAIT_SEND;
        }
        // 支付类型
        $payType = explode(',', $activity['pay_type']);
        // 活动限制
        if (!empty($activity['activity_limit'])) {
            $activityLimit = explode(',', $activity['activity_limit']);
        }
        // 不参与商品
        if (!empty($activity['goods_limit'])) {
            $goodsLimit = explode(',', $activity['goods_limit']);
        }


        // 累计消费
        if ($activity['type'] == 0) {
            // 是否参与过
            $isExists = ConsumeRewardLogModel::find()
                ->where(['member_id' => $memberId, 'activity_id' => $activity['id'], 'is_finish' => 1])
                ->exists();
            if ($isExists) {
                return error('已参与过活动');
            }
            // 查找所有订单
            $allOrder = OrderModel::find()
                ->where(['member_id' => $memberId])
                ->andWhere(['>=', 'created_at', $activity['start_time']])
                ->andWhere(['>=', 'status', $orderStatus])
                ->get();
            $sumPrice = 0; // 累计金额
            $orderIds = []; // 保存可用的订单id 查询预售订单用
            foreach ($allOrder as $item) {
                $payPrice = ConsumeRewardLogModel::checkOrder($item, $payType, $activityLimit ?? [], $goodsLimit ?? []);
                if (is_error($payPrice)) {
                    continue;
                }
                // 只要没有限制 就算
                $orderIds[] = $item['id'];
                $sumPrice += $payPrice;
            }

            $rewardArray = [];
            if ($activity['rules']['award']) {
                foreach ($activity['rules']['award'] as $item) {
                    if ($sumPrice >= $item['money']) {
                        $rewardArray = $item;
                    }
                }
            }

            if (empty($rewardArray)) {
                return error('不满足条件(1)');
            }
        } else {

            // 单次消费
            // 如果不能重复参与 检查是否参与过
            if ($activity['is_repeat'] == 0) {
                $isExists = ConsumeRewardLogModel::find()
                    ->where(['member_id' => $memberId, 'activity_id' => $activity['id'], 'is_finish' => 1])
                    ->exists();
                if ($isExists) {
                    return error('已参与过活动');
                }
            }

            $payPrice = ConsumeRewardLogModel::checkOrder($order, $payType, $activityLimit ?? [], $goodsLimit ?? []);
            if (is_error($payPrice)) {
                return $payPrice;
            }


            $rewardArray = [];
            if ($activity['rules']['award']) {
                foreach ($activity['rules']['award'] as $item) {
                    if ($payPrice >= $item['money']) {
                        $rewardArray = $item;
                        break;
                    }
                }
            }

            if (empty($rewardArray)) {
                return error('不满足条件(2)');
            }
        }

        // 如果活动为空
        if (empty($rewardArray['reward'])) {
            return error('发送失败');
        }

        if (in_array('1', $rewardArray['reward'])) {

            if (!is_array($rewardArray['coupon_ids'])) {
                $rewardArray['coupon_ids_array'] = explode(',', $rewardArray['coupon_ids']);
            } else {
                $rewardArray['coupon_ids_array'] = $rewardArray['coupon_ids_array']['coupon_ids'];
            }


            $coupons = CouponModel::getCouponInfo($rewardArray['coupon_ids_array']);

            // 重置
            $rewardArray['coupon_ids_array'] = [];
            foreach ($coupons as $index => $item) {
                if ($item['stock_type'] == 1 && $item['stock'] - $item['get_total'] <= 0) {
                    unset($coupons[$index]);
                } else {
                    $rewardArray['coupon_ids_array'][] = $item['id'];
                }
            }

            if (!empty($coupons)) {
                $rewardArray['coupon_info'] = array_values($coupons);
            } else {
                // 如果只有优惠券活动 且 优惠券为空
                if (count($rewardArray['reward']) == 1) {
                    return error('无活动');
                }
            }
        }

        // 发送奖励
        $sendReward = [
            'reward' => $rewardArray['reward']
        ];

        // 发送奖励
        foreach ($rewardArray['reward'] as $reward) {
            // 优惠券
            if ($reward == 1) {
                $res = CouponModel::activitySendCoupon($memberId, $rewardArray['coupon_ids_array']);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward'][1]);
                }
                $sendReward['coupon_ids'] = implode(',', $rewardArray['coupon_ids_array']);
                $sendReward['member_coupon_ids'] = $res;
            } else if ($reward == 2) {
                // 积分
                $res = MemberModel::updateCredit($memberId, $rewardArray['credit'], 0, 'credit', 1, '消费奖励', MemberCreditRecordStatusConstant::CONSUME_REWARD_SEND_CREDIT);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward_array'][2]);
                }
                $sendReward['credit'] = $rewardArray['credit'];
            } else if ($reward == 3) {
                // 余额
                $res = MemberModel::updateCredit($memberId, $rewardArray['balance'], 0, 'balance', 1, '消费奖励', MemberCreditRecordStatusConstant::CONSUME_REWARD_SEND_BALANCE);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($rewardArray['reward_array'][2]);
                }
                $sendReward['balance'] = $rewardArray['balance'];
            } else if ($reward == 4) {

                $redPackage = $rewardArray['red_package'];
                MemberRedPackageModel::createLog([
                    'member_id' => $memberId,
                    'money' => $redPackage['money'],
                    'expire_time' => date('Y-m-d H:i:s', time() + $redPackage['expiry'] * 86400),
                    'scene' => MemberRedPackageModel::SCENE_CONSUME_REWARD,
                    'scene_id' => $log->id,
                    'extend' => Json::encode($rewardArray['red_package'])
                ]);

                $sendReward['red_package'] = $redPackage;
            }
        }

        // 记录log TODO 青岛开店星信息技术有限公司 删除历史无用记录
        $log->reward = Json::encode($sendReward);
        $log->is_finish = 1;
        if (!$log->save()) {
            return error('记录保存失败' . $log->getErrorMessage());
        }
        // 发送记录 +1
        ConsumeRewardActivityModel::updateAllCounters(['send_count' => 1], ['id' => $activity['id']]);

        return true;
    }


    /**
     * 维权退回
     * @param int $memberId
     * @param int $orderId
     * @param int $orderGoodsId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public static function refundBack(int $memberId, int $orderId, int $orderGoodsId = 0)
    {
        // 查找订单
        $order = OrderModel::find()->where(['id' => $orderId])->first();
        // 根据下单时间 获取活动 (可能多个)
        $activity = ConsumeRewardActivityModel::find()->where([
            'and',
            [
                'or',
                [
                    'and',
                    ['stop_time' => 0],
                    ['<=', 'start_time', $order['created_at']],
                    ['>=', 'end_time', $order['created_at']],
                ],
                [
                    'and',
                    ['<>', 'stop_time', 0],
                    ['<=', 'start_time', $order['created_at']],
                    ['>=', 'stop_time', $order['created_at']],
                ]
            ]
        ])->get();
        if (empty($activity)) {
            return error('活动不存在');
        }
        // 根据活动id和用户id查找记录
        foreach ($activity as $item) {
            // 单笔发的时候  根据订单查 因为可能多条
            if ($item['type'] == 1) {
                $log = ConsumeRewardLogModel::find()
                    ->where(['member_id' => $memberId, 'activity_id' => $item['id'], 'is_finish' => 1, 'order_id' => $orderId])
                    ->first();
            } else {
                // 累计时根据活动就可以
                $log = ConsumeRewardLogModel::find()
                    ->where(['member_id' => $memberId, 'activity_id' => $item['id'], 'is_finish' => 1])
                    ->first();
            }
            if (empty($log)) {
                continue;
            }
            // 发送结点 订单完成后
            if ($item['send_type'] == 0) {
                $orderStatus = OrderStatusConstant::ORDER_STATUS_SUCCESS;
            } else {
                // 订单付款后
                $orderStatus = OrderStatusConstant::ORDER_STATUS_WAIT_SEND;
            }
            // 支付类型
            $payType = explode(',', $item['pay_type']);
            // 活动限制
            if (!empty($item['activity_limit'])) {
                $activityLimit = explode(',', $item['activity_limit']);
            }
            // 不参与商品
            if (!empty($item['goods_limit'])) {
                $goodsLimit = explode(',', $item['goods_limit']);
            }
            // 消费类型 累计消费
            if ($item['type'] == 0) {
                // 查找所有订单
                $allOrder = OrderModel::find()
                    ->where(['member_id' => $memberId])
                    ->andWhere(['>=', 'created_at', $item['start_time']])
                    ->andWhere(['>=', 'status', $orderStatus])
                    ->get();
                $sumPrice = 0; // 累计金额
                foreach ($allOrder as $value) {
                    $payPrice = ConsumeRewardLogModel::checkOrder($value, $payType, $activityLimit ?? [], $goodsLimit ?? []);
                    if (is_error($payPrice)) {
                        continue;
                    }
                    $sumPrice += $payPrice;
                }
                // 合计小于活动金额 退款
                if ($sumPrice < $item['money']) {
                    self::returnReward($log);
                }
            } else {
                // 单笔消费 判断剩余金额是否满足
                // 维权类型
                if ($orderGoodsId) {
                    // 判断订单剩余金额是否满足奖励 TODO 青岛开店星信息技术有限公司 预售的
                    $payPrice = ConsumeRewardLogModel::checkOrder($order, $payType, $activityLimit ?? [], $goodsLimit ?? []);
                    if ($payPrice < $item['money']) {
                        self::returnReward($log);
                    }
                } else {
                    // 整单维权 肯定退
                    self::returnReward($log);
                }
            }

            //退回红包
        }
        return true;
    }


    /**
     * 退回
     * @param array $log
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public static function returnReward(array $log)
    {
        $reward = Json::decode($log['reward']);
        // 优惠券
        if (!empty($reward['member_coupon_ids'])) {
            // 查找 退回
            foreach ($reward['member_coupon_ids'] as $item) {
                $memberCoupon = CouponMemberModel::findOne(['id' => $item, 'order_id' => 0]);
                if (!empty($memberCoupon)) {
                    // 可以回退
                    $memberCoupon->delete();
                    // 发放数量减一
                    CouponModel::updateAllCounters(['get_total' => -1], ['id' => $memberCoupon->coupon_id]);
                }
            }
        }

        // 获取用户
        $member = MemberModel::findOne(['id' => $log['member_id']]);

        // 积分
        if (!empty($reward['credit'])) {
            if ($member->credit < $reward['credit']) {
                $reward['credit'] = $member->credit;
            }
            MemberModel::updateCredit($log['member_id'], $reward['credit'], 0, 'credit', 2, '消费奖励退回', MemberCreditRecordStatusConstant::CONSUME_REWARD_REFUND_CREDIT);
        }

        // 余额
        if (!empty($reward['balance'])) {
            if ($member->balance < $reward['balance']) {
                $reward['balance'] = $member->balance;
            }
            MemberModel::updateCredit($log['member_id'], $reward['balance'], 0, 'balance', 2, '消费奖励退回', MemberCreditRecordStatusConstant::CONSUME_REWARD_REFUND_BALANCE);
        }

        //红包
        if (!empty($reward['red_package'])) {
            MemberRedPackageModel::updateAll([
                'status' => -1,
//                'updated_at' => DateTimeHelper::now()
            ], [
                'scene' => MemberRedPackageModel::SCENE_CONSUME_REWARD,
                'scene_id' => $log['id'],
                'status' => 0
            ]);
        }

        return true;
    }


}