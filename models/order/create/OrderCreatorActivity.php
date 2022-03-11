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

namespace shopstar\models\order\create;

use shopstar\events\OrderCreatorEvents;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\helpers\StringHelper;
use shopstar\models\order\create\interfaces\OrderCreateAppActivityModuleInterface;

class OrderCreatorActivity implements OrderCreateAppActivityModuleInterface
{
    /**
     * 订单核心处理器实体
     * @author 青岛开店星信息技术有限公司
     * @var OrderCreatorKernel
     */
    private $orderCreatorKernel;

    /**
     * 商品级别优惠，在每种商品上处理
     * @var array
     */
    protected $goodsLevelActivityProcessor = [];

    /**
     * 订单级别优惠，在商品级别优惠处理之后，处理订单级别优惠
     * @var array
     */
    protected $orderLevelActivityProcessor = [];

    /**
     * OrderCreatorActivity constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel $orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 初始化
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function init()
    {
        // 触发活动前事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_BEFORE_ACTIVITY_RUN, $this->orderCreatorKernel);

        // 活动之前的事件
        $this->beforeActivity();

        // 运行活动
        $this->execActivity();

        // 活动之后的事件
        $this->afterActivity();

        // 触发活动之后事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_AFTER_ACTIVITY_RUN, $this->orderCreatorKernel);
    }

    public function beforeActivity()
    {
        // 这里自动加载了两种级别优惠：订单级别优惠 和 商品级别优惠
        list($this->orderLevelActivityProcessor, $this->goodsLevelActivityProcessor) = OrderCreatorActivityConfig::loadActivityProcessorList(
            $this->orderCreatorKernel->goodsIds,
            $this->orderCreatorKernel->optionIds,
            $this->orderCreatorKernel->clientType,
            $this->orderCreatorKernel->isOriginalBuy,
            (int)($this->orderCreatorKernel->inputData['extend_params']['activity_id'] ?? 0)
        );
    }

    /**
     * 执行活动
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function execActivity()
    {
        // 执行商品级别优惠
        $goodsActivity = $this->dispatcher($this->goodsLevelActivityProcessor);
        if (!empty($goodsActivity)) {

            //添加订单商品优惠标记
            foreach ($goodsActivity as $goodsActivityIndex => $goodsActivityItem) {
                //添加活动确认订单返回值
                $this->orderCreatorKernel->orderData['activity_return_data'] = array_merge(
                    $this->orderCreatorKernel->orderData['activity_return_data'] ?: [],
                    $goodsActivityItem['activity_return_data']
                );

                $this->orderCreatorKernel->orderActivity[] = [
                    'id' => $goodsActivityItem['id'],
                ];
            }
        }

        //执行订单级别优惠
        foreach ($this->orderLevelActivityProcessor as $orderLevelActivityProcessor) {
            $orderLevelResult = $this->dispatcher($orderLevelActivityProcessor);
            foreach ($orderLevelResult as $orderLevelResultIndex => $orderLevelResultItem) {
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
        $this->orderCreatorKernel->orderData['extra_price_package'] = [];
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

    public function afterActivity()
    {


    }

    /**
     * 活动分发器，用于指派活动处理
     * 插件的活动处理分发到自定义的命名空间上
     * @param $ActivityProcessor
     * @return array
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function dispatcher($ActivityProcessor)
    {
        $execActivity = [];
        foreach ($ActivityProcessor as $type => $activityConfig) {
            // 确定活动标识
            if (is_numeric($type)) {
                if (empty($activityConfig['type'])) {
                    throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_ERROR, $type . '是无效的优惠活动');
                }
                $type = $activityConfig['type'];
            }

            //分发指派活动处理器
            //获取对应活动的实体类
            $rules = OrderCreatorActivityConfig::getRules();
            // 自定义处理类
            if (!empty($rules[$type]['class'])) {
                $activityProcessorModule = $rules[$type]['class'];
            } else {
                // 默认处理类
                //基础活动处理类
                $activityProcessorPackage = '\shopstar\models\order\create\activityProcessor\\';
                $activityProcessorClass = StringHelper::camelize($type) . 'Activity';
                $activityProcessorModule = $activityProcessorPackage . $activityProcessorClass;
            }

            if (!class_exists($activityProcessorModule)) {
                continue;
//                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_PROCESSOR_CLASS_ERROR, '未找到' . $type . '活动处理模块：' . $activityProcessorClass . '.php');
            }

            $activityProcessor = new $activityProcessorModule();
            if (!method_exists($activityProcessorModule, 'processor')) {
                continue;
//                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_ACTIVITY_INVALID_ACTIVITY_PROCESSOR_METHOD_ERROR, '未找到可调用的活动处理器：' . $activityProcessorClass . '->processor()');
            }

            // 实例化订单助手
            $orderAssistant = new OrderCreatorActivityAssistant(
                $this->orderCreatorKernel->orderGoodsData
            );

            $activityResult = false;
            if (!empty($orderAssistant->getGoodsInfo())) {
                // 拥有可操作商品时，可以进入活动处理器
                $activityResult = $activityProcessor->processor(
                    $orderAssistant, $activityConfig ?: [], $this->orderCreatorKernel);
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
                'id' => $activityConfig['id'] ?: 0,
                'type' => $type,
                'rule_index' => $activity['rule_index'] ?: 0,
                'activity_return_data' => $returnActivityData
            ];
        }

        return $execActivity;
    }
}
