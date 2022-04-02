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

namespace shopstar\services\utility;

ignore_user_abort(); //忽略关闭浏览器
set_time_limit(0); //永远执行

use shopstar\bases\service\BaseService;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\core\CoreSettings;

/**
 * 公用执行模型
 * Class AsyncTaskExecuteService
 * @package shopstar\services\utility
 * @author 青岛开店星信息技术有限公司
 */
class AsyncTaskExecuteService extends BaseService
{

    /**
     * @var array 任务映射
     */
    private static $taskMap = [];

    /**
     * 获取系统设置
     * @return array
     * @author likexin
     */
    private static function getSystemSettings(): array
    {
        return CoreSettings::get('crontab');
    }

    /**
     * 获取配置
     * @return mixed
     * @author likexin
     */
    private static function getConfigMap()
    {
        if (empty(self::$taskMap)) {
            self::$taskMap = require_once __DIR__ . '/task-map.php';
        }

        return self::$taskMap;
    }

    /**
     * 执行单个店铺(前端异步调用)
     * @param bool $check 检测缓存与API_KEY
     * @param array $settings 系统设置
     * @param bool $isAll
     * @return bool|array
     * @author likexin
     */
    public static function singleShop(bool $check = false, array $settings = [], bool $isAll = false)
    {
        // 检测
        if ($check) {
            $cacheKey = 'async_task_single_shop_';
            $cache = CacheHelper::exists($cacheKey);
            if ($cache) {
                return error('缓存未失效');
            }
        }
        // 读取系统设置
        if (empty($settings)) {
            $settings = self::getSystemSettings();
        }
        if ($settings['execute_type'] == 1 && !$isAll) {
            return error('执行方式错误');
        }

        // 获取任务映射表
        $taskMap = self::getConfigMap();

        // 执行任务
        foreach ($taskMap as $task) {
            if (empty($task) || !isset($task['class']) || count($task['class']) != 2) {
                continue;
            } elseif (!class_exists($task['class'][0])) {
                continue;
            }

            // 被动触发  查找缓存
            if ($settings['execute_type'] == 0) {
                $redis = \Yii::$app->redis;
                $key = 'kdx_shop_' . '_async_task_' . $task['settings_key'];
                $isNotExecute = $redis->get($key);
                // 未超时 跳过
                if ($isNotExecute) {
                    continue;
                }
                // 触发时间
                $time = (int)$settings['params'][$task['settings_key']];
                // 如果没配置  跳出
                if (empty($time)) {
                    continue;
                }
                $redis->setex($key, $time * 60, DateTimeHelper::now());
            }
            $params = [];
            if (!empty($task['options'])) {
                $params = array_merge($params, $task['options']);
            }

            try {
                // 调用操作类与方法
                call_user_func_array($task['class'], $params);
            } catch (\Exception $exception) {

            }
        }

        return true;
    }

    /**
     * 执行所有店铺(主动模式，crontab调用)
     * @param string $apiKey
     * @return bool|array
     * @author likexin
     */
    public static function apiShop(string $apiKey = '')
    {

        if (empty($apiKey)) {
            return error('API_KEY参数为空');
        }

        // 读取系统设置
        $settings = self::getSystemSettings();

        if (empty($settings)) {
            return error('读取设置错误');
        }

        // 执行类型 0:被动 1:主动
        $type = (int)$settings['execute_type'];
        if (empty($type)) {
            return error('当前系统设置为被动模式');
        }

        // 验证API_KEY
        if ($apiKey !== (string)$settings['api_key']) {
            return error('API_KEY参数错误');
        }
        self::singleShop(false, $settings, true);

        return true;
    }

}
