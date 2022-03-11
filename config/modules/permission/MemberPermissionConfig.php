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
 * 会员权限
 * Class MemberPermissionConfig
 * @package shop\config\permission
 */
class MemberPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'member';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/member/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '会员';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'list' => [
            'title' => '会员列表',
            'alias' => 'member.list',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['change-level', 'change-group', 'set-black', 'recharge'],
                ]
            ],
        ],
        'detail' => [
            'title' => '会员详情',
            'alias' => 'member.detail',
            'perm' => [
                'view_detail' => [
                    'title' => '查看',
                    'actions' => ['index'],
                    'depends' => [
                        'member.list.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['change-mobile','change-remark', 'change-password', 'delete'],
                    'depends' => [
                        'member.list.manage'
                    ]
                ],
            ]
        ],
        'level' => [
            'multi' => [
                [
                    'title' => '会员等级',
                    'alias' => 'member.level',
                    'perm'  => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['detail', 'check-level'],
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['add', 'edit', 'change-state', 'delete'],
                        ],
                    ],
                ],
                [
                    'title' => '升级设置',
                    'alias' => 'member.level_upgrade',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['get-type'],
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['set-type'],
                        ]
                    ]
                ]
            ],
        ],
        'group' => [
            'title' => '标签组',
            'alias' => 'member.group',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['empty'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete'],
                ],
            ]
        ],
        'rank' => [
            'title' => '排行榜',
            'alias' => 'member.rank',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit'],
                ],
            ],
        ],
    ];
   
}