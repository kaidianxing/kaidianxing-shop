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

namespace shopstar\admin\system\apps;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\CloudServiceHelper;
use shopstar\helpers\LiYangHelper;
use shopstar\helpers\RequestHelper;
use shopstar\services\core\CoreAppService;

/**
 * 获取未安装应用列表
 * Class CreditController
 * @package app\controllers\manage\sysset
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 未安装应用列表
     * @param string $keywords
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGetList()
    {
        $get = RequestHelper::get();
        $keywords = $get['keywords'];
        // 调用云端
        $result = CloudServiceHelper::post(LiYangHelper::ROUTER_APP_LIST_NOT_INSTALL, [
            'local_app_identity' => CoreAppService::getAppIdentity(), // 获取本地安装的应用标识
            'keywords' => $keywords,
        ]);
        if (is_error($result)) {
            return $this->result($result);
        }

        if (!empty($result['list'])) {
            array_walk($result['list'], function (&$row) {
                $row['sales'] = '-';

                /* @change likexin 云端接口返回，本地无需处理
                 * // 定义可以安装
                 * $row['can_install'] = true;
                 * // 判断系统版本要求
                 * if (!empty($row['system_version'])) {
                 * $row['can_install'] = version_compare($row['system_version'], SHOP_STAR_VERSION, '<=');
                 * }*/
            });
        }

        return $this->result($result);
    }
}