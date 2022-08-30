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
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\form\FormLogModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property int $id
 * @property int $order_type 订单类型 10普通订单 20虚拟订单
 * @property int $activity_type 订单活动类型 0普通订单 1 预售订单
 * @property string $order_no 订单号
 * @property string $trade_no 微信支付订单号
 * @property string $out_trade_no 外部交易单号
 * @property int $member_id 用户ID
 * @property int $status 订单状态 0未支付 10待发货 11部分发货 20待收货 21 待自提 30已完成 40已评价 -1 订单取消(关闭)
 * @property int $close_type 关闭类型 1 买家关闭 2 卖家关闭 3自动关闭订单
 * @property string $original_price 订单原始总金额
 * @property int $pay_type 1 后台确认  2 余额支付  3 货到付款  20 微信支付  30 支付宝支付
 * @property string $pay_price 实际支付金额（包含运费）
 * @property string $goods_price 商品价格
 * @property string $original_goods_price 原始商品价格（改价）
 * @property string $goods_info 商品的快照信息   goods_id,商品id  option_id,规格id  total,购买数量  title,商品名称  option_title,规格名称  thumb, 图片  unit, 单位  price金额,  price_unit 单价
 * @property string $address_state 收货地址省
 * @property string $address_city 收货地址市
 * @property string $address_area 收货地址区
 * @property string $address_code 地址编码
 * @property string $address_detail 详细地址
 * @property string $address_info 地址信息
 * @property int $agent_id 上级分销商id
 * @property string $created_at 下单时间
 * @property string $pay_time 支付时间
 * @property string $send_time 发货时间
 * @property string $finish_time 完成时间
 * @property string $cancel_time 取消时间
 * @property string $cancel_reason 取消理由
 * @property string $buyer_name 收件人姓名
 * @property string $buyer_mobile 收件人手机号
 * @property int $refund_type 售后类型 1整单售后  2单品维权
 * @property int $is_refund 维权订单 0 无维权  1 有维权
 * @property string $buyer_remark 买家备注
 * @property int $user_delete 用户是否删除订单 1删除 0没有
 * @property string $member_nickname 会员昵称
 * @property string $member_realname 会员真实姓名
 * @property string $member_mobile 会员手机号
 * @property string $change_price 改价后的金额
 * @property int $change_price_count 改价次数
 * @property string $dispatch_info 地址信息
 * @property int $dispatch_type 配送方式 0无需配送 10快递 20自提 30 同城配送
 * @property string $dispatch_price 运费
 * @property string $change_dispatch 改价运费
 * @property string $original_dispatch_price 原始运费
 * @property int $create_from 订单来源   10:wap   20:公众号   21:微信小程序   30:字节跳动小程序    50:PC
 * @property string $remark 卖家备注
 * @property string $cost_price 成本价
 * @property string $auto_finish_time 预计自动确认收货时间
 * @property string $auto_close_time 自动关闭时间
 * @property string $extra_discount_rules_package 执行折扣的活动规则
 * @property string $extra_price_package 附加价格信息包（JSON）存储了很重要的价格信息，包括了各种活动优惠价格：优惠券抵扣金额，积分抵扣金额，余额抵扣金额，秒杀优惠金额，限时折扣优惠金额，满减优惠金额，运费优惠金额等。用于存储所有不直接参与sql查询的价格信息
 * @property string $extra_package 扩展数据包
 * @property string $invoice_info 发票信息
 * @property string $extra_pay_price 额外的支付价格包
 * @property string $refund_price 维权金额，只有维权完成才赋值该字段
 * @property int $is_count 是否统计 冗余字段 退款/退货退款完成后 该字段置为0
 * @property int $scene 场景
 * @property int $scene_value 场景值
 * @property string $seller_remark 商家备注
 */
class OrderModel extends BaseActiveRecord
{

    /**
     * 订单状态对照
     * @var array
     */
    public static $orderStatus = [
        OrderStatusConstant::ORDER_STATUS_CLOSE => '已取消',
        OrderStatusConstant::ORDER_STATUS_WAIT_PAY => '待付款',
        OrderStatusConstant::ORDER_STATUS_WAIT_SEND => '待发货',
        OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND => '部分发货',
        OrderStatusConstant::ORDER_STATUS_WAIT_PICK => '待收货',
        OrderStatusConstant::ORDER_STATUS_SUCCESS => '已完成',
    ];

    /**
     * 支付状态
     * @var array
     */
    public static $orderPayType = [
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON => '无需支付',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM => '后台确认',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BALANCE => '余额支付',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY => '货到付款',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT => '微信支付',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY => '支付宝支付',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BYTEDANCE_ALIPAY => '字节跳动支付',
        OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BYTEDANCE_WECHAT => '字节跳动支付',
    ];

    /**
     * 配送方式
     * @var array
     */
    public static $orderDispatchType = [
        0 => '无需配送',
        OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS => '快递',
        OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH => '到店核销',
        OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY => '同城配送',
    ];

    /**
     * 订单类型
     * @var array
     */
    public static $orderType = [
        OrderTypeConstant::ORDER_TYPE_ORDINARY => '普通订单',
    ];

    /**
     * 订单活动类型
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public static $OrderActivityType = [

    ];

    /**
     * 活动字段
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public static $activityTypeFieldMap = [
        'full_deduct' => '满额立减',
        'platform_full_deduct' => '平台满额立减',
        'coupon' => '优惠券',
        'platform_coupon' => '平台优惠券',
        'credit' => '积分抵扣',
        'platform_credit' => '积分抵扣',
        'balance' => '余额抵扣',
        'platform_balance' => '余额抵扣',
        'member_price' => '会员价',
        'platform_member_price' => '会员价',
        'presell' => '商品预售',
        'seckill' => '秒杀',
        'gift_card' => '礼品卡',
        'full_reduce' => '满减折',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_type', 'activity_type', 'member_id', 'status', 'close_type', 'pay_type', 'agent_id', 'refund_type', 'is_refund', 'user_delete', 'change_price_count', 'dispatch_type', 'create_from', 'is_count', 'scene', 'scene_value'], 'integer'],
            [['trade_no', 'out_trade_no', 'goods_info', 'address_info', 'dispatch_info', 'extra_discount_rules_package', 'extra_price_package', 'extra_package', 'invoice_info', 'extra_pay_price'], 'string'],
            [['original_price', 'pay_price', 'goods_price', 'original_goods_price', 'change_price', 'dispatch_price', 'change_dispatch', 'original_dispatch_price', 'cost_price', 'refund_price'], 'number'],
            [['goods_info', 'extra_discount_rules_package', 'extra_price_package'], 'required'],
            [['created_at', 'pay_time', 'send_time', 'finish_time', 'cancel_time', 'auto_finish_time', 'auto_close_time'], 'safe'],
            [['order_no', 'cancel_reason'], 'string', 'max' => 50],
            [['address_state', 'address_city', 'address_area'], 'string', 'max' => 120],
            [['address_code'], 'string', 'max' => 6],
            [['address_detail', 'member_nickname', 'member_realname'], 'string', 'max' => 191],
            [['buyer_name', 'buyer_mobile'], 'string', 'max' => 30],
            [['buyer_remark', 'remark'], 'string', 'max' => 191],
            [['member_mobile'], 'string', 'max' => 15],
            [['order_no'], 'unique'],
            [['seller_remark'], 'string', 'max' => 200],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'close' => [
                'title' => '关闭订单',
                'item' => [
                    'order_no' => '订单编号',
                    'goods_info' => [
                        'title' => '商品信息',
                        'item' => [
                            'goods_id' => '商品id',
                            'title' => '商品名称'
                        ]
                    ]
                ]
            ],
            'close_and_refund' => [
                'title' => '退款',
                'item' => [
                    'order_no' => '订单编号',
                    'refund_price' => '退款金额',
                ]
            ],
            'change_price' => [
                'title' => '订单改价',
                'item' => [
                    'order_no' => '订单编号',
                    'change_dispatch_price' => '修改运费',
                    'goods_info' => [
                        'title' => '商品信息',
                        'item' => [
                            'title' => '商品名称',
                            'price' => '商品价格',
                        ]
                    ]
                ]
            ],
            'change_address' => [
                'title' => '修改收货',
                'item' => [
                    'buyer_name' => '收货人',
                    'buyer_mobile' => '手机号码',
                    'address_info' => '所属地区',
                    'address_detail' => '详细地址'
                ]
            ],
            'pay' => [
                'title' => '确认付款',
                'item' => [
                    'order_no' => '订单编号',
                    'goods_info' => [
                        'title' => '商品信息',
                        'item' => [
                            'goods_id' => '商品id',
                            'title' => '商品名称'
                        ]
                    ]
                ]
            ],
            'send' => [
                'title' => '确认发货',
                'item' => [
                    'order_no' => '订单编号',
                    'dispatch_type' => '发货类型',
                    'express' => '快递公司',
                    'express_sn' => '快递单号',
                    'order_goods_id' => '商品id',
                    'send_type' => '发货方式'
//                    'goods_info' => [
//                        'title' => '商品信息',
//                        'item' => [
//                            'goods_id' => '商品id',
//                            'title' => '商品名称'
//                        ]
//                    ]
                ]
            ],
            'change_express' => [
                'title' => '修改物流',
                'item' => [
                    'order_no' => '订单编号',
                    'express' => '快递公司',
                    'express_sn' => '快递单号',
                ]
            ],
            'finish' => [
                'title' => '确认收货',
                'item' => [
                    'order_no' => '订单编号',
                    'goods_info' => [
                        'title' => '商品信息',
                        'item' => [
                            'goods_id' => '商品id',
                            'title' => '商品名称'
                        ]
                    ],
                    'time' => '操作时间'
                ]
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_type' => '订单类型 10普通订单 20虚拟订单',
            'activity_type' => '订单活动类型 0普通订单 1 预售订单',
            'order_no' => '订单号',
            'trade_no' => '微信支付订单号',
            'out_trade_no' => '外部交易单号',
            'member_id' => '用户ID',
            'status' => '订单状态 0未支付 10待发货 11部分发货 20待收货 21 待自提 30已完成 40已评价 -1 订单取消(关闭)  ',
            'close_type' => '关闭类型 1 买家关闭 2 卖家关闭 3自动关闭订单',
            'original_price' => '订单原始总金额',
            'pay_type' => '1 后台确认
2 余额支付
3 货到付款
10 微信支付
20 支付宝支付',
            'pay_price' => '实际支付金额（包含运费）',
            'goods_price' => '商品价格',
            'original_goods_price' => '原始商品价格（改价）',
            'goods_info' => '商品的快照信息
goods_id,商品id
option_id,规格id
total,购买数量
title,商品名称
option_title,规格名称
thumb, 图片
unit, 单位
price金额,
price_unit 单价',
            'address_state' => '收货地址省',
            'address_city' => '收货地址市',
            'address_area' => '收货地址区',
            'address_code' => '地址编码',
            'address_detail' => '详细地址',
            'address_info' => '地址信息',
            'agent_id' => '上级分销商id',
            'created_at' => '下单时间',
            'pay_time' => '支付时间',
            'send_time' => '发货时间',
            'finish_time' => '完成时间',
            'cancel_time' => '取消时间',
            'cancel_reason' => '取消理由',
            'buyer_name' => '收件人姓名',
            'buyer_mobile' => '收件人手机号',
            'refund_type' => '售后类型 1整单售后  2单品维权',
            'is_refund' => '维权订单 0 无维权  1 有维权',
            'buyer_remark' => '买家备注',
            'user_delete' => '用户是否删除订单 1删除 0没有',
            'member_nickname' => '会员昵称',
            'member_realname' => '会员真实姓名',
            'member_mobile' => '会员手机号',
            'change_price' => '改价后的金额',
            'change_price_count' => '改价次数',
            'dispatch_info' => '地址信息',
            'dispatch_type' => '配送方式 0无需配送 10快递 20自提 30 同城配送',
            'dispatch_price' => '运费',
            'change_dispatch' => '改价运费',
            'original_dispatch_price' => '原始运费',
            'create_from' => '订单来源
10:wap
20:公众号
21:微信小程序
30:字节跳动小程序
50:PC',
            'remark' => '卖家备注',
            'cost_price' => '成本价',
            'auto_finish_time' => '预计自动确认收货时间',
            'auto_close_time' => '自动关闭时间',
            'extra_discount_rules_package' => '执行折扣的活动规则',
            'extra_price_package' => '附加价格信息包（JSON）存储了很重要的价格信息，包括了各种活动优惠价格：优惠券抵扣金额，积分抵扣金额，余额抵扣金额，秒杀优惠金额，限时折扣优惠金额，满减优惠金额，运费优惠金额等。用于存储所有不直接参与sql查询的价格信息',
            'extra_package' => '扩展数据包',
            'invoice_info' => '发票信息',
            'extra_pay_price' => '额外的支付价格包',
            'refund_price' => '维权金额，只有维权完成才赋值该字段',
            'is_count' => '是否统计 冗余字段 退款/退货退款完成后 该字段置为0',
            'scene' => '场景',
            'scene_value' => '场景值',
            'seller_remark' => '商家备注'
        ];
    }

    /**
     * 获取用户订单信息
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberOrder(int $memberId = 0)
    {
        $where = [
            'member_id' => $memberId,
        ];

        // 累计成功订单数和金额
        $success = self::find()
            ->select('count(*) success_count, sum(pay_price) success_price')
            ->where($where)
            ->andWhere(['>', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY])
            ->asArray()
            ->one();

        if ($success['success_price'] == null) {
            $success['success_price'] = 0;
        } else {
            $success['success_price'] = bcadd($success['success_price'], 0, 2);
        }

        $refundWhere = [
            'member_id' => $memberId,
            'is_count' => 0
        ];

        // 维权完成的订单数和金额
        $refundInfo = OrderModel::find()
            ->select('id, sum(refund_price) refund_price')
            ->where($refundWhere)
            ->groupBy('id')
            ->asArray()
            ->all();

        $refund['refund_count'] = count($refundInfo);
        $refund['refund_price'] = bcadd(array_sum(array_column($refundInfo, 'refund_price')), 0, 2);

        return array_merge($success, $refund);
    }


    /**
     * 获取订单和订单商品
     * @param int $id
     * @param int $orderGoodsId
     * @return array|bool|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderAndOrderGoods(int $id, int $orderGoodsId = 0)
    {

        $orderWhere = [
            'id' => $id,
        ];

        $order = self::find()->where($orderWhere)
            ->with([
                'orderGoods' => function ($query) use ($orderGoodsId) {
                    // 兼容维权详情 维权单商品详情只展示一个商品
                    if (!empty($orderGoodsId)) {
                        $query->where(['id' => $orderGoodsId]);
                    }
                }
            ])
            ->asArray()
            ->one();

        if (empty($order)) {
            return [];
        }
        $order = self::decode($order);


        $formData = FormLogModel::find()
            ->where([
                'order_id' => $order['id'],
            ])
            ->get();

        foreach ($order['orderGoods'] as &$item) {
            foreach ($formData as $k => $v) {
                if ($item['goods_id'] == $v['goods_id']) {
                    $item['form_data'] = $v;
                }
            }
        }
        unset($item);


        // 如果整单维权 查找维权状态
        if ($order['refund_type'] == 1 && $order['is_refund'] == 1) {
            $refund = OrderRefundModel::find()->select('status')->where(['order_id' => $id])->first();
            $order['refund_status'] = $refund['status'];
            if ($refund['status'] < 0 || $refund['status'] > 9) {
                $order['refund_text'] = '已完成';
            } else {
                $order['refund_text'] = '维权中';
            }
        }

        array_walk($order['orderGoods'], function (&$result) {
            OrderGoodsModel::decode($result);
        });

        // 如果订单已发货
        if ($order['status'] > 10) {
            // 查找包裹信息
            $package = OrderPackageModel::find()
                ->select('package.express_sn, express.name as express_com,package.express_name')
                ->alias('package')
                ->leftJoin(CoreExpressModel::tableName() . ' express', 'express.id=package.express_id')
                ->where(['order_id' => $id])
                ->get();

            foreach ($package as &$item) {
                !empty($item['express_name']) && $item['express_com'] = $item['express_name'];
            }

            $order['package_info'] = $package;
            // 11 肯定是分包裹
            if ($order['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND) {
                $order['send_type'] = 1; // 分包裹
            } else {
                // 分包裹
                if (count($package) > 1) {
                    $order['send_type'] = 1;
                } else {
                    // 整单
                    $order['send_type'] = 0;
                }
            }
        }

        // 积分商城
        if ($order['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            $creditShopOrder = CreditShopOrderModel::find()->select(['order_id', 'pay_credit', 'credit_unit'])->where(['order_id' => $order['id']])->first();
            $order['pay_credit'] = $creditShopOrder['pay_credit'];
            $order['orderGoods'][0]['credit'] = $creditShopOrder['pay_credit'];
            $order['orderGoods'][0]['credit_unit'] = $creditShopOrder['credit_unit'];
        }

        return $order;
    }

    /**
     * 解析订单数据
     * @param $order
     * @return array | bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function decode($order)
    {
        if (empty($order)) {
            return false;
        }

        //支付方式
        if (isset($order['pay_type'])) {
            $order['pay_type_text'] = self::$orderPayType[$order['pay_type']];
            // 代付款的订单特殊处理
            if ($order['pay_type'] == '0' && isset($order['status']) && $order['status'] == '0') {
                $order['pay_type_text'] = '-';
            }
        }

        //配送方式
        if (isset($order['dispatch_type'])) {
            $order['dispatch_type_text'] = self::$orderDispatchType[$order['dispatch_type']];
        }

        //状态信息
        if (isset($order['status'])) {
            $order['status_text'] = self::$orderStatus[$order['status']];
        }

        //发票信息
        if (!empty($order['invoice_info'])) {
            $order['invoice_info'] = Json::decode($order['invoice_info']);

        }

        //商品快照
        $goodsInfoMap = [];
        if (!empty($order['goods_info']) && is_string($order['goods_info'])) {
            $order['goods_info'] = Json::decode($order['goods_info']) ?? [];
            //补充商品信息
            $goodsInfoMap = array_column($order['goods_info'], NULL, 'goods_id');
        }

        //商品信息
        if (!empty($order['orderGoods'])) {
            array_walk($order['orderGoods'], function (&$g) use ($goodsInfoMap) {
                OrderGoodsModel::decode($g);
                $g['type'] = $goodsInfoMap[$g['goods_id']]['type'];
                $g['auto_deliver'] = $goodsInfoMap[$g['goods_id']]['auto_deliver'];
                $g['auto_deliver_content'] = $goodsInfoMap[$g['goods_id']]['auto_deliver_content'];
            });
        }

        //订单来源
        if (isset($order['create_from'])) {
            $order['create_from_text'] = ClientTypeConstant::getText($order['create_from']);
        }

        //附加价格信息
        if (!empty($order['extra_price_package'])) {
            $order['extra_price_package'] = Json::decode($order['extra_price_package']);
            if (is_array($order['extra_price_package'])) {
                foreach ($order['extra_price_package'] as $packageIndex => $packageItem) {
                    $order['extra_price_package_text'][self::$activityTypeFieldMap[$packageIndex]] = $packageItem;
                }
            }
        }

        //活动规则信息
        if (!empty($order['extra_discount_rules_package'])) {
            $order['extra_discount_rules_package'] = Json::decode($order['extra_discount_rules_package']);
        }

        if (!empty($order['extra_package'])) {
            $order['extra_package'] = Json::decode($order['extra_package']);
        }

        if (isset($order['order_type']) && $order['order_type'] > 0) {
            $order['type_text'] = self::$orderType[$order['order_type']];
        }

        //会员昵称
        if (!empty($order['member_nickname'])) {
            if (is_object($order)) {
                $orderArr = $order->toArray();
            } else {
                $orderArr = $order;
            }
            $orderArr['mobile'] = $order['member_mobile'];
            unset($orderArr);
        }

        return $order;
    }

    /**
     * 获取自动确认收货天数
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAutoReceiveDays()
    {
        $autoFinishDays = ShopSettings::get('sysset.trade.auto_receive_days');
        return $autoFinishDays;
    }

    /**
     * 获取是否自动收货
     * @return array|mixed|string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getAutoReceive()
    {
        return (ShopSettings::get('sysset.trade.auto_receive', 0) == SyssetTypeConstant::CUSTOMER_AUTO_RECEIVE_TIME);
    }

    /**
     * 删除订单
     * @param $orderInfo
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteOrder($orderInfo)
    {
        if (is_int($orderInfo)) {
            $orderInfo = self::findOne(['id' => $orderInfo]);
        }

        if (empty($orderInfo)) {
            return error('订单不存在');
        }

        $orderInfo->user_delete = 1;
        if (!$orderInfo->save()) {
            return error($orderInfo->getErrorMessage());
        }

        return true;
    }

    /**
     * 获取订单的所有包裹
     * @param $orderId
     * @param int $packageId
     * @param array $order
     * @return array
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPackages($orderId, $packageId = 0, $order = [])
    {
        if (empty($order)) {
            $order = self::getOrderAndOrderGoods($orderId);
        }

        if (empty($order)) {
            return [];
        }

        $where = [];
        if (!empty($packageId)) {
            $where['id'] = $packageId;
        }
        $where['order_id'] = $order['id'];

        $packages = OrderPackageModel::find()->where($where)->orderBy('id asc')->asArray()->all();
        if (!empty($packages)) {
            foreach ($packages as &$package) {
                OrderPackageModel::setPackage($package);
            }
        }

        return $packages;
    }

    /**
     * 计算订单实际金额
     * 支付 + 余额抵扣 - 维权
     * @param array $list 查找出来的订单列表
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculateOrderPrice(array $list)
    {
        // 合计
        $sum = 0;
        foreach ($list as $key => $value) {
            $deductInfo = Json::decode($value['extra_price_package']);
            $balanceDeduct = 0; // 余额抵扣
            if (isset($deductInfo['balance']) && $deductInfo['balance'] != 0) {
                $balanceDeduct = $deductInfo['balance'];
            }
            $sum = bcadd($sum, $value['pay_price'], 2); // 实际支付
            $sum = bcadd($sum, $balanceDeduct, 2); // 余额抵扣
            $sum = bcsub($sum, $value['refund_price'], 2); // 维权
            // 加上定金
            if (isset($value['extra_discount_rules_package'][0]['presell'])) {
                $sum = bcadd($sum, $value['extra_discount_rules_package'][0]['presell']['front_money']);
            }
        }

        return $sum;
    }

    /**
     * 订单商品
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getOrderGoods()
    {
        return $this->hasMany(OrderGoodsModel::class, ['order_id' => 'id']);
    }

    /**
     * 商品
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGoods()
    {
        return $this->hasMany(GoodsModel::class, ['id' => 'goods_id'])->via('orderGoods');
    }

    /**
     * 物流
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getPackage()
    {
        return $this->hasMany(OrderPackageModel::class, ['order_id' => 'id']);
    }

    /**
     * 售后
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getRefunds()
    {
        return $this->hasMany(OrderRefundModel::class, ['order_id' => 'id']);
    }

    public function getMember()
    {
        return $this->hasOne(MemberModel::class, ['id' => 'member_id']);
    }

    /**
     * 获取支付订单数量
     * 支付订单数 - 维权订单数
     * @param int $memberId
     * @param int $status
     * @param array $andWhere
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderCount(int $memberId, int $status, array $andWhere = [])
    {
        return self::find()
            ->where([
                'and',
                ['member_id' => $memberId],
                ['>=', 'status', $status],
                ['is_count' => 1]
            ])
            ->andWhere($andWhere)
            ->count();
    }

    /**
     * 获取订单金额
     * 实际支付 + 余额抵扣 - 维权金额
     * @param int $memberId
     * @param int $status 订单时间节点  付款后 或 订单完成后
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderPrice(int $memberId, int $status)
    {
        // 获取所有支付订单
        $list = self::find()
            ->select(['id', 'pay_price', 'refund_price', 'extra_price_package', 'extra_discount_rules_package'])
            ->where([
                'and',
                ['member_id' => $memberId],
                ['>=', 'status', $status],
            ])->get();
        // 计算
        return self::calculateOrderPrice($list);
    }

    /**
     * 获取消费排名
     * @param $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getConsumeRanking($memberId)
    {
        $ranking = self::find()->select('SUM(o.pay_price - o.refund_price) AS total, o.member_id, m.avatar')
            ->leftJoin(MemberModel::tableName() . ' m', 'm.id = o.member_id')
            ->alias('o')
            ->where(['m.is_black' => 0, 'm.is_deleted' => 0])
            ->andWhere(['>', 'status', 0])
            ->groupBy('o.member_id')
            ->orderBy('total desc')
            ->asArray()
            ->all();

        $rankNum = 0;
        $avatar = '';
        $total = 0;
        if (!empty($ranking)) {
            foreach ($ranking as $rankItem) {
                $rankNum++;
                if ($rankItem['member_id'] == $memberId) {
                    $avatar = $rankItem['avatar'];
                    $total = $rankItem['total'];
                    break;
                }
            }
        }

        return ['rank' => $rankNum, 'avatar' => $avatar, 'total' => $total];
    }

    /**
     * 本周的消费变化
     * @param $memberId
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWeekConsume($memberId)
    {
        $week = DateTimeHelper::getWeekDate(date('Y'), date('W'));
        $startWeek = $week[0];
        $endWeek = date('Y-m-d', strtotime($week[1]) + 86400);
        $weekConsume = OrderModel::find()
            ->select('sum(pay_price-refund_price) as total')
            ->where([
                'and',
                ['member_id' => $memberId],
                ['>', 'pay_time', $startWeek],
                ['<', 'pay_time', $endWeek],
                ['>', 'status', 0]
            ])->asArray()
            ->first();

        return empty($weekConsume) ? 0 : $weekConsume['total'];
    }

    /**
     * 获取订单商品
     * @param $orderId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderGoodsInfo($orderId)
    {
        $order = OrderModel::find()
            ->where([
                'id' => $orderId
            ])
            ->select(
                [
                    'id',
                    'member_id',
                    'order_no',//订单编码
                    'order_type',//订单类型
                    'pay_type', //支付方式
                    'create_from', // 订单来源
                    'created_at',// 下单时间
                    'pay_time',// 付款时间
                    'finish_time', //完成时间
                    'original_goods_price', // 合计
                    'pay_price', //实际支付价格
                    'dispatch_price', // 运费,
                    'remark', // 卖家备注
                    'buyer_remark', // 买家备注
                    'address_info', //收货信息
                    'buyer_name',
                    'buyer_mobile',
                    'goods_info',
                    'dispatch_type',
                    'extra_price_package',
                    'extra_package',
                    'extra_discount_rules_package'
                ]
            )
            ->first();

        return $order;
    }


}
