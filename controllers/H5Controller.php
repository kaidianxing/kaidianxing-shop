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
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\shop\ShopSettings;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * 店铺WAP端入口
 * Class H5Controller
 * @package shopstar\controllers
 * @author 青岛开店星信息技术有限公司
 */
class H5Controller extends BaseViewController
{

    /**
     * 入口
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function actionIndex(): string
    {
        $baseUrl = Url::base(true);

        $params = [
            'base_url' => $baseUrl . "/h5/api",
            'attachment_url' => CoreAttachmentService::getRoot(),
            'public_url' => $baseUrl . '/',
            'wap_dist_url' => $baseUrl . '/static/dist/shop/kdx_wap/',
            'wap_url' => ShopUrlHelper::wap('', [], true),
        ];

        return $this->render([
            'settingsJson' => Json::encode($params),
            'settingsArray' => $params,

            // 全局代码统计
            'globalCode' => ShopSettings::get('sysset.mall.basic.global_code') ?: '',
        ]);
    }

    /**
     * 中转跳转页
     * @return \yii\web\Response
     */
    public function actionRedirect(): \yii\web\Response
    {
        $redirectUrl = RequestHelper::get('redirect_url');
        if (empty($redirectUrl)) {
            die('访问错误');
        }

        return $this->redirect(urldecode($redirectUrl));
    }

}
