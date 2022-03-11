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

use shopstar\constants\RefundConstant;
use shopstar\exceptions\order\RefundException;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\services\order\refund\OrderRefundService;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

class AutoTimeoutCancelOrderJob extends BaseObject implements JobInterface
{
    public $orderId;

    public $orderGoodsId;

    /**
     * 超时维权时间自动关闭维权
     * @param \yii\queue\Queue $queue
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     * @return mixed|void
     */
    public function execute($queue)
    {
        echo "<<<<<<<<<<<<<<<<<<<<关闭维权订单开始:" . Json::encode([$this->orderId]) . ">>>>>>>>>>>>>>>>>>>>>\n";
        //删除维权订单 修改订单状态
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 更新维权表状态
            $res = OrderRefundService::cancelRefund($this->orderId, $this->orderGoodsId);
            if (is_error($res)) {
                echo "订单自动关闭失败,id:'{$this->orderId} 失败原因：{$res['message']}'\n";
                return;
            }
            
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            echo "订单自动关闭失败,id:'{$this->orderId} 失败原因：{$res['message']}'\n";
            return;
        }

        echo "订单维权超时自动关闭完成\n";
        return;
    }
}
