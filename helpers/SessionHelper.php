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
 * 会话助手类
 * Class SessionHelper
 * @package shopstar\helpers
 */
class SessionHelper
{

    /**
     * 获取Session
     * @param null $key
     * @param null $default_value
     * @return mixed|null
     * @author liyang
     */
    public static function get($key = null, $default_value = null)
    {
        $session = \Yii::$app->getSession()->get($key);
        if(is_array($session) && isset($session['value'])){
            if( $session['expire']==0 || ( $session['expire']>0 && $session['expire'] >= time())){
                return $session['value'];
            }
        }
        return  $default_value;
    }

    /**
     * 设置session
     * @param string $key
     * @param string|array $value
     * @param int $expire
     * @return mixed
     * @author liyang
     */
    public static function set($key, $value,$expire = 0)
    {
        if( $expire <0) {
            return static::remove($key);
        }
        $value=[
            'value'=>$value,
            'expire'=> $expire != 0 ? time() + $expire : 0
        ];
        return \Yii::$app->getSession()->set($key, $value);
    }

    /**
     * $session 是否存在 key
     * @param $key
     * @return bool
     * @author liyang
     */
    public static function has($key)
    {
        $session = static::get($key);
        if (is_null($session) || !is_array($session)) {
            return false;
        }
        if ($session['expire'] > time()) {
            static::remove($key);
            return false;
        }
        return true;
    }

    /**
     * @param $key
     * @return mixed
     * @author liyang
     */
    public static function remove($key)
    {
        return \Yii::$app->getSession()->remove($key);
    }

    /**
     * 清空 Cookies
     * @param $key
     */
    public static function removeAll()
    {
        \Yii::$app->getSession()->removeAll();
    }

}