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

/**
 * 加密助手
 * Class CryptHelper
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class CryptHelper
{

    /**
     * @param $password
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSaltPassword($password): string
    {
        return $password . 'free-kdx';
    }

    /**
     * 密码hash生成
     * @param string $password
     * @param array $options
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function passwordHash(string $password, array $options = []): string
    {
        $password = self::getSaltPassword($password);
        $options = array_merge([
            'cost' => 10
        ], $options);

        return password_hash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * 密码hash验证
     * @param string $password
     * @param string $hash
     * @return bool
     * @author likexin
     */
    public static function passwordVerify(string $password, string $hash): bool
    {
        $password = self::getSaltPassword($password);
        //sha512
        if (strlen($password) >= 128) {
            return hash_equals($hash, self::passwordHash($password));
        }

        // bcrypt
        return password_verify($password, $hash);
    }

    /**
     * 加解密
     * @param string $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return false|string
     * @author likexin
     */
    public static function encrypt(string $string, string $operation = 'ENCODE', string $key = '', int $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key != '' ? $key : "jd9n3an6");
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . '_' . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        $time = time();
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr((int)$result, 0, 10) - $time > 0) && substr($result, 11, 16) == substr(md5(substr($result, 27) . $keyb), 0, 16)) {
                return substr($result, 27);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

}