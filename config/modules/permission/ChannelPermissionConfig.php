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
 *
 * Class ChannelPermissionConfig
 * @package shop\config\permission
 */
class ChannelPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'channel';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '渠道';

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'apps/wap/index' => [
            'title' => 'H5',
            'alias' => 'channel.wap',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set'],
                ]
            ],
        ],


        'channel/index' => [
            'title' => 'H5',
            'alias' => 'channel.wap',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set-status'],
                    'depends' => [
                        'channel.wap.manage'
                    ]
                ]
            ]
        ],

        'apps/wechat/index' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'edit-attention'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['empty', 'set', 'get-type', 'get-url', 'test', 'add-attention', 'edit-attention', 'list', 'delete', 'add', 'edit', 'update', 'check-keyword'],
                ]
            ],
        ],
        'apps/wechat/wechat-rule' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'edit', 'edit-attention'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit', 'add', 'update', 'delete', 'add-attention'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/wechat-rule-keyword' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'simple-list', 'edit', 'edit-attention'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['check-keyword'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/menu' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'edit', 'add'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit', 'add', 'enable', 'delete'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/media' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['upload-image', 'upload', 'sync', 'delete'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/wechat-fans' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['batch-del-tag', 'batch-add-tag', 'sync', 'black', 'change-tag'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/wechat-fans-tag' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list'],
                    'depends' => [
                        'channel.wechat.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['save', 'delete', 'sync', 'delete'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wechat/wechat-sync' => [
            'title' => '微信公众号',
            'alias' => 'channel.wechat',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['get-task-info'],
                    'depends' => [
                        'channel.wechat.manage'
                    ]
                ]
            ]
        ],
        'apps/wxapp/index' => [
            'title' => '微信小程序',
            'alias' => 'channel.wxapp',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get'],
                    'depends' => [
                        'channel.wap.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set-setting', 'get-setting'],
                    'depends' => [
                        'channel.wxapp.manage'
                    ],
                ],
            ],
        ],
        'apps/byteDance/index' => [
            'title' => '头条/抖音小程序',
            'alias' => 'channel.byteDance',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-byte-dance-qrcode'],
                    'depends' => [
                        'channel.wap.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set-setting', 'get-setting'],
                    'depends' => [
                        'channel.byteDance.manage'
                    ],
                ],
            ],
        ],
        'apps/wxapp/upload' => [
            'title' => '微信小程序',
            'alias' => 'channel.wxapp',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['list', 'init', 'get-wx-audio-qrcode', 'get-login-qrcode', 'get-login-qrcode-status', 'upload', 'get-upload-status']
                ]
            ],
        ],
        'apps/byteDance/upload' => [
            'title' => '头条/抖音小程序',
            'alias' => 'channel.byteDance',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['log', 'init', 'get-captcha', 'sms-login', 'send-sms', 'email-login', 'get-login-status', 'upload', 'get-upload-status']
                ]
            ],
        ],
        'channel/registry-setting' => [
            'title' => '注册设置',
            'alias' => 'channel.registry',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set'],
                ]
            ]
        ],
    ];

}