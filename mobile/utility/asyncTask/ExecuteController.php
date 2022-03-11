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

namespace shopstar\mobile\utility\asyncTask;

ignore_user_abort(); //忽略关闭浏览器
set_time_limit(0); //永远执行

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\RequestHelper;
use shopstar\services\utility\AsyncTaskExecuteService;

/**
 * 客户端执行
 * Class ExecuteController
 * @package modules\utility\client\asyncTask
 */
class ExecuteController extends BaseMobileApiController
{
    public $configActions = [
        'allowActions' => ['*'],   // 允许不登录访问的Actions
        'allowSessionActions' => ['api', 'index'],
        'allowClientActions' => ['api', 'index'],
    ];

    /**
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        AsyncTaskExecuteService::singleShop();
        return $this->success();
    }

    /**
     * API调用
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionApi()
    {
        $apiKey = RequestHelper::get('api_key');
        if (empty($apiKey)) {
            return $this->error('错误的请求');
        }

        // 执行所有的店铺
        AsyncTaskExecuteService::apiShop($apiKey);

        return $this->success();
    }

}