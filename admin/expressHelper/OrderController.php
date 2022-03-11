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



namespace shopstar\admin\expressHelper;


use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\RefundConstant;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\expressHelper\ExpressHelperRequestRecordModel;
use shopstar\models\expressHelper\ExpressHelperSendBillLogModel;
use shopstar\models\expressHelper\ExpressHelperSuccessRecordModel;
use shopstar\bases\KdxAdminApiController;
use shopstar\models\order\OrderPackageModel;

/**
 * 订单
 * Class OrderController
 * @author 青岛开店星信息技术有限公司
 * @package apps\expressHelper\manage
 */
class OrderController extends KdxAdminApiController
{
    /**
     * 订单列表
     * @return array|int[]|\yii\web\Response
     */
    public function actionList()
    {
        //订单商品查询
        $orderGoodsList = OrderGoodsModel::getColl([
            'alias' => 'order_goods',
            'leftJoins' => [
                [OrderPackageModel::tableName() . ' order_package', 'order_package.order_id = order_goods.order_id']
            ],
            'select' => [
                'order_goods.id',
                'order_goods.order_id',
                'order_goods.goods_id',
                'order_goods.title',
                'order_goods.status',
                'order_goods.option_title',
                'order_goods.option_id',
                'order_goods.thumb',
                'order_goods.price',
                'order_goods.price_unit',
                'order_goods.total',
                'order_goods.is_print',
            ],
            'where' => [
                'and',
                [
                    'order_goods.refund_status' => [RefundConstant::REFUND_STATUS_CANCEL, RefundConstant::REFUND_STATUS_REJECT, RefundConstant::REFUND_STATUS_APPLY],
                ],
                [
                    'or',
                    [
                        'order_package.order_id' => null
                    ],
                    [
                        'and',
//                        'find_in_set(order_package.order_goods_ids,order_goods.id)',
                        'find_in_set(order_goods.id,order_package.order_goods_ids)',
                        ['!=', 'order_package.express_com', 'qita']   //强制过滤其他快递
                    ]
                ]
            ],
            'searchs' => [
                ['is_print', 'int'],
                ['title', 'like'],
            ]
        ], [
            'disableSort' => false,
            'pager' => false,
            'onlyList' => true,
            'callable' => function (&$row) use (&$orderList) {
                OrderGoodsModel::decode($row);
                $row['print_num'] = 0;
            }
        ]);


        $orderList = OrderModel::getColl([
            'where' => [
                'and',
                ['id' => array_unique(array_column($orderGoodsList, 'order_id'))],
                ['order_type' => OrderTypeConstant::ORDER_TYPE_ORDINARY],
                [
                    'or',
                    ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
                    [
                        'pay_type' => 3,
                        'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK
                    ]
                ],
            ],
            'searchs' => [
                ['order_no', 'like'],
                ['created_at', 'between'],
                [['buyer_name', 'buyer_mobile'], 'like', 'buyer_name'],
                ['status', 'int'],
                ['activity_type', 'int'],
                ['is_bill_print', 'int'],
            ],
            'indexBy' => 'id',
            'select' => [
                'id',
                'order_no',
                'order_type',
                'status',
                'pay_price',
                'member_nickname',
                'buyer_name',
                'buyer_mobile',
                'created_at',
                'is_bill_print',
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ]);

        // 查询发货单数量
        $billPrintNumList = ExpressHelperSendBillLogModel::getCount(array_column($orderList['list'], 'id'));

        $orderGoodsId = [];
        //重组结构
        if (!empty($orderGoodsList) && !empty($orderList['list'])) {
            foreach ($orderGoodsList as $orderGoodsIndex => $orderGoodsItem) {
                if (isset($orderList['list'][$orderGoodsItem['order_id']])) {
                    $orderGoodsId[] = $orderGoodsItem['id'];
                    $orderList['list'][$orderGoodsItem['order_id']]['order_goods'][] = $orderGoodsItem;
                }
            }

            //获取打印次数
            $printNum = ExpressHelperSuccessRecordModel::getPrintNum($orderGoodsId);

            //获取打印关系
            $printRelation = ExpressHelperRequestRecordModel::getPrintRelation(array_keys($orderList['list']));

            //循环赋值打印次数
            foreach ((array)$orderList['list'] as $orderListIndex => $orderListItem) {
                $orderList['list'][$orderListIndex]['bill_print_num'] = (int)$billPrintNumList[$orderListIndex]['bill_print_num'] ?? 0;
                foreach ((array)$orderListItem['order_goods'] as $orderGoodsIndex => $orderGoodsItem) {

                    //判断是否有打印次数
                    if (!empty($printNum[$orderGoodsItem['id']])) {
                        $orderList['list'][$orderListIndex]['order_goods'][$orderGoodsIndex]['print_num'] = $printNum[$orderGoodsItem['id']]['print_num'];
                    }

                    //赋值打印关系id
                    foreach ((array)$printRelation as $printRelationIndex => $printRelationItem) {
                        //是否存在数组，如果存在则把整个关系全部赋值在商品上
                        if (in_array($orderGoodsItem['id'], $printRelationItem['order_goods_id'])) {
                            $orderList['list'][$orderListIndex]['order_goods'][$orderGoodsIndex]['print_relation'] = $printRelationItem['order_goods_id'];
                        }
                    }

                }
            }

        }


        $orderList['list'] = array_values($orderList['list']);

        return $this->result($orderList);
    }
}
