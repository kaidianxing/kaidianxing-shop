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


// 检测配置、读取本地配置
$localConfig = \shopstar\helpers\YamlHelper::loadFile(__DIR__ . '/conf.yaml');
if (empty($localConfig)) {
    die('配置加载失败，请检查 config/conf.yaml 配置文件');
}

// kdx-cli的配置挂载到params中
if (empty($localConfig['params'])) {
    $localConfig['params'] = [];
}
$localConfig['params']['kdx_cli'] = $localConfig['kdx_cli'] ?? [];

/**
 * @var array 数据库配置处理
 */
$database = [
    'class' => 'yii\db\Connection',
    'commandClass' => 'shopstar\bases\yii\DbCommand',
    'commandMap' => [
        'mysqli' => 'shopstar\bases\yii\DbCommand',
        'mysql' => 'shopstar\bases\yii\DbCommand',
    ],
    'dsn' => "mysql:host={$localConfig['mysql']['host']};port={$localConfig['mysql']['port']};dbname={$localConfig['mysql']['database']}",
    'username' => $localConfig['mysql']['username'],
    'password' => $localConfig['mysql']['password'],
    'tablePrefix' => 'shopstar_',
    'charset' => 'utf8mb4',
    'slaves' => (array)$localConfig['mysql']['slaves'],
    'enableSchemaCache' => false,
];

// 处理数据库主从
if (!empty($localConfig['mysql']['slaves'])) {
    $database['slaveConfig'] = [
        'attributes' => [
            \PDO::ATTR_TIMEOUT => 10,
        ],
    ];
    $database['slaves'] = [];
    foreach ($localConfig['mysql']['slaves'] as $slave) {
        $database['slaves'][] = ['dsn' => "mysql:host={$slave['host']};port={$slave['port']};dbname={$slave['database']}"];
    }
}

/**
 * @var array Yii的配置结构
 */
return [
    'id' => 'shopstar',
    'basePath' => SHOP_STAR_PATH,
    'bootstrap' => $localConfig['redis'] ? ['log', 'queue'] : ['log'],
    'defaultRoute' => 'index',
    'vendorPath' => SHOP_STAR_VENDOR_PATH,
    'controllerNamespace' => 'shopstar\controllers',
    'viewPath' => SHOP_STAR_PATH . '/shopstar/views',
    'components' => [

        /**
         * @var array Mysql配置
         */
        'db' => $localConfig['mysql'] ? $database : null,

        /**
         * @var array Redis配置
         */
        'redis' => $localConfig['redis'] ? [
            'class' => 'yii\redis\Connection',
            'hostname' => $localConfig['redis']['host'],
            'port' => $localConfig['redis']['port'],
            'database' => $localConfig['redis']['database'] ?? 11,
            'password' => $localConfig['redis']['password'],
            'connectionTimeout' => $localConfig['redis']['timeout'] ?? 20,
            'retries' => 5,
        ] : null,

        /**
         * @var array Redis持久库配置
         */
        'redisPermanent' => $localConfig['redis'] ? [
            'class' => 'yii\redis\Connection',
            'hostname' => $localConfig['redis']['host'],
            'port' => $localConfig['redis']['port'],
            'database' => $localConfig['redis']['permanentDatabase'] ?? 12,
            'password' => $localConfig['redis']['password'],
            'connectionTimeout' => $localConfig['redis']['timeout'] ?? 20,
            'retries' => 5,
        ] : null,

        /**
         * @var array 缓存配置
         */
        'cache' => [
            'class' => $localConfig['redis'] ? 'yii\redis\Cache' : 'yii\caching\FileCache',
            'keyPrefix' => 'kdx_shopstar_cache_',
        ],

        /**
         * @var array 缓存配置(文件)
         */
        'cacheFile' => [
            'class' => 'yii\caching\FileCache',
            'keyPrefix' => 'kdx_shopstar_cache_',
        ],

        /**
         * @var array 日志配置
         */
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => YII_DEBUG ? ['error', 'warning', 'info'] : ['error', 'warning'],    // 开发模式打开info
                ],
            ],
        ],

        /**
         * @var array 队列配置
         */
        'queue' => $localConfig['redis'] ? [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redisPermanent', // 连接组件或它的配置
            'channel' => 'kdx_shopstar_queue', // Queue channel key
        ] : null,

    ],

    /**
     * @var array 定义参数
     */
    'params' => (array)$localConfig['params'],
];
