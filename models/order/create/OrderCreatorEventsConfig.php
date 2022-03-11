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
use shopstar\models\order\create\activityProcessor\BalanceActivity;
use shopstar\models\order\create\activityProcessor\CreditActivity;

/**
 * 创建订单事件配置
 *
 * Class OrderCreatorEventsConfig
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\models\order\create
 */
class OrderCreatorEventsConfig
{
    /**
     * 自动注册当前类的所有事件方法
     * OrderCreatorEventsConfig constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        foreach (get_class_methods(self::class) as $index => $item) {
            if ($item == '__construct') {
                continue;
            }

            $this->$item();
        }
    }

    /**
     * 订单初始化事件
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_INIT()
    {
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_INIT, [OrderCreatorEvents::class, 'test']);
    }

    /**
     * 订单创建前事件
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_BEFORE_CREATE()
    {
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_BEFORE_CREATE, [OrderCreatorEvents::class, 'test']);
    }

    /**
     * 订单创建完成后事件
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_AFTER_CREATE()
    {

        //满减折
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_AFTER_CREATE, [FullReduceActivityService::class, 'afterCreator']);

        //余额抵扣
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_AFTER_CREATE, [BalanceActivity::class, 'afterCreator']);

        //积分抵扣
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_AFTER_CREATE, [CreditActivity::class, 'afterCreator']);
    }

    /**
     * 订单事务提交后事件
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_AFTER_CREATE_COMMIT()
    {
        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_AFTER_CREATE_COMMIT, [OrderCreatorEvents::class, 'autoCloseOrder']);
//        OrderCreatorEventAssistant::register(OrderCreatorEvents::EVENT_AFTER_CREATE_COMMIT, [OrderCreatorEvents::class, 'AutoReceiveOrder']);
    }

    /**
     * 活动执行前
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_BEFORE_ACTIVITY_RUN()
    {
    }

    /**
     * 活动执行后
     * @author 青岛开店星信息技术有限公司
     */
    private function EVENT_AFTER_ACTIVITY_RUN()
    {
    }


}
