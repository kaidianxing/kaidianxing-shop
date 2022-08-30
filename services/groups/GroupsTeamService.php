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

namespace shopstar\services\groups;

use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\groups\GroupsTeamStatusConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\exceptions\groups\GroupsOrderException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\groups\AutoCloseTeamJob;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\consumeReward\ConsumeRewardLogModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\groups\GroupsCrewModel;
use shopstar\models\groups\GroupsTeamModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shoppingReward\ShoppingRewardLogModel;
use shopstar\services\goods\GoodsService;
use shopstar\services\order\OrderService;
use yii\helpers\Json;

/**
 * 拼团 团服务类
 * Class GroupsTeamService
 * @package shopstar\services\groups
 * @author likexin
 */
class GroupsTeamService
{

    /**
     * 开团
     * @param int $activityId
     * @param int $memberId
     * @param array $options
     * @return int|array
     * @author likexin
     */
    public static function startTeam(int $activityId, int $memberId, array $options = [])
    {
        // 合并主要参数
        $options = array_merge([
            'success_num' => 0,
            'is_ladder' => 0,
            'ladder' => 0,
            'limit_time' => 0,
        ], $options);

        $model = new GroupsTeamModel();
        $model->setAttributes([
            'team_no' => OrderNoHelper::getGroupsNo(),//团编号
            'activity_id' => $activityId,
            'leader_id' => $memberId, //团长id
            'created_at' => DateTimeHelper::now(), //当前时间
            'is_ladder' => $options['is_ladder'], //是否是阶梯
            'ladder' => $options['ladder'], //几级阶梯
            'limit_time' => $options['limit_time'], //限时
            'count' => 1, // 当前参与人数  开团固定1
            'success_num' => $options['success_num'] //总人数
        ]);

        //保存
        if (!$model->save()) {
            return error($model->getErrorMessage());
        }

        return $model->id;
    }

    /**
     * 完成后
     * @param int $teamId
     * @return false
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function finishAfter(int $teamId): bool
    {
        // 获取参与的有效的订单
        $orderId = GroupsCrewModel::find()
            ->where([
                'team_id' => $teamId,
                'is_valid' => GroupsCrewModel::IS_VAlID,
            ])
            ->select([
                'order_id'
            ])
            ->column();

        // 获取参与的订单
        $order = OrderModel::find()->where([
            'id' => $orderId,
        ])->select([
            'id',
            'goods_info',
            'dispatch_type',
            'member_id',
            'order_type',
            'pay_time'
        ])->get();

        // 订单不存在
        if (empty($order)) {
            return false;
        }

        //循环订单处理虚拟商品
        foreach ((array)$order as $item) {

            $orderGoods = Json::decode($item['goods_info']);

            if ($item['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {

                // 核销的不发货,直接待收货
                OrderService::ship($item['id'], [
                    'order_goods_id' => array_column($orderGoods, 'order_goods_id'),
                    'no_express' => 1 // 不需要快递
                ]);

            } else {

                // 获取虚拟商品
                $result = GoodsService::checkOrderGoodsVirtualType($item);
                if ($result) {
                    // 虚拟商品自动发货
                    OrderService::virtualAutoSend($item['id']);
                }
            }

            // 消费奖励
            ConsumeRewardLogModel::sendReward($item['member_id'], $item['id'], 1);

            // 购物奖励
            ShoppingRewardLogModel::sendReward($item['member_id'], $item['id'], 0);
        }

        // 关闭无效拼团订单
        self::closeGroupsOrder($teamId, false, false);

        // 添加拼团成功通知
        $teamInfo = GroupsTeamModel::find()
            ->alias('team')
            ->leftJoin(GroupsCrewModel::tableName() . 'as crew', 'crew.team_id = team.id and is_leader=1')
            ->leftJoin(GoodsModel::tableName() . 'as goods', 'goods.id = crew.goods_id')
            ->select([
                'team.id',
                'team.leader_id',
                'goods.title',
                'team.success_num',
                'team.end_time',
            ])
            ->where([
                'team.id' => $teamId,
            ])
            ->first();

        $teamMember = GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(MemberModel::tableName() . 'as member', 'member.id = crew.member_id')
            ->select([
                'member.nickname',
            ])
            ->where([
                'crew.team_id' => $teamId,
            ])
            ->get();

        $leaderOrder = GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(OrderModel::tableName() . 'as order', 'order.id = crew.order_id')
            ->select('order.pay_price')
            ->where([
                'crew.member_id' => $teamInfo['leader_id'],
                'crew.team_id' => $teamId,
                'crew.is_leader' => 1,
            ])
            ->first();

        $groupsTeamMember = implode(',', array_column($teamMember, 'nickname'));
        $groupsTeamMember = mb_substr($groupsTeamMember, 0, 254);

        // 组成消息数据
        $messageData = [
            'goods_title' => $teamInfo['title'],
            'goods_info' => $teamInfo['title'],
            'groups_member_nickname_all' => $groupsTeamMember,
            'send_time' => '-',
            'groups_member_num' => $teamInfo['success_num'],
            'groups_end_time' => $teamInfo['end_time'],
            'groups_price' => $leaderOrder['pay_price'] ?? 0,
        ];

        // 发送消息
        $notice = NoticeComponent::getInstance(NoticeTypeConstant::GROUPS_SUCCESS, $messageData, 'groups');
        if (!is_error($notice)) {
            $notice->sendMessage($teamInfo['leader_id']);
        }

        return true;
    }

    /**
     * 关闭拼团订单
     * @param int $teamId
     * @param bool $activityFailure 活动失效
     * @param bool $isAll 是否关闭全部 true:关闭当前团id下所有订单  false:关闭当前团下的无效订单
     * @return array|bool
     * @throws \yii\db\Exception
     * @throws \shopstar\exceptions\order\OrderException|\yii\base\Exception
     * @author likexin
     */
    public static function closeGroupsOrder(int $teamId, bool $activityFailure = false, bool $isAll = true)
    {
        // 组合查询条件
        $where = [
            'crew.team_id' => $teamId,
        ];
        if (!$isAll) {
            $where['crew.is_valid'] = GroupsCrewModel::IS_NOT_VALID;
        }

        $orderInfo = GroupsCrewModel::find()
            ->alias('crew')
            ->leftJoin(OrderModel::tableName() . 'as order', 'order.id = crew.order_id')
            ->leftJoin(OrderGoodsModel::tableName() . 'as order_goods', 'order_goods.order_id = order.id')
            ->where($where)
            ->select([
                'crew.order_id',
                'crew.is_valid',
                'crew.member_id',
                'order.pay_price',
                'order_goods.title',
            ])
            ->get();

        $team = GroupsTeamModel::findOne($teamId);

        // 判断订单id是否为空
        if (empty($orderInfo)) {
            return error('order_id为空');
        }

        //全部退款
        foreach ((array)$orderInfo as $item) {

            // 判断是否已支付，如果已支付则退款 否则关闭订单
            if ($item['is_valid'] == GroupsCrewModel::IS_VAlID) {

                // 退款
                $result = OrderService::closeAndRefund($item['order_id']);

                //判断是否退款完成
                if (is_error($result)) {

                    // 写入错误日志
                    LogHelper::error('[GROUPS ORDER REFUND ERROR]:', $result['message']);
                    continue;
                }

                // 组合消息数据
                $messageData = [
                    'goods_title' => $item['title'] ?: '',
                    'price_unit' => $item['pay_price'],
                    'refund_price' => $item['pay_price'],
                    'groups_member_num' => $team['success_num'],
                    'groups_end_time' => $team['end_time'],
                ];

                // 发送消息
                $notice = NoticeComponent::getInstance(NoticeTypeConstant::GROUPS_DEFEATED, $messageData, 'groups');
                if (!is_error($notice)) {
                    $notice->sendMessage($item['member_id']);
                }
            } else {

                // 关闭订单
                OrderService::closeOrder($item['order_id'], OrderConstant::ORDER_CLOSE_TYPE_SYSTEM_AUTO_CLOSE, 0, [
                    'transaction' => false,
                    'cancel_reason' => $activityFailure ? '拼团活动失效,关闭订单' : '拼团过期自动关闭订单'
                ]);

            }
        }

        return true;
    }

    /**
     * 关闭活动推送队列
     * @param int $activityId
     * @return bool
     * @author likexin
     */
    public static function failureActivityPushQueue(int $activityId): bool
    {
        $teamId = GroupsTeamModel::find()->where([
            'activity_id' => $activityId,
            'success' => GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT
        ])->select(['id'])->column();

        foreach ((array)$teamId as $item) {

            //循环投递关闭订单
            QueueHelper::push(new AutoCloseTeamJob([
                'data' => [
                    'delete_activity' => true,
                    'team_id' => $item,
                ]
            ]));
        }

        return true;
    }

    /**
     * 团到期自动关闭
     * @param int $teamId
     * @param bool $activityFailure 活动失效
     * @return array|bool
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function autoCloseTeam(int $teamId, bool $activityFailure = false)
    {
        /**
         * @var $team GroupsTeamModel
         */
        $team = GroupsTeamModel::find()->where([
            'id' => $teamId,
            'success' => GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT,
        ])->select([
            'id',
            'end_time',
            'activity_id',
            'success_num',
            'is_valid'
        ])->one();

        // 如果活动删除则强制停止
        if (!$activityFailure) {

            //判断是否已过期
            if (strtotime($team['end_time']) > time()) {
                return error('团暂未过期');
            }
        }

        $success = false;

        //是否可以虚拟拼团
        if ($team->is_valid == GroupsTeamModel::IS_VAlID) {
            //增加虚拟人数
            $success = self::virtualSuccess($team->activity_id, $teamId, $team->success_num);
        }

        //团有效并且添加虚拟人数成功
        if ($team->is_valid == GroupsTeamModel::IS_VAlID && $success) {

            //虚拟拼团成功
            $team->success = GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_SUCCESS;
            $team->success_time = DateTimeHelper::now();

            //标记虚拟拼团
            $team->is_fictitious = 1;

            //处理成功后
            self::finishAfter($teamId);

            //成功直接返回
            return $team->save();

        } else {
            //修改团失败
            $team->success = GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_DEFEATED;
            $team->end_time = DateTimeHelper::now();
        }

        //保存团状态
        $team->save();

        //拼团退款
        return self::closeGroupsOrder($teamId, $activityFailure, $team->success != GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_SUCCESS);
    }

    /**
     * 虚拟成团
     * @param int $activityId
     * @param int $teamId
     * @param int $teamSuccessNum
     * @return bool
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function virtualSuccess(int $activityId, int $teamId, int $teamSuccessNum): bool
    {
        $activity = ShopMarketingModel::find()
            ->where([
                'id' => $activityId,
            ])
            ->select([
                'rules'
            ])
            ->first();

        $rules = Json::decode($activity['rules']);

        // 判断是否开启虚拟成团
        if ($rules['virtual_success'] == 0) {
            return false;
        }

        // 获取参团人数
        $crewCount = GroupsCrewModel::getCrewNumIncludeLeader($teamId);

        // 判断人数是否足够
        if ($teamSuccessNum > ($rules['virtual_success_num'] + $crewCount)) {

            //人数不足
            return false;
        }

        // 获取随机会员
        $randMember = MemberModel::getRandMember($teamSuccessNum - $crewCount);
        if (empty($randMember)) {
            return false;
        }

        $insertData = [];

        $date = DateTimeHelper::now();
        foreach ($randMember as $item) {
            $insertData[] = [
                'team_id' => $teamId,
                'member_id' => $item['id'],
                'created_at' => $date,
                'is_valid' => GroupsCrewModel::IS_VAlID
            ];
        }

        //追加会员
        GroupsCrewModel::batchInsert(array_keys(current($insertData ?: [])), $insertData);

        // TEAM表增加count
        return GroupsTeamModel::updateAllCounters(
            [
                'count' => count($insertData),
            ],
            [
                'id' => $teamId,
            ],
        );
    }

    /**
     * 根据获取团信息
     * @param int|array $orderId
     * @return array
     * @author likexin
     */
    public static function getGroupsInfo($orderId): array
    {
        if (empty($orderId)) {
            return [];
        }

        //获取团id
        return GroupsCrewModel::find()->where([
            'order_id' => $orderId,
            'is_valid' => GroupsCrewModel::IS_VAlID,//获取生效的拼团信息
        ])
            ->with([
                'team' => function ($query) {
                    $query->select([
                        'id',
                        'is_ladder',
                        'success'
                    ]);
                },
            ])
            ->indexBy('order_id')
            ->select([
                'team_id',
                'order_id'
            ])->get();
    }

    /**
     * 提交订单后拼团处理
     * @param OrderCreatorKernel $orderCreatorKernel
     * @return bool
     * @throws GroupsOrderException
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function afterCreator(OrderCreatorKernel $orderCreatorKernel): bool
    {
        if (!GroupsActivity::$groups) {
            return false;
        }
        //获取是否开团
        $isJoin = GroupsActivity::$isJoin;

        $teamId = GroupsActivity::$teamId;

        $orderId = $orderCreatorKernel->orderData['id'];

        $goodId = $orderCreatorKernel->goodsIds[0];


        // 判断开团还是参团
        return self::afterCreatorStartTeam($orderCreatorKernel->memberId, $teamId, $orderId, $goodId, $isJoin);
    }

    /**
     * 订单创建后绑定团和订单的关系
     * @param int $memberId
     * @param int $teamId
     * @param int $orderId
     * @param int $goodsId
     * @param bool $isJoin
     * @return bool
     * @throws GroupsOrderException
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function afterCreatorStartTeam(int $memberId, int $teamId, int $orderId, int $goodsId, bool $isJoin = false): bool
    {
        //添加参团人
        $isJoin ? $isLeader = 0 : $isLeader = 1;

        $result = GroupsCrewService::saveCrewInfo($orderId, $teamId, $memberId, $goodsId, $isLeader);
        if (is_error($result)) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_ORDER_CREATE_CREW_ERROR);
        }

        return true;
    }

    /**
     * 获取五个未成团信息
     * @param int $activityId
     * @param int $goodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author likexin
     */
    public static function getGroupsSimpleTeam(int $activityId, int $goodsId): array
    {
        return GroupsTeamModel::find()
            ->alias('team')
            ->select([
                'team.id',
                'team.count',
                'team.success_num',
                'team.end_time',
                'team.ladder',
                'member.id as member_id',
                'member.nickname',
                'member.avatar'
            ])
            ->leftJoin(MemberModel::tableName() . 'member', 'member.id = team.leader_id')
            ->leftJoin(GroupsCrewModel::tableName() . 'crew', 'crew.team_id = team.id')
            ->where([
                'team.activity_id' => $activityId,
                'team.success' => 0,
                'team.is_valid' => 1,
                'crew.goods_id' => $goodsId,
            ])
            ->orderBy([
                'team.created_at' => SORT_DESC
            ])
            ->limit(5)
            ->get();
    }

    /**
     * 团生效
     * @param int $teamId
     * @param int $memberId
     * @param int $orderId
     * @param int $delay
     * @return bool|array
     * @author likexin
     */
    public static function teamEffect(int $teamId, int $memberId = 0, int $orderId = 0, int $delay = 0)
    {
        // 获取参与记录
        /**
         * @var GroupsCrewModel $crewRecord
         */
        $crewRecord = GroupsCrewModel::find()->where([
            'team_id' => $teamId,
            'member_id' => $memberId,
            'order_id' => $orderId,
        ])->one();

        // 判断记录是否存在
        if (empty($crewRecord)) {
            return error('拼团参与记录不存在');
        }

        //如果是团长
        if ($crewRecord->is_leader) {

            // 结束时间
            $endTime = DateTimeHelper::after(time(), $delay * 60);

            // 团生效
            GroupsTeamModel::updateAll([
                'is_valid' => 1,
                'end_time' => $endTime,
            ], [
                'id' => $teamId,
                'leader_id' => $memberId,
            ]);

            // 开团投递队列
            QueueHelper::push(new AutoCloseTeamJob([
                'data' => [
                    'team_id' => $teamId,
                ]
            ]), strtotime($endTime) - time());
        }

        // 设置为生效
        $crewRecord->is_valid = 1;

        // 如果不是团长，team表参与+1
        if (!$crewRecord->is_leader) {
            GroupsTeamModel::updateAllCounters([
                'count' => 1,
            ], [
                'id' => $teamId,
            ]);
        }

        //使..参与人生效
        return $crewRecord->save();
    }

    /**
     * 拼团支付完成后
     * @param OrderModel $order
     * @param int $memberId
     * @return array|bool
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\db\Exception
     * @throws \yii\base\Exception
     * @author likexin
     */
    public static function paySuccess(OrderModel $order, int $memberId)
    {
        /**
         * @var GroupsCrewModel $crewRecord
         */
        $crewRecord = GroupsCrewModel::find()
            ->where([
                'member_id' => $memberId,
                'order_id' => $order->id,
            ])
            ->one();
        if (empty($crewRecord)) {
            return error('拼团参与记录不存在');
        }

        // 获取团
        /**
         * @var GroupsTeamModel $team
         */
        $team = GroupsTeamModel::find()->where([
            'id' => $crewRecord->team_id,
        ])->one();

        // 判断团是否找到
        if (empty($team)) {
            return error('团未找到');
        }

        // 获取参团人数
        $crewCount = GroupsCrewModel::getCrewNumIncludeLeader($team->id);

        // 判断团是否有效
        if ($team->success != GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT) {

            //判断参团人数过多退款
            in_array($order['pay_type'], [OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT, OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY]) && OrderService::closeAndRefund($order);

            return error('团状态不正确');
        }

        // 启动redis
        $redis = \Yii::$app->redisPermanent;

        //redis key
        $key = 'activity_groups_' . $team->activity_id . '_' . $team->id;

        // 获取redis参团人数
        $redisJoinNum = $redis->get($key);

        // 判断参团人数是否满足
        if ($redisJoinNum >= $team->success_num || $crewCount >= $team->success_num) {

            //判断参团人数过多退款
            in_array($order['pay_type'], [OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT, OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY]) && OrderService::closeAndRefund($order);

            return error('参团人数过多');
        }

        // 获取订单商品
        $orderGoods = OrderGoodsModel::findOne([
            'order_id' => $order->id,
        ]);

        // 检查库存并操作库存
        $result = GroupsGoodsService::checkGroupsGoodsStock($team->activity_id, $orderGoods->goods_id, $orderGoods->option_id, $orderGoods->total);
        if (is_error($result)) {

            // 活动商品库存不足退款
            in_array($order['pay_type'], [OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT, OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY]) && OrderService::closeAndRefund($order);

            return error($result['message']);
        }

        // 开团\参团
        $result = self::teamEffect($team->id, $memberId, $order->id, $team->limit_time);
        if (is_error($result)) {

            // 开团，参团失败退款
            in_array($order['pay_type'], [OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_WECHAT, OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ALIPAY]) && OrderService::closeAndRefund($order);

            return error($result['message']);
        }

        // 如果是参团 拼装参团消息信息
        if (!$crewRecord['is_leader']) {
            $joinNickname = MemberModel::find()
                ->where([
                    'id' => $memberId,
                ])
                ->select(['nickname'])
                ->first();

            $orderInfo = OrderModel::find()
                ->alias('order')
                ->leftJoin(OrderGoodsModel::tableName() . ' as order_goods', 'order_goods.order_id = order.id')
                ->select([
                    'order.pay_price',
                    'order_goods.title',
                ])
                ->where([
                    'order.id' => $order->id,
                ])
                ->first();

            $messageData = [
                'team_no' => $team->team_no,
                'groups_member_nickname' => $joinNickname['nickname'],
                'goods_title' => $orderInfo['title'] ?: '',
                'groups_price' => $orderInfo['pay_price'],
                'groups_member_num' => $team['success_num'],
                'groups_end_time' => $team['end_time'],
            ];

            //发送参团消息通知
            $result = NoticeComponent::getInstance(NoticeTypeConstant::GROUPS_JOIN, $messageData, 'groups');
            if (!is_error($result)) {
                $result->sendMessage($team->leader_id);
            }
        }

        // 检查拼团是否成功
        self::checkTempCrewNum($team);
        if (is_error($result)) {
            return error($result['message']);
        }

        // redis 增加参团个数
        $redis->incr($key);

        return true;
    }


    /**
     * 检查团是否组成
     * @param $team
     * @return bool|array
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function checkTempCrewNum($team)
    {
        //判断类型是否相等
        if (!$team instanceof GroupsTeamModel) {

            // 获取团
            $team = GroupsTeamModel::find()->where([
                'id' => $team,
            ])->one();

            // 判断团是否找到
            if (empty($team)) {
                return error('团未找到');
            }
        }

        // 获取参团人数
        $crewCount = GroupsCrewModel::getCrewNumIncludeLeader($team->id);
        if (empty($crewCount)) {
            return error('参团人数异常');
        }

        //启动redis
        $redis = \Yii::$app->redisPermanent;

        //redis key
        $key = 'activity_groups_' . $team->activity_id . '_' . $team->id;

        //获取redis参团人数
        $redisJoinNum = $redis->get($key);

        //判断拼团人数是否相等
        if ($redisJoinNum != $team->success_num && $crewCount != $team->success_num) {
            return error('人数不等');
        }

        //更改成功状态
        $team->success = GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_SUCCESS;
        $team->success_time = DateTimeHelper::now();
        $team->save();

        // 完成后条用
        self::finishAfter($team->id);

        // 保存并返回
        return true;
    }

    /**
     * 关闭所有活动
     * @param string $type
     * @return bool
     * @author likexin
     */
    public static function failureAllActivityPushQueue(string $type = 'groups'): bool
    {
        $orderIds = ShopMarketingModel::find()
            ->where([
                'type' => $type,
                'status' => [0, 1],
            ])
            ->select(['id'])
            ->column();

        if ($orderIds) {
            $teamId = GroupsTeamModel::find()->where([
                'activity_id' => $orderIds,
                'success' => GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT
            ])->select(['id'])->column();

            foreach ((array)$teamId as $item) {
                //循环投递关闭订单
                QueueHelper::push(new AutoCloseTeamJob([
                    'data' => [
                        'delete_activity' => true,
                        'team_id' => $item,
                    ]
                ]));
            }
        }

        return true;
    }

}
