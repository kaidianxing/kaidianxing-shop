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

namespace shopstar\admin\material;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\material\MaterialLogConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

class SettingController extends KdxAdminApiController
{
    /**
     * 获取基础设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSetting()
    {
        $setting = ShopSettings::get('material');

        return $this->result(['data' => $setting]);
    }


    /**
     * 设置基础设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetSetting()
    {
        $post = RequestHelper::post();
        $data = [
            'status' => (string)$post['status'] ?? 0,
        ];

        ShopSettings::set('material', $data);

        // 添加日志
        LogModel::write(
            $this->userId,
            MaterialLogConstant::BASIC_SETTING,
            MaterialLogConstant::getText(MaterialLogConstant::BASIC_SETTING),
            1,
            [
                'log_data' => [
                    'status' => (int)$post['status'],
                ],
                'log_primary' => [
                    '应用' => (int)$post['status'] == 1 ? '开启' : '关闭',
                ],
                'dirty_identify_code' => [
                    MaterialLogConstant::BASIC_SETTING,
                ]
            ]
        );

        return $this->success();
    }
}
