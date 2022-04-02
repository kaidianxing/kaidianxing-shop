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

namespace shopstar\admin\commission;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\exceptions\commission\CommissionOrderException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\services\commission\CommissionOrderService;
use yii\helpers\Json;

/**
 * 分销订单
 * Class OrderController
 * @package shopstar\admin\commission
 */
class OrderController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'change-commission',
        ],
        'allowHeaderActions' => [
            'index'
        ]
    ];

    /**
     * 分销订单列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $get = RequestHelper::get();
        $export = $get['export'] ?? 0;

        $where = [];
        $select = [
            'order.created_at', // 下单时间
            'order.order_no', // 订单号
            'order.status', // 订单状态
            'order.pay_type', // 支付方式
            'order.order_type', // 订单类型
            'order.activity_type', // 订单活动类型
            'order.dispatch_price', // 运费
            'order.member_nickname', // 会员昵称
            'order.member_mobile', // 会员手机号
            'order.buyer_mobile',
            'order.buyer_name',
            'order.pay_price', // 支付金额
            'order.goods_info', // 商品信息
            'order.create_from', // 订单来源
            'order.extra_discount_rules_package', // 活动包
            'order.member_id',
            'order.pay_time', // 支付时间
            'order.finish_time', // 完成时间
            'order.is_refund', // 是否维权
            'order.order_type', // 订单类型
            'order_refund.status as refund_status', // 维权状态
            'commission_order.order_id', // 订单id
            'commission_order.commission_level', // 分销明细
            'commission_order.account_time', // 到账时间
        ];
        // 订单状态 已付款
        $where[] = ['commission_order.is_count_refund' => 1];
        // 下单时间
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $where[] = ['between', 'order.created_at', $get['start_time'], $get['end_time']];
        }
        // 分销状态
        if ($get['commission_status'] != '') {
            // 未入账
            if ($get['commission_status'] == 0) {
                $where[] = [
                    'or',
                    ['>', 'commission_order.account_time', DateTimeHelper::now()],
                    ['commission_order.account_time' => 0]
                ];
            } else { // 已入账
                $where[] = [
                    'and',
                    ['<=', 'commission_order.account_time', DateTimeHelper::now()],
                    ['<>', 'commission_order.account_time', 0]
                ];
            }
        }
        // 商品名称
        if (!empty($get['goods_title'])) {
            $orderIds = OrderGoodsModel::find()->select('order_id')
                ->where(['like', 'title', $get['goods_title']])
                ->all();
            $orderIds = array_column($orderIds, 'order_id');
            $where[] = ['in', 'commission_order.order_id', $orderIds];
        }

        if ($get['type'] && $get['type'] != 'all') {
            $where[] = ['order.order_type' => $get['type']];
        }

        $leftJoins = [
            [OrderModel::tableName() . ' as order', 'order.id = commission_order.order_id'],
            [OrderRefundModel::tableName() . ' as order_refund', 'order_refund.order_id = commission_order.order_id']
        ];

        if (!empty($get['member_id'])) {
            if ($get['member_type'] == 0) {
                $where[] = ['order.member_id' => $get['member_id']];
            } else {
                $leftJoins[] = [CommissionOrderDataModel::tableName() . 'order_data', 'order_data.order_id = order.id'];
                $where[] = ['order_data.agent_id' => $get['member_id']];
            }
        }


        $searchs = [
            [['order.member_nickname', 'order.member_realname', 'order.member_mobile'], 'like', 'member_keyword'],
            ['order.status', 'int', 'order_status'],
            ['order.order_no', 'like', 'order_no']
        ];
        $params = [
            'searchs' => $searchs,
            'andWhere' => $where,
            'alias' => 'commission_order',
            'leftJoins' => $leftJoins,
            'orderBy' => ['order.created_at' => SORT_DESC],
            'groupBy' => 'commission_order.order_id',
            'select' => $select,
        ];

        // 导出
        if ($export) {
            CommissionOrderService::export($where, $searchs);
        }

        $list = CommissionOrderModel::getColl($params, [
            'callable' => function (&$row) {
                $row['goods_info'] = Json::decode($row['goods_info']);

                $row['commission_level'] = Json::decode($row['commission_level']);
                // 分销等级佣金信息
                $row['anent_info'] = CommissionOrderDataModel::getAgentInfo($row['order_id']);
                // 到账状态
                // 订单结束天数大于设置结算天数
                if ($row['account_time'] <= DateTimeHelper::now() && $row['account_time'] != 0) {
                    $row['commission_status'] = 1;
                    $row['commission_time'] = $row['account_time'];
                } else {
                    $row['commission_status'] = 0;
                }
            },
        ]);

        return $this->result($list);
    }

    /**
     * 获取佣金信息
     * @throws CommissionOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCommission()
    {
        $orderId = RequestHelper::get('order_id');
        $memberId = RequestHelper::get('member_id');
        if (empty($orderId) || empty($memberId)) {
            throw new CommissionOrderException(CommissionOrderException::GET_COMMISSION_PARAMS_ERROR);
        }
        $commissionInfo = [];
        $commissionInfo['is_can_edit'] = CommissionOrderModel::isCanEditCommission($memberId, $orderId);
        // 取所有订单商品
        $orderGoods = OrderGoodsModel::find()
            ->where(['is_count' => 1])
            ->get();
        // 获取下单用户信息
        $commissionInfo['member_info'] = MemberModel::find()->select('id, nickname, avatar, realname, mobile')->where(['id' => $memberId])->first();
        // 取该订单所有商品佣金信息
        $commissionOrderGoods = CommissionOrderGoodsModel::find()->where(['order_id' => $orderId])->get();
        // 遍历 组装
        foreach ($orderGoods as $index => $item) {
            // 商品是否分销商品
            $isCommission = false;
            // 佣金信息
            foreach ($commissionOrderGoods as $key => $value) {
                if ($value['goods_id'] == $item['goods_id'] && $value['option_id'] == $item['option_id']) {
                    $isCommission = true;
                    $commissionInfo['commission_info'][$index]['level_' . $value['level']]['commission'] = $value['commission']; // 佣金
                    $commissionInfo['commission_info'][$index]['level_' . $value['level']]['ladder_commission'] = $value['ladder_commission']; // 阶梯佣金
                    $commissionInfo['commission_info'][$index]['level_' . $value['level']]['original_commission'] = $value['original_commission']; // 原始佣金
                    $commissionInfo['commission_info'][$index]['level_' . $value['level']]['agent_id'] = $value['agent_id']; // 分销商id

                }
            }
            if (!$isCommission) {
                continue;
            }
            // 商品基本信息
            $commissionInfo['commission_info'][$index]['thumb'] = $item['thumb'];
            $commissionInfo['commission_info'][$index]['title'] = $item['title'];
            $commissionInfo['commission_info'][$index]['total'] = $item['total'];
            $commissionInfo['commission_info'][$index]['option_title'] = $item['option_title'];
            $commissionInfo['commission_info'][$index]['price'] = $item['price'];
            $commissionInfo['commission_info'][$index]['goods_id'] = $item['goods_id'];
            $commissionInfo['commission_info'][$index]['option_id'] = $item['option_id'];
        }

        if (!empty($commissionInfo['commission_info'])) {
            // 重新排序分销信息
            $commissionInfo['commission_info'] = array_values($commissionInfo['commission_info']);
        }

        return $this->result($commissionInfo);
    }

    /**
     * 修改佣金
     * @throws CommissionOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeCommission()
    {
        $post = RequestHelper::post();
        $orderId = $post['order_id'];
        $memberId = $post['member_id'];
        if (empty($orderId) || empty($memberId) || !is_array($post['commission_info'])) {
            throw new CommissionOrderException(CommissionOrderException::CHANGE_COMMISSION_PARAMS_ERROR);
        }
        $isCanEdit = CommissionOrderModel::isCanEditCommission($memberId, $orderId);
        // 不可编辑
        if (!$isCanEdit) {
            throw new CommissionOrderException(CommissionOrderException::CHANGE_COMMISSION_NOT_ALLOW_CHANGE_COMMISSION);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 日志数据
            $logPrimary = [];
            // 更新佣金信息
            foreach ($post['commission_info'] as $item) {
                // 一级
                if (isset($item['level_1']['commission'])) {
                    CommissionOrderGoodsModel::updateAll(
                        ['commission' => $item['level_1']['commission'], 'can_withdraw_commission' => $item['level_1']['commission']],
                        ['order_id' => $orderId, 'goods_id' => $item['goods_id'], 'option_id' => $item['option_id'], 'level' => 1]
                    );
                    $logPrimary[] = [
                        '订单id' => $orderId,
                        '商品id' => $item['goods_id'],
                        '规格id' => $item['option_id'],
                        '分销层级' => '一级',
                        '佣金' => $item['level_1']['commission'],
                    ];
                }
                // 二级
                if (isset($item['level_2']['commission'])) {
                    CommissionOrderGoodsModel::updateAll(
                        ['commission' => $item['level_2']['commission'], 'can_withdraw_commission' => $item['level_2']['commission']],
                        ['order_id' => $orderId, 'goods_id' => $item['goods_id'], 'option_id' => $item['option_id'], 'level' => 2]
                    );
                    $logPrimary[] = [
                        '订单id' => $orderId,
                        '商品id' => $item['goods_id'],
                        '规格id' => $item['option_id'],
                        '分销层级' => '二级',
                        '佣金' => $item['level_2']['commission'],
                    ];
                }
                // 三级
                if (isset($item['level_3']['commission'])) {
                    CommissionOrderGoodsModel::updateAll(
                        ['commission' => $item['level_3']['commission'], 'can_withdraw_commission' => $item['level_3']['commission']],
                        ['order_id' => $orderId, 'goods_id' => $item['goods_id'], 'option_id' => $item['option_id'], 'level' => 3]
                    );
                    $logPrimary[] = [
                        '订单id' => $orderId,
                        '商品id' => $item['goods_id'],
                        '规格id' => $item['option_id'],
                        '分销层级' => '三级',
                        '佣金' => $item['level_3']['commission'],
                    ];
                }
            }
            // 合计佣金
            $commissionTotal = 0;
            // 获取旧的每个层级的佣金
            $oldOrderCommission = CommissionOrderDataModel::find()->select('agent_id, level, commission')->where(['order_id' => $orderId, 'is_count_refund' => 1])->indexBy('level')->get();
            // 获取每个层级合计
            $orderData = CommissionOrderGoodsModel::find()->select('level, sum(commission) as commission')->where(['order_id' => $orderId, 'is_count_refund' => 1])->groupBy('level')->get();

            foreach ($orderData as $order) {
                if ($order['level'] == 1) {
                    // 更新order_data
                    CommissionOrderDataModel::updateAll(['commission' => $order['commission']], ['order_id' => $orderId, 'level' => 1]);
                    // 获取佣金差  新的减旧的
                    $commission = bcsub($order['commission'], $oldOrderCommission[1]['commission'], 2);
                    // 更新
                    CommissionAgentModel::updateAllCounters(['commission_total' => $commission], ['member_id' => $oldOrderCommission[1]['agent_id']]);
                }
                if ($order['level'] == 2) {
                    CommissionOrderDataModel::updateAll(['commission' => $order['commission']], ['order_id' => $orderId, 'level' => 2]);
                    // 获取佣金差  新的减旧的
                    $commission = bcsub($order['commission'], $oldOrderCommission[2]['commission'], 2);
                    // 更新
                    CommissionAgentModel::updateAllCounters(['commission_total' => $commission], ['member_id' => $oldOrderCommission[2]['agent_id']]);
                }
                if ($order['level'] == 3) {
                    CommissionOrderDataModel::updateAll(['commission' => $order['commission']], ['order_id' => $orderId, 'level' => 3]);
                    // 获取佣金差  新的减旧的
                    $commission = bcsub($order['commission'], $oldOrderCommission[3]['commission'], 2);
                    // 更新
                    CommissionAgentModel::updateAllCounters(['commission_total' => $commission], ['member_id' => $oldOrderCommission[3]['agent_id']]);
                }
                $commissionTotal = bcadd($commissionTotal, $order['commission']);
            }
            // 更新订单佣金
            CommissionOrderModel::updateAll(['commission' => $commissionTotal], ['order_id' => $orderId]);

            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::CHANGE_COMMISSION,
                CommissionLogConstant::getText(CommissionLogConstant::CHANGE_COMMISSION),
                $orderId,
                [
                    'log_data' => $post,
                    'log_primary' => $logPrimary,
                    'dirty_identity_code' => [
                        CommissionLogConstant::CHANGE_COMMISSION,
                    ]
                ]
            );

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new CommissionOrderException(CommissionOrderException::CHANGE_COMMISSION_FAIL, $exception->getMessage());
        }

        return $this->success();
    }

}
