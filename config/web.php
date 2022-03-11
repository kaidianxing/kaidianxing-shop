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

$mainConfig = require 'main.php';

$config = [
    'id' => 'shopstar-web',
    'controllerNamespace' => 'shopstar\controllers',
    'bootstrap' => [],
    'components' => array_merge([
        /**
         * @var array Session配置
         */
        'session' => [
            'class' => 'yii\web\Session'
        ],

        /**
         * @var array URL美化
         */
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'shopstar\config\modules\KdxUrlRuleConfig',
                ],
            ]
        ],

        /**
         * @var array 请求
         */
        'request' => [
            'csrfParam' => '_csrf-web',
            'cookieValidationKey' => 'kdx-web',
            'trustedHosts' => isset($localConfig['request']) && !empty($localConfig['request']['trustedHosts']) ? $localConfig['request']['trustedHosts'] : [],   // yii安全机制，允许反向代理获取请求协议的hosts
        ],
    ], require 'components.php'),

    /**
     * @var array 注册模块
     */
    'modules' => require 'modules.php',
];

// Yii Debug
if (YII_DEBUG) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ["*"],
        'panels' => [],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ["*"],
    ];
    $config['aliases'] = [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ];
}

return \yii\helpers\ArrayHelper::merge($mainConfig, $config);