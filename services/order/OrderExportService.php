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


namespace shopstar\services\order;


use shopstar\bases\service\BaseService;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\helpers\ExcelHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\form\FormLogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\order\refund\OrderRefundModel;
use yii\helpers\Json;

class OrderExportService extends BaseService
{

    /**
     * 默认导出的字段
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public static $exportField = [
        [
            'field' => 'member_id',
            'title' => '会员id',
        ],
        [
            'field' => 'member_nickname',
            'title' => '会员昵称',
        ],
        [
            'field' => 'member_realname',
            'title' => '会员姓名',
        ],
        [
            'field' => 'member_level_name',
            'title' => '会员等级',
        ],
        [
            'field' => 'member_group_name',
            'title' => '会员标签',
        ],
        [
            'field' => 'member_mobile',
            'title' => '会员手机号',
        ],
        [
            'field' => 'order_no',
            'title' => '订单编号',
        ],
        [
            'field' => 'create_from',
            'title' => '订单来源',
        ],
        [
            'field' => 'created_at',
            'title' => '下单时间',
        ],
        [
            'field' => 'pay_time',
            'title' => '支付时间',
        ],
        [
            'field' => 'pay_type_text',
            'title' => '订单支付方式',
        ],
        [
            'field' => 'buyer_name',
            'title' => '收货人姓名',
        ],
        [
            'field' => 'buyer_mobile',
            'title' => '收货人电话',
        ],
        [
            'field' => 'address_state',
            'title' => '收货地址省份',
        ],
        [
            'field' => 'address_city',
            'title' => '收货人地址城市',
        ],
        [
            'field' => 'address_area',
            'title' => '收货人地址地区',
        ],
        [
            'field' => 'address_detail',
            'title' => '收货地址',
        ],
        [
            'field' => 'goods_title',
            'title' => '商品名称',
        ],
        [
            'field' => 'short_name',
            'title' => '商品简称',
        ],
        [
            'field' => 'bar_code',
            'title' => '商品条码',
        ],
        [
            'field' => 'goods_sku',
            'title' => '商品编码',
        ],
        [
            'field' => 'option_title',
            'title' => '商品规格',
        ],
        [
            'field' => 'price_unit',
            'title' => '商品价格',
        ],
        [
            'field' => 'cost_price',
            'title' => '商品成本价',
        ],
        [
            'field' => 'total',
            'title' => '商品数量',
        ],
        [
            'field' => 'goods_form',
            'title' => '商品表单',
        ],
        [
            'field' => 'goods_price',
            'title' => '商品小计',
        ],
        [
            'field' => 'out_trade_no',
            'title' => '外部交易单号',
        ],
        [
            'field' => 'extra_price_package_text',
            'title' => '优惠详情',
        ],
        [
            'field' => 'discount_price',
            'title' => '优惠金额',
        ],
        [
            'field' => 'dispatch_price',
            'title' => '运费',
        ],
        [
            'field' => 'express_name',
            'title' => '快递',
        ],
        [
            'field' => 'express_sn',
            'title' => '快递单号',
        ],
        [
            'field' => 'change_price',
            'title' => '订单改价',
        ],
        [
            'field' => 'change_dispatch',
            'title' => '运费改价',
        ],
        [
            'field' => 'pay_price',
            'title' => '应收款',
        ],
        [
            'field' => 'status_text',
            'title' => '状态',
        ],
        [
            'field' => 'refund_price',
            'title' => '维权金额',
        ],
        [
            'field' => 'refund_status_text',
            'title' => '维权状态',
        ],
        [
            'field' => 'seller_remark',
            'title' => '商家备注',
        ],
        [
            'field' => 'order_form',
            'title' => '下单表单',
        ],
        [
            'field' => 'verify_title',
            'title' => '核销点选择',
        ],
    ];


    /**
     * 导出
     * @param array $where
     * @param array $searchs
     * @param array $field
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function export(array $where = [], array $searchs = [], array $field = [])
    {
        set_time_limit(0);
        $params = [
            'alias' => 'og',
            'leftJoins' => [
                [OrderModel::tableName() . 'o', 'o.id=og.order_id'],
                [OrderPackageModel::tableName() . 'package', 'package.id=og.package_id'],
                [OrderRefundModel::tableName() . ' refund', 'o.id = refund.order_id and refund.is_history = 0'],
            ],
            'searchs' => $searchs,
            'where' => $where,
            'select' => [
                'o.id as order_id',
                'o.order_no',
                'o.activity_type',
                'o.create_from',
                'o.buyer_name',
                'o.buyer_mobile',
                'o.pay_type',
                'o.member_id',
                'o.member_realname',
                'o.member_nickname',
                'o.member_mobile',
                'o.goods_price',
                'o.address_state',
                'o.address_city',
                'o.address_area',
                'o.address_detail',
                'o.change_price',
                'o.change_dispatch',
                'o.dispatch_price',
                'o.pay_price',
                'o.status',
                'o.refund_price',
                'o.goods_info',
                'o.created_at',
                'o.pay_time',
                'o.send_time',
                'o.finish_time',
                'o.finish_time',
                'o.extra_price_package',
                'o.extra_discount_rules_package',
                'og.cost_price',
                'og.title as goods_title',
                'og.short_name',
                'og.price_unit',
                'og.option_title',
                'og.total',
                'og.goods_sku',
                'og.bar_code',
                'og.price_unit as order_goods_price',
                'og.refund_status',
                'og.refund_type',
                'package.express_com',
                'package.express_id',
                'package.express_sn',
                'o.seller_remark',
                'o.order_type',
                'o.seller_remark',
                'o.out_trade_no',
                'og.goods_id',
            ],
            'orderBy' => [
                'o.created_at' => SORT_DESC
            ]
        ];

        $creditShopOrderId = []; // 积分商城订单id

        //查询订单
        $list = OrderGoodsModel::getColl($params, [
            'pager' => false,
            'onlyList' => true,
            'callable' => function (&$row) use (&$creditShopOrderId) {
                //快递公司
                $row['express_name'] = CoreExpressModel::getNameById($row['express_id']);
                $row = OrderModel::decode($row);
                $row = OrderGoodsModel::decode($row);
                //优惠金额 非预售
                if ($row['activity_type'] != OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL) {
                    $row['discount_price'] = array_sum(array_values($row['extra_price_package'] ?: []));
                } else {
                    // 预售
                    $row['discount_price'] = $row['extra_discount_rules_package'][0]['presell']['actual_deduct'];
                    if ($row['extra_discount_rules_package'][0]['presell']['presell_type'] == 0) {
                        $row['pay_price'] += $row['extra_discount_rules_package'][0]['presell']['front_money'];
                    }
                }
                if ($row['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                    // 积分商城订单
                    $creditShopOrderId[] = $row['order_id'];
                }

                $row['member_nickname'] = trim($row['member_nickname'], '=');
                $row['pay_type_text'] = OrderPaymentTypeConstant::getText($row['pay_type']);
                // 待支付订单特殊显示
                if ($row['pay_type'] == '0' && $row['status'] == '0') {
                    $row['pay_type_text'] = '未支付';
                }
                //折扣
                $extraPriceStr = '';
                foreach ((array)$row['extra_price_package_text'] as $extraPriceIndex => $extraPriceItem) {
                    if ($extraPriceIndex == '商品预售') {
                        // 看 extra_discount_rules_package 字段
                        $extraPriceStr .= $extraPriceIndex . ':' . $row['extra_discount_rules_package'][0]['presell']['actual_deduct'] . "\n, ";
                    } else {
                        // 非商品预售
                        $extraPriceStr .= $extraPriceIndex . ':' . $extraPriceItem . "\n, ";
                    }
                }
                $row['extra_price_package_text'] = $extraPriceStr;
                //订单来源
                $row['create_from'] = $row['create_from_text'];
                // 商品成本价
                $row['cost_price'] = $row['cost_price'] != '0.00' ? $row['cost_price'] : '';
            },
//            'get_sql' => true
        ]);

        $memberId = array_column($list, 'member_id');
        $MemberLevel = MemberModel::find()
            ->alias('member')
            ->leftJoin(MemberLevelModel::tableName() . 'member_level', 'member_level.id=member.level_id')
            ->leftJoin(MemberGroupMapModel::tableName() . ' member_group_map', 'member_group_map.member_id = member.id')
            ->leftJoin(MemberGroupModel::tableName() . ' member_group', 'member_group.id = member_group_map.group_id')
            ->where([
                'member.id' => $memberId,
            ])
            ->select('member.id,member_level.level_name,member_group.group_name member_group_name')
            ->indexBy('id')->asArray()->all();

        // 预约 每条 order_goods 记录 可能对应多条预约 要复制成多条
        $newList = [];

        $goodsId = array_column($list, 'goods_id');
        $orderId = array_column($list, 'order_id');
        $flagArray = [
            'goods_form',
            'order_form',
        ];
        // 查询表单数据
        foreach ($flagArray as $flagItem) {
            $where = [
                'order_id' => $orderId,
                'member_id' => $memberId,
                'goods_id' => 0
            ];
            if ($flagItem == 'goods_form') {
                $where['goods_id'] = $goodsId;
            }
            $$flagItem = FormLogModel::find()
                ->where($where)
                ->select([
                    'content',
                    'goods_id',
                    'order_id',
                ])
                ->indexBy('order_id')
                ->asArray()
                ->all();
        }


        foreach ($list as $listIndex => &$listItem) {
            //会员等级名称
            $listItem['member_level_name'] = $MemberLevel[$listItem['member_id']]['level_name'];
            $listItem['member_group_name'] = $MemberLevel[$listItem['member_id']]['member_group_name'] ?? '';

            foreach ($flagArray as $flagValue) {
                // 拼接表单
                self::splicingParams($listItem, $$flagValue[$listItem['order_id']]['content'], $flagValue);
            }
            $newList[] = $listItem;

        }

        // 重新赋值
        $list = $newList;
        unset($newList);

        $diffFields = [
            'goods_title',
            'express_name',
            'express_sn',
            'short_name',
            'goods_sku',
            'bar_code',
            'price_unit',
            'total',
            'option_title',
            'price_discount',
            'add_credit',
            'price_change',
            'refund_status_text',
            'express_name',
            'express_sn',
            'cost_price',
        ];

        $list = ExcelHelper::exportFilter($list, $diffFields, 'order_id');

        if (empty($field)) {
            $field = self::$exportField;
        }

        ExcelHelper::export($list, $field, '订单数据导出');
        die;
    }

    /**
     * 拼接导出页面的表单参数
     * @param $listItem
     * @param $form
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    /**
     * 拼接导出页面的表单参数
     * @param $listItem
     * @param $form
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function splicingParams(&$listItem, $form, $flag)
    {
        $formItem = is_null($form) ? '' : Json::decode($form);
        if (is_array($formItem)) {
            foreach ($formItem as $formValue) {
                $itemValue = '';
                // 图片不展示
                if ($formValue['type'] == 'pictures') {
                    $itemValue = '略';
                }
                // 日期范围 时间范围
                if ($formValue['type'] == 'daterange' || $formValue['type'] == 'timerange' || $formValue['type'] == 'datetimerange') {
                    $itemValue = $formValue['params']['start']['value'] . ' - ' . $formValue['params']['end']['value'];
                } else if ($formValue['type'] == 'city') {
                    $itemValue = $formValue['params']['province'] . $formValue['params']['city'] . $formValue['params']['area'];
                } else {
                    // 处理多选项的答案展示
                    if (is_array($formValue['params']['value']) && $formValue['type'] != 'pictures') {
                        foreach ($formValue['params']['value'] as $formValueItem) {
                            $itemValue .= $formValueItem . ',';
                        }
                        $itemValue = rtrim($itemValue, ',');
                    } elseif (!is_array($formValue['params']['value']) && $formValue['type'] != 'pictures') {
                        $itemValue .= $formValue['params']['value'];
                    }

                }

                $listItem[$flag] .= '【' . $formValue['params']['title'] . '】 ' . $itemValue . ';';
            }
        }
    }

}