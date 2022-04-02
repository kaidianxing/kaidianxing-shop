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

use shopstar\bases\module\BaseModule;

/**
 * 商城模块配置()
 * Class KdxModule
 * @package shopstar\config\modules
 * @author 青岛开店星信息技术有限公司
 */
class KdxModule extends BaseModule
{

    /**
     * 初始化商城模块
     * @author likexin
     */
    public function init()
    {
        parent::init();

        // 设置控制器命名空间
        $this->setControllerNamespace('shopstar\controllers', false);

        // 设置视图路径
        $this->setViewPath('@shopstar/views');

        // 注册API模块
        $this->setModule('api', [
            'class' => KdxApiModule::class,
            'isMobile' => $this->isMobile(),
        ]);

        parent::init();
    }


    /**
     * 是否手机端
     * @return bool
     * @author likexin
     */
    private function isMobile(): bool
    {
        // 如果标记了自定义域名
        if (\Yii::$app->params['isWapCustomDomain']) {
            return true;
        }
        $routes = explode('/', \Yii::$app->request->pathInfo);
        return isset($routes[0]) && $routes[0] === 'h5';
    }

}