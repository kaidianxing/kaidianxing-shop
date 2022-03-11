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

namespace app\controllers\manage\order;

use shopstar\models\order\OrderModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 订单数据统计
 * Class IndexController
 * @author 青岛开店星信息技术有限公司
 * @package app\controllers\manage\order
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 统计数据
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $orderData = [];

    public function actionIndex()
    {

        //今日
        $this->today();
        //昨日
        $this->yesterday();
        //近7天
        $this->sevenDay();
        //本月
        $this->month();
        return $this->success($this->orderData);
    }

    /**
     * 今日
     * @author 青岛开店星信息技术有限公司
     */
    private function today()
    {
        $startTime = date('Y-m-d 00:00:00');
        $endTime = date('Y-m-d 23:59:59');

        $order = OrderModel::find()->where(
            ['BETWEEN', 'created_at', $startTime, $endTime]
        )->asArray()->all();


        $this->orderData['today']['order_num'] = count($order);
        $this->orderData['today']['order_price'] = array_sum(array_column($order, 'pay_price'));
    }

    /**
     * 昨日
     * @author 青岛开店星信息技术有限公司
     */
    private function yesterday()
    {
        $startTime = date('Y-m-d 00:00:00', strtotime(date('Y-m-d'), '-1 day'));
        $endTime = date('Y-m-d 23:59:59', strtotime(date('Y-m-d'), '-1 day'));;

        $order = OrderModel::find()->where(
            ['BETWEEN', 'created_at', $startTime, $endTime]
        )->asArray()->all();


        $this->orderData['yesterday']['order_num'] = count($order);
        $this->orderData['yesterday']['order_price'] = array_sum(array_column($order, 'pay_price'));
    }


    /**
     * 进7天
     * @author 青岛开店星信息技术有限公司
     */
    private function sevenDay()
    {
        $startTime = date('Y-m-d 00:00:00', strtotime(date('Y-m-d'), '-7 day'));
        $endTime = date('Y-m-d 23:59:59', strtotime(date('Y-m-d'), '-7 day'));

        $order = OrderModel::find()->where(
            ['BETWEEN', 'created_at', $startTime, $endTime]
        )->asArray()->all();


        $this->orderData['seven_day']['order_num'] = count($order);
        $this->orderData['seven_day']['order_price'] = array_sum(array_column($order, 'pay_price'));
    }

    /**
     * 本月
     * @author 青岛开店星信息技术有限公司
     */
    private function month()
    {
        $startTime = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
        $endTime = date('Y-m-d 239:5:59', strtotime("$startTime +1 month -1 day"));

        $order = OrderModel::find()->where(
            ['BETWEEN', 'created_at', $startTime, $endTime]
        )->asArray()->all();


        $this->orderData['month']['order_num'] = count($order);
        $this->orderData['month']['order_price'] = array_sum(array_column($order, 'pay_price'));
    }


}