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

namespace shopstar\jobs\printer;


use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;

use shopstar\models\printer\PrinterTaskModel;
use yii\base\BaseObject;

/**
 * 自动打印订单
 * Class AutoPrinterOrder
 * @package apps\printer\jobs
 */
class AutoPrinterOrder extends BaseObject implements \yii\queue\JobInterface
{

    /**
     * 参数传递
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $job;


    public function execute($queue)
    {
        echo '自动打印订单执行时间：' . DateTimeHelper::now() . "\n";;

        $data = $this->job;

        try {
            //执行任务
            PrinterTaskModel::executeTask($data['scene'], $data['order_id']);
        } catch (\Throwable $e) {
            echo '自动打印订单发送失败: ' . $e->getMessage() . "\n";
            LogHelper::error('[Auto Printer Order]:' . $e->getMessage(), $data);
            return false;
        }

        echo("自动打印订单发送成功,时间:" . date('Y-m-d H:i:s') . "\n");
        return true;
    }
}