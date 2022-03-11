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

use shopstar\bases\controller\BaseViewController;
use shopstar\helpers\SessionHelper;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * 店铺商家端入口
 * Class ShopController
 * @package shop\controllers
 */
class AdminController extends BaseViewController
{

    /**
     * 入口
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function actionIndex()
    {
        $baseUrl = Url::base(true);

        $params = [
            'base_url' => $baseUrl . '/api',
            'attachment_url' => CoreAttachmentService::getRoot(),
            'public_url' => $baseUrl . '/',
            'wap_dist_url' => $baseUrl . '/static/dist/shop/kdx_wap/',
            'route' => 'history',
        ];

        // 传递前端登录user_session
        $userSession = SessionHelper::get('user_session');
        if (!empty($userSession)) {
            $params['user_session'] = $userSession;
        }

        return $this->render([
            'settingsJson' => Json::encode($params),
        ]);
    }

}