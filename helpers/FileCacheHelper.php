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

use Yii;
use yii\caching\CacheInterface;

/**
 * 系统安装-缓存助手类
 * Class FileCacheHelper
 * @package install\helpers
 * @author likexin
 */
class FileCacheHelper
{

    /**
     * @var $cache CacheInterface
     */
    private static CacheInterface $cache;

    /**
     * @return CacheInterface
     */
    public static function getCacheClass(): CacheInterface
    {
        return self::$cache ?? Yii::$app->cacheFile;
    }


    /**
     * 获取缓存
     * @param $key
     * @param string $defaultValue
     * @return mixed|string
     * @author likexin
     */
    public static function get($key, $defaultValue = '')
    {
        $cache = static::getCacheClass()->get($key);
        if (empty($cache)) {
            return $defaultValue;
        }
        return $cache;
    }

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param null $duration
     * @param null $dependency
     * @return bool
     * @author likexin
     */
    public static function set($key, $value, $duration = null, $dependency = null): bool
    {
        return static::getCacheClass()->set($key, $value, $duration, $dependency);
    }

    /**
     * 删除缓存项
     * @param $key
     * @return bool
     * @author likexin
     */
    public static function delete($key): bool
    {
        return static::getCacheClass()->delete($key);
    }

}