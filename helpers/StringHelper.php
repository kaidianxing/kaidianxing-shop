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

use yii\base\InvalidArgumentException;
use yii\helpers\StringHelper as BaseStringHelper;

/**
 * 字符串助手类
 * Class StringHelper
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class StringHelper extends BaseStringHelper
{

    const SEL_AND = 'AND';
    const SEL_OR = 'OR';

    private static $ATTACHMENT_URL;

    /**
     * @param int $length
     * @param bool $numeric
     * @param bool $string
     * @return string
     * @throws null
     * @author 青岛开店星信息技术有限公司
     */
    static function random(int $length = 0, bool $numeric = false, bool $string = false): string
    {
        if (PHP_VERSION_ID < 70000) {
            return \random($length, $numeric);
        }
        if ($length < 0) {
            throw new InvalidArgumentException('长度错误! 必须大于0的正整数');
        }
        if ($numeric) {
            $chars = '0123456789';
        } elseif ($string) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        } else {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }
        $len = strlen($chars) - 1;

        $string = '';

        while ($length > 0) {
            $random = random_int(0, $len);
            $string .= $chars[$random];
            $length--;
        }
        return $string;
    }

    /**
     * 判断是否包含字符串
     * @param string $string 字符串
     * @param string|array $find 查找字符串 或字符串数组
     * @param string $operator 当为查找字符串数组时 ，判断方式 OR / AND
     * @param bool $isFirst 是否从头查找
     * @return bool
     */
    static function exists(string $string = '', $find = '', string $operator = self::SEL_AND, bool $isFirst = false): bool
    {
        if (is_array($find)) {
            if ($operator == self::SEL_AND) {
                foreach ($find as $f) {
                    if ($isFirst) {
                        $position = !(strpos($string, $f) === FALSE) ? strpos($string, $f) : true;
                        $ret = !$position;
                    } else {
                        $ret = !(strpos($string, $f) === FALSE);
                    }
                    if (!$ret) {
                        return false;
                    }
                }
                return true;
            } else {
                foreach ($find as $f) {
                    if ($isFirst) {
                        $position = !(strpos($string, $f) === FALSE) ? strpos($string, $f) : true;
                        $ret = !$position;
                    } else {
                        $ret = !(strpos($string, $f) === FALSE);
                    }
                    if ($ret) {
                        return true;
                    }
                }
                return false;
            }
        }

        if ($isFirst) {
            $position = !(strpos($string, $find) === FALSE) ? strpos($string, $find) : true;
            return !$position;
        } else {
            return !(strpos($string, $find) === FALSE);
        }
    }

    /**
     * 字符串长度
     * @param $string
     * @param string $charset
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    static function length($string, string $charset = 'utf8'): int
    {

        if (strtolower($charset) == 'gbk') {
            $charset = 'gbk';
        } else {
            $charset = 'utf8';
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, $charset);
        } else {
            $n = $noc = 0;
            $strlen = strlen($string);

            if ($charset == 'utf8') {

                while ($n < $strlen) {
                    $t = ord($string[$n]);
                    if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                        $n++;
                        $noc++;
                    } elseif (194 <= $t && $t <= 223) {
                        $n += 2;
                        $noc++;
                    } elseif (224 <= $t && $t <= 239) {
                        $n += 3;
                        $noc++;
                    } elseif (240 <= $t && $t <= 247) {
                        $n += 4;
                        $noc++;
                    } elseif (248 <= $t && $t <= 251) {
                        $n += 5;
                        $noc++;
                    } elseif ($t == 252 || $t == 253) {
                        $n += 6;
                        $noc++;
                    } else {
                        $n++;
                    }
                }
            } else {

                while ($n < $strlen) {
                    $t = ord($string[$n]);
                    if ($t > 127) {
                        $n += 2;
                        $noc++;
                    } else {
                        $n++;
                        $noc++;
                    }
                }
            }

            return $noc;
        }
    }

    /**
     * 字节转换
     * @param $bytes
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    static function toSize($bytes): string
    {
        if ($bytes >= pow(2, 40)) { // 如果提供的字节数大于等于2的40次方，则条件成立
            $return = round($bytes / pow(1024, 4), 2); // 将字节大小转换为同等的T大小
            $suffix = 'TB'; // 单位为TB
        } elseif ($bytes >= pow(2, 30)) { // 如果提供的字节数大于等于2的30次方，则条件成立
            $return = round($bytes / pow(1024, 3), 2); // 将字节大小转换为同等的G大小
            $suffix = 'GB'; // 单位为GB
        } elseif ($bytes >= pow(2, 20)) { // 如果提供的字节数大于等于2的20次方，则条件成立
            $return = round($bytes / pow(1024, 2), 2); // 将字节大小转换为同等的M大小
            $suffix = 'MB'; // 单位为MB
        } elseif ($bytes >= pow(2, 10)) { // 如果提供的字节数大于等于2的10次方，则条件成立
            $return = round($bytes / pow(1024, 1), 2); // 将字节大小转换为同等的K大小
            $suffix = 'KB'; // 单位为KB
        } else { // 否则提供的字节数小于2的10次方，则条件成立
            $return = $bytes; // 字节大小单位不变
            $suffix = 'Byte'; // 单位为Byte
        }
        return $return . ' ' . $suffix; // 返回合适的文件大小和单位
    }

    /**
     * 获取guid
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    static function guid(): string
    {
        $newguid = '';
        if (function_exists('com_create_guid')) {
            //window下
            $newguid = com_create_guid();
        } else {
            //非windows下
            mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125); // "}"
            $newguid = $uuid;
        }
        $newguid = str_replace("{", '', $newguid);
        $newguid = str_replace("}", '', $newguid);
        return strtolower($newguid);
    }

    /**
     * 限制字符串长度
     * @param string $str
     * @param int $length
     * @param bool $tail
     * @return array|string|string[]|null
     * @author 青岛开店星信息技术有限公司
     */
    static function limit(string $str = '', int $length = 0, bool $tail = true)
    {

        $str = trim($str);
        $str = strip_tags($str);
        $pre_len = strlen($str);
        $str = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $length . '}).*#s', '$1', $str);

        if ($pre_len <= strlen($str)) {
            return $str;
        } else if ($pre_len != strlen($str)) {
            if ($tail) {
                $str .= "...";
            }
        }
        return $str;
    }

    /**
     * 清除Html & Script
     * @param $document
     * @return array|string|string[]|null
     * @author 青岛开店星信息技术有限公司
     */
    static function replaceHtmlAndJs($document)
    {

        $document = trim($document);
        if (strlen($document) <= 0) {
            return $document;
        }
        $search = array("'<script[^>]*?>.*?</script>'si", // 去掉 javascript
            "'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记
            "'([\r\n])[\s]+'", // 去掉空白字符
            "'&(quot|#34);'i", // 替换 HTML 实体
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i"
        );
        $replace = array('',
            '',
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " "
        );
        return preg_replace($search, $replace, $document);
    }

    /**
     *  trim字符连接串
     * @param string $str 字符串
     * @param string $splitter 连接符
     * @param bool $filter_empty 是否过滤空字符
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function trimSplitter(string $str, string $splitter = ',', bool $filter_empty): string
    {

        $arr = explode($splitter, $str);
        array_walk($arr, function (&$value) use (&$result) {
            $value = trim($value);
        });
        if ($filter_empty) {
            $arr = array_filter($arr);
        }
        return implode($splitter, $arr);

    }

    /**
     * 匹配出内容的所有图片
     * @param $content
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getImages($content): array
    {
        preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $content, $matches);
        if (empty($matches[0])) {
            return [];
        }
        $imgs = [];
        foreach ($matches[0] as $key => $match) {
            $imgs[] = ['html' => $matches[0][$key], 'src' => $matches[2][$key]];
        }
        return $imgs;
    }

    /**
     * 隐藏星号(脱敏)
     * @param string $str 原始字符串
     * @param bool $is_pwd 是否是密码
     * @param int $start 初始保留长度
     * @param int $end 末尾保留长度
     * @return string
     * @change 倪增超 修改了$start 和 $end 的逻辑, 可以设置任意头尾数, 并且脱敏后字符串长度保持不变
     * @author 青岛开店星信息技术有限公司
     */
    public static function secret(string $str, bool $is_pwd = true, int $start = 1, int $end = 1): string
    {
        if ($start < 1 && $end < 1) {
            return $str;
        }

        //获取字符串长度
        $strlen = mb_strlen($str, 'utf-8');
        $startEndSum = $start + $end;

        //如果字符创长度<=startEndSum，不做任何处理
        if ($strlen <= $startEndSum) {
            if (empty($str)) {
                return '';
            }
            return $is_pwd ? str_repeat('*', $startEndSum > $strlen ? $strlen : $startEndSum) : $str;
        } else {
            //mb_substr — 获取字符串的部分
            $firstStr = mb_substr($str, 0, $start, 'utf-8');
            $lastStr = mb_substr($str, -1 * $end, $end, 'utf-8');
            //str_repeat — 重复一个字符串
            return $firstStr . str_repeat('*', $strlen - $startEndSum) . $lastStr;
        }
    }

    /**
     * 脱敏字符串与其它字符串一致性对比
     * @param string $secretStr 脱敏过的字符串, 必须带*星号
     * @param string $compareStr 需对比的字符串
     * @return bool
     * @author nizengchao
     * @date 2021/6/30
     */
    public static function secretCompare(string $secretStr = '', string $compareStr = ''): bool
    {
        $same = false;
        $secretStrLen = self::length($secretStr);
        $compareStrLen = self::length($compareStr);

        if (mb_substr_count($secretStr, '*') < 1) {
            return $same;
        }

        // 空串, 两个串长度不一致, 返回false
        if (empty($secretStr) || empty($compareStr) || $secretStrLen != $compareStrLen) {
            return $same;
        }

        // 全是*号 时, 直接返回true
        if (mb_substr_count($secretStr, '*') == $secretStrLen) {
            $same = true;
            return $same;
        }

        // 截取脱敏字符串, 头, 中 ,尾各部分
        $pattern = '/([^*]*)(\**)([^*]*)/';
        preg_match($pattern, $secretStr, $matches);
        if (count($matches) == 0 || !isset($matches[1]) || !isset($matches[3])) {
            return $same;
        }

        // 获取对比字符串相同的部分
        // 头串长度
        $matche1Len = self::length($matches[1]);
        // 尾串长度
        $matche3Len = self::length($matches[3]);
        // 对比串的头
        $compareStrFirst = mb_substr($secretStr, 0, $matche1Len);
        // 对比串的尾
        $compareStrLast = $matche3Len > 0 ? mb_substr($secretStr, -$matche3Len) : '';

        // 对比头尾处相似度
        if ($matches[1] != $compareStrFirst || $matches[3] != $compareStrLast) {
            return $same;
        }

        return true;
    }

    /**
     * 数据脱敏
     * @param array $data 需脱敏的数据
     * @param array $keys 需脱敏的数据的key
     * @param int $start 脱敏串开始预留
     * @param int $end 脱敏串结束预留
     * @return array
     * @author nizengchao
     */
    public static function doSecret(array $data = [], array $keys = [], int $start = 5, int $end = 5): array
    {
        array_walk($data, function (&$v, $k) use ($keys, $start, $end) {
            in_array($k, $keys) && $v = self::secret($v, true, $start, $end);
        });
        return $data;
    }

    /**
     * 数据脱敏 - 二维数组
     * @param array $data 需脱敏数据, 二维
     * @param array $keys 脱敏keys , 二维
     * @param int $start
     * @param int $end
     * @return array
     * @author nizengchao
     */
    public static function doSecretArray(array $data = [], array $keys = [], int $start = 5, int $end = 5): array
    {
        if (empty($data) || empty($keys)) {
            return $data;
        }
        foreach ($data as $arrayKey => $arrayItem) {
            if (empty($arrayItem) || !is_array($arrayItem) || !isset($keys[$arrayKey])) {
                continue;
            }
            foreach ($arrayItem as $itemKey => $item) {
                if (empty($item) || !in_array($itemKey, $keys[$arrayKey])) {
                    continue;
                }

                // 去敏, 并存储去敏与原始数据的映射
                $data[$arrayKey][$itemKey] = self::secret($item, true, $start, $end);
            }
        }

        return $data;
    }

    /**
     * 对比脱敏数据
     * @param array $originData 原始数据
     * @param array $data 脱敏后数据
     * @param array $keys 需对比的数据key
     * @return array
     * @author nizengchao
     */
    public static function compareSecret(array $originData = [], array $data = [], array $keys = []): array
    {
        if (empty($data)) {
            return error('参数不能为空');
        }

        array_walk($data, function (&$v, $k) use ($keys, $originData) {
            // 包含*号, 强制使用原数据
            if (mb_substr_count($v, '*')) {
                $v = $originData[$k];
            } else {
                // 数据不一致使用新数据 一致使用原数据
                $same = false;
                in_array($k, $keys) && $originData[$k] && $same = self::secretCompare($v, $originData[$k]);
                $v = $same ? $originData[$k] : $v;
            }
        });

        return $data;
    }

    /**
     * 对比脱敏数据 - 二维数组
     * @param array $originData 原始数据 二维数组
     * @param array $data 脱敏后数据 二维数组
     * @param array $keys 需对比的数据key 二维数组
     * @return array
     * @author nizengchao
     */
    public static function compareSecretArray(array $originData = [], array $data = [], array $keys = []): array
    {

        if (empty($data) || empty($keys)) {
            return $data;
        }
        foreach ($data as $arrayKey => $arrayItem) {
            if (empty($arrayItem) || !is_array($arrayItem) || !isset($keys[$arrayKey])) {
                continue;
            }
            foreach ($arrayItem as $itemKey => $item) {
                if (empty($item) || !in_array($itemKey, $keys[$arrayKey])) {
                    continue;
                }

                // 去敏, 并存储去敏与原始数据的映射
                $data[$arrayKey][$itemKey] = !mb_substr_count($item, '*') && !StringHelper::secretCompare($item, $originData[$arrayKey][$itemKey]) ? $item : $originData[$arrayKey][$itemKey];
            }
        }

        return $data;
    }

    /**
     * @param $str
     * @param int $decimals
     * @return float|string
     * @author 青岛开店星信息技术有限公司
     */
    static function numberPrice($str, $decimals = 2)
    {
        $str = (float)$str;
        if (self::exists($str, '.')) {
            $array = explode('.', $str);
            if (!isset($array[1]) || empty($array[1])) {
                return number_format($array[0], $decimals);
            }
            return round($str, $decimals);
        }
        return round($str, $decimals);
    }

    /**
     * 对字符串执行指定次数替换
     * @param Mixed $search 查找目标值
     * @param Mixed $replace 替换值
     * @param Mixed $subject 执行替换的字符串／数组
     * @param Int $limit 允许替换的次数，默认为-1，不限次数
     * @return Mixed
     */
    static function replaceAll($search, $replace, $subject, int $limit = -1)
    {

        if (is_array($search)) {
            foreach ($search as $k => $v) {
                $search[$k] = '`' . preg_quote($search[$k], '`') . '`';
            }
        } else {
            $search = '`' . preg_quote($search, '`') . '`';
        }
        return preg_replace($search, $replace, $subject, $limit);
    }


    /**
     * 简单加密 数字|字符串
     * @param $val
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function encode($val)
    {
        return \Yii::$app->hashids->encodeHex($val);
    }


    /**
     * 解密
     * @param $hex
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function decode($hex)
    {
        return \Yii::$app->hashids->decodeHex($hex);
    }

    /**
     * 处理日志内容
     * @param $log
     * @return array
     * @author likexin
     */
    public static function processLog($log)
    {
        $type = substr($log, 1, 1);
        $content = substr($log, 3);
        return ['type' => $type, 'content' => $content];
    }

    /**
     * 根据字符转驼峰
     * @param $uncamelized_words
     * @param string $separator
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ucwords(ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator));
    }

    /**
     * 判断是否是JSON
     * @param $string
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 保存图片处理
     * @param string $detail
     * @param string $attachmentUrl
     * @return array|string|string[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function htmlImages(string $detail = '', string $attachmentUrl = '')
    {
        $detail = htmlspecialchars_decode($detail);
        preg_match_all("/<img.*?src=[\\\'| \\\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg]?))[\\\'|\\\"].*?[\/]?>/", $detail, $imgs);

        $images = array();
        if (isset($imgs[1])) {

            foreach ($imgs[1] as $img) {
                $im = array(
                    "old" => $img,
                    "new" => str_replace($attachmentUrl, '', $img)
                );
                $images[] = $im;
            }
        }

        foreach ($images as $img) {
            $detail = str_replace($img['old'], $img['new'], $detail);
        }

        return $detail;
    }

    /**
     * 商品详情图片转化为图片链接
     * @param string $detail
     * @param string $attachmentUrl
     * @return mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function htmlToImages(string $detail = '', string $attachmentUrl = '')
    {
        $detail = htmlspecialchars_decode($detail);
        preg_match_all("/<img.*?src=[\\\'| \\\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg]?))[\\\'|\\\"].*?[\/]?>/", $detail, $imgs);
        $images = array();
        if (isset($imgs[1])) {
            foreach ($imgs[1] as $img) {
                $im = array(
                    "old" => $img,
                    "new" => ValueHelper::isUrl($img) ? $img : $attachmentUrl . $img
                );
                $images[] = $im;
            }
        }
        $count = 1;
        foreach ($images as $img) {
            $detail = str_replace($img['old'], $img['new'], $detail, $count);
        }

        if ($count > 1) {
            // 清除多余一次的替换
            $replaceStr = str_repeat($attachmentUrl, $count);
            $detail = str_replace($replaceStr, $attachmentUrl, $detail);
        }
        // 本地视频
        $detail = self::htmlToVideo($detail, $attachmentUrl);
        // 处理富文本中tx视频
        $detail = VideoHelper::parseRichTextTententVideo($detail);

        return $detail;
    }

    /**
     * base64校验
     * @param $str
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function base64($str)
    {
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }


    /**
     * 返回16位md5值
     * 不要怀疑碰撞概率，截取后的还是均匀分布，碰撞的概率要看你有多少数据了
     * @param string $str 字符串
     * @return string $str 返回16位的字符串
     * @author 青岛开店星信息技术有限公司
     */
    public static function shortMd5($str)
    {
        return substr(md5($str), 8, 16);
    }

    /**
     * 前导零
     * @param int $data
     * @param int $num
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function replenishZero(int $data, int $num)
    {
        return str_pad($data, $num, "0", STR_PAD_LEFT);
    }

    /**
     * 处理本地视频 拼接链接
     * @param string $detail
     * @param string $attachmentUrl
     * @return mixed|string
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function htmlToVideo(string $detail = '', string $attachmentUrl = '')
    {
        $detail = htmlspecialchars_decode($detail);
        preg_match_all("/<video.*?src=[\\\'| \\\"](.*?(?:[\.mp4]?))[\\\'|\\\"].*?[\/]?>/", $detail, $videos);
        $newVideos = array();
        if (isset($videos[1])) {
            foreach ($videos[1] as $video) {
                $vi = array(
                    "old" => $video,
                    "new" => ValueHelper::isUrl($video) ? $video : $attachmentUrl . $video
                );
                $newVideos[] = $vi;
            }
        }
        $count = 1;
        foreach ($newVideos as $video) {
            $detail = str_replace($video['old'], $video['new'], $detail, $count);
        }

        if ($count > 1) {
            // 清除多余一次的替换
            $replaceStr = str_repeat($attachmentUrl, $count);
            $detail = str_replace($replaceStr, $attachmentUrl, $detail);
        }

        return $detail;
    }
}
