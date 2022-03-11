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

namespace shopstar\models\shoppingReward;

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberRedPackageModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;

use yii\helpers\Json;

/**
 * This is the model class for table "{{%app_shopping_reward_log}}".
 *
 * @property string $id
 * @property int $member_id 会员id
 * @property int $activity_id 活动id
 * @property int $client_type 渠道
 * @property string $reward 奖励内容
 * @property string $created_at 领取时间
 * @property int $order_id 订单id
 * @property string $goods_ids 相关商品id
 * @property int $is_view 是否查看过
 * @property int $is_finish 是否完成 (赠送完成)
 */
class ShoppingRewardLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_shopping_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'activity_id', 'client_type', 'order_id', 'is_view', 'is_finish'], 'integer'],
            [['created_at'], 'safe'],
            [['goods_ids'], 'string'],
            [['reward'], 'string', 'max' => 199],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'activity_id' => '活动id',
            'client_type' => '渠道 ',
            'reward' => '奖励内容',
            'created_at' => '领取时间',
            'order_id' => '订单id',
            'goods_ids' => '相关商品id',
            'is_view' => '是否查看过',
            'is_finish' => '是否完成 (赠送完成)',
        ];
    }

    /**
     * 创建记录
     * @param int $memberId 会员id
     * @param int $orderId 订单id
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

        // 获取订单商品
        $orderGoods = Json::decode($order['goods_info']);
        $goodsId = array_column($orderGoods, 'goods_id');


        // 获取活动
        $activity = ShoppingRewardActivityModel::getOpenActivity($orderClientType, $goodsId);
        if (is_error($activity)) {
            return error('活动不存在');
        }
        // 渠道限制 (必选)
        $clientType = explode(',', $activity['client_type']);
        // 渠道
        if (!in_array($order['create_from'], $clientType)) {
            return error('渠道不支持');
        }
        // 判断商品是否满足
        if ($activity['goods_type'] != 0) {
            // 是否在商品限制里
            $inGoodsLimit = true;
            // 获取商品限制
            $goodsRule = ShoppingRewardActivityGoodsRuleModel::find()->where(['activity_id' => $activity['id']])->indexBy('goods_or_cate_id')->get();
            $limitId = array_keys($goodsRule);
            // 商品分类限制
            if ($activity['goods_type'] == 3) {
                foreach ($orderGoods as $item) {
                    $goodsCategory = GoodsCategoryMapModel::find()->where(['goods_id' => $item['goods_id']])->get();
                    $goodsCategoryId = array_column($goodsCategory, 'category_id');
                    $intersect = array_intersect($goodsCategoryId, $limitId);
                    if (!empty($intersect)) {
                        $inGoodsLimit = false; // 不在限制里  可用
                    }
                }
            } else {
                // 允许商品限制
                $intersect = array_intersect($goodsId, $limitId);
                if ($activity['goods_type'] == 1 && !empty($intersect)) {
                    $inGoodsLimit = false; // 不在限制里
                } else if ($activity['goods_type'] == 2 && empty($intersect)) {
                    // 不允许商品限制
                    $inGoodsLimit = false; // 不在限制里
                }
            }

            if ($inGoodsLimit) {
                return error('商品限制');
            }
        }

        // 会员限制
        if ($activity['member_type'] != 0) {
            $memberRule = ShoppingRewardActivityMemberRuleModel::find()->where(['activity_id' => $activity['id']])->indexBy('level_or_group_id')->get();
            $limitId = array_keys($memberRule);
            // 会员信息
            $member = MemberModel::findOne($memberId);
            // 等级限制
            if ($activity['member_type'] == 1) {
                if (!in_array($member['level_id'], $limitId)) {
                    return error('会员等级限制');
                }
            } else {
                // 标签限制
                $memberGroup = $member->groupsMap;
                if (empty($memberGroup)) {
                    return error('会员标签限制');
                }

                $memberGroup = array_column($memberGroup, 'group_id');
                if (!array_intersect($memberGroup, $limitId)) {
                    return error('会员标签限制');
                }
            }
        }

        // 领取次数 1每人活动期间最多领取  2每人每天做多领取
        if ($activity['pick_times_type'] == 1) {
            $pickTimes = self::find()->where(['activity_id' => $activity['id'], 'member_id' => $memberId, 'is_finish' => 1])->count();
            if ($activity['pick_times_limit'] <= $pickTimes) {
                return error('超过领取次数');
            }
        } else if ($activity['pick_times_type'] == 2) {
            $startDay = date('y-m-d');
            $endDay = date('Y-m-d') . ' 23:59:59';
            $pickTimes = self::find()
                ->where(['activity_id' => $activity['id'], 'member_id' => $memberId, 'is_finish' => 1])
                ->andWhere(['between', 'created_at', $startDay, $endDay])
                ->count();
            if ($activity['pick_times_limit'] <= $pickTimes) {
                return error('超过领取次数');
            }
        }

        // 记录log
        $log = new self();
        $log->member_id = $memberId;
        $log->client_type = $orderClientType;
        $log->activity_id = $activity['id'];
        $log->order_id = $orderId;
        if (!$log->save()) {
            return error('记录保存失败' . $log->getErrorMessage());
        }

        return true;
    }

    /**
     * 发送奖励
     * @param int $memberId
     * @param int $orderId
     * @param int $type
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendReward(int $memberId, int $orderId, int $type)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }
        // 获取记录
        $log = self::findOne(['member_id' => $memberId, 'order_id' => $orderId]);
        if (empty($log)) {
            return error('记录不存在');
        }
        // 获取活动
        $activity = ShoppingRewardActivityModel::find()->where(['id' => $log->activity_id])->first();
        if (is_error($activity)) {
            return error('活动不存在');
        }
        // 发送结点
        if ($activity['send_type'] != $type) {
            return error('发送结点不匹配');
        }
        // 领取次数 1每人活动期间最多领取  2每人每天做多领取
        if ($activity['pick_times_type'] == 1) {
            $pickTimes = self::find()->where(['activity_id' => $activity['id'], 'member_id' => $memberId, 'is_finish' => 1])->count();
            if ($activity['pick_times_limit'] < $pickTimes) {
                return error('超过领取次数');
            }
        } else if ($activity['pick_times_type'] == 2) {
            $startDay = date('y-m-d');
            $endDay = date('Y-m-d') . ' 23:59:59';
            $pickTimes = self::find()
                ->where(['activity_id' => $activity['id'], 'member_id' => $memberId, 'is_finish' => 1])
                ->andWhere(['between', 'created_at', $startDay, $endDay])
                ->count();
            if ($activity['pick_times_limit'] < $pickTimes) {
                return error('超过领取次数');
            }
        }
        // 可以领取
        // 判断活动库存
        $activity['reward_array'] = explode(',', $activity['reward']);
        if (in_array('1', $activity['reward_array'])) {
            $activity['coupon_ids_array'] = explode(',', $activity['coupon_ids']);
            $coupons = CouponModel::getCouponInfo($activity['coupon_ids_array']);
            // 重置
            $activity['coupon_ids_array'] = [];
            foreach ($coupons as $index => $item) {
                if ($item['stock_type'] == 1 && $item['stock'] - $item['get_total'] <= 0) {
                    unset($coupons[$index]);
                } else {
                    $activity['coupon_ids_array'][] = $item['id'];
                }
            }
            if (!empty($coupons)) {
                $activity['coupon_info'] = array_values($coupons);
            } else {
                // 如果只有优惠券活动 且 优惠券为空
                if (count($activity['reward_array']) == 1) {
                    return error('无活动');
                }
            }
        }
        // 发送奖励
        $sendReward = [];
        // 发送奖励
        foreach ($activity['reward_array'] as $reward) {
            // 优惠券
            if ($reward == 1) {
                $res = CouponModel::activitySendCoupon($memberId, $activity['coupon_ids_array']);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['reward_array'][1]);
                }
                $sendReward['coupon_ids'] = implode(',', $activity['coupon_ids_array']);
                $sendReward['member_coupon_ids'] = $res;
            } else if ($reward == 2) {
                // 积分
                $res = MemberModel::updateCredit($memberId, $activity['credit'], 0, 'credit', 1, '购物奖励', MemberCreditRecordStatusConstant::SHOPPING_REWARD_SEND_CREDIT);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['reward_array'][2]);
                }
                $sendReward['credit'] = $activity['credit'];
            } else if ($reward == 3) {
                // 余额
                $res = MemberModel::updateCredit($memberId, $activity['balance'], 0, 'balance', 1, '购物奖励', MemberCreditRecordStatusConstant::SHOPPING_REWARD_SEND_BALANCE);
                // 发送失败 删除此活动
                if (is_error($res)) {
                    unset($activity['reward_array'][2]);
                }
                $sendReward['balance'] = $activity['balance'];
            } else if ($reward == 4) {

                $redPackage = Json::decode($activity['red_package']);
                MemberRedPackageModel::createLog([
                    'member_id' => $memberId,
                    'money' => $redPackage['money'],
                    'expire_time' => date('Y-m-d H:i:s', time() + $redPackage['expiry'] * 86400),
                    'scene' => MemberRedPackageModel::SCENE_SHOPPING_REWARD,
                    'scene_id' => $log->id,
                    'extend' => $activity['red_package']
                ]);

                $sendReward['red_package'] = $redPackage;
            }
        }
        // 如果活动为空
        if (empty($activity['reward_array'])) {
            return error('发送失败');
        }
        // 记录log TODO 青岛开店星信息技术有限公司 删除历史无用记录
        $log->reward = Json::encode($sendReward);
        $log->is_finish = 1;
        if (!$log->save()) {
            return error('记录保存失败' . $log->getErrorMessage());
        }
        // 发送记录 +1
        ShoppingRewardActivityModel::updateAllCounters(['send_count' => 1], ['id' => $activity['id']]);

        return true;
    }

    /**
     * 发生维权 退回奖励
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
        // 查找记录
        $log = self::find()->where(['member_id' => $memberId, 'order_id' => $orderId])->first();
        if (empty($log)) {
            return error('无活动');
        }
        // 获取活动
        $activity = ShoppingRewardActivityModel::find()->where(['id' => $log['activity_id']])->first();
        if (empty($activity)) {
            return error('找不到活动');
        }
        // 整单维权
        if (empty($orderGoodsId)) {
            // 退回
            self::returnReward($log);
        } else {
            // 单品维权
            // 检查订单剩余未维权商品是否可用
            $otherOrderGoods = OrderGoodsModel::find()
                ->where(['and', ['order_id' => $orderId], ['<>', 'id', $orderGoodsId]])
                ->get();
            // 如果为空 退
            if (empty($otherOrderGoods)) {
                self::returnReward($log);
            } else {
                // 则检查其他商品是否维权完成
                // 获取商品限制
                if ($activity['goods_type'] != 0) {
                    $goodsRule = ShoppingRewardActivityGoodsRuleModel::find()->where(['activity_id' => $activity['id']])->indexBy('goods_or_cate_id')->get();
                    $limitId = array_keys($goodsRule);
                }
                // 所有商品都维权
                $allRefund = true;
                // 遍历检查商品是否可用
                foreach ($otherOrderGoods as $orderGoods) {
                    // 无限制
                    if (empty($limitId)) {
                        // 存在没维权商品
                        if ($orderGoods['refund_status'] < RefundConstant::REFUND_STATUS_SUCCESS) {
                            $allRefund = false;
                        }
                    } else {
                        // 是否在商品限制里
                        $inGoodsLimit = true;
                        // 商品分类限制
                        if ($activity['goods_type'] == 3) {
                            $goodsCategory = GoodsCategoryMapModel::find()->where(['goods_id' => $orderGoods['goods_id']])->get();
                            $goodsCategoryId = array_column($goodsCategory, 'category_id');
                            $intersect = array_intersect($goodsCategoryId, $limitId);
                            if (!empty($intersect)) {
                                $inGoodsLimit = false; // 不在限制里  可用
                            }

                        } else {
                            // 允许商品限制
                            $intersect = array_intersect((array)$orderGoods['goods_id'], $limitId);
                            if ($activity['goods_type'] == 1 && !empty($intersect)) {
                                $inGoodsLimit = false; // 不在限制里
                            } else if ($activity['goods_type'] == 2 && empty($intersect)) {
                                // 不允许商品限制
                                $inGoodsLimit = false; // 不在限制里
                            }
                        }
                        // 不再限制里 判断维权状态 在限制里的不用管状态
                        if (!$inGoodsLimit) {
                            if ($orderGoods['refund_status'] < RefundConstant::REFUND_STATUS_SUCCESS) {
                                $allRefund = false;
                            }
                        }
                    }
                }
                // 如果所有都维权 退
                if ($allRefund) {
                    self::returnReward($log);
                }
            }
        }

        return true;
    }

    /**
     * 退回奖励
     * @param array $log
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    private static function returnReward(array $log)
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
            MemberModel::updateCredit($log['member_id'], $reward['credit'], 0, 'credit', 2, '购物奖励退回', MemberCreditRecordStatusConstant::SHOPPING_REWARD_REFUND_CREDIT);
        }
        // 余额
        if (!empty($reward['balance'])) {
            if ($member->balance < $reward['balance']) {
                $reward['balance'] = $member->balance;
            }
            MemberModel::updateCredit($log['member_id'], $reward['balance'], 0, 'balance', 2, '购物奖励退回', MemberCreditRecordStatusConstant::SHOPPING_REWARD_REFUND_BALANCE);
        }

        //红包
        if (!empty($reward['red_package'])) {
            MemberRedPackageModel::updateAll([
                'status' => -1
            ], [
                'scene' => MemberRedPackageModel::SCENE_SHOPPING_REWARD,
                'scene_id' => $log['id'],
                'status' => 0
            ]);
        }
        return true;
    }

}