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

namespace shopstar\mobile\order;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\helpers\ValueHelper;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;
use yii\web\Response;

/**
 * @author 青岛开店星信息技术有限公司
 */
class ListController extends BaseMobileApiController
{
    /**
     * 全部订单
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAll(): Response
    {
        return $this->getList();
    }

    /**
     * 获取最后一个要关闭的订单
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetFirstCloseOrder()
    {
        $order = [];
        if (!empty($this->memberId)) {
            $order = OrderModel::find()
                ->where([
                    'member_id' => $this->memberId,
                    'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY
                ])
                ->andWhere([
                    '!=', 'auto_close_time', '0000-00-00 00:00:00'
                ])->orderBy(['auto_close_time' => SORT_ASC])->select([
                    'id',
                    'order_no',
                    'pay_price',
                    'auto_close_time',
                    'goods_info'
                ])->asArray()->one();
        }

        if (!empty($order['goods_info'])) {
            $order['goods_info'] = Json::decode($order['goods_info']);
        }

        return $this->result(['order' => $order]);
    }

    /**
     * 获取列表
     * @param string $status
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    private function getList(string $status = 'ALL'): Response
    {
        $where = [
            ['member_id' => $this->memberId],
        ];

        // 回收站
        if ($status != 'DELETE') {
            $where[] = ['o.user_delete' => 0];
        } else {
            $where[] = ['o.user_delete' => 1];
        }

        switch ($status) {
            case 'WAIT_PAY':
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY];
                break;
            case 'WAIT_SEND':
                $where[] = ['o.status' => [OrderStatusConstant::ORDER_STATUS_WAIT_SEND, OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND]];
                break;
            case 'WAIT_PICK':
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK];
                break;
            case 'FINISH':
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_SUCCESS];
                break;
            case 'CLOSE':
                $where[] = ['o.status' => OrderStatusConstant::ORDER_STATUS_CLOSE];
                break;
        }

        $select = 'o.id, o.buyer_name, o.pay_type,  o.order_no, o.pay_price, o.order_type, o.activity_type, o.extra_discount_rules_package, o.status,o.created_at,o.auto_close_time,o.auto_finish_time, o.is_refund, o.refund_type, o.goods_info, o.dispatch_type';

        $params = [
            'andWhere' => $where,
            'alias' => 'o',
            'select' => $select,
            'with' => [
                'orderGoods',
                'refunds' => function ($query) {
                    $query->where(['is_history' => 0]);
                }
            ],
            'indexBy' => 'id',
            'orderBy' => ['created_at' => SORT_DESC]
        ];

        // 积分商品id
        $creditShopOrderId = [];

        $list = OrderModel::getColl($params, [
            'callable' => function ($row) use (&$creditShopOrderId) {
                if ($row['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                    $creditShopOrderId[] = $row['id'];
                }
            }
        ]);

        $subShop = [];

        // 核销订单的商户id
        foreach ($list['list'] as $listKey => $listItem) {
            //商品快照
            if (!empty($listItem['goods_info']) && is_string($listItem['goods_info'])) {
                $listItem['goods_info'] = Json::decode($listItem['goods_info']);
            }
            //补充商品信息

            $goodsInfoMap = array_column($listItem['goods_info'] ?: [], NULL, 'goods_id');

            //商品信息
            $orderGoods = [];
            if (!empty($listItem['orderGoods'])) {
                array_walk($listItem['orderGoods'], function (&$g) use ($goodsInfoMap, $list, &$orderGoods) {
                    $g['type'] = $goodsInfoMap[$g['goods_id']]['type'];

                    if (!empty($g['ext_field']) && ValueHelper::isJson($g['ext_field'])) {
                        $g['ext_field'] = Json::decode($g['ext_field']);
                    }
                    $orderGoods[] = $g;
                });
            }

            $list['list'][$listKey]['orderGoods'] = $orderGoods;
            unset($list['list'][$listKey]['goods_info']);

            //处理订单包裹id
            $list['list'][$listKey]['comment_status'] = 0;

            $packageId = [];
            $packageId[] = array_column($listItem['orderGoods'], 'package_id');
            $packageId = array_unique(array_filter($packageId));
            if (count($packageId) > 1) {
                $list['list'][$listKey]['package_id'] = current($packageId);
            }

            //处理订单评论状态
            $commentStatus = array_column($listItem['orderGoods'], 'comment_status');
            if (empty(array_intersect($commentStatus, [0]))) {
                $list['list'][$listKey]['comment_status'] = 1;
            }
            // 判断维权状态
            if ($listItem['is_refund'] == 1) {
                $list['list'][$listKey]['refund_finish'] = 0;
                // 如果是整单维权
                if ($listItem['refund_type'] == 1) {
                    // 如果已完成
                    if ($listItem['refunds'][0]['status'] > 9) {
                        $list['list'][$listKey]['refund_finish'] = 1;
                    }
                } else {

                    // 单品维权 判断所有商品维权完成才算完成
                    // 比较维权信息和订单商品数量
                    $refundCount = count($listItem['refunds']);
                    $orderGoodsCount = count($listItem['orderGoods']);
                    // 如果数量相等 再进行订单商品维权状态判断
                    if ($refundCount == $orderGoodsCount) {
                        $refundSuccess = true;
                        foreach ($listItem['refunds'] as $refund) {
                            if ($refund['status'] < 10) {
                                // 当有一个不满足 跳出
                                $refundSuccess = false;
                                break;
                            }
                        }
                        if ($refundSuccess) {
                            $list['list'][$listKey]['refund_finish'] = 1;
                        }
                    }
                }

                // 使用完销毁
                unset($list['list'][$listKey]['refunds']);
            }

        }

        $list['list'] = array_values($list['list']);

        //评价设置
        $setting = ShopSettings::get('sysset.trade');

        $list['comment_setting'] = [
            'order_comment' => $setting['order_comment'],
            'show_comment' => $setting['show_comment'],
            'comment_audit' => $setting['comment_audit']
        ];
        return $this->success($list);
    }

    /**
     * 待付款
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay(): Response
    {
        return $this->getList('WAIT_PAY');
    }

    /**
     * 待发货
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSend(): Response
    {
        return $this->getList('WAIT_SEND');
    }

    /**
     * 待收货
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPick(): Response
    {
        return $this->getList('WAIT_PICK');
    }

    /**
     * 已完成
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionFinish(): Response
    {
        return $this->getList('FINISH');
    }

    /**
     * 回收站
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): Response
    {
        return $this->getList('DELETE');
    }

    /**
     * 回收站
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionClose(): Response
    {
        return $this->getList('CLOSE');
    }

    /**
     * 获取每个订单状态下的数量
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetTotal()
    {
        $data = [
            'wait_pay' => 0, // 待付款
            'wait_send' => 0, // 待发货
            'wait_receive' => 0, // 待收货
            'refund' => 0, // 维权
        ];

        // 待付款
        $data['wait_pay'] = OrderModel::find()
            ->where(['member_id' => $this->memberId, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PAY])
            ->count();
        // 待发货
        $data['wait_send'] = OrderModel::find()
            ->where(['member_id' => $this->memberId])
            ->andWhere([
                'or',
                ['status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
                ['status' => OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND]
            ])->count();
        // 待收货
        $data['wait_receive'] = OrderModel::find()
            ->where(['member_id' => $this->memberId, 'status' => OrderStatusConstant::ORDER_STATUS_WAIT_PICK])
            ->count();
        // 维权
        $data['refund'] = OrderRefundModel::find()
            ->where(['member_id' => $this->memberId, 'is_history' => 0])
            ->andWhere(['between', 'status', RefundConstant::REFUND_STATUS_APPLY, RefundConstant::REFUND_STATUS_WAIT])
            ->count();

        return $this->result($data);
    }

}
