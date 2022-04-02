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


namespace shopstar\jobs\wechat;

use shopstar\models\wechat\WechatSyncTaskModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * @author 青岛开店星信息技术有限公司
 */
class WechatSyncJob extends BaseObject implements JobInterface
{
    /**
     * @var
     * @author 青岛开店星信息技术有限公司.
     */
    public $data;

    /**
     * 微信同步任务
     * @param Queue $queue
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    public function execute($queue): bool
    {
        echo '微信同步任务：类型' . $this->data['type'] . ';任务id:' . $this->data['task_id'] . "\n\r";

        //开始同步
        try {

            //执行同步
            WechatSyncTaskModel::sync($this->data['task_id'], (int)$this->data['type'], $this->data['options']);
        } catch (\Throwable $exception) {
            echo '微信同步任务失败：' . $exception->getMessage() . '类型' . $this->data['type'] . ';任务id:' . $this->data['task_id'] . "\n\r";
            return false;
        }

        echo '微信同步任务结束：类型' . $this->data['type'] . ';任务id:' . $this->data['task_id'] . "\n\r";

        return true;
    }
}