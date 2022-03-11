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

namespace shopstar\helpers;

use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * 队列
 * @author 青岛开店星信息技术有限公司
 * Class Queue
 * @package shopstar\helpers
 */
class QueueHelper extends BaseObject
{

    /**
     * @return \yii\queue\db\Queue
     * @author 青岛开店星信息技术有限公司
     */
    public static function client()
    {
        return \Yii::$app->queue;
    }

    /**
     * 推送队列
     * @param JobInterface $job 任务类
     * @param int $delay 延时 秒
     * @return string|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function push(JobInterface $job, $delay = 0)
    {
        return self::client()->delay($delay)->push($job);
    }

    /**
     * 作业等待执行。
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isWaiting(int $id)
    {
        return self::client()->isWaiting($id);
    }

    /**
     * 从队列获取作业，并执行它
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isReserved(int $id)
    {
        return self::client()->isReserved($id);
    }

    /**
     * 作业执行完成
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isDone(int $id)
    {
        return self::client()->isDone($id);
    }

    /**
     * 移出队列
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function remove(int $id)
    {
        return self::client()->remove($id);
    }

    /**
     * 执行队列事件
     * Queue::EVENT_BEFORE_PUSH    PushEvent    Adding job to queue using Queue::push() method
     * Queue::EVENT_AFTER_PUSH    PushEvent    Adding job to queue using Queue::push() method
     * Queue::EVENT_BEFORE_EXEC    ExecEvent    Before each job execution
     * Queue::EVENT_AFTER_EXEC    ExecEvent    After each success job execution
     * Queue::EVENT_AFTER_ERROR    ExecEvent    When uncaught exception occurred during the job execution
     * cli\Queue:EVENT_WORKER_START    WorkerEvent    When worker has been started
     * cli\Queue:EVENT_WORKER_LOOP    WorkerEvent    Each iteration between requests to queue
     * cli\Queue:EVENT_WORKER_STOP    WorkerEvent    When worker has been stopped
     * @param \yii\queue\Queue $eventName
     * @param $event
     * @author 青岛开店星信息技术有限公司
     */
    public static function on(\yii\queue\Queue $eventName, $event)
    {
        return self::client()->on($eventName, $event);
    }


}
