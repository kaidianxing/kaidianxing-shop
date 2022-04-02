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
 * 分销权限
 * Class PermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'commission';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/commission/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '分销';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'title' => '分销首页',
            'alias' => 'commission.index',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index']
                ]
            ]
        ],
        'agent' => [
            'multi' => [
                [
                    'title' => '全部分销商',
                    'alias' => 'commission.agent',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['index', 'detail', 'child-list']
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['change-status', 'change-level', 'change-upgrade', 'change-agent', 'manual-agent', 'unbind']
                        ]
                    ]
                ],
                [
                    'title' => '分销商审核',
                    'alias' => 'commission.wait_agent',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['wait-list']
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['change-status']
                        ],
                    ]
                ]
            ],

        ],
        'level' => [
            'title' => '分销等级',
            'alias' => 'commission.level',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'detail']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'change-status', 'delete'],
                ]
            ]
        ],
        'goods' => [
            'title' => '分销商品',
            'alias' => 'commission.goods',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['cancel'],
                ]
            ]
        ],
        'order' => [
            'title' => '分销订单',
            'alias' => 'commission.order',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'get-commission'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['change-commission'],
                ]
            ]
        ],
        'apply' => [
            'title' => '提现管理',
            'alias' => 'commission.apply',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['init-list', 'get-wait-check-list', 'get-wait-remit-list', 'get-success-list', 'get-invalid-list', 'detail'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['check-agreed', 'check-again', 'check-refuse', 'remit', 'manual-remit'],
                ]
            ]
        ],
        'settings/commission' => [
            'title' => '分销设置',
            'alias' => 'commission.settings.commission',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['empty'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set'],
                ]
            ]
        ],
        'settings/settlement' => [
            'title' => '结算设置',
            'alias' => 'commission.settings.settlement',
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
        'settings/other' => [
            'title' => '其它设置',
            'alias' => 'commission.settings.other',
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
        'settings/rank' => [
            'title' => '排行榜设置',
            'alias' => 'commission.settings.rank',
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