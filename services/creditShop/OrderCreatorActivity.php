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

namespace shopstar\services\creditShop;

use shopstar\constants\order\OrderTypeConstant;
use shopstar\events\OrderCreatorEvents;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorEventAssistant;
use shopstar\models\order\create\OrderCreatorKernel;

/**
 * 积分商城活动处理器
 * 不需要判断互斥
 * Class OrderCreatorActivity.
 * @package shopstar\services\creditShop
 */
class OrderCreatorActivity
{
    /**
     * 支持的活动
     * @var array
     */
    public array $shopActivity = [
        'balance' => [
            'class' => '\shopstar\models\order\create\activityProcessor\BalanceActivity',
            'check_perm' => false,
        ],
    ];

    /**
     * 订单核心处理器实体
     * @var OrderCreatorKernel
     */
    private OrderCreatorKernel $orderCreatorKernel;

    /**
     * OrderCreatorActivity constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel $orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 初始执行处理
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function init()
    {
        // 触发活动前事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_BEFORE_ACTIVITY_RUN, $this->orderCreatorKernel);

        // 运行活动
        $this->execActivity();

        // 触发活动之后事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_AFTER_ACTIVITY_RUN, $this->orderCreatorKernel);
    }

    /**
     * 执行活动前
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function beforeActivity()
    {

    }

    /**
     * 执行活动
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execActivity()
    {
        $this->orderCreatorKernel->orderData['extra_price_package'] = [];
        // 优惠券订单跳过
        if ($this->orderCreatorKernel->orderData['order_type'] == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON) {
            return;
        }

        // 执行活动
        foreach ($this->shopActivity as $key => $activity) {
            // 是否存在
            if (!class_exists($activity['class'])) {
                continue;
            }

            // 判断权限
            if ($activity['check_perm']) {
                continue;
            }

            $activityProcessor = new $activity['class']();
            if (!method_exists($activity['class'], 'processor')) {
                continue;
            }

            // 实例化订单助手
            $orderAssistant = new OrderCreatorActivityAssistant($this->orderCreatorKernel->orderGoodsData);
            if (!empty($orderAssistant->getGoodsInfo())) {
                // 拥有可操作商品时，可以进入活动处理器 TODO config 确认 这里可以为空 用不到
                $activityResult = $activityProcessor->processor($orderAssistant, [], $this->orderCreatorKernel);
            }

            //判断返回值是否是订单商品助手类，如果是的话那么执行合并订单商品操作
            if ($activityResult instanceof OrderCreatorActivityAssistant) {
                // 活动处理结束，开始合并商品
                $orderGoodsDataSegments = $activityResult->getGoodsInfo();
                foreach ($this->orderCreatorKernel->orderGoodsData as &$orderGoodsOld) {
                    foreach ($orderGoodsDataSegments as $key => $orderGoodsNew) {
                        if ($orderGoodsOld['goods_id'] === $orderGoodsNew['goods_id'] &&
                            $orderGoodsOld['option_id'] === $orderGoodsNew['option_id']) {
                            $orderGoodsOld = $orderGoodsNew;
                        }
                    }
                }
            } else {
                // 不能处理的结果，进入下一个活动
                continue;
            }

            //获取参与的活动
            $activity = $orderAssistant->getActivity();

            //获取需要返回的商品活动
            $returnActivityData = $orderAssistant->getActivityReturnData();

            $execActivity[] = [
                'id' => 0,
                'type' => $key,
                'rule_index' => $activity['rule_index'] ?: 0,
                'activity_return_data' => $returnActivityData
            ];
        }

        if (!empty($execActivity)) {
            foreach ($execActivity as $orderLevelResultIndex => $orderLevelResultItem) {
                //添加活动确认订单返回值
                $this->orderCreatorKernel->orderData['activity_return_data'] = array_merge(
                    $this->orderCreatorKernel->orderData['activity_return_data'] ?: [],
                    $orderLevelResultItem['activity_return_data']
                );

                $this->orderCreatorKernel->orderActivity[] = [
                    'id' => $orderLevelResultItem['id'],
                ];
            }
        }

        // 根据orderGoods处理订单数据
        $this->orderCreatorKernel->orderData['extra_discount_rules_package'] = [];
        foreach ($this->orderCreatorKernel->orderGoodsData as $orderGoods) {
            foreach ((array)$orderGoods['activity_package'] as $actSign => $actRules) {
                if (empty($this->orderCreatorKernel->orderData['extra_price_package'][$actSign])) {
                    $this->orderCreatorKernel->orderData['extra_price_package'][$actSign] = 0;
                }
                $this->orderCreatorKernel->orderData['extra_price_package'][$actSign] += $actRules['price'];
                if (!in_array($actRules['rule'], $this->orderCreatorKernel->orderData['extra_discount_rules_package'])) {
                    $this->orderCreatorKernel->orderData['extra_discount_rules_package'][] = $actRules['rule'];
                }
            }
        }

        $this->orderCreatorKernel->orderData['extra_price_package'] = array_map(function ($value) {
            return round2($value, 2);
        }, $this->orderCreatorKernel->orderData['extra_price_package']);
    }

    /**
     * 执行活动后
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function afterActivity()
    {

    }
}
