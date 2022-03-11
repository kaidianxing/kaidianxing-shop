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
use yii\helpers\Json;

/**
 * 数据值助手类
 * Class ValueHelper
 * @package shopstar\helpers
 */
class ValueHelper
{

    /**
     * 数据转义
     * @param $var
     * @return array|string
     * @author likexin
     */
    public static function stripslashes($var)
    {
        if (is_array($var)) {
            foreach ($var as $key => &$value) {
                $var[stripslashes($key)] = self::stripslashes($value);
            }
            unset($value);
            return $var;
        }
        return stripslashes(trim($var));
    }

    /**
     * html转义
     * @param string $var
     * @return array|string|string[]
     * @author likexin
     */
    public static function htmlspecialchars(string $var)
    {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[htmlspecialchars($key, ENT_QUOTES)] = self::htmlspecialchars($value);
            }
        } else {
            $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
        }
        return $var;
    }

    /**
     * 验证变量是否是全英文
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isEnglish(string $value)
    {
        if (preg_match("/^[A-Za-z]+$/", $value)) {
            return true;
        }
        return false;
    }

    /**
     * 验证变量是否是手机号
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isMobile(string $value): bool
    {
        if (preg_match("/^1[3456789]{1}\d{9}$/", $value)) {
            return true;
        }
        return false;
    }
    
    /**
     * 是否电话号  兼容座机
     * @param string $value
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isTel(string $value)
    {
        if (preg_match("/^((0\d{2,3}-\d{7,8})|(^1[3456789]{1}\d{9}$))/", $value)) {
            return true;
        }
        return false;
    }

    /**
     * 验证密码
     * @param string $value 密码值
     * @param int $level 密码等级
     * @return array|bool
     * @author likexin
     */
    public static function checkPassword(string $value, int $level = 2)
    {
        $length = strlen($value);

        if ($length < 8 || $length > 20) {
            return error('请填写8-20位密码');
        }

        $count = 0;

        // 正则匹配数字
        $matches = [];
        preg_match("/\d+/", $value, $matches);
        if (!empty($matches[0])) {
            $count++;
        }

        // 正则匹配字母
        $matches = [];
        preg_match("/[a-zA-Z]+/", $value, $matches);
        if (!empty($matches[0])) {
            $count++;
        }

        // 正则匹配标点
        $matches = [];
        preg_match("/[\_\#\@\$\^\%\*\&\!\~\+\-]+/", $value, $matches);
        if (!empty($matches[0])) {
            $count++;
        }

        if ($count < $level) {
            return error("英文、数字、标点符号至少输入{$level}种");
        }

        return true;
    }

    /**
     * 是否是400电话
     * 规则: 10位数字 400开头 第4位数字：只能是 016789 其中的一个
     * @param string $value
     * @param int $type 格式 1: 400-188-8888 2: 4001888888
     * @return bool
     * @author nizengchao
     */
    public static function is400(string $value, int $type = 1)
    {
        if ($type == 1) {
            $pattern = '/^400-[016789]\d{2}-\d{4}$/';
        } else {
            $pattern = '/^400[016789]\d{6}$/';
        }
        if (preg_match($pattern, $value)) {
            return true;
        }
        return false;
    }

    /**
     * 验证是否是座机号码(区号和分机号可不填)
     * @param string $value 变量
     * @param bool $needAreaCode 是否必须含区号
     * @return bool
     * @author likexin
     */
    static function isTelephone(string $value, bool $needAreaCode = true)
    {
        $area_code = '/^(0[0-9]{2,3}-)?';
        if ($needAreaCode) {
            $area_code = '/^(0[0-9]{2,3}-)';
        }
        $pattern = $area_code . '([2-9][0-9]{6,7})+(-[0-9]{1,4})?$/';
        if (preg_match($pattern, $value)) {
            return true;
        }
        return false;
    }

    /**
     * 验证变量是否是IP地址
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isIP(string $value): bool
    {
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/', $value)) {
            return true;
        }
        return false;
    }

    /**
     * 验证变量是否是邮箱地址
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isEmail(string $value): bool
    {
        if (empty($value)) {
            return false;
        }
        return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $value) ? true : false;
    }

    /**
     * 验证变量是否是URL链接
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isUrl(string $value)
    {
        if (empty($value)) {
            return false;
        }
        return preg_match('#(http|https|ftp|ftps)://([\w(-)]+\.)+[\w(-)]+(/[\w(-)./?%&=]*)?#i', $value) ? true : false;
    }

    /**
     * 验证变量是否是用户名格式(3-20位，非纯数字用户名)
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isUserName(string $value)
    {
        if (empty($value)) {
            return false;
        }
        return preg_match("/^[a-zA-Z]{1}([a-zA-Z0-9]|[._]){2,19}$/", $value) ? true : false;

    }

    /**
     * 验证变量是否是中文
     * @param string $value 变量
     * @param string $charset 编码（默认utf-8,支持gb2312）
     * @return bool
     * @author likexin
     */
    public static function isChinese(string $value, $charset = 'utf-8')
    {
        if (empty($value)) {
            return false;
        }
        $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/"
            : "/^[x{4e00}-x{9fa5}]+$/u";
        return preg_match($match, $value) ? true : false;
    }

    /**
     * 验证变量是否是UTF-8格式
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isUtf8(string $value)
    {
        if (empty($value)) {
            return false;
        }
        return (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $value)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $value)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $value)
            == true) ? true : false;
    }


    /**
     * 验证变量是否是日期格式
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isDate(string $value)
    {
        if (!StringHelper::exists($value, ['-', '/'], 'OR')) {
            return false;
        }

        $dateArr = explode(StringHelper::exists($value, '-') ? '-' : '/', $value);
        if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
            if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证变量是否是时间格式
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isTime(string $value)
    {
        $timeArr = explode(":", $value);
        if (is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2])) {
            if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 验证变量是否是DateTime格式 19XX-01-01 00:00:00
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isDateTime(string $value)
    {
        $date = substr($value, 0, 10);
        $isDate = self::isDate($date);
        $time = substr($value, 11, 8);
        $isTime = self::isTime($time);
        if ($isTime && $isDate) {
            return true;
        }
        return false;
    }

    /**
     * 验证中文姓名
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isChineseName(string $value)
    {
        if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证身份证号
     * @param string $value
     * @return bool
     * @author likexin
     */
    public static function isIdCardNo(string $value)
    {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $value)) return false;
        if (!in_array(substr($value, 0, 2), $vCity)) return false;
        $vStr = preg_replace('/[xX]$/i', 'a', $value);
        $vLength = strlen($vStr);
        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
            }
            if ($vSum % 11 != 1) return false;
        }
        return true;
    }

    /**
     * 判断变量是否是json格式
     * @param $string
     * @param bool $asArray
     * @return bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function isJson($string, $asArray = true)
    {
        try {
            $res = Json::decode($string, $asArray);
            return $res;
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * 变量转为json
     * @param $value
     * @param array $keys
     * @author likexin
     */
    public static function toJson(&$value, array $keys = [])
    {
        foreach ($keys as $key) {
            $ret = Json::decode($value[$key]);

            if (isset($value[$key]) && $ret !== false) {
                $value[$key] = $ret;
            }
        }
    }

    /**
     * 过滤小数点后的0
     * @param $value
     * @return float|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function delZero($value)
    {
        $value = (float)$value;
        $valueArray = explode('.', $value);
        if ($valueArray[1] == 0) {
            return $valueArray[0];
        }
        return $value;
    }
}