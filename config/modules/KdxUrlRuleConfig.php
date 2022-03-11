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
namespace shopstar\config\modules;

use yii\base\BaseObject;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;

/**
 * 商城URL规则配置类
 * Class KdxUrlRuleConfig
 * @package shop\config
 * @author likexin
 */
class KdxUrlRuleConfig extends BaseObject implements UrlRuleInterface
{

    /**
     * 匹配请求
     * @param UrlManager $manager
     * @param Request $request
     * @return array|bool
     * @author likexin
     */
    public function parseRequest($manager, $request)
    {
        // 匹配手机端地址
        $parseWap = $this->parseWapRequest($request);
        if ($parseWap) {
            return $parseWap;
        }

        // 匹配Pc端地址
        $parsePc = $this->parsePcRequest($request);
        if ($parsePc) {
            return $parsePc;
        }

        // 匹配商家端地址
        $parseShop = $this->parseShopRequest($request);
        if ($parseShop) {
            return $parseShop;
        }

        $parseShop = $this->parseShopH5Request($request);
        if ($parseShop) {
            return $parseShop;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function createUrl($manager, $route, $params)
    {
        return false;
    }

    /**
     * 匹配手机端请求
     * @param $request
     * @return array|bool
     * @author likexin
     */
    private function parseWapRequest(Request $request)
    {
        // 正则匹配 wap开头路由
        if (!preg_match('%^h5(.*?)$%', $request->pathInfo, $matches)) {
            return false;
        }
        if (count($matches) !== 2) {
            return false;
        }

        // 解析第二参数匹配
        $routes = explode('/', $matches[0]);

        // 解析数据去空
        $routes = array_values(array_filter($routes));

        // 如果第一个路由不是shop
        if ($routes[0] !== 'h5') {
            return false;
        }
        $params = [];
        // 如果第一层路由是api，转发api
        if (isset($routes[1]) && $routes[1] === 'api') {
            array_splice($routes, 0, 1);

            return ['kdx/' . implode('/', $routes), $params];
        }

        return ['kdx/h5', $params];
    }


    /**
     * 匹配手机端请求
     * @param $request
     * @return array|bool
     * @author likexin
     */
    private function parsePcRequest(Request $request)
    {
        // 正则匹配 pc 开头路由
        if (!preg_match('%^pc(.*?)$%', $request->pathInfo, $matches)) {
            return false;
        }
        if (count($matches) !== 2) {
            return false;
        }

        // 解析第二参数匹配
        $routes = explode('/', $matches[0]);

        // 解析数据去空
        $routes = array_values(array_filter($routes));

        // 如果第一个路由不是shop
        if ($routes[0] !== 'pc') {
            return false;
        }
        $params = [];
        // 如果第一层路由是api，转发api
        if (isset($routes[1]) && $routes[1] === 'api') {
            array_splice($routes, 0, 1);

            return ['kdx/' . implode('/', $routes), $params];
        }

        return ['kdx/pc', $params];


    }

    /**
     * 匹配请商家端请求
     * @param Request $request
     * @return array|bool
     * @author likexin
     */
    private function parseShopRequest(Request $request)
    {
        if (!preg_match('%^api(.*?)$%', $request->pathInfo, $matches)) {
            return false;
        }
        if (count($matches) !== 2) {
            return false;
        }

        // 解析第二参数匹配
        $routes = explode('/', $matches[0]);

        // 解析数据去空
        $routes = array_values(array_filter($routes));

        // 如果第一个路由不是shop
        if ($routes[0] !== 'api') {
            return false;
        }

        return [ 'kdx/' . implode('/', $routes) , []];
    }


    /**
     * 匹配请商家端请求
     * @param Request $request
     * @return array|bool
     * @author likexin
     */
    private function parseShopH5Request(Request $request)
    {
        if (defined('SHOP_STAR_IS_NOTIFY') && SHOP_STAR_IS_NOTIFY) {
            return ['', []];
        }

        if (SHOP_STAR_IS_INSTALLED) {
            // 其他情况默认转发商家端index
            return ['kdx/admin', []];
        }


        return false;
    }

}
