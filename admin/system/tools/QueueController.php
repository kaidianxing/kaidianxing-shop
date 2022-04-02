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

namespace shopstar\admin\system\tools;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\core\TestJob;

/**
 * 数据管理
 * Class CrontabController
 * @package shopstar\admin\system\tools
 * @author 青岛开店星信息技术有限公司
 */
class QueueController extends KdxAdminApiController
{

    /**
     * @var string 缓存key前缀
     */
    private $cacheKeyPrefix = 'test_job_cache_';

    /**
     * 发送任务
     * @return array|int[]|\yii\web\Response
     */
    public function actionSendJob()
    {
        // 投递时间
        $time = microtime(true);

        // 投递任务
        $jobId = QueueHelper::push(new TestJob([
            'push_time' => $time,
            'cache_key' => $this->cacheKeyPrefix . intval($time),
        ]));
        if (!$jobId) {
            return $this->error('任务投递失败');
        }

        return $this->result([
            'tmp_job_id' => intval($time)
        ]);
    }

    /**
     * 获取状态
     * @return array|int[]|\yii\web\Response
     */
    public function actionGetStatus()
    {
        $time = RequestHelper::getInt('tmp_job_id');
        if (empty($time)) {
            return $this->error('参数错误 tmp_job_id不能为空');
        }

        // 读取缓存
        $cache = CacheHelper::get($this->cacheKeyPrefix . $time);
        if (empty($cache)) {
            return $this->error('等待任务执行');
        }

        return $this->result([
            'elapsed_time' => $cache['elapsed_time'] ?? 0,
        ]);
    }

}