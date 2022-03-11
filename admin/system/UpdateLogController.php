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

namespace shopstar\admin\system;

use shopstar\bases\KdxAdminApiController;
use modules\admin\services\system\UpgradeService;
use shopstar\helpers\CloudServiceHelper;
use shopstar\helpers\LiYangHelper;
use shopstar\helpers\RequestHelper;

/**
 * 积分余额设置
 * Class CreditController
 * @package app\controllers\manage\sysset
 */
class UpdateLogController extends KdxAdminApiController
{

    /**
     * 读取列表
     * @return array|\yii\web\Response
     * @throws \shopstar\exceptions\core\CloudServiceException
     * @author likexin
     */
    public function actionGetList()
    {
        $result = CloudServiceHelper::get(LiYangHelper::ROUTE_SYSTEM_UPGRADE_GET_LOG_LIST, [
            'page' => RequestHelper::getInt('page', 1),
            'page_size' => RequestHelper::getInt('page_size', 20),
        ]);

        return $this->result($result);
    }

    /**
     * 获取当前版本信息
     * @return array|\yii\web\Response
     * @throws \shopstar\exceptions\core\CloudServiceException
     * @author likexin
     */
    public function actionGetVersionLog()
    {
        $result = CloudServiceHelper::get(LiYangHelper::ROUTE_SYSTEM_UPGRADE_GET_VERSION_LOG);

        // 如果云端返回当前补丁未找到
        if (!is_error($result) && empty($result['version'])) {
            $result['version'] = [
                'version' => SHOP_STAR_VERSION . '(本地)',
                'release' => SHOP_STAR_RELEASE,
                'release_time' => SHOP_STAR_RELEASE,
                'logs_add' => [],
                'logs_optimize' => [],
                'logs_repair' => [],
            ];
        }


        return $this->result($result);
    }
}