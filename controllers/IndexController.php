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

namespace shopstar\controllers;

use yii\web\Controller;

/**
 * 控制器首页
 * Class IndexController
 * @package shopstar\controllers
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends Controller
{

    /**
     * @var bool
     */
    public $layout = false;

    /**
     * @author likexin
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (!SHOP_STAR_IS_INSTALLED) {
            // 判断安装模块是否存在
            if (!is_file(SHOP_STAR_PATH . '/install/Module.php')) {
                return $this->render('install');
            }

            return $this->redirect('/install');
        }

        return $this->redirect('/admin');
    }

}
