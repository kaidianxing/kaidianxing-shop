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

namespace shopstar\admin\utility;

use shopstar\models\core\CoreAddressModel;
use shopstar\bases\KdxAdminUtilityController;
use yii\helpers\Json;

/**
 * 系统地址控制器
 * Class AddressController
 * @package modules\utility\manage
 */
class AddressController extends KdxAdminUtilityController
{

    public $configActions = [
        'allowActions' => ['*'],  // 允许不登录访问
    ];

    /**
     * 获取地址库列表
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        $cache_key = 'areas_listall_manager';
        $result = \Yii::$app->cache->get($cache_key);
        if ($result === false || $result === null) {
            $result = CoreAddressModel::getResult();
            \Yii::$app->cache->set($cache_key, Json::encode($result), 86400 * 7);
        } else {
            $result = Json::decode($result);
        }
        return $this->result(['list' => $result]);
    }

    /**
     * 所有地址
     * @return array|int[]|\yii\web\Response
     * @author yuning
     */
    public function actionAddressAll()
    {
        $cache_key = 'areas_listall';
        $result = \Yii::$app->cache->get($cache_key);
        if ($result === false || $result === null) {
            $result = CoreAddressModel::getAll();
            \Yii::$app->cache->set($cache_key, Json::encode($result), 86400 * 7);
        } else {
            $result = Json::decode($result);
        }

        return $this->result(['result' => $result]);
    }





}