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

use yii\helpers\Url;

/**
 * 链接生成器 - 快速生成各个端的链接
 * Class ShopUrlHelper
 * @package shopstar\models
 * @author 青岛开店星信息技术有限公司
 */
class ShopUrlHelper
{

    /**
     * 生成Url
     * @param string $route 路由
     * @param array $params 参数
     * @param bool $scheme 是否长链接
     * @return string
     * @author likexin
     */
    public static function build(string $route, array $params = [], bool $scheme = false): string
    {
        $url = Url::base($scheme) . '/' . $route;
        // TODO 青岛开店星信息技术有限公司  放在获取渠道url那里了
//        /** @change likexin 手机端进入ios分享问题，必须是/wap/11/ 以斜杠结尾
        $url = trim($url, '/');
//        */
        if (!empty($params)) {
            // 如果url中包含?则追加&
            $url .= StringHelper::exists($url, '?') ? '&' : '?';
            $url .= http_build_query($params);
        }

        return $url;
    }

    /**
     * 客户端接口地址
     * @param string $route 接口路由
     * @param array $params 参数
     * @param bool $scheme 是否长链接
     * @return string
     * @author likexin
     */
    public static function client(string $route, array $params = [], bool $scheme = false): string
    {
        return self::build('client/' . rtrim($route, '/'), $params, $scheme);
    }

    /**
     * PC前端地址
     * @param string $route 前端路由
     * @param array $params 参数
     * @param bool $scheme 是否长链接
     * @return string
     * @author terry
     */
    public static function pc(string $route = '', array $params = [], bool $scheme = false): string
    {
        $route = trim($route, '/');
        return self::build('pc/' .  $route, $params, $scheme);
    }

    /**
     * 手机端前端地址
     * @param string $route 前端路由
     * @param array $params 参数
     * @param bool $scheme 是否长链接
     * @return string
     * @author likexin
     */
    public static function wap(string $route = '', array $params = [], bool $scheme = false): string
    {
        $route = trim($route, '/');
        return self::build('h5/' .  $route, $params, $scheme);
    }

    /**
     * 小程序端
     * @param string $path 小程序页面路径
     * @param array $params 页面参数
     * @return string
     * @author likexin
     */
    public static function wxapp(string $path, array $params = []): string
    {
        if (empty($params)) {
            return $path;
        }

        // 如果path中包含?则追加&
        $path .= StringHelper::exists($path, '?') ? '&' : '?';

        return $path . http_build_query($params);
    }

}