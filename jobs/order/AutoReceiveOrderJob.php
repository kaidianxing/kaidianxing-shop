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

use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\helpers\LogHelper;
 
use shopstar\models\order\OrderModel;
use shopstar\services\order\OrderService;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * 自动关闭订单
 * Class AutoReceiveOrderJob
 * @author 青岛开店星信息技术有限公司
 */
class AutoReceiveOrderJob extends BaseObject implements JobInterface
{
    public $orderId;

    /**
     * 订单自动收货
     * @inheritDoc
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {

        echo "<<<<<<<<<<<<<<<<<<<<自动收货开始:" . Json::encode([$this->orderId]) . ">>>>>>>>>>>>>>>>>>>>>\n";

        $order = OrderModel::getOrderAndOrderGoods($this->orderId);

        $result = OrderService::complete($order,1,[
            'auto_receive' => true
        ]);

        if (is_error($result)) {
            LogHelper::error('[AUTO_RECEIVE_ORDER_ERROR]', [
                'data' => $result['message']
            ]);
            echo "订单自动收货失败:{$result['message']}\n";
        }

        echo "订单自动收货完成\n";
        return;
    }
}
