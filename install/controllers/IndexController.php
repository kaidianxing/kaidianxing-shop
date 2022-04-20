<?php
/**
 * 开店星商城系统1.0
 * @author 青岛开店星信息技术有限公司
 * @copyright Copyright (c) 2015-2021 Qingdao ShopStar Information Technology Co., Ltd.
 * @link https://www.kaidianxing.com
 * @warning This is not a free software, please get the license before use.
 * @warning 这不是一个免费的软件，使用前请先获取正版授权。
 */

namespace install\controllers;

use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * 安装入口
 */
class IndexController extends Controller
{

    /**
     * 渲染视图
     * @return string
     */
    public function actionIndex(): string
    {
        $base = Url::base(true);

        $params = [
            'base_url' => $base . '/install/api/',
            'router' => 'history',
        ];

        // 渲染视图
        return $this->render('index', [
            'settingsJson' => Json::encode($params),
        ]);
    }

}