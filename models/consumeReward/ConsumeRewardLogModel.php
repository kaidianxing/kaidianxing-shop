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

namespace shopstar\models\consumeReward;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberRedPackageModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%consume_reward_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $activity_id 活动id
 * @property int $type 消费类型
 * @property int $client_type 领取渠道
 * @property string $reward 奖励内容
 * @property int $order_id 订单id
 * @property string $created_at 领取时间
 * @property string $order_no 订单号
 * @property int $is_view 是否已弹窗
 * @property int $is_finish 是否完成 (赠送完成)
 */
class ConsumeRewardLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%consume_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [

            [['member_id', 'activity_id', 'type', 'client_type', 'order_id', 'is_view', 'is_finish'], 'integer'],
            [['created_at'], 'safe'],
            [['reward'], 'string'],
            [['order_no'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'activity_id' => '活动id',
            'type' => '消费类型',
            'client_type' => '领取渠道',
            'reward' => '奖励内容',
            'order_id' => '订单id',
            'created_at' => '领取时间',
            'order_no' => '订单号',
            'is_view' => '是否已弹窗',
            'is_finish' => '是否完成 (赠送完成)',
        ];
    }

    /**
     * 创建发放记录
     * @param int $memberId
     * @param int $orderId
     * @param int $orderClientType 下单客户端类型
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function createLog(int $memberId, int $orderId, int $orderClientType)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }
        // 查找订单 （该订单触发）
        $order = OrderModel::find()->where(['id' => $orderId])->first();
        // 获取活动
        $activity = ConsumeRewardActivityModel::getOpenActivity($orderClientType);
        if (is_error($activity)) {
            return error('活动不存在');
        }
        // 渠道限制 (必选)
        $clientType = explode(',', $activity['client_type']);
        // 渠道
        if (!in_array($order['create_from'], $clientType)) {
            return error('渠道不支持');
        }
        // 记录log
        $log = new self();
        $log->member_id = $memberId;
        $log->type = $activity['type'];
        $log->client_type = $orderClientType;
        $log->activity_id = $activity['id'];
        $log->order_no = $order['order_no'];
        $log->order_id = $orderId;
        if (!$log->save()) {
            return error('记录保存失败' . $log->getErrorMessage());
        }

        return true;
    }

    /**
     * 检测订单是否用 可用则返回实际金额
     * @param array $order 订单信息
     * @param array $payType 活动限制支付方式
     * @param array $activityLimit 活动限制
     * @param array $goodsLimit 商品限制
     * @return array|float
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkOrder(array $order, array $payType, array $activityLimit = [], array $goodsLimit = [])
    {
        if (!in_array($order['pay_type'], $payType)) {
            return error('支付方式不支持');
        }
        // 支付金额(减去运费)
        $price = bcsub($order['pay_price'], $order['dispatch_price'], 2);
        // 活动限制
        if (!empty($activityLimit)) {
            $extraPrice = Json::decode($order['extra_price_package']);
            foreach ($activityLimit as $item) {
                // 使用优惠券不能参与
                if ($item == 1 && !empty($extraPrice['coupon'])) {
                    return error('优惠券限制');
                } else if ($item == 2 && !empty($extraPrice['full_deduct'])) {
                    // 满减限制
                    return error('满减限制');
                }
            }
        }

        $orderGoods = OrderGoodsModel::find()->where(['order_id' => $order['id']])->get();
        foreach ($orderGoods as $item) {
            $subPrice = 0; // 要减去的钱
            // 不参与的商品
            if (!empty($goodsLimit)) {
                // 如果匹配 则减去该商品的钱
                if (in_array($item['goods_id'], $goodsLimit)) {
                    $subPrice = bcadd($subPrice, $item['price'], 2);
                }
            }
            // 如果商品维权 减去维权金额（订单如果有维权 直接就查不出来，到这只能是单品维权）
            if ($item['is_count'] == 0) {
                $deductInfo = Json::decode($item['activity_package']);
                $refund = OrderRefundModel::find()
                    ->where(['order_id' => $order['id'], 'order_goods_id' => $item['id'], 'is_history' => 0])
                    ->first();
                $subPrice = $refund['price'];
                // 维权金额减去余额抵扣 剩下的就是商品支付金额
                if (!empty($deductInfo['balance']['price'])) {
                    $subPrice -= $deductInfo['balance']['price'];
                }

            }
            if (!empty($subPrice)) {
                $price = bcsub($price, $subPrice, 2);
            }
        }
        return $price;
    }

    /**
     * 发送奖励
     * @param int $memberId 会员id
     * @param int $orderId 订单id
     * @param int $type 0 订单完成后  1 订单付款后
     * @return array|bool
     * @author likexin
     */
    public static function sendReward(int $memberId, int $orderId, int $type)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }

        // 查找订单 （该订单触发）
        $order = OrderModel::find()
            ->where([
                'id' => $orderId,
            ])
            ->first();

        // 获取记录
        $log = self::findOne([
            'member_id' => $memberId,
            'order_id' => $orderId,
        ]);
        if (empty($log)) {
            return error('记录不存在');
        }

        // 获取活动
        $activity = ConsumeRewardActivityModel::find()
            ->where([
                'id' => $log->activity_id,
                'is_deleted' => 0,
            ])
            ->first();
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
        if ($activity['rules'] && $activity['rules']['permission'] != 0) {

            //获取会员信息
            $member = MemberModel::find()
                ->where([
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

                // 如果没有会员标签则没有权限
                if (empty($memberTag)) {
                    return error('没有权限');
                }

                // 如果没有交集则没有权限
                if (!array_intersect($memberTag, $activity['rules']['permission_value'])) {
                    return error('没有权限');
                }
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
                ->where([
                    'member_id' => $memberId,
                    'activity_id' => $activity['id'],
                    'is_finish' => 1,
                ])
                ->exists();
            if ($isExists) {
                return error('已参与过活动');
            }
            // 查找所有订单
            $allOrder = OrderModel::find()
                ->where([
                    'member_id' => $memberId,
                ])
                ->andWhere(['>=', 'create_time', $activity['start_time']])
                ->andWhere(['>=', 'status', $orderStatus])
                ->get();
            $sumPrice = 0; // 累计金额
            $orderIds = []; // 保存可用的订单id 查询预售订单用

            foreach ($allOrder as $item) {
                $payPrice = self::checkOrder($item, $payType, $activityLimit ?? [], $goodsLimit ?? []);
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
                    ->where([
                        'member_id' => $memberId,
                        'activity_id' => $activity['id'],
                        'is_finish' => 1,
                    ])
                    ->exists();
                if ($isExists) {
                    return error('已参与过活动');
                }
            }

            $payPrice = self::checkOrder($order, $payType, $activityLimit ?? [], $goodsLimit ?? []);
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
            if ($reward == 1) {
                // 优惠券

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

                // 红包
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

        // 记录log
        $log->reward = Json::encode($sendReward);
        $log->is_finish = 1;
        if (!$log->save()) {
            return error('记录保存失败' . $log->getErrorMessage());
        }

        // 发送记录 +1
        ConsumeRewardActivityModel::updateAllCounters(['send_count' => 1], [
            'id' => $activity['id'],
        ]);

        return true;
    }


}