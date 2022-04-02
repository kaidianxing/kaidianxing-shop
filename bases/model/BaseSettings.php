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

namespace shopstar\bases\model;

use shopstar\helpers\ArrayHelper;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\StringHelper;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * 设置基类，所有设置都继承此类(ShopSettings\CoreSettings)
 * Class BaseSettings
 * @package shopstar\bases\model
 * @author 青岛开店星信息技术有限公司
 */
class BaseSettings extends ActiveRecord
{

    /**
     * @var string 缓存前缀
     */
    private static $cachePrefix = '';

    /**
     * @param string $key
     * @param null $defaultValue
     * @param array $dbWhere
     * @return null
     * @author likexin
     */
    public static function baseGet(string $key, $defaultValue = null, array $dbWhere = [])
    {
        // 分层级获取所有的key
        [$primaryKey, $originalKey, $childKeys] = static::getKeys($key);

        // 默认设置
        $defaultSettingsValue = static::getDefaultSettings($originalKey);

        // 先从缓存读取
        $value = CacheHelper::get($primaryKey, []);

        // 缓存没有，是否从数据库读入的
        $isFromDatabase = false;

        // 如果缓存中没有则查询数据库
        if (empty($value)) {
            $where = ['key' => $originalKey];
            foreach ($dbWhere as $k => $v) {
                $where[$k] = $v;
            }
            $dbValue = static::find()->select('value')->where($where)->limit(1)->scalar();
            if (!empty($dbValue)) {
                $value = Json::decode($dbValue);

                $isFromDatabase = true;
            }
        }

        // 合并默认设置的值
        $defaultSettingsValue = static::mergeDefaultSettingsValue($defaultSettingsValue, $value);
        if (is_array($value) && is_array($defaultSettingsValue)) {
            $value = ArrayHelper::merge($defaultSettingsValue, $value);
        } elseif (empty($value) && !empty($defaultSettingsValue)) {
            $value = $defaultSettingsValue;
        } elseif (is_null($value)) {
            return $defaultValue;
        }

        // 设置缓存
        if ($isFromDatabase) {
            CacheHelper::set($primaryKey, $value);
        }

        // 递归取值
        return self::unlimitedGetValue($value, $childKeys, $defaultValue);
    }

    /**
     * 递归获取数据值
     * @param array $data
     * @param array $fields
     * @param mixed $defaultValue
     * @return mixed
     * @author likexin
     */
    public static function unlimitedGetValue(array $data, array $fields, $defaultValue = null)
    {
        if (empty($fields)) {
            return $data ?? $defaultValue;
        }

        static $value;

        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                break;
            }

            $data = $data[$field];

            // 删除这一层的字段
            array_shift($fields);

            if (is_array($data) && !empty($fields)) {
                // data还有下一级、并且字段还有，走进递归
                static::unlimitedGetValue($data, $fields, $defaultValue);
            } elseif (!empty($fields)) {
                // data已经没有下一级了，字段还有，跳出要走默认值了
                break;
            } else {
                // data没有下一级，字段也没有了，value就是data
                $value = $data;
            }
        }

        return $value ?? $defaultValue;
    }

    /**
     * 设置缓存
     * @param string $key
     * @param null $value
     * @param bool $mergeOriginalData
     * @param array $dbAttributes
     * @return bool
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function baseSet(string $key, $value = null, bool $mergeOriginalData = true, array $dbAttributes = [])
    {
        // 分层级获取所有的key
        [$primaryKey, $originalKey, $childKeys] = static::getKeys($key);

        // 获取原始数据
        $originalValue = [];
        if ($mergeOriginalData) {
            $originalValue = self::baseGet($originalKey, null, $dbAttributes);
        }

        // 递归赋值
        $value = self::unlimitedSetValue((array)$originalValue, $childKeys, $value);

        $keys = ['`key`', '`value`'];
        $values = [
            ':key' => $originalKey,
            ':value' => Json::encode($value)
        ];

        foreach ($dbAttributes as $k => $v) {
            $keys[] = "`{$k}`";
            $values[":{$k}"] = $v;
        }

        $keys = implode(',', $keys);
        $params = implode(',', array_keys($values));

        // 更新数据库
        self::getDb()->createCommand("REPLACE INTO " . static::tableName() . " ({$keys}) VALUES ({$params})", $values)->execute();

        // 更新缓存
        CacheHelper::set($primaryKey, $value);

        return true;
    }

    /**
     * 递归赋值
     * @param array $data
     * @param array $fields
     * @param $value
     * @return array
     * @author likexin
     */
    public static function unlimitedSetValue(array $data, array $fields, $value)
    {
        if (empty($fields)) {
            return $value;
        }

        $current = current($fields);

        // 删除这一层级
        array_shift($fields);

        if (!empty($fields)) {
            // 字段还有下一级
            $data[$current] = self::unlimitedSetValue($data[$current] ?: [], $fields, $value);
        } else {
            $data[$current] = $value;
        }

        return $data;
    }

    /**
     * 删除设置项
     * @param string $key
     * @param array $dbAttributes
     * @return bool
     * @author likexin
     */
    public static function baseRemove(string $key, array $dbAttributes = [])
    {
        try {

            // 分层级获取所有的key
            [$primaryKey, $originalKey, $childKeys] = static::getKeys($key);

            // 如果没有子级则全部删除
            if (empty($childKeys)) {

                // 删除数据库
                static::deleteAll(ArrayHelper::merge($dbAttributes, [
                    'key' => $originalKey,
                ]));

                // 删除缓存
                CacheHelper::delete($primaryKey);

                return true;
            }

            // 如果有子级处理子级
            $originalValue = self::baseGet($originalKey, null, $dbAttributes);

            // 递归删除设置项
            $value = self::unlimitedUnset($originalValue, $childKeys);

            $keys = ['`key`', '`value`'];
            $values = [
                ':key' => $originalKey,
                ':value' => Json::encode($value)
            ];

            foreach ($dbAttributes as $k => $v) {
                $keys[] = "`{$k}`";
                $values[":{$k}"] = $v;
            }

            $keys = implode(',', $keys);
            $params = implode(',', array_keys($values));

            // 更新数据库
            self::getDb()->createCommand("REPLACE INTO " . static::tableName() . " ({$keys}) VALUES ({$params})", $values)->execute();

            // 更新缓存
            CacheHelper::set($primaryKey, $value);

        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 递归卸载数据项
     * @param array $data
     * @param array $fields
     * @return array
     * @author likexin
     */
    public static function unlimitedUnset(array $data, array $fields)
    {
        if (empty($fields)) {
            return $data;
        }

        $current = current($fields);

        // 删除这一层级
        array_shift($fields);

        if (!empty($fields)) {
            // 字段还有下一级
            $data[$current] = self::unlimitedUnset($data[$current], $fields);
        } else {
            unset($data[$current]);
        }

        return $data;
    }

    /**
     * 获取默认设置
     * @param string $key
     * @return mixed
     * @author likexin
     */
    public static function getDefaultSettings(string $key = '')
    {
        $settings = static::defaultSettings();
        if (empty($key)) {
            return $settings;
        }

        // 如果没有多级直接返回
        if (!StringHelper::exists($key, '.')) {
            return $settings[$key] ?? null;
        }

        // 多级返回
        $keys = explode('.', $key);
        $mainKey = $keys[0];
        if (isset($settings[$mainKey]) && isset($settings[$mainKey][$keys[1]])) {
            return $settings[$mainKey][$keys[1]];
        }
    }

    /**
     * 合并默认设置值
     * @param mixed $defaultSettingsValue 默认设置的值
     * @param $value
     * @return array
     * @author likexin
     */
    public static function mergeDefaultSettingsValue($defaultSettingsValue, $value)
    {
        if (!is_array($defaultSettingsValue)) {
            return $defaultSettingsValue;
        }

        if (empty($value)) {
            $value = [];
        }

        foreach ($defaultSettingsValue as $a => $b) {
            if (isset($b[0]) && is_array($b) && !empty($value[$a])) {
                $defaultSettingsValue[$a] = [];
            } else {
                $defaultSettingsValue[$a] = static::mergeDefaultSettingsValue($b, $value[$a]);
            }
        }
        return $defaultSettingsValue;
    }

    /**
     * 分层级获取所有的key
     * @param string $key
     * @return array
     * @author likexin
     */
    public static function getKeys(string $key)
    {
        // 所有得key以点分开
        $keys = explode('.', trim($key));

        // 分离出第一个元素为主key
        $primaryKey = $originalKey = $keys[0];
        array_shift($keys);

        // 取出前缀
        $cachePrefix = static::getCachePrefix();
        if (!empty($cachePrefix)) {
            $primaryKey = rtrim($cachePrefix, '_') . '_' . $primaryKey;
        }

        return [$primaryKey, $originalKey, $keys];
    }

    /**
     * 获取缓存前缀
     * @return string
     * @author likexin
     */
    protected static function getCachePrefix()
    {
        return (string)self::$cachePrefix;
    }

    /**
     * 设置缓存前缀
     * @param string $cachePrefix
     * @author likexin
     */
    protected static function setCachePrefix(string $cachePrefix)
    {
        self::$cachePrefix = $cachePrefix;
    }

}
