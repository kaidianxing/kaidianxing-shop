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
 * 店铺权限
 * Class ShopPermissionConfig
 * @package shop\config\permission
 */
class ShopPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'shop';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '店铺';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'diypage/page/list' => [
            'title' => '店铺装修',
            'alias' => 'diypage.page.list',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-shop', 'get-app', 'get-diy'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'change-status']
                ]
            ]
        ],
        'diypage/page/shop' => [
            'title' => '店铺装修',
            'alias' => 'diypage.page.shop',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list-view'],
                    'depends' => [
                        'diypage.page.list.view'
                    ]
                ]
            ]
        ],
        'diypage/menu' => [
            'title' => '菜单管理',
            'alias' => 'diypage.menu',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-content', 'list']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'change-status']
                ]
            ]
        ],
        'diypage/template' => [
            'title' => '模板市场',
            'alias' => 'diypage.template',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'system-list', 'my-list', 'add', 'delete']
                ],
            ]
        ],

        'merchant/merchant/plugins/diypage/template-style' => [
            'title' => '模板列表',
            'alias' => 'diypage.template',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['system-list', 'my-list', 'get']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'delete']
                ]
            ]
        ],
        'diypage/login-auth' => [
            'title' => '登录授权',
            'alias' => 'diypage.login_auth',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set']
                ],
            ]
        ],
        'diypage/theme-color' => [
            'title' => '主题色',
            'alias' => 'diypage.theme_color',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set'],
                ],
            ],
        ],
    ];
}