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

namespace shopstar\bases\controller;

use yii\web\Controller;

/**
 * 控制器最底层基类，一般不在此层级写逻辑
 * Class BaseController
 * @package shopstar\bases\controller
 * @author 青岛开店星信息技术有限公司
 */
class BaseController extends Controller
{

    /**
     * @var bool 禁用Layout
     */
    public $layout = false;

    public $configActions = [
        'postActions' => [],  // 需要post请求
        'allowActions'  => [],  // 允许不登录访问的Actions
        'allowSessionActions'  => [],  // 允许不携带Session头访问
        'allowClientActions'  => [],   // 检查 Client-Type
        'allowHeaderActions'  => [],  // 允许Get代替Header参数
        'allowPermActions' => [],  // 允许-不进行role 权限检查

        'needBindMobileActions' => [],  // 需要绑定手机
        'allowNotLoginActions' => [],   // 允许不登录用户访问
        'allowShopCloseActions' => [],  // 店铺关闭允许访问

    ];

    /**
     * 重写逻辑，可省略view
     * @param string $view
     * @param array $params
     * @return string
     * @author likexin
     */
    public function render($view = '', $params = []): string
    {
        if (is_array($view) || empty($view)) {
            if (is_array($view)) {
                $params = $view;
            }
            $view = $this->action->id;
        }

        if (strpos($view, '.twig') == false && strpos($view, '.php') == false) {
            $view .= '.php';
        }

        return parent::render($view, $params);
    }

}