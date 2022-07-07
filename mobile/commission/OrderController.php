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

namespace shopstar\mobile\commission;

use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\OrderConstant;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\services\groups\GroupsTeamService;

/**
 * 分销订单
 * Class OrderController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class OrderController extends CommissionClientApiController
{
    /**
     * 全部列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAll()
    {
        $list = $this->getList('ALL');
        return $this->result($list);
    }

    /**
     * 待付款
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWait()
    {
        $list = $this->getList('WAIT');
        return $this->result($list);
    }

    /**
     * 已付款
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay()
    {
        $list = $this->getList('PAY');
        return $this->result($list);
    }

    /**
     * 已完成
     * @author 青岛开店星信息技术有限公司
     */
    public function actionFinish()
    {
        $list = $this->getList('FINISH');
        return $this->result($list);
    }

    /**
     * 获取列表
     * @param string $status
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getList(string $status = 'ALL')
    {
        $where = [
            ['order_data.agent_id' => $this->memberId],
            ['order_data.is_count_refund' => 1],
        ];

        switch ($status) {
            case 'WAIT': // 待付款
                $where[] = ['order.status' => OrderConstant::ORDER_STATUS_WAIT_PAY];
                break;
            case 'PAY': // 已付款
                $where[] = ['>', 'order.status', OrderConstant::ORDER_STATUS_WAIT_PAY];
                $where[] = ['<', 'order.status', OrderConstant::ORDER_STATUS_SUCCESS];
                break;
            case 'FINISH': // 已完成
                $where[] = ['>=', 'order.status', OrderConstant::ORDER_STATUS_SUCCESS];
                break;
        }

        $params = [
            'select' => [
                'order.created_at',
                'order.order_no',
                'order.status',
                'order.is_refund',
                'order_data.order_id',
                'order_data.member_id',
                'member.nickname',
                'member.avatar',
                'order_data.commission',
                'order_data.level',
                'order.activity_type',
            ],
            'alias' => 'order_data',
            'andWhere' => $where,
            'with' => [
                'orderGoods' => function ($query) {
                    $query->select('id, order_id, title, option_title, thumb, goods_id, price, total');
                },
                'commissionOrderGoods' => function ($query) {
                    $query->select('order_goods_id, order_id, commission, ladder_commission, original_commission, level, is_count_refund')->where(['agent_id' => $this->memberId]);
                }
            ],
            'leftJoins' => [
                [OrderModel::tableName() . ' order', 'order.id=order_data.order_id'],
                [MemberModel::tableName() . ' member', 'member.id=order_data.member_id']
            ],
            'orderBy' => [
                'order.created_at' => SORT_DESC
            ],
        ];

        // 拼团订单ID
        $groupsOrderId = [];

        $list = CommissionOrderDataModel::getColl($params, [
            'callable' => function (&$row) use (&$groupsOrderId) {

                // 拼团
                if ($row['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS) {
                    // 获取拼团订单的id
                    $groupsOrderId[] = $row['order_id'];
                }

                // 佣金
                foreach ($row['orderGoods'] as &$item) {
                    foreach ($row['commissionOrderGoods'] as $value) {
                        $item['ladder_commission'] = '0.00';//阶梯佣金
                        $item['has_changed'] = 0; // 是否有过改佣金
                        if ($value['order_goods_id'] == $item['id'] && $value['is_count_refund'] == 1) {
                            $item['commission'] = $value['commission'];
                            $item['ladder_commission'] = $value['ladder_commission'];

                            if ($value['commission'] != $value['original_commission']) {
                                $item['has_changed'] = 1;
                            }
                        }
                    }
                }

                unset($row['commissionOrderGoods']);
                unset($item);
            }
        ]);

        // 获取拼团订单信息
        if (!empty($groupsOrderId)) {
            $groupsTeamInfo = GroupsTeamService::getGroupsInfo($groupsOrderId);
            // 循环塞入订单拼团信息
            foreach ($list['list'] as $listIndex => &$listItem) {
                if ($listItem['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS) {
                    $listItem['groups_team_info'] = $groupsTeamInfo[$listItem['order_id']]['team'] ?? [];
                }
            }
        }

        return $list;
    }

}
