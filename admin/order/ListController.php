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

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\RefundConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\services\order\OrderExportService;

/**
 * 订单列表
 * Class ListController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\order
 */
class ListController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowHeaderActions' => [
            'index',
            'close',
            'pay',
            'send',
            'success',
            'pick',
            'refund',
        ],
        'allowActions' => [
            'goods-type'
        ]
    ];

    /**
     * 订单类型
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActivityType()
    {
        $type = [
            ['key' => '0', 'value' => '普通订单', 'identity' => 'shop']
        ];

        // 需要校验权限的
        $needPermType = [
            ['key' => OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL, 'value' => '秒杀订单', 'identity' => 'seckill'],
        ];

        foreach ($needPermType as $item) {
            $type[] = $item;
        }

        return $this->result(['activity_type' => $type]);
    }

    /**
     * 商品类型
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsType()
    {
        $type = [
            ['key' => OrderTypeConstant::ORDER_TYPE_ORDINARY, 'value' => '实体商品', 'identity' => 'shop'],
            ['key' => OrderTypeConstant::ORDER_TYPE_VIRTUAL, 'value' => '虚拟商品', 'identity' => 'shop'],
        ];

        // 需要校验权限的
        $needPermType = [
            ['key' => OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT, 'value' => '虚拟卡密', 'identity' => 'virtualAccount'],
            ['key' => OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON, 'value' => '优惠券', 'identity' => 'creditShop'],
        ];

        // 如果是多商户 需不需要校验权益
        foreach ($needPermType as $item) {
            $type[] = $item;
        }

        // 业绩考核返回的数据过滤
        if (RequestHelper::get('is_commission_assessment') == 1) {
            $type = array_column($type, null, 'key');
            unset($type['40']);// 删除优惠券
            $type = array_values($type);
        }

        return $this->result(['goods_type' => $type]);
    }

    /**
     * 配送方式
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDispatchType()
    {
        $type = [
            ['id' => '10', 'name' => '快递配送',],
            ['id' => '30', 'name' => '同城配送',],
        ];

        return $this->result(['dispatch_type' => $type]);
    }

    /**
     * 查看所有订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): \yii\web\Response
    {
        $data = $this->lists();
        return $this->success($data);
    }

    /**
     * 查看已关闭订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionClose(): \yii\web\Response
    {
        $data = $this->lists('CLOSE');
        return $this->success($data);
    }

    /**
     * 查看待支付订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay(): \yii\web\Response
    {
        $data = $this->lists('WAIT_PAY');
        return $this->success($data);
    }

    /**
     * 查看待发货订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSend(): \yii\web\Response
    {
        $data = $this->lists('WAIT_SEND');
        return $this->success($data);
    }

    /**
     * 查看待收货订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPick(): \yii\web\Response
    {
        $data = $this->lists('WAIT_PICK');
        return $this->success($data);
    }

    /**
     * 查看已完成订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSuccess(): \yii\web\Response
    {
        $data = $this->lists('SUCCESS');
        return $this->success($data);
    }

    /**
     * 维权订单
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRefund(): \yii\web\Response
    {
        $data = $this->lists('REFUND');
        return $this->success($data);
    }

    /**
     * 订单列表数据
     * @param null $status
     * @return array|OrderModel[]
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function lists($status = null)
    {
        $get = RequestHelper::get();
        if (is_null($status)) {
            $status = strtoupper($get['status']);
        }

        $where = ['and'];
        $andWhere = [];
        $searchs = [];
        $leftJoins = [];

        //关键词查询  订单号 第三方支付订单号 运单号
        $keywords = trim($get['keywords']);

        $allowKeywordsType = ['order_no', 'trade_no', 'express_sn'];
        if (!empty($get['search_field']) && in_array($get['search_field'], $allowKeywordsType) && !empty($keywords)) {
            if ($get['search_field'] == 'express_sn') {
                $packageOrderIdsArray = OrderPackageModel::find()->select('order_id')->where(["express_sn" => $keywords])->asArray()->all();
                $orderIds = array_column($packageOrderIdsArray, 'order_id');
                $where[] = ['o.id' => $orderIds];
            } elseif ($get['search_field'] == 'order_no') {
                $searchs[] = ["o.order_no", 'like', 'keywords'];
            } else {
                $searchs[] = ["o.{$get['search_field']}", 'like', 'keywords'];
            }
        }
        if ($get['search_field'] == 'member_id') {
            $searchs[] = ['o.member_id', 'int', 'keywords'];
        }

        //商品名称
        if ($get['search_field'] == 'goods_title') {
            $searchs[] = ['o.goods_info', 'like', 'keywords'];
        }

        //商品编码
        if ($get['search_field'] == 'goods_sku') {
            $leftJoins[] = [OrderGoodsModel::tableName() . 'as og', 'o.id = og.order_id'];
            $searchs[] = ['og.goods_sku', 'like', 'keywords'];
        }

        //会员信息信息
        if ($get['search_field'] == 'member_keywords') {
            $searchs[] = [[
                'o.member_nickname',
                'o.member_nickname',
                'o.member_realname',
                'o.member_mobile'
            ], 'like', 'keywords'];
        }

        //买家信息信息(收件人)
        if ($get['search_field'] == 'buyer_keywords') {
            $searchs[] = [['o.buyer_name', 'o.buyer_mobile'], 'like', 'keywords'];
        }

        //地址信息
        if ($get['search_field'] == 'address_keywords') {
            $searchs[] = [[
                'o.address_state',
                'o.address_city',
                'o.address_area',
                'o.address_detail',
            ], 'like', 'keywords'];
        }

        $orderBy = [];
        //订单类型
        $searchs[] = ['o.activity_type', 'int', 'activity_type'];
        //订单来源
        $searchs[] = ['o.create_from', 'int', 'create_from'];
        //时间范围查询
        $searchs[] = ["o.created_at", 'between', 'time'];

        //配送方式
        $searchs[] = ["o.dispatch_type", 'int', 'dispatch_type'];

        //支付方式查询
        if (isset($get['pay_type']) && $get['pay_type'] != '') {
            switch ($get['pay_type']) {
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON:
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON]; //未付款
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM:
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM]; //后台确认
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BALANCE:
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BALANCE];//余额支付
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY:
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY];//货到付款
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT:
//                    $where[] = ['between', 'o.pay_type', 10, 19];//微信支付
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT];//微信支付
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY:
//                    $where[] = ['between', 'o.pay_type', 20, 29];//支付宝支付
                    $where[] = ['o.pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY];//支付宝支付
                    break;
                case OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BYTEDANCE_WECHAT:
//                    $where[] = ['between', 'o.pay_type', 20, 29];//支付宝支付
                    $where[] = ['o.pay_type' => [OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BYTEDANCE_WECHAT, OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_BYTEDANCE_ALIPAY]];//字节跳动支付

                    break;
                default:
                    break;
            }
        }

        //订单状态查询条件
        if (isset($status)) {
            if ($status == 'WAIT_PAY') {//待支付
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY];
            } elseif ($status == 'CLOSE') { //取消订单
                $where[] = ['<', 'o.status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY];
            } elseif ($status == 'WAIT_SEND') { //待发货
                $where[] = ['o.status' => [OrderStatusConstant::ORDER_STATUS_WAIT_SEND, OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND]];
            } elseif ($status == 'WAIT_PICK') { //待收货
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK];
            } elseif ($status == 'SUCCESS') { //已完成
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_SUCCESS];
            } elseif ($status == 'REFUND') { // 去掉非维权订单
                $where[] = ['o.is_refund' => 1];
                $orderBy = ['refund.created_at' => SORT_DESC];
            }
        }

        //如果是店铺助手，过滤商品
        if ($this->clientType == ClientTypeConstant::MANAGE_SHOP_ASSISTANT) {
            $andWhere[] = ['not in', 'order_type', [OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT]];
            // 2021-08-06 店铺助手v2优化 同步拼团返利的订单
            $andWhere[] = ['not in', 'activity_type', [OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL]];
        }

        $leftJoins[] = [OrderRefundModel::tableName() . ' as refund', 'o.id = refund.order_id and refund.is_history = 0'];
        // 维权状态条件 0 为维权  1维权中  2已完成
        if ($get['refund_status'] != '') {
            switch ($get['refund_status']) {
                case 0: // 未维权
                    $where[] = ['o.is_refund' => OrderConstant::IS_REFUND_NO];
                    break;
                case 1: // 维权中
                    $where[] = ['between', 'refund.status', RefundConstant::REFUND_STATUS_APPLY, RefundConstant::REFUND_STATUS_WAIT];
                    break;
                case 2: // 已完成 买家取消的不算维权
                    $where[] = ['>=', 'refund.status', RefundConstant::REFUND_STATUS_SUCCESS];
                    break;
            }
        }

        // 维权类型筛选
        if (!empty($get['refund_type'])) {
            // 4 退款的订单
            if ($get['refund_type'] != 4) {
                $where[] = ['refund.refund_type' => $get['refund_type']];
            } else { // 仅退款 退货退款
                $where[] = [
                    'or',
                    ['refund.refund_type' => RefundConstant::TYPE_REFUND],
                    ['refund.refund_type' => RefundConstant::TYPE_RETURN],
                ];
            }
        }

        // 订单商品类型筛选
        if ($get['type'] && $get['type'] != 'all') {
            $where[] = ['o.order_type' => $get['type']];
        }

        $orderBy['o.id'] = SORT_DESC;

        //如果是导出
        if ($get['export']) {
            OrderExportService::export($where, $searchs);
        }

        $params = [
            'searchs' => $searchs,
            'where' => $where,
            'andWhere' => $andWhere,
            'alias' => 'o',
            'leftJoins' => $leftJoins,
            'orderBy' => $orderBy,
            'select' => $this->orderFields,
            'indexBy' => 'id',
            'groupBy' => 'o.id',
        ];

        //拼团订单id
        $groupsOrderId = [];

        //查询订单
        $orders = OrderModel::getColl($params, [
            'callable' => function (&$row) use (&$groupsOrderId) {
                $row = OrderModel::decode($row);
                $row['auto_close_time'] = strtotime($row['auto_close_time']);
                // 查找订单分销信息
                $commissionData = CommissionOrderDataModel::find()
                    ->select('id, level, commission, agent_id, ladder_commission')
                    ->where(['order_id' => $row['id'], 'is_count_refund' => 1])
                    ->indexBy('level')
                    ->get();
                if (!empty($commissionData)) {
                    $row['commission_info'] = $commissionData;
                }

                //初始化icon
                $row['icon'] = [
                    'electronic_sheet' => 0
                ];

            }
        ]);

        //订单商品条件
        $orderGoodsWhere = [
            'order_id' => array_keys($orders['list']),
        ];

        //如果有快递助手 and 不等于代付款时 需要验证是否存在其他快递
        if ($status != 'WAIT_PAY') {

            //查询包裹是否是使用其他快递
            $qitaPackage = OrderPackageModel::where([
                'order_id' => array_keys($orders['list']),
            ])->select([
                'count(*) as total',
                "count(if(express_com='qita',true,null)) qita_total",
                'order_id'
            ])->groupBy('order_id')->get();

            //不显示电子面单按钮组
            $notShowExpressHelper = [];
            foreach ($qitaPackage as $item) {
                if ($item['total'] <= $item['qita_total']) $notShowExpressHelper[] = $item['order_id'];
            }
            unset($item);


            if (!empty($notShowExpressHelper)) {
                foreach ($notShowExpressHelper as $item) {
                    $orders['list'][$item]['not_show_express'] = 1;
                }
            }
        }
        // 订单类型与商品类型关系 为了省去下面多查一次商品信息
        $goodsType = [
            OrderTypeConstant::ORDER_TYPE_ORDINARY => GoodsTypeConstant::GOODS_TYPE_ENTITY,
            OrderTypeConstant::ORDER_TYPE_VIRTUAL => GoodsTypeConstant::GOODS_TYPE_VIRTUAL,
            OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT => GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT,
            OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON => GoodsTypeConstant::GOODS_TYPE_CREDIT_SHOP_COUPON,
        ];

        //订单商品查询
        OrderGoodsModel::getColl([
            'select' => $this->orderGoodsFields,
            'where' => $orderGoodsWhere,
        ], [
            'disableSort' => false,
            'pager' => false,
            'onlyList' => true,
            'callable' => function ($row) use (&$orders, $goodsType) {
                // 订单类型和商品类型一一对应
                $row['type'] = (string)$goodsType[$orders['list'][$row['order_id']]['order_type']];
                $row['has_option'] = $row['option_id'] > 0 ? '1' : '0';

                //赋值其他快递
                OrderGoodsModel::decode($row);
                $orders['list'][$row['order_id']]['order_goods'][] = $row;
                $orders['list'][$row['order_id']]['goods_count'] += 1;
            }
        ]);


        foreach ($orders['list'] as $orderIndex => &$orderItem) {
            if (is_array($orderItem['order_goods']) && !empty($orderItem['order_goods'])) {
                foreach ($orderItem['order_goods'] as $orderGoodsItem) {

                    //如果订单状态大于等于付款 并且没有维权
                    if ($orderItem['status'] >= OrderStatusConstant::ORDER_STATUS_WAIT_SEND && !($orderGoodsItem['refund_type'] != 0 && $orderGoodsItem['refund_status'] > 0)) {

                        //添加可打印电子面单icon
                        $orderItem['icon']['electronic_sheet'] = 1;
                        break;
                    }
                }
            }
        }

        $orders['list'] = array_values($orders['list']);

        return $orders;

    }

    /**
     * 订单列表返回的字段
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    private $orderFields = [
        // 订单相关
        'o.id',
        'o.status',
        'o.order_no',
        'o.trade_no',
        'o.pay_type',
        'o.goods_price',
        'o.activity_type',
        'o.original_goods_price',
        'o.scene',
        // 会员相关
        'o.member_id',
        'o.member_realname',
        'o.member_nickname',
        'o.member_mobile',
        // 价格相关
        'o.pay_price',
        // 配送相关
        'o.dispatch_price',
        'o.dispatch_info',
        // 收货信息
        'o.buyer_remark',
        // 时间信息
        'o.created_at',
        // 附带信息
        'o.order_type',
        // 其他信息
        'o.create_from',
        'o.send_time',
        'o.buyer_name',
        'o.buyer_mobile',
        'o.change_price',
        'o.change_dispatch',
        'o.dispatch_type',
        'o.extra_price_package',
        // 维权相关
        'o.is_refund',
        'o.refund_type',
        'o.auto_close_time',
        'refund.order_goods_id',
        'refund.status refund_status',
        'refund.refund_type order_refund_type',
        'refund.need_platform', // 平台介入
        //发票信息
        'invoice_info',
        'extra_discount_rules_package',
        //订单商家备注
        'o.seller_remark'
    ];

    /**
     * 订单商品字段
     * @var array
     */
    private $orderGoodsFields = [
        'id',
        'order_id',
        'goods_id',
        'option_id',
        'price',
        'price_unit',
        'price_discount',
        'total',
        'title',
        'option_title',
        'thumb',
        'is_single_refund',
        'refund_status',
        'refund_type',
        'package_id',
        'package_cancel_reason',
        'goods_sku',
        'plugin_identification',
        'shop_goods_id',
        'shop_option_id',
        'ext_field',
        'is_count',
    ];

}
