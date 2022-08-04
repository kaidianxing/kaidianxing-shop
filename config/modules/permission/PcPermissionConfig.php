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

class PcPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'pc';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/pc/';

    /**
     * @var string 分组名称
     */
    protected $groupName = 'PC渠道';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'goods-group' => [
            'title' => '商品组',
            'alias' => 'pc.goods-group',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['types','list','detail']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['add','edit','delete']
                ]
            ]
        ],
        'home-ads' => [
            'title' => '首页广告',
            'alias' => 'pc.home-ads',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list','detail']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['add','edit','delete']
                ]
            ]
        ],
        'menus' => [
            'title' => '顶部和底部菜单',
            'alias' => 'pc.menus',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list','detail']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['add','edit','delete','change-status']
                ]
            ]
        ],
        'sysset/basic' => [
            'title' => '基础配置',
            'alias' => 'pc.sysset.basic',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['set']
                ]
            ]
        ],

        'sysset/copyright' => [
            'title' => '版权配置',
            'alias' => 'pc.sysset.copyright',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['set']
                ]
            ]
        ],

        'sysset/customer-service' => [
            'title' => '版权配置',
            'alias' => 'pc.sysset.customer-service',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '编辑',
                    'actions' => ['set']
                ]
            ]
        ],
    ];
}
