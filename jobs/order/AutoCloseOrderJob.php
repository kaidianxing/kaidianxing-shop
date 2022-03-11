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


namespace shopstar\jobs\order;

use shopstar\constants\order\OrderConstant;
use shopstar\models\order\OrderModel;
use shopstar\services\order\OrderService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * 自动关闭订单
 * Class AutoCloseOrderJob
 * @author 青岛开店星信息技术有限公司
 */
class AutoCloseOrderJob extends BaseObject implements JobInterface
{
    public $orderId;

    /**
     * 订单自动关闭
     * @inheritDoc
     */
    public function execute($queue)
    {
        $order = OrderModel::findOne([
            'id' => $this->orderId,
        ]);

        $result = OrderService::closeOrder($order, OrderConstant::ORDER_CLOSE_TYPE_SYSTEM_AUTO_CLOSE, 0, [
            'cancel_reason' => '订单支付超时'
        ]);

        if (is_error($result)) {
            echo "订单自动关闭失败,id:'{$this->orderId} 失败原因：{$result['message']}'\n";
            return;
        }

        echo "订单自动关闭完成,id:'{$this->orderId}'\n";
        return;
    }
}
