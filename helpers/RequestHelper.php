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
use yii\helpers\Json;

/**
 * 请求类
 * @package system\core
 * @see \yii\web\Request
 * @method getUserIp() static
 * @author 青岛开店星信息技术有限公司
 */
class RequestHelper
{

    /**
     * @param $name
     * @param $params
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function __callStatic($name, $params)
    {
        return Yii::$app->getRequest()->$name(...$params);
    }

    /**
     * 是否为 GET请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isGet(): bool
    {
        return Yii::$app->getRequest()->isGet;
    }

    /**
     * 是否为 POST请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isPost(): bool
    {
        return Yii::$app->getRequest()->isPost;
    }

    /**
     * 是否为 PUT请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isPut(): bool
    {
        return Yii::$app->getRequest()->isPut;
    }

    /**
     * 是否为 DELTE请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isDelete(): bool
    {
        return Yii::$app->getRequest()->isDelete;
    }

    /**
     * 是否为HEAD请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isHead(): bool
    {
        return Yii::$app->getRequest()->isHead;
    }

    /**
     * 是否为 PATCH请求
     * @return bool
     */
    public static function isPatch(): bool
    {
        return Yii::$app->getRequest()->isPatch;
    }

    /**
     * 是否为OPTIONS请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isOptions(): bool
    {
        return Yii::$app->getRequest()->isOptions;
    }

    /**
     * 是否为cli
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isCli(): bool
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isCgi(): bool
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 当前是否Ajax请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAjax(): bool
    {
        return Yii::$app->getRequest()->isAjax;
    }

    /**
     * 是否是ajax获取
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAjaxGet(): bool
    {
        return self::isAjax() && self::isGet();
    }

    /**
     * 是否是ajax请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAjaxPost(): bool
    {
        return self::isAjax() && self::isPost();
    }

    /**
     * 当前是否Pjax请求
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isPjax(): bool
    {
        return Yii::$app->getRequest()->isPjax;
    }

    /**
     * Request数据
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function input(): string
    {
        return Yii::$app->getRequest()->getRawBody();
    }

    /**
     * GET 数据
     * @param null $name
     * @param mixed $default
     * @return array|mixed
     */
    public static function get($name = null, $default = '')
    {
        if ($name === null) {
            return Yii::$app->getRequest()->getQueryParams();
        }
        return Yii::$app->getRequest()->getQueryParam($name, $default);
    }

    /**
     * 获取整型数据
     * @param $name
     * @param string $default
     * @return int
     */
    public static function getInt($name, $default = 0): int
    {
        return (int)self::get($name, $default);
    }

    /**
     * 获取数字数据
     * @param null $name
     * @param int $dec
     * @param int $default
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    public static function getFloat($name, int $dec = 2, int $default = 0): float
    {
        return round(self::get($name, $default), $dec);
    }

    /**
     * 获取数组
     * @param string $name
     * @param string $delimiter
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getArray(string $name, string $delimiter = ','): array
    {
        $get = self::get($name);
        if (empty($get)) {
            return [];
        } elseif (is_array($get)) {
            return $get;
        }
        return ArrayHelper::explode($delimiter, $get);
    }

    /**
     * POST数据
     * @param null $name
     * @param string|null $default
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function post($name = null, ?string $default = '')
    {
        $post = Yii::$app->getRequest()->post();
        array_walk($post, function (&$value) {
            if ($value === null) {
                $value = '';
            }
        });

        if (is_null($name)) {
            return $post;
        }

        if (is_array($name)) {
            $array = [];
            foreach ($name as $key) {
                if (empty($key) || is_array($key)) {
                    continue;
                }
                $array[$key] = self::post($key);
            }
            return $array;
        }
        if (!empty($name) && StringHelper::exists($name, '.')) {
            //获取 array[key] 这种方式，可以直接 Request::post('array.key')
            $keys = explode('.', $name);
            if (empty($keys[0]) || empty($keys[1])) {
                return $default;
            }
            if (!isset($post[$keys[0]])) {
                return $default;
            }
            $value = $post[$keys[0]];
            if (!is_array($value) || !isset($value[$keys[1]])) {
                return $default;
            }
            return $value[$keys[1]];
        }

        if (!isset($post[$name]) || $post[$name] === '') {
            return $default;
        }
        return $post[$name];

    }

    /**
     * 获取post数据int化
     * @param string $name
     * @param int $default
     * @return int
     */
    public static function postInt(string $name, int $default = 0): int
    {
        return (int)self::post($name, $default);
    }

    /**
     * 获取数字数据
     * @param $name
     * @param int $dec
     * @param int $default
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function postFloat($name, int $dec = 2, $default = 0): int
    {
        return round(self::post($name, $default), $dec);
    }

    /**
     * 获取数组
     * @param string $name
     * @param string $delimiter
     * @return array
     */
    public static function postArray(string $name, string $delimiter = ','): array
    {
        $post = self::post($name);
        if (empty($post)) {
            return [];
        } elseif (is_array($post)) {
            return $post;
        }
        return ArrayHelper::explode($delimiter, $post);
    }

    /**
     * 获取header信息
     * @param string $name
     * @param string $default
     * @return mixed
     * @author likexin
     */
    public static function header(string $name, string $default = '')
    {
        return Yii::$app->request->headers[$name] ?? $default;
    }

    /**
     * 获取分页
     * @param string|null $key
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPage(string $key = null)
    {
        if (is_null($key)) {
            $key = static::get('pagekey', 'page');
        }
        return max(1, self::getInt($key, '1'));
    }

    /**
     * 获取每页记录数
     * @param null $defaultSize
     * @param string $key
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPageSize($defaultSize = null, string $key = 'pagesize')
    {
        if (is_null($key)) {
            $key = static::get('pagesizekey', 'pagesize');
        }
        if (is_null($defaultSize)) {
            $defaultSize = static::getInt($key, 20);
        }
        $pagesize = self::getInt($key, $defaultSize);
        return min(1000, $pagesize);
    }

    /**
     * 获取小程序传回来的json
     * @return mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function json($name = null)
    {
        $result = static::input($name);

        $tmp_array = Json::decode($result);

        if (is_array($tmp_array)) {
            return $name ? $tmp_array[$name] : $tmp_array;
        }
        return $name ? $result[$name] : $result;
    }

}
