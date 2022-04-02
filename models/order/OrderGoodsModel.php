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

namespace shopstar\models\order;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\ValueHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order_goods}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $goods_id 商品id
 * @property int $option_id 商品多规格id
 * @property int $member_id 会员id
 * @property int $status 订单商品状态，跟订单表走
 * @property string $title 商品名称
 * @property string $short_name 商品短标题
 * @property string $option_title 商品规格名称
 * @property string $thumb 商品缩略图
 * @property string $price 实际支付金额
 * @property string $price_original 商品标价总和
 * @property string $price_unit 商品单价
 * @property string $price_discount 优惠的金额平摊到本商品,包含余额抵扣
 * @property string $price_change 改价的金额平摊的本商品
 * @property int $total 商品数量
 * @property string $dispatch_info 配送信息
 * @property int $is_single_refund 是否单品维权 0:否 1是
 * @property string $package_cancel_reason 取消发货原因
 * @property int $package_id 包裹ID 0: 未发货 -1: 已取消发货 >0:已发货包裹id
 * @property string $goods_sku 商品编号
 * @property string $bar_code 商品条码
 * @property int $refund_type 维权方式 1:退款 2:退货退款 3:换货
 * @property int $refund_status -2:取消; -1:拒绝; 0:申请; 1:用户填写快递单号; 2:店家填写快递单号; 3:等待退款; 10:完成; 11:手动退款完成;
 * @property string $created_at 创建时间
 * @property int $comment_status 是否已评价 0未评价 1 已首次评价 2 已追加评价
 * @property string $activity_package 执行的优惠，含标识，可能多个（优惠大礼包）
 * @property string $pay_time 付款时间 跟随订单
 * @property int $is_count 是否统计 (已 退款/退货退款 完成的不统计) 冗余字段 退款/退货退款完成后 该字段置为0
 * @property string $plugin_identification 插件标识 商品参加的插件活动标识
 * @property string $weight 重量
 * @property int $is_print 是否已打印电子面单 1是0否
 * @property string $cost_price 成本价
 * @property int $shop_goods_id 商城商品id
 * @property int $shop_option_id 商城规格id
 * @property string $ext_field 扩展信息
 */
class OrderGoodsModel extends BaseActiveRecord
{

    /**
     * 维权类型
     * @var array
     */
    public static $orderRefundType = [
        1 => '仅退款',
        2 => '退货退款',
        3 => '换货'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'option_id', 'member_id', 'status', 'total', 'is_single_refund', 'package_id', 'refund_type', 'refund_status', 'comment_status', 'is_count', 'is_print', 'shop_goods_id', 'shop_option_id'], 'integer'],
            [['member_id', 'price_original', 'price_unit'], 'required'],
            [['price', 'price_original', 'price_unit', 'price_discount', 'price_change', 'weight', 'cost_price'], 'number'],
            [['created_at', 'pay_time'], 'safe'],
            [['activity_package', 'ext_field'], 'string'],
            [['title', 'short_name'], 'string', 'max' => 128],
            [['option_title'], 'string', 'max' => 60],
            [['thumb', 'dispatch_info', 'package_cancel_reason', 'bar_code', 'plugin_identification'], 'string', 'max' => 255],
            [['goods_sku'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单id',
            'goods_id' => '商品id',
            'option_id' => '商品多规格id',
            'member_id' => '会员id',
            'status' => '订单商品状态，跟订单表走',
            'title' => '商品名称',
            'short_name' => '商品短标题',
            'option_title' => '商品规格名称',
            'thumb' => '商品缩略图',
            'price' => '实际支付金额',
            'price_original' => '商品标价总和',
            'price_unit' => '商品单价',
            'price_discount' => '优惠的金额平摊到本商品,包含余额抵扣',
            'price_change' => '改价的金额平摊的本商品',
            'total' => '商品数量',
            'dispatch_info' => '配送信息',
            'is_single_refund' => '是否单品维权 0:否 1是',
            'package_cancel_reason' => '取消发货原因',
            'package_id' => '包裹ID 0: 未发货 -1: 已取消发货 >0:已发货包裹id',
            'goods_sku' => '商品编号',
            'bar_code' => '商品条码',
            'refund_type' => '维权方式 1:退款 2:退货退款 3:换货',
            'refund_status' => '-2:取消; -1:拒绝; 0:申请; 1:用户填写快递单号; 2:店家填写快递单号; 3:等待退款; 10:完成; 11:手动退款完成;',
            'created_at' => '创建时间',
            'comment_status' => '是否已评价 0未评价 1 已首次评价 2 已追加评价',
            'activity_package' => '执行的优惠，含标识，可能多个（优惠大礼包）',
            'pay_time' => '付款时间 跟随订单',
            'is_count' => '是否统计 (已 退款/退货退款 完成的不统计) 冗余字段 退款/退货退款完成后 该字段置为0',
            'plugin_identification' => '插件标识 商品参加的插件活动标识 ',
            'weight' => '重量',
            'is_print' => '是否已打印电子面单 1是0否',
            'cost_price' => '成本价',
            'shop_goods_id' => '商城商品id',
            'shop_option_id' => '商城规格id',
            'ext_field' => '扩展信息',
        ];
    }

    /**
     * 解析订单数据
     * @param $orderGoods
     * @return array | bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function decode(&$orderGoods)
    {
        //商品信息
        if (!empty($orderGoods)) {
            isset($orderGoods['unit']) && $orderGoods['unit'] = $orderGoods['unit'] ?: '件';
            !empty($orderGoods['dispatch']) && $orderGoods['dispatch'] = Json::decode($orderGoods['dispatch']);
            ValueHelper::isJson($orderGoods['plugin_identification']) && $orderGoods['plugin_identification'] = Json::decode($orderGoods['plugin_identification']);
            if (!empty($orderGoods['ext_field']) && ValueHelper::isJson($orderGoods['ext_field'])) {
                $orderGoods['ext_field'] = Json::decode($orderGoods['ext_field']);
            }
        }

        //配送方式
        if ($orderGoods['refund_type'] > 0) {
            $orderGoods['refund_type_text'] = self::$orderRefundType[$orderGoods['refund_type']];
            // 维权状态
            if ($orderGoods['refund_status'] != null) {
                if ($orderGoods['refund_status'] < 0 || $orderGoods['refund_status'] > 9) {
                    $orderGoods['refund_status_text'] = '已完成';
                } else {
                    $orderGoods['refund_status_text'] = '维权中';
                }
            }
        } else {
            $orderGoods['refund_status_text'] = '';
        }

        return $orderGoods;
    }

    /**
     * 获取订单IDs
     * @param array $params
     * @return array|\yii\db\ActiveRecord[]
     * @author likexin
     */
    public static function getOrderIds(array $params = [])
    {
        $params = array_merge([
            'andWhere' => [],
            'groupBy' => 'order_id',
            'select' => ['order_id']
        ], $params);

        $list = self::find()->where($params['andWhere'])->groupBy($params['groupBy'])->select($params['select'])->asArray()->all();

        return array_column($list, 'order_id');
    }

    /**
     * 获取会员已购买个数
     * @param int $memberId
     * @param int $goodsId
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getBuyTotal(int $memberId, int $goodsId)
    {
        return OrderGoodsModel::find()
            ->alias('order_goods')
            ->where([
                'and',
                ['order_goods.member_id' => $memberId],
                ['order_goods.goods_id' => $goodsId],
                ['>=', 'order_goods.status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY],
                ['order_goods.refund_type' => 0],
                ['<>', 'order.activity_type', [OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL, OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL]], // 不统计的订单类型 预售 秒杀
                ['order_goods.shop_goods_id' => 0]
            ])
            ->leftJoin(OrderModel::tableName() . ' order', 'order.id = order_goods.order_id')
            ->sum('total');
    }

    /**
     * 获取用户已购买商品 id
     * @param int $memberId
     * @param int $status 10 已付款  30 已完成
     * @param array $goodsIds
     * @param string $time 统计时间  分销用
     * @param string $degradeTime 降级时间, 分销用
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberOrderGoodsIds(int $memberId, int $status, array $goodsIds = [], string $time = '', string $degradeTime = '')
    {
        // 获取用户已购买的商品ids
        $query = OrderGoodsModel::find()
            ->select('goods_id')
            ->where([
                'and',
                ['>=', 'status', $status],
                ['member_id' => $memberId],
                ['is_count' => 1],
                ['shop_goods_id' => 0],
            ]);
        // 订单时间  分销用
        if (!empty($time)) {
            $query->andWhere(['>', 'created_at', $time]);
        }
        // 商品ids
        if (!empty($goodsIds)) {
            $query->andWhere(['in', 'goods_id', $goodsIds]);
        }

        // 降级时间  分销用
        if (!empty($degradeTime)) {
            $query->andWhere(['>=', 'created_at', $degradeTime]);
        }
        $memberGoodsIds = $query->get();
        return array_column($memberGoodsIds, 'goods_id');
    }

    /**
     * 是否可用货到付款
     * @param $orderId
     * @param int $clientType
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isDeliveryPay($orderId, int $clientType): bool
    {
        $settingEnable = ShopSettings::get('sysset.payment.typeset.' . ClientTypeConstant::getIdentify($clientType) . '.enable');
        if ($settingEnable == 0) {
            return false;
        }

        $orderGoods = OrderGoodsModel::find()
            ->alias('order_goods')
            ->leftJoin(GoodsModel::tableName() . ' goods', 'goods.id=order_goods.goods_id')
            ->where(['order_goods.order_id' => $orderId])
            ->select([
                'goods.ext_field'
            ])
            ->asArray()
            ->all();

        array_walk($orderGoods, function (&$result) {
            $result = Json::decode($result['ext_field']);
        });

        $isDeliveryPay = array_column($orderGoods, 'is_delivery_pay');

        //是否有交集0
        if (in_array(0, $isDeliveryPay)) {
            return false;
        }

        return true;
    }

}
