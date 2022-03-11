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

namespace shopstar\admin\byteDance;

use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;
use shopstar\models\byteDance\ByteDanceUploadLogModel;
use shopstar\bases\KdxAdminApiController;

class IndexController extends KdxAdminApiController
{
    
    /**
     * 获取设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSetting()
    {
        $data = ShopSettings::get('channel_setting.byte_dance');
        
        return $this->result(['data' => $data]);
    }
    
    /**
     * 设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetSetting()
    {
        $data = [
            'appid' => RequestHelper::post('appid'),
            'app_secret' => RequestHelper::post('app_secret'),
            'maintain' => RequestHelper::post('maintain'),
            'maintain_explain' => RequestHelper::post('maintain_explain'),
            'show_commission' => RequestHelper::post('show_commission'),
        ];
        ShopSettings::set('channel.byte_dance', 1);
        ShopSettings::set('channel_setting.byte_dance', $data);
        
        return $this->success();
    }
    
}