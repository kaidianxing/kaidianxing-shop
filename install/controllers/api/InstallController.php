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
use install\services\InstallService;
use shopstar\config\modules\KdxInitialize;
use yii\web\Response;

/**
 * 安装相关操作接口
 * Class UserController
 * @package install\controllers\api
 * @author likexin
 */
class InstallController extends BaseController
{

    /**
     * @var array Controller配置
     */
    public array $config = [
        // 需要POST请求的Actions
        'postActions' => [
            'start',
        ],
    ];

    /**
     * 创建数据表
     * @return Response
     */
    public function actionCreateTableStruct(): Response
    {
        // ->service
        $result = InstallService::createTableStruct();

        return $this->result($result);
    }

    /**
     * 创建默认数据
     * @return Response
     */
    public function actionCreateDefaultData(): Response
    {
        // ->service
        $result = InstallService::createDefaultData();

        // 初始化默认数据
        if (!is_error($result)) {
            KdxInitialize::init();
        }

        return $this->result($result);
    }

    /**
     * 注册站点
     * @return Response
     * @author likexin
     */
    public function actionRegisterSite(): Response
    {
        // ->service
        $result = InstallService::registerSite();

        return $this->result($result);
    }

}