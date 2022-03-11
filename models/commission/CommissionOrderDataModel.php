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
use shopstar\constants\OrderConstant;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;

/**
 * This is the model class for table "{{%commission_order_data}}".
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property string $order_no 订单编号
 * @property int $member_id 下单会员ID
 * @property int $agent_id 分销商会员ID
 * @property int $level 订单级别
 * @property int $assessment_id 关联的考核id
 * @property string $commission 实际佣金
 * @property string $original_commission 原始佣金
 * @property string $ladder_commission 包含的阶梯佣金
 * @property string $original_ladder_commission 原始阶梯佣金
 * @property string $order_finish_time 订单完成时间
 * @property int $is_count_refund 订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计
 * @property int $self_buy 自购订单
 */
class CommissionOrderDataModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_commission_order_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'member_id', 'agent_id', 'level', 'assessment_id', 'is_count_refund', 'self_buy'], 'integer'],
            [['commission', 'original_commission', 'ladder_commission', 'original_ladder_commission'], 'number'],
            [['order_finish_time'], 'safe'],
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
            'order_id' => '订单ID',
            'order_no' => '订单编号',
            'member_id' => '下单会员ID ',
            'agent_id' => '分销商会员ID',
            'level' => '订单级别',
            'assessment_id' => '关联的考核id',
            'commission' => '实际佣金',
            'original_commission' => '原始佣金',
            'ladder_commission' => '包含的阶梯佣金',
            'original_ladder_commission' => '原始阶梯佣金',
            'order_finish_time' => '订单完成时间',
            'is_count_refund' => '订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计',
            'self_buy' => '自购订单',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMember()
    {
        return $this->hasOne(MemberModel::class, ['id' => 'agent_id']);
    }

    /**
     * 获取订单商品信息
     * @author 青岛开店星信息技术有限公司
     */
    public function getOrderGoods()
    {
        return $this->hasMany(OrderGoodsModel::class, ['order_id' => 'order_id']);
    }

    /**
     * 获取分销订单商品信息
     * @author 青岛开店星信息技术有限公司
     */
    public function getCommissionOrderGoods()
    {
        return $this->hasMany(CommissionOrderGoodsModel::class, ['order_id' => 'order_id']);
    }

    /**
     * 获取上级分销商信息
     * @param int $orderId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAgentInfo(int $orderId)
    {
        $agentInfo = [];
        $agentInfo['commission_sum'] = 0;
        $list = self::find()
            ->alias('order_data')
            ->where(['order_data.order_id' => $orderId])
            ->leftJoin(CommissionOrderGoodsModel::tableName() . ' order_goods', 'order_goods.order_id=order_data.order_id and order_goods.member_id=order_data.member_id')
            ->with([
                'member' => function ($query) {
                    $query->select("id, nickname, avatar, mobile,source");
                }
            ])->groupBy('order_data.agent_id')->get();

        if (!empty($list)) {
            foreach ($list as $key => $value) {
                // 合计佣金
                $agentInfo['commission_sum'] = bcadd($agentInfo['commission_sum'], $value['commission'], 2);
                switch ($value['level']) {
                    case 1:
                        $agentInfo['agent_level1'] = $value;
                        break;
                    case 2:
                        $agentInfo['agent_level2'] = $value;
                        break;
                    case 3:
                        $agentInfo['agent_level3'] = $value;
                        break;
                }
            }
        }

        return $agentInfo;
    }

    /**
     * 获取分销订单金额
     * 余额抵扣 + 实际支付 - 维权金额
     * @param int $memberId
     * @param int $level 分销层级  0 全部
     * @param string $degradeTime 降级时间
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderPrice(int $memberId, int $level = 0, string $degradeTime = '')
    {
        $where = [
            'and',
            ['>=', 'order.status', OrderStatusConstant::ORDER_STATUS_SUCCESS],
            ['order_data.agent_id' => $memberId]
        ];
        // 分销层级
        if (!empty($level)) {
            $where[] = ['order_data.level' => $level];
        }

        $andWhere = [];
        // 有降级时间, 查找降级时间之后的数据
        if (!empty($degradeTime)) {
            $andWhere = ['>=', 'order.created_at', $degradeTime];
        }
        // 查找所有支付订单
        $list = self::find()
            ->select('order.id, order.pay_price, order.refund_price, order.extra_price_package, order.extra_discount_rules_package')
            ->alias('order_data')
            ->leftJoin(OrderModel::tableName() . ' order', 'order.id=order_data.order_id')
            ->where($where)
            ->andWhere($andWhere)
            ->get();
        // 计算
        return OrderModel::calculateOrderPrice($list);
    }

    /**
     * 获取分销订单数量
     * 不包含 退款 和 退货退款 完成的订单
     * @param int $memberId
     * @param int $level 分销层级
     * @param int $status
     * @param string $degradeTime 降级时间
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderCount(int $memberId, int $level = 0, int $status = OrderConstant::ORDER_STATUS_SUCCESS, string $degradeTime = '')
    {
        $where = [
            'and',
            ['order_data.agent_id' => $memberId],
            ['order_data.is_count_refund' => 1]
        ];
        if (!empty($level)) {
            $where[] = ['order_data.level' => $level];
        }
        if ($status == OrderConstant::ORDER_STATUS_SUCCESS) {
            $where[] = [
                'or',
                ['>=', 'order.status', $status],
                ['<>', 'order.finish_time', 0]
            ];
        }

        $andWhere = [];
        // 有降级时间, 查找降级时间之后的数据
        if (!empty($degradeTime)) {
            $andWhere = ['>=', 'order.created_at', $degradeTime];
        }

        // 全部订单数量
        return self::find()
            ->alias('order_data')
            ->leftJoin(OrderModel::tableName() . ' order', 'order.id=order_data.order_id')
            ->where($where)
            ->andWhere($andWhere)
            ->count();
    }

    /**
     * 获取订单分销信息
     * @param int $orderId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderCommissionInfo(int $orderId)
    {
        return self::find()
            ->select('member.nickname, member.id, member.avatar, order_data.level, order_data.commission,order_data.ladder_commission')
            ->alias('order_data')
            ->where([
                'and',
                ['order_data.order_id' => $orderId],
                ['order_data.is_count_refund' => 1]
            ])
            ->leftJoin(MemberModel::tableName() . ' member', 'member.id=order_data.agent_id')
            ->indexBy('level')
            ->get();
    }

}
