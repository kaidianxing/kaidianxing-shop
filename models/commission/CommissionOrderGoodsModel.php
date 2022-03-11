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
use shopstar\models\member\MemberModel;

/**
 * This is the model class for table "{{%commission_order_goods}}".
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property string $order_no 订单编号
 * @property int $goods_id 商品ID
 * @property int $option_id 规格ID
 * @property int $member_id 下单会员ID
 * @property int $agent_id 分销商会员ID
 * @property int $order_goods_id 订单商品ID
 * @property int $level 分销层级
 * @property int $assessment_id 关联的考核id
 * @property string $commission 实际佣金
 * @property string $original_commission 原始佣金
 * @property string $ladder_commission 包含的阶梯佣金
 * @property string $original_ladder_commission 原始阶梯佣金
 * @property int $status 佣金状态 0: 正常 1: 已申请提现 2: 审核通过 3: 已打款 4: 手动处理 -1: 审核不通过 -2: 维权退款不通过
 * @property int $is_count_refund 订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计
 * @property string $remark 备注
 * @property string $can_withdraw_commission 可提现佣金
 */
class CommissionOrderGoodsModel extends BaseActiveRecord
{
    /**
     * 状态文字
     * @var array
     */
    public static $orderGoodsStatus = [
        0 => '正常',
        1 => '已申请提现',
        2 => '审核通过',
        3 => '已打款',
        4 => '手动处理',
        -1 => '审核不通过',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_commission_order_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'option_id', 'member_id', 'agent_id', 'order_goods_id', 'level', 'assessment_id', 'status', 'is_count_refund'], 'integer'],
            [['commission', 'original_commission', 'ladder_commission', 'original_ladder_commission', 'can_withdraw_commission'], 'number'],
            [['order_no'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 191],
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
            'goods_id' => '商品ID',
            'option_id' => '规格ID',
            'member_id' => '下单会员ID',
            'agent_id' => '分销商会员ID',
            'order_goods_id' => '订单商品ID',
            'level' => '分销层级',
            'assessment_id' => '关联的考核id',
            'commission' => '实际佣金',
            'original_commission' => '原始佣金',
            'ladder_commission' => '包含的阶梯佣金',
            'original_ladder_commission' => '原始阶梯佣金',
            'status' => '佣金状态 0: 正常 1: 已申请提现 2: 审核通过 3: 已打款 4: 手动处理 -1: 审核不通过 -2: 维权退款不通过',
            'is_count_refund' => '订单维权状态  0 已维权（退款/退货退款）且未结算 不统计 1未维权 统计',
            'remark' => '备注',
            'can_withdraw_commission' => '可提现佣金',
        ];
    }


    /**
     * 处理字段文字
     * @param array $row
     * @author likexin
     */
    public static function handleFieldText(array &$row)
    {
        if (isset($row['status'])) {
            $row['status_text'] = self::$orderGoodsStatus[$row['status']];
        }
    }

    /**
     * 获取订单商品分销信息
     * @param int $orderId
     * @param int $orderGoodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderGoodsCommissionInfo(int $orderId, int $orderGoodsId)
    {
        return self::find()
            ->alias('order_goods')
            ->select('member.nickname, member.id, member.avatar, order_goods.level, order_goods.commission,order_goods.ladder_commission')
            ->where([
                'and',
                ['order_goods.order_id' => $orderId],
                ['order_goods.order_goods_id' => $orderGoodsId],
                ['order_goods.is_count_refund' => 1]
            ])
            ->leftJoin(MemberModel::tableName() . ' member', 'member.id=order_goods.agent_id')
            ->indexBy('level')
            ->get();
    }
}