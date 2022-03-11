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


namespace shopstar\admin\channel;

use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;

/**
 * 注册设置
 * Class RegistrySettingController
 * @author 青岛开店星信息技术有限公司
 * @package shop\manage\channel
 */
class RegistrySettingController extends KdxAdminApiController
{
    /**
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $data = ShopSettings::get('channel_setting.registry_settings');

        return $this->result(['data' => $data]);
    }

    /**
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $data = RequestHelper::postArray('data');

        $bindMethod = ShopSettings::get('channel_setting.registry_settings');

        //合并数据
        $data['coerce_auth_channel'] = array_merge($bindMethod['coerce_auth_channel'] ?: [], $data['coerce_auth_channel']);

        //合并绑定场景数据
        $data['bind_scene'] = array_merge($bindMethod['bind_scene'] ?: [], $data['bind_scene']);

        ShopSettings::set('channel_setting.registry_settings', $data);

        return $this->success();
    }

}
