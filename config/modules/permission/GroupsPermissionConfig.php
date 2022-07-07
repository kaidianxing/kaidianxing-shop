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
 * 拼团权限
 * Class PermissionConfig
 * @package shopstar\config\modules\permission
 * @author likexin
 */
class GroupsPermissionConfig extends BasePermissionConfig
{

    /**
     * @var string 权限模块标识
     */
    protected $identity = 'groups';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/groups/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '多人拼团';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'multi' => [
                [
                    'title' => '拼团列表',
                    'alias' => 'groups',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['detail', 'list', 'statistics', 'get-type'],
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['add', 'edit', 'manual-stop', 'delete', 'get-status', 'change-status']
                        ],
                    ]
                ],
            ],
        ],
        'team' => [
            'title' => '拼团',
            'alias' => 'groups',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'get-team-detail'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit-groups-status']
                ],
            ]
        ],
        'data' => [
            'title' => '活动数据',
            'alias' => 'groups_statistics',
            'perm' => [
                'manage' => [
                    'title' => '查看',
                    'actions' => ['index', 'view', 'activity', 'get-update-times', 'goods'],
                ]
            ]
        ],
        'platform' => [
            'title' => '应用概览',
            'alias' => 'groups_statistics',
            'perm' => [
                'manage' => [
                    'title' => '查看',
                    'actions' => ['index', 'view', 'activity', 'get-update-times', 'goods', 'get-merchant-statistics'],
                ]
            ]
        ],
        'message' => [
            'title' => '消息通知',
            'alias' => 'groups_message',
            'perm' => [
                'manage' => [
                    'title' => '查看',
                    'actions' => ['wechat-circularize'],
                ]
            ]
        ],
        'settings' => [
            'title' => '拼团设置',
            'alias' => 'groups_setting',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['get', 'set'],
                ]
            ]
        ],
    ];

}