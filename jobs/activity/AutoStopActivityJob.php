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

namespace shopstar\jobs\activity;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * @author 青岛开店星信息技术有限公司
 */
class AutoStopActivityJob extends BaseObject implements JobInterface
{

    /**
     * @var int 活动id
     */
    public $id;

    /**
     * 自动停止活动
     * @param Queue $queue
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        // 查找活动
        $activity = ShopMarketingModel::findOne(['id' => $this->id]);
        if (empty($activity)) {
            echo "活动自动停止失败,id:" . $this->id . ",原因:活动未找到\n";
            die;
        }
        // 停止时间不为空
        if ($activity->stop_time != 0) {
            echo "活动自动停止失败,id:" . $this->id . ",原因:活动已停止\n";
            die;
        }
        if ($activity->end_time > DateTimeHelper::now()) {
            echo "活动自动停止失败,id:" . $this->id . ",原因:未到停止时间\n";
            die;
        }
        $activity->stop_time = DateTimeHelper::now();
        $activity->status = -1;
        if (!$activity->save()) {
            echo "活动自动停止失败,id:" . $this->id . ",原因:" . $activity->getErrorMessage() . "\n";
            die;
        }
    }
}