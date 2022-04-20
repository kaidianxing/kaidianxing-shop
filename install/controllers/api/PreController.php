<?php
/**
 * 开店星商城系统1.0
 * @author 青岛开店星信息技术有限公司
 * @copyright Copyright (c) 2015-2021 Qingdao ShopStar Information Technology Co., Ltd.
 * @link https://www.kaidianxing.com
 * @warning This is not a free software, please get the license before use.
 * @warning 这不是一个免费的软件，使用前请先获取正版授权。
 */

namespace install\controllers\api;

use install\bases\BaseController;
use install\services\CheckEnvService;
use shopstar\helpers\KdxCloudHelper;
use yii\web\Response;

/**
 * 安装接口
 * Class V2Controller
 * @package install\controllers\api
 * @author likexin
 */
class PreController extends BaseController
{

    /**
     * 启动页，加载广告
     * @return Response
     * @author likexin
     */
    public function actionInit(): Response
    {
        // ->kdx-cloud
        $settings = KdxCloudHelper::get('/install/index/init');

        return $this->result($settings);
    }

    /**
     * 检测运行环境
     * @return Response
     * @author likexin
     */
    public function actionCheckEnv(): Response
    {
        // ->service
        $check = CheckEnvService::check();

        return $this->result($check);
    }

}