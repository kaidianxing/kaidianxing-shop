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

use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
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
class ConsumeRewardLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%consume_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
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
    public function attributeLabels()
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


}