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

use shopstar\constants\activity\ActivityConstant;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\groups\GroupsTeamStatusConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\groups\GroupsOrderException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\groups\GroupsCrewModel;
use shopstar\models\groups\GroupsGoodsModel;
use shopstar\models\groups\GroupsTeamModel;
use shopstar\models\order\create\activityProcessor\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

/**
 * 拼团活动处理器
 * Class GroupsActivity
 * @package shopstar\services\groups
 * @author likexin
 */
class GroupsActivity implements OrderCreatorActivityProcessorInterface
{

    /**
     * 团id
     * @var int
     * @author likexin
     */
    public static $teamId = 0;

    /**
     * 是否参团
     * @var bool
     * @author likexin
     */
    public static $isJoin = false;

    /**
     * 是否是有效拼团活动
     * @var bool
     * @author likexin
     */
    public static $groups = false;

    /**
     * 是否可用优惠券
     * @var bool
     * @author likexin
     */
    public static $useCoupon = true;

    /**
     * 接收优惠活动分发器指派过来的活动处理任务
     * ---------------------------------------------------
     * 优惠处理器processor方法使用注意 ：
     * 期望能返回\common\models\order\OrderAssistant对象，
     * 以便订单能够自动处理订单结果。
     * 如果你返回其他类型的值，
     * 那么你需要自己修改订单数据，因为这将不能自动合并优惠结果！
     * ---------------------------------------------------
     * @param OrderCreatorActivityAssistant $assistant 传递过来的订单助手实例，里面包含了当前活动所支持的商品片段
     * @param array $activityInfo 当前活动信息都会原样传递回来
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实例，里面包含了关于当前订单的一切
     *
     * @return OrderCreatorActivityAssistant
     * @throws GroupsOrderException
     */
    public function processor(OrderCreatorActivityAssistant $assistant, array $activityInfo, OrderCreatorKernel &$orderCreatorKernel)
    {

        //获取拼团需要处理的参数
        $inputData = $orderCreatorKernel->inputData['extend_params'];

        //是否是阶梯团
        $isLadder = $inputData['is_ladder'] ?? 0;

        //阶梯
        $ladder = $inputData['ladder'] ?? 0;

        //判断参数是否正确
        if (!isset($inputData['is_join'])) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_PARAMS_ERROR);
        }

        //是否参团
        $isJoin = self::$isJoin = (bool)$inputData['is_join'];

        //是否有团队ID
        $teamId = $inputData['team_id'] ?? 0;

        //您已有正在进行的订单，不可参团
        if ($isJoin) {
            $joinInfo = GroupsCrewModel::getOne($teamId, $orderCreatorKernel->memberId);

            if ($joinInfo) {
                throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_JOIN_TEAM_IS_REPEAT);
            }
        }

        // 活动商品
        $activityGoods = $assistant->getGoodsInfo();

        // 判断同时购买商品
        if (count($activityGoods) > 1) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_GOODS_COUNT_ERROR);
        }

        // 获取首个订单商品 拼团同时只能买一个商品  ps：一个不是一件
        $orderGoods = current($activityGoods);

        // 活动商品为空
        if (empty($orderGoods)) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_ORDER_GOODS_NOT_EXIST_ERROR);
        }

        // 校验商品是不是下单减库存
        if ($orderGoods['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_REDUCTION_TYPE_ERROR);
        }

        // 查找规则
        $activityRules = ShopMarketingModel::getActivityInfo($orderGoods['goods_id'], $orderCreatorKernel->clientType, 'groups', $orderGoods['option_id'], [
            'member_id' => $orderCreatorKernel->memberId,
            'not_check_time' => (bool)$isJoin, //参团不需要判断时间
            'activity_id' => $activityInfo['activity_id'],//活动id
        ]);

        // 拆rules
        $rules = $activityRules['rules'];

        // 判断是否可用优惠券
        self::$useCoupon = $rules['use_coupon'] == 1;

        // 获取拼团价格
        $groupsPriceInfo = GroupsGoodsModel::getOne($activityRules['id'], $orderGoods['goods_id'], $orderGoods['option_id']);

        // 拼团价格是否存在
        if (empty($groupsPriceInfo)) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_ORDER_GOODS_PRICE_NOT_EXIST_ERROR);
        }

        // 预定义成团人数
        $successNum = 0;

        // 拼团价格
        $groupsPrice = 0;

        //团长价
        $groupsLeaderPrice = 0;

        //赋值参团人数
        $successNum = $rules['success_num'];

        // 赋值价格
        $groupsPrice = $groupsPriceInfo['price'];

        //赋值团长价
        $groupsPriceInfo['leader_price'] > 0 && $groupsLeaderPrice = $groupsPriceInfo['leader_price'];

        //需要支付的原价
        $payPrice = $assistant->getTotalPayPrice();

        //计算多件商品价格
        $groupsPrice = round2($groupsPrice * $orderGoods['total']);

        //计算优惠金额
        $groupsPrice = $payPrice - $groupsPrice;

        //执行拼团优惠
        $assistant->setCutPrice($orderGoods['goods_id'], $orderGoods['option_id'], $groupsPrice, 'groups', [
            'groups' => $activityRules
        ]);

        //执行团长优惠  (实际支付金额 - 拼团优惠金额 - (团长金额*商品数量)) 等于优惠金额
        $groupsLeaderPrice = $payPrice - $groupsPrice - ($groupsLeaderPrice * $orderGoods['total']);
        if (!$isJoin && $groupsPriceInfo['leader_price'] >= 0) {
            $assistant->setCutPrice($orderGoods['goods_id'], $orderGoods['option_id'], $groupsLeaderPrice, 'groups_leader', [
                'groups_leader' => $groupsLeaderPrice
            ]);
        }

        // 获取活动商品库存
        $activityGoodsStock = ShopMarketingGoodsMapModel::find()
            ->where([
                'activity_id' => $activityRules['id'],
                'goods_id' => $orderGoods['goods_id'],
                'option_id' => $orderGoods['option_id'],
            ])
            ->select([
                'activity_stock'
            ])
            ->one();

        // 添加活动类型
        $orderCreatorKernel->orderData['activity_type'] = OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS;

        $orderCreatorKernel->orderActivity[] = [
            'id' => $activityRules['id'],
            'type' => 'groups',
            'rule_index' => 0,
        ];

        // 设置订单自动关闭
        $this->setAutoCloseOrderTime($orderCreatorKernel);

        //如果是确认订单则直接返回
        if ($orderCreatorKernel->isConfirm) {

            // 返回活动规则
            $assistant->setActivityReturnData('groups', [
                'buy_count' => (int)$activityRules['buy_count'], //已购买个数
                'activity_stock' => $activityGoodsStock['activity_stock'],
                'limit_num' => $rules['limit_num'],
                'limit_type' => $rules['limit_type'],
            ]);

            return $assistant;
        }

        //判断限购
        $this->checkLimitBuy((int)$orderGoods['total'], $rules, (int)$activityRules['buy_total'] ?? 0);

        //检查库存并处理并发
        $this->checkGoodsActivityStock($activityRules['id'], $orderGoods['goods_id'], $orderGoods['option_id'], $orderGoods['total']);

        //确定是有效拼团活动，在订单afterCreator 使用
        self::$groups = true;


        //如果是参团则直接结束
        if ($isJoin) {

            //参团必须有团队iD
            if (empty($teamId)) {
                throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_JOIN_TEAM_ID_IS_ERROR);
            }

            //判断团状态是否正确
            $team = GroupsTeamModel::getOne($teamId, [
                'select' => [
                    'id',
                    'success',
                ]
            ]);

            // 判断团是否有效
            if (empty($team)) {
                throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_JOIN_TEAM_IS_EMPTY_ERROR);
            }

            // 团状态无效
            if ($team['success'] != GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT) {
                throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_JOIN_TEAM_STATUS_ERROR);
            }

            self::$teamId = $teamId;

            return $assistant;
        }

        // 创建团
        $teamId = $this->createTeam($activityRules['id'], $orderCreatorKernel->memberId, $successNum, (int)$isLadder, $ladder, $rules['limit_time']);

        // 设置团id
        self::$teamId = $teamId;

        return $assistant;
    }

    /**
     * @param int $activityId
     * @param int $memberId
     * @param int $successNum
     * @param int $isLadder
     * @param int $ladder
     * @param int $limitTime
     * @return int
     * @throws GroupsOrderException
     * @author likexin
     */
    private function createTeam(int $activityId, int $memberId, int $successNum, int $isLadder, int $ladder, int $limitTime): int
    {
        // 开团
        $teamId = GroupsTeamService::startTeam($activityId, $memberId, [
            'success_num' => $successNum,
            'is_ladder' => $isLadder,
            'ladder' => $ladder,
            'limit_time' => $limitTime,
        ]);

        if (is_error($teamId)) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_ORDER_CREATE_TEAM_ERROR, $teamId['message']);
        }

        return $teamId;
    }

    /**
     * 判断限购
     * @param int $total
     * @param array $rules
     * @param int $buyTotal
     * @throws GroupsOrderException
     * @author likexin
     */
    private function checkLimitBuy(int $total, array $rules, int $buyTotal)
    {
        // 判断限购
        if ($rules['limit_type'] != ActivityConstant::ACTIVITY_LIMIT_TYPE_NOT_LIMIT) {
            if (($buyTotal + $total) > $rules['limit_num']) {
                throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_BUY_LIMIT);
            }
        }
    }

    /**
     * 检查活动库存
     * @param int $activityId
     * @param int $goodsId
     * @param int $optionId
     * @param int $total
     * @throws GroupsOrderException
     * @author likexin
     */
    private function checkGoodsActivityStock(int $activityId, int $goodsId, int $optionId, int $total)
    {
        $activityGoods = ShopMarketingGoodsMapModel::find()
            ->where([
                'activity_id' => $activityId,
                'goods_id' => $goodsId,
                'option_id' => $optionId,
            ])
            ->select([
                'activity_stock',
                'original_stock'
            ])
            ->first();

        //活动商品异常
        if (empty($activityGoods)) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_ACTIVITY_GOODS_ERROR);
        }

        //启用redis
        $redis = \Yii::$app->redisPermanent;

        //拼接redisKey
        $key = 'shop_star_groups_goods_stock_' . $activityId . '_' . $goodsId . '_' . $optionId;

        //读取redis设置
        $redisTotal = (int)$redis->get($key);

        //判断库存是否充足
        if ($activityGoods['activity_stock'] < $total || ($redisTotal + $total) > $activityGoods['original_stock']) {
            throw new GroupsOrderException(GroupsOrderException::GROUPS_ORDER_PROCESSOR_GOODS_ACTIVITY_STOCK_ERROR);
        }
    }

    /**
     * 设置自动关闭时间
     * @param OrderCreatorKernel $orderCreatorKernel
     * @author likexin
     */
    private function setAutoCloseOrderTime(OrderCreatorKernel &$orderCreatorKernel)
    {
        // 修改订单关闭时间 读取拼团设置
        $setting = ShopSettings::get('activity.groups');

        // 永不关闭
        if ($setting['auto_close']['open'] == 0) {
            $orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_NOT_CLOSE;
            $orderCreatorKernel->confirmData['auto_close_time'] = '0000-00-00 00:00:00';
            $orderCreatorKernel->autoCloseTime = 0;
        } else {

            // 关闭时间
            $orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME;

            // 自动关闭时间不直接运用计算
            $orderCreatorKernel->confirmData['auto_close_time'] = $setting['auto_close']['close_time'];

            //设置自动关闭时间
            $orderCreatorKernel->autoCloseTime = DateTimeHelper::after($orderCreatorKernel->createTime, $setting['auto_close']['close_time'] * 60);
        }
    }

}