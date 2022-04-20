<?php
/**
 * 开店星商城系统1.0
 * @author 青岛开店星信息技术有限公司
 * @copyright Copyright (c) 2015-2021 Qingdao ShopStar Information Technology Co., Ltd.
 * @link https://www.kaidianxing.com
 * @warning This is not a free software, please get the license before use.
 * @warning 这不是一个免费的软件，使用前请先获取正版授权。
 */

namespace install;

/**
 * 安装模块类
 * Class Module
 * @package install
 * @author likexin
 */
class Module extends \yii\base\Module
{

    /**
     * @var string 默认路由
     */
    public $defaultRoute = 'index';

    /**
     * @var bool views禁用layout
     */
    public $layout = false;

    /**
     * 初始化模块
     * @author likexin
     */
    public function init()
    {

        // 设置控制器命名空间
        $this->controllerNamespace = 'install\controllers';

        // 设置视图路径
        $this->setViewPath(dirname(__DIR__) . '/install/views');

        parent::init();
    }

}