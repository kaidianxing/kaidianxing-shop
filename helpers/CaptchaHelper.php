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

/**
 * 验证码助手类
 * Class CaptchaHelper
 * @package shopstar\helpers
 */
class CaptchaHelper
{

    protected static $code;//验证码
    protected static $img;//图形资源句柄
    protected static $params;//参数

    //生成随机码
    protected static function createCode()
    {
        $_len = strlen(self::$params['chars']) - 1;
        for ($i = 0; $i < self::$params['length']; $i++) {
            self::$code .= self::$params['chars'][mt_rand(0, $_len)];
        }
        \Yii::$app->redis->setex('captcha_code_' . self::$params['sessionId'] . self::$params['type'], 120, strtolower(self::$code));
    }

    //生成背景
    protected static function createBg()
    {
        self::$img = imagecreatetruecolor(self::$params['width'], self::$params['height']);
//        $color = imagecolorallocate(self::$img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        $color = imagecolorallocate(self::$img, 255, 255, 255);
        imagefilledrectangle(self::$img, 0, self::$params['height'], self::$params['width'], 0, $color);
    }

    //生成文字
    protected static function createFont()
    {
        $font = Yii::getAlias('@static') . '/fonts/msyh.ttf';
        $_x = self::$params['width'] / self::$params['length'];
        for ($i = 0; $i < self::$params['length']; $i++) {
            $fontcolor = imagecolorallocate(self::$img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext(self::$img, self::$params['size'], mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), self::$params['height'] / 1.4, $fontcolor, $font, self::$code[$i]);
        }
    }

    //生成线条、雪花
    protected static function createLine()
    {
        //线条
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate(self::$img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline(self::$img, mt_rand(0, self::$params['width']), mt_rand(0, self::$params['height']), mt_rand(0, self::$params['width']), mt_rand(0, self::$params['height']), $color);
        }
        //雪花
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate(self::$img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring(self::$img, mt_rand(1, 5), mt_rand(0, self::$params['width']), mt_rand(0, self::$params['height']), '*', $color);
        }
    }

    /**
     * 生成验证码
     * @param array $params
     * @param int $type
     * @param string $sessionId
     */
    public static function create($params = [], $type = 1)
    {
        $chars = '';
        if ($type == 1) {
            $chars = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
        } else if ($type == 2) {
            $chars = '1234567890';
        }
        self::$params = array_merge([
            'type' => '',
            'chars' => $chars,
            'length' => 4,
            'width' => 120,
            'height' => 50,
            'size' => 20
        ], $params);
        if ((int)self::$params['width'] <= 0) {
            self::$params['width'] = 120;
        }
        if ((int)self::$params['height'] <= 0) {
            self::$params['height'] = 50;
        }
        self::createBg();
        self::createCode();
        self::createLine();
        self::createFont();
        header('Content-type:image/png');
        imagepng(self::$img);
        imagedestroy(self::$img);
        exit();
    }

    /**
     * 检测验证码
     * @param $verifyCode
     * @param string $identity
     * @return bool
     */
    public static function check($captchaCode, $sessionId = '')
    {
        $cacheCode = \Yii::$app->redis->get('captcha_code_' . $sessionId);
        if (empty($cacheCode) || empty($captchaCode)) {
            return false;
        }
        return strtolower($captchaCode) == strtolower($cacheCode);
    }

    /**
     * 删除验证码
     * @param string $type
     * @return bool
     * @author blue
     */
    public static function remove($type = '')
    {
//        LocalSession::remove('captcha_code_' . $type);
        return true;
    }


}
