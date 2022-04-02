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

namespace shopstar\bases\traits;

use shopstar\helpers\ConstantHelper;
use Yii;
use yii\base\Exception;

/**
 * 常量Trait
 * Class ConstantTrait
 * @package shopstar\bases\traits
 * @author 青岛开店星信息技术有限公司
 */
trait ConstantTrait
{

    /**
     * @var array 类缓存
     */
    private static $classCache = [];

    /**
     * @param string $name
     * @param array|null $arguments
     * @return mixed
     * @throws null
     * @author likexin
     */
    public static function __callStatic(string $name, $arguments)
    {
        if (empty($arguments)) {
            throw new Exception('The Code is required');
        }

        $code = $arguments[0];
        $name = strtolower(substr($name, 3));

        /**
         * 正常应该是@Message()，由于Exception::getMessage()冲突，此处适配下
         * @author likexin
         */
        if ($name == 'messages') {
            $name = 'message';
        }

        // 获取类反射
        $reflections = self::getReflection();

        return $reflections[$code][$name];
    }

    /**
     * 获取类反射
     * @return array|mixed
     * @author likexin
     */
    private static function getReflection()
    {
        if (isset(self::$classCache[static::class])) {
            return self::$classCache[static::class];
        }

        $reflections = Yii::$app->cache->get(static::class);
        $filemtimeCache = Yii::$app->cache->get(static::class . "_filemtime");

        $ref = new \ReflectionClass(static::class);
        $filemtime = filemtime($ref->getFileName());

        // 如果缓存为空或者 文件修改时间，重新获取
        if (empty($reflections) || $filemtime > $filemtimeCache) {
            $reflections = self::getAll($ref);
            Yii::$app->cache->set(static::class, $reflections, 3600);
            Yii::$app->cache->set(static::class . "_filemtime", $filemtime);
        }

        // 记录类缓存
        self::$classCache[static::class] = $reflections;

        return $reflections;
    }

    /**
     * 获取全部
     * @param null $class
     * @return array
     * @author likexin
     */
    public static function getAll($class = null): array
    {
        $class = $class ?? get_called_class();
        return (array)ConstantHelper::collectClass($class);
    }

    /**
     * 获取列表
     * @param string $valueField
     * @return array
     * @author likexin
     */
    public static function getList(string $valueField = 'code'): array
    {
        $all = self::getAll();
        $list = [];

        foreach ($all as $code => $item) {
            $list[] = array_merge($item, [
                $valueField => $code,
            ]);
        }

        return $list;
    }

    /**
     * 根据code获取一个
     * @param string|int $code
     * @return mixed|null
     * @author likexin
     */
    public static function getOneByCode($code)
    {
        $all = self::getAll();
        return $all[$code] ?? null;
    }

    /**
     * 根据索引获取一个
     * @param string $index 索引字段
     * @param string $value 索引值
     * @param string|null $filed 字段
     * @return array|mixed|null
     * @author likexin
     */
    public static function getOneByIndex(string $index, string $value, string $filed = null)
    {
        $all = self::getAll();
        if (empty($all)) {
            return [];
        }

        foreach ($all as $code => $item) {
            if (!$item[$index]) {
                continue;
            } elseif ($item[$index] == $value) {
                if ($filed == 'code') {
                    return $code;
                }
                return is_null($filed) ? $item : ($item[$filed] ?? null);
            }
        }
    }

    /**
     * 获取全部(指定索引字段)
     * @param string $index
     * @return array
     * @author likexin
     */
    public static function getAllByIndex(string $index): array
    {
        $all = self::getAll();
        if (empty($all)) {
            return [];
        }

        $newList = [];

        foreach ($all as $code => $item) {
            $item['code'] = $code;
            if (!isset($item[$index])) {
                continue;
            }
            $newList[$item[$index]] = $item;
        }

        return $newList;
    }

    /**
     * 获取全部列
     * @param string $field
     * @return array
     * @author likexin
     */
    public static function getAllColumn(string $field): array
    {
        if ($field == 'code') {
            return array_keys(self::getAll());
        }

        return array_column(self::getAll(), $field);
    }

    /**
     * 获取全部列（固定INDEX）
     * @param string $field
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllColumnFixedIndex(string $field): array
    {
        $all = self::getAll();

        return array_combine(array_keys($all), array_column($all, $field));
    }

}