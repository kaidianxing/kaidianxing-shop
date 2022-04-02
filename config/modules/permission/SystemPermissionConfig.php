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

namespace shopstar\config\modules\permission;

use shopstar\components\permission\BasePermissionConfig;

/**
 * 系统设置
 * Class SystemPermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class SystemPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'system';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '系统设置';

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'system/apps/index' => [
            'title' => '未安装应用列表',
            'alias' => 'system.apps.index',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-list']
                ],

            ]
        ],


        'system/tools/crontab' => [
            'title' => '数据管理',
            'alias' => 'system.tools.crontab',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set']
                ]
            ]
        ],


        'system/attachment' => [
            'title' => '附件设置',
            'alias' => 'system.attachment',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set']
                ]
            ]
        ],

        'system/storage' => [
            'title' => '远程存储',
            'alias' => 'system.storage',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'get-qiniu-domain', 'get-oss-bucket', 'get-cos-bucket']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set',]
                ]
            ]
        ],

        'system/tools/cache' => [
            'title' => '清除缓存',
            'alias' => 'system.tools.cache',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['flush']
                ]
            ]
        ],
        'system/tools/queue' => [
            'title' => '清除缓存',
            'alias' => 'system.tools.queue',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-status'],
                    'depends' => [
                        'system.tools.cache.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['send-job'],
                    'depends' => [
                        'system.tools.cache.manage'
                    ]
                ]
            ]
        ],

        'system/tools/clear-data' => [
            'title' => '数据清理',
            'alias' => 'system.tools.clear-data',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['submit']
                ]
            ]
        ],

        'system/upgrade' => [
            'title' => '系统升级',
            'alias' => 'system.upgrade',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['init', 'get-status']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['reset', 'start', 'execute',]
                ]
            ]
        ],


        'system/repair' => [
            'title' => '系统修复',
            'alias' => 'system.repair',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['init', 'version']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['reset', 'check', 'request', 'download-file', 'execute', 'execute-script', 'complete',]
                ]
            ]
        ],


        'system/update-log' => [
            'title' => '更新日志',
            'alias' => 'system.update-log',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-list', 'get-version-log']
                ]
            ]
        ],


        'system/licence' => [
            'title' => '授权信息',
            'alias' => 'system.licence',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'copyright']
                ]
            ]
        ],

    ];

}