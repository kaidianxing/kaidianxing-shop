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

namespace shopstar\traits;

use shopstar\helpers\CacheHelper;

/**
 * 缓存
 * Trait CacheTrait
 * @package shopstar\traits
 */
trait CacheTrait
{

    /**
     * 设置字符串缓存
     * @param $cacheType
     * @param $cacheInfo
     * @param null $additionalKey
     * @return bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function stringCache($cacheType, $cacheInfo, $additionalKey = null)
    {
        if (empty($cacheInfo)) {
            return false;
        }
        $cache_key = CacheHelper::getKey($cacheType, $additionalKey);

        $cache_result = \Yii::$app->redis->set($cache_key, $cacheInfo);

        self::setExpire($cacheType, $cache_key);

        return $cache_result;
    }

    /**
     * 获取字符串缓存
     * @param $cacheType
     * @param null $additionalKey
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStringCache($cacheType, $additionalKey = null)
    {
        $cache_key = CacheHelper::getKey($cacheType, $additionalKey);

        return \Yii::$app->redis->get($cache_key);
    }

    /**
     * 删除缓存
     * @param $cacheType
     * @param null $additionalKey
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteCache($cacheType, $additionalKey = null)
    {
        $cache_key = CacheHelper::getKey($cacheType, $additionalKey);

        return \Yii::$app->redis->del($cache_key);
    }

    /**
     * 获取缓存剩余时间
     * @param $cacheType
     * @param null $additionalKey
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function ttlCache($cacheType, $additionalKey = null)
    {
        $cache_key = CacheHelper::getKey($cacheType, $additionalKey);

        return \Yii::$app->redis->ttl($cache_key);
    }

    private static function setExpire($cacheType, $cacheKey)
    {
        $expire_time = CacheHelper::getExpire($cacheType);

        if ($expire_time != 0) {
            \Yii::$app->redis->expire($cacheKey, CacheHelper::getExpire($cacheType));
        }
    }
}