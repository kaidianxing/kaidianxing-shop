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
 * 设置权限
 * Class SyssetPermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class SyssetPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'sysset';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '设置';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'sysset/mall/basic' => [
            'title' => '基础设置',
            'alias' => 'sysset.mall.basic',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['empty']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit']
                ]
            ]
        ],
        'sysset/mall/share' => [
            'title' => '分享设置',
            'alias' => 'sysset.mall.share',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit']
                ]
            ]
        ],
        'sysset/mall/notice' => [
            'title' => '公告管理',
            'alias' => 'sysset.mall.notice',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'detail']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'change-status', 'delete']
                ]
            ]
        ],
        'sysset/mall/contact' => [
            'title' => '联系我们',
            'alias' => 'sysset.mall.contact',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['detail']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['save']
                ]
            ]
        ],

        'sysset/trade' => [
            'title' => '交易设置',
            'alias' => 'sysset.trade',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],

        
        'sysset/refund' => [
            'title' => '维权设置',
            'alias' => 'sysset.refund',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/credit' => [
            'title' => '积分管理',
            'alias' => 'sysset.credit',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info', 'get-statistics']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/balance' => [
            'title' => '余额管理',
            'alias' => 'sysset.balance',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info', 'get-statistics']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/pay-type-set' => [
            'title' => '支付方式',
            'alias' => 'sysset.pay-type_set',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/pay-set' => [
            'title' => '打款设置',
            'alias' => 'sysset.pay_set',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/pay-template-set' => [
            'title' => '支付模版管理',
            'alias' => 'sysset.pay_template_set',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'detail']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'update', 'delete']
                ]
            ]
        ],

        'sysset/express' => [
            'title' => '物流配置',
            'alias' => 'sysset.express',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit']
                ]
            ]
        ],
        'sysset/address-set' => [
            'title' => '地址设置',
            'alias' => 'sysset.address_set',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update']
                ]
            ]
        ],
        'sysset/refund-address' => [
            'title' => '退货地址',
            'alias' => 'sysset.refund_address',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'detail', 'all-refund-address']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['delete', 'add', 'edit', 'change-default']
                ]
            ]
        ],
        'user/role' => [
            'title' => '角色管理',
            'alias' => 'user.role',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'edit', 'all-role']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['forbidden', 'active', 'save', 'create', 'delete', 'get-all-perms']
                ],
            ],
        ],
        'user/index' => [
            'title' => '操作员管理',
            'alias' => 'user.index',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'edit', 'check-user']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['forbidden', 'active', 'save', 'create', 'delete']
                ],
            ],
        ],
        'sysset/log' => [
            'title' => '操作日志',
            'alias' => 'sysset.log',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'detail']
                ]
            ]
        ]
    ];
    
}