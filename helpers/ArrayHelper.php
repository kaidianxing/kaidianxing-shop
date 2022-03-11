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

use yii\helpers\ArrayHelper as BaseArrayHelper;
use yii\helpers\Json;

/**
 * 数组助手类
 * Class ArrayHelper
 * @package shopstar\helpers
 */
class ArrayHelper extends BaseArrayHelper
{

    /**
     * 判断数组键值是否存在
     * @param array $array 数组
     * @param string $key 键值
     * @return bool
     */
    public static function keyEmpty(array $array, $key)
    {
        return !is_array($array) || !isset($array[$key]) || $array[$key] === '' || $array[$key] === NULL;
    }

    /**
     * 将数组转换为XML字符串
     * 转换 到XML
     * @param array $array
     * @param int $level
     * @return null|string|string[]
     */
    static function toXML(array $array, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($array as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . self::toXML($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);

        return $level == 1 ? $s . "</xml>" : $s;
    }

    /**
     * XML 转换成数组
     * @param string $xml XML字符串
     * @return array|mixed 返回数组或者错误信息
     */
    static function fromXML($xml)
    {
        if (empty($xml)) {
            return [];
        }
        $result = [];
        $xmlobj = XmlHelper::fromString($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xmlobj instanceof \SimpleXMLElement) {
            $result = Json::decode(Json::encode($xmlobj));
            if (is_array($result)) {
                return $result;
            }
        }
        return $result;
    }

    /**
     * trim数组，将数组中的值 trim,支持k-v的数组
     * @param array $array 数组
     * @return array 返回处理后的数组
     */
    static function trim(array $array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::trim($array);
            } else {
                $value = trim($value);
            }
        }
        unset($value);

        return $array;
    }


    /**
     * Json数据格式化
     * @param array $data 数组
     * @param bool $html 是否输入html格式
     * @return JSON
     */
    static function jsonFormat(array $data, $html = true)
    {
        $json = $data;
        if (is_array($json)) {
            $json = Json::encode($json);
        }
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;
        if ($html) {
            $tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
            $newline = "<br/>";
        } else {
            $tab = "\t";
            $newline = "\n";
        }
        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];
            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch ($char) {
                    case '{':
                        $tabcount++;
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '}':
                        $tabcount--;
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        break;
                    case ',':
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) $ignorenext = true;
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }
        return $result;
    }

    /**
     * 截取数组记录
     * @param array $array
     * @param int $pindex
     * @param int $psize
     * @return array
     */
    public static function subArray(array $array, $pindex = 0, $psize = 100)
    {
        return array_slice($array, ($pindex - 1) * $psize, $psize);
    }

    /**
     * explode字符串，过滤空
     * @param string $delimiter 分隔符
     * @param string $string 字符串
     * @return array
     */
    public static function explode($delimiter, $string = '')
    {
        return array_values(array_filter(explode($delimiter, $string)));
    }

    /**
     * 过滤数组字段
     * @param array $array
     * @param null $fieIds
     * @return array
     */
    public static function filterFields(array $array, $fieIds = null)
    {
        if (empty($fieIds)) {
            return $array;
        }
        if (!is_array($fieIds)) {
            $fieIds = [$fieIds];
        }

        foreach ($array as $key => &$row) {
            if (is_array($row)) {
                foreach ($fieIds as $fieId) {
                    if (!isset($row[$fieId])) {
                        continue;
                    }
                    unset($row[$fieId]);
                }
            } elseif (is_string($row) && is_numeric($key)) {
                if (in_array($row, $fieIds)) {
                    unset($array[$key]);
                }
            } elseif (is_string($key) && in_array($key, $fieIds)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * 返回数组中的重复元素
     * @param $array
     * @return array
     */
    public static function getRepeat(array $array)
    {
        // 获取去掉重复数据的数组
        $unique_arr = array_unique($array);
        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc($array, $unique_arr);
        return $repeat_arr;
    }

    /**
     * 键值的总和
     * @param array $array
     * @return int|mixed
     * @author likexin
     */
    public static function valueSum(array $array)
    {
        $sum = 0;
        foreach ($array as $index => $value) {
            $sum += (float)$value;
        }
        return $sum;
    }

    /**
     * 计算列的总和
     * @param array $array
     * @param string $column
     * @param bool $toArray
     * @return float|int
     * @author likexin
     */
    public static function columnSum(array $array, $column, $toArray = false)
    {
        $sum = 0;
        foreach ($array as $row) {
            if ($toArray) {
                $row = $row->toArray();
            }
            if (!is_array($row) || !isset($row[$column])) {
                continue;
            }
            $sum += (float)$row[$column];
        }
        return $sum;
    }

    /**
     * 按照指定的键名截取数组
     * @param array $data
     * @param array $keys
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function Intercept(array $data, array $keys)
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $keys)) {
                $newData[$key] = $value;
            }
        }
        return $newData;
    }


    /**
     * 按照KEY 在第一个插入
     * @param $arr
     * @param $ins_array
     * @return array
     */
    function array_unshift_assoc(&$arr, $ins_array)
    {
        if (!empty($arr)) {
            $arr = array_reverse($arr, true);
            $ins_array = array_reverse($ins_array, true);
            foreach ($ins_array as $key => $val) {
                $arr[$key] = $val;
            }
            $arr = array_reverse($arr, true);
        }

        return $arr;
    }

    /**
     * @name is_array2
     * @description  判断是否是二维数组
     * @param $array array
     * @return bool
     */
    public static function is_array2(array $array): bool
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                return is_array($v);
            }
            return false;
        }
        return false;
    }

    /**
     * 获取数组中的几个key
     * @param array $array
     * @param $keys
     * @return array
     * @author likexin
     */
    public static function only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function arrayGet(array $array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * 过滤数组空元素 包含 '0.00'
     * @param array $array
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function arrayFilterEmpty(array $array)
    {
        $tmp = [];
        foreach ($array as $key => $value) {
            if ($value != 0 && !empty($value)) {
                $tmp[$key] = $value;
            }
        }
        return $tmp;
    }

    /**
     * 递归处理数据
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $childNode
     * @return array
     * @author Vencenty
     */
    public static function unlimitedSort(array $data, $pid = 0, $field = 'parent_id', $childNode = 'children')
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item[$field] == $pid) {
                $item[$childNode] = static::unlimitedSort($data, $item['id'], $field, $childNode);
                // 卸载掉空的数组元素
                if ($item[$childNode] == null) {
                    unset($item[$childNode]);
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 递归获取字段值
     * @param array $data
     * @param string $field
     * @param string $childNode
     * @param bool $filterEmpty 是否过滤空
     * @return array
     * @author likexin
     */
    public static function unlimitedField(array $data, $field = 'name', $childNode = 'children', bool $filterEmpty = false)
    {
        static $values = [];
        foreach ($data as $row) {
            if (isset($row[$field])) {
                $values[] = $row[$field];
            }
            if (is_array($row[$childNode]) && !empty($row[$childNode])) {
                static::unlimitedField($row[$childNode], $field, $childNode);
            }
        }

        if ($filterEmpty) {
            return array_filter($values);
        }
        return $values;
    }
    
    /**
     * 根据值删除
     * @param array $data 数组
     * @param $value // 要删除的值
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteByValue(array $data, $value)
    {
        foreach ($data as $index => $item) {
            if ($item == $value) {
                unset($data[$index]);
            }
        }
        return $data;
    }

    /**
     * 根据key找出数组中指定的列
     * @param array $arr 数组
     * @param string $keys 要提取的key
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getColumns($arr, $keys){
        $result = [];
        $keys =array_flip($keys);
        foreach($arr as $k=>$v){

            $result[]=array_intersect_key($v,$keys);
        }
        return $result;
    }
}
