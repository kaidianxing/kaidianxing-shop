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

namespace shopstar\events;

use shopstar\helpers\QueueHelper;
use shopstar\jobs\order\AutoCloseOrderJob;
use shopstar\jobs\order\AutoReceiveOrderJob;
use shopstar\models\order\create\OrderCreatorKernel;
use yii\base\Event;

/**
 * @author 青岛开店星信息技术有限公司
 */
class OrderCreatorEvents extends Event
{
    /**
     * @var string 初始化事件
     */
    public const EVENT_INIT = 'INIT';

    /**
     * @var string 订单创建之前
     */
    public const EVENT_BEFORE_CREATE = 'BEFORE_CREATE';

    /**
     * @var string 订单创建之后
     */
    public const EVENT_AFTER_CREATE = 'AFTER_CREATE';

    /**
     * @var string 订单创建提交事务之后
     */
    public const EVENT_AFTER_CREATE_COMMIT = 'AFTER_CREATE_COMMIT';

    /**
     * 活动执行前
     */
    public const EVENT_BEFORE_ACTIVITY_RUN = 'EVENT_BEFORE_ACTIVITY_RUN';

    /**
     * 活动执行后
     */
    public const EVENT_AFTER_ACTIVITY_RUN = 'EVENT_AFTER_ACTIVITY_RUN';

    /**
     * 订单自动关闭
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public static function autoCloseOrder(OrderCreatorKernel $orderCreatorKernel)
    {

        if (empty($orderCreatorKernel->autoCloseTime)) {
            return;
        }

        $delay = strtotime($orderCreatorKernel->autoCloseTime) - time();
        if ($delay <= 0) {
            return;
        }

        QueueHelper::push(new AutoCloseOrderJob([
            'orderId' => $orderCreatorKernel->orderData['id'],
        ]), $delay);
    }

    /**
     * 自动收货   (废弃)
     * @param OrderCreatorKernel $orderCreatorKernel
     * @discard
     */
    public static function autoReceiveOrder(OrderCreatorKernel $orderCreatorKernel)
    {
        if (empty($orderCreatorKernel->autoReceiveTime)) {
            return;
        }

        $delay = strtotime($orderCreatorKernel->autoReceiveTime) - time();
        if ($delay <= 0) {
            return;
        }

        QueueHelper::push(new AutoReceiveOrderJob([
            'orderId' => $orderCreatorKernel->orderData['id'],
        ]), $delay);
    }


}
