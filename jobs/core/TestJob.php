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

namespace shopstar\jobs\core;

use shopstar\helpers\CacheHelper;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * 测试任务
 * Class TestJob
 * @package shopstar\jobs\core
 * @author 青岛开店星信息技术有限公司
 */
class TestJob extends BaseObject implements JobInterface
{

    /**
     * @var string 缓存key
     */
    public $cache_key;

    /**
     * @var int 发送时间戳
     */
    public $push_time;

    /**
     * 执行任务
     * @param \yii\queue\Queue $queue
     * @return void
     */
    public function execute($queue)
    {
        $time = microtime(true);

        // 记录缓存执行事件
        CacheHelper::set($this->cache_key, [
            'push_time' => $this->push_time,
            'execute_time' => $time,
            'elapsed_time' => round($time - (float)$this->push_time, 4),
        ], 60 * 10);
    }
}