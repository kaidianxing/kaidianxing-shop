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


namespace shopstar\admin\expressHelper;


use shopstar\bases\KdxAdminApiController;
use shopstar\constants\expressHelper\ExpressHelperLogConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;

/**
 * 设置参数
 * Class IndexController
 * @author 青岛开店星信息技术有限公司
 * @package apps\expressHelper\manage
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 设置参数
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $data = RequestHelper::post();
        ShopSettings::set('plugin_express_helper.express.kdn', $data);

        //写入日志
        LogModel::write(
            $this->userId,
            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_BASE_SET,
            ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_BASE_SET),
            0,
            [
                'log_data' => [],
                'log_primary' => [
                    'appid' => $data['appid'],
                    'key' => $data['key']
                ],
            ]
        );
        return $this->success();
    }

    /**
     * 获取参数
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $result = ShopSettings::get('plugin_express_helper.express.kdn');

        return $this->success($result);
    }
}
