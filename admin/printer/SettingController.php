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

namespace shopstar\admin\printer;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\constants\printer\PrinterTypeConstant;
use shopstar\models\shop\ShopSettings;

/**
 * 小票打印配置
 * Class SettingController
 * @package apps\printer\manage
 */
class SettingController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'get-type',
            'get-scene'
        ]
    ];

    /**
     * 获取打印机类型
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetType()
    {
        $printerType = PrinterTypeConstant::getAllColumnFixedIndex('text');

        $returnData = [];
        foreach ($printerType as $value => $name) {
            $returnData[] = [
                'name' => $name,
                'value' => $value
            ];
        }

        return $this->result(['data' => $returnData]);
    }

    /**
     * 获取打印场景
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetScene()
    {
        $printerScene = PrinterSceneConstant::getAllColumnFixedIndex('text');

        $returnData = [];
        foreach ($printerScene as $value => $name) {
            $returnData[] = [
                'name' => $name,
                'value' => $value
            ];
        }

        return $this->result(['data' => $returnData]);
    }

    /**
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGetDelivery()
    {
        $data = [];
        $setting = ShopSettings::get('dispatch');
        $verifySetting = ShopSettings::get('verify.base_setting.verify_is_open');

        if ($setting['express']['enable']) {
            $data['delivery'][] = 'express';
        }

        if ($setting['express']['intracity']) {
            $data['delivery'][] = 'intracity';
        }

        if ($verifySetting) {
            $data['delivery'][] = 'verify';
        }

        return $this->result($data);
    }

}