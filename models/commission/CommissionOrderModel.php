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

namespace shopstar\models\commission;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;


/**
 * This is the model class for table "{{%commission_order}}".
 *
 * @property int $order_id 订单ID
 * @property int $member_id 下单会员ID
 * @property int $agent_id 分销商会员ID
 * @property int $assessment_id 业绩考核ID
 * @property string $ladder_commission 包含的阶梯佣金
 * @property string ladder_commission_rule 阶梯佣金规则
 * @property string $commission 分销佣金
 * @property string $commission_level 分销佣金级别详细
 * @property string $order_finish_time 订单完成时间
 * @property int $is_count_refund 订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计
 * @property string $account_time 佣金到账时间
 */
class CommissionOrderModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_commission_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'member_id', 'agent_id', 'assessment_id', 'is_count_refund'], 'integer'],
            [['commission', 'ladder_commission'], 'number'],
            [['ladder_commission_rule'], 'string'],
            [['order_finish_time', 'account_time'], 'safe'],
            [['order_no'], 'string', 'max' => 50],
            [['commission_level'], 'string', 'max' => 191],
            [['order_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => '订单ID',
            'order_no' => '订单编号',
            'member_id' => '下单会员ID',
            'agent_id' => '分销商会员ID',
            'assessment_id' => '关联的考核id',
            'commission' => '分销佣金',
            'commission_level' => '分销佣金级别详细',
            'ladder_commission' => '包含的阶梯佣金',
            'ladder_commission_rule' => '阶梯佣金级别详细',
            'order_finish_time' => '订单完成时间',
            'is_count_refund' => '订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计',
            'account_time' => '佣金到账时间',
        ];
    }

    /**
     * 获取成为分销商后的订单数量
     * @param int $memberId
     * @param string $becomeTime
     * @param string $degradeTime 降级时间
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderCount(int $memberId, string $becomeTime, string $degradeTime = '')
    {
        $andWhere = [];
        // 有降级时间, 查找降级时间之后的数据
        if (!empty($degradeTime)) {
            $andWhere = ['>=', 'order.created_at', $degradeTime];
        }

        // 完成订单数
        return OrderModel::find()
            ->alias('order')
            ->where([
                'and',
                ['>=', 'order.status', OrderStatusConstant::ORDER_STATUS_SUCCESS],
                ['order.member_id' => $memberId],
                ['>=', 'order.created_at', $becomeTime],
                ['order.refund_price' => 0]
            ])
            ->andWhere($andWhere)
            ->count();
    }

    /**
     * 获取成为分销商后的订单金额
     * @param int $memberId
     * @param string $becomeTime
     * @param string $degradeTime 降级时间
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderPrice(int $memberId, string $becomeTime, string $degradeTime = '')
    {
        $where = [
            'and',
            ['order.member_id' => $memberId],
            ['>=', 'order.created_at', $becomeTime],
            ['>=', 'order.status', OrderStatusConstant::ORDER_STATUS_SUCCESS]
        ];


        $andWhere = [];
        // 有降级时间, 查找降级时间之后的数据
        if (!empty($degradeTime)) {
            $andWhere = ['>=', 'order.created_at', $degradeTime];
        }

        $list = OrderModel::find()
            ->alias('order')
            ->select('order.id, order.pay_price, order.refund_price, order.extra_price_package, order.extra_discount_rules_package')
            ->where($where)
            ->andWhere($andWhere)
            ->get();

        return OrderModel::calculateOrderPrice($list);
    }

    /**
     * 更新订单状态
     * @param int $orderId
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateOrderFinish(int $orderId)
    {
        $order = self::findOne(['order_id' => $orderId]);
        if (empty($order)) {
            return error('该订单不是分销订单');
        }
        // 结算天数
        $set = CommissionSettings::get('settlement');
        if ($set['settlement_day_type'] == 1) {
            $accountTime = DateTimeHelper::now();
        } else {
            $accountTime = DateTimeHelper::after(time(), $set['settlement_days'] * 60 * 60 * 24);
        }
        $order->account_time = $accountTime;
        $order->order_finish_time = DateTimeHelper::now();
        if ($order->save() === false) {
            return error('修改订单完成失败');
        }
        // 修改 order_data 表
        CommissionOrderDataModel::updateAll(['order_finish_time' => DateTimeHelper::now()], ['order_id' => $orderId]);

        return true;
    }


    /**
     * 是否可修改佣金
     * 已结算的不可修改
     * @param int $memberId
     * @param int $orderId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isCanEditCommission(int $memberId, int $orderId)
    {
        // 获取分销订单 未结算的
        $orderData = CommissionOrderModel::find()
            ->where(['member_id' => $memberId, 'order_id' => $orderId])
            ->andWhere([
                'and',
                ['is_count_refund' => 1],
                [
                    'or',
                    ['>', 'account_time', DateTimeHelper::now()],
                    ['account_time' => 0]
                ]
            ])->first();

        // 如果已结算  不可编辑
        if (empty($orderData)) {
            return false;
        }
        return true;
    }

    /**
     * 获取通知人
     * @param array $commissionOrderData
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNoticeMember(array $commissionOrderData)
    {
        $member = [];
        // 内购
        if ($commissionOrderData['self_buy']) {
            // 一级通知
            if (!empty($commissionOrderData[2])) {
                $member[1] = MemberModel::find()->where(['id' => $commissionOrderData[2]['member_id']])->select('mobile')->first();
                // 二级通知
                if (!empty($order[3])) {
                    $member[2] = MemberModel::find()->where(['id' => $commissionOrderData[3]['member_id']])->select('mobile')->first();
                }
            }
        } else {
            // 一级通知
            if (!empty($commissionOrderData[1])) {
                $member[1] = MemberModel::find()->where(['id' => $commissionOrderData[1]['member_id']])->select('mobile')->first();
                // 二级通知
                if (!empty($commissionOrderData[2])) {
                    $member[2] = MemberModel::find()->where(['id' => $commissionOrderData[2]['member_id']])->select('mobile')->first();
                    if (!empty($commissionOrderData[3])) {
                        $member[3] = MemberModel::find()->where(['id' => $commissionOrderData[3]['member_id']])->select('mobile')->first();
                    }
                }
            }
        }
        return $member;
    }

}
