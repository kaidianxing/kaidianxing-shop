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
 * 财务权限
 * Class FinancePermissionConfig
 * @package shop\config\permission
 */
class FinancePermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'finance';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '财务';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'finance/log' => [
            'multi' => [
                [
                    'title' => '充值记录',
                    'alias' => 'finance.recharge',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['recharge'],
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['recharge-refund']
                        ]
                    ],
                ],
                [
                    'title' => '提现申请',
                    'alias' => 'finance.withdraw',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['withdraw'],
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['update-status', 'withdraw-apply'],
                        ],
                    ],
                ]
            ],
        ],
        'finance/credit-record' => [
            'multi' => [
                [
                    'title' => '余额明细',
                    'alias' => 'finance.balance',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['balance'],
                        ],
                    ],
                ],
                [
                    'title' => '积分明细',
                    'alias' => 'finance.credit',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['integral'],
                        ],
                    ],
                ]
            ],
        ],
        'finance/order' => [
            'title' => '订单管理',
            'alias' => 'finance.order',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list','detail'],
                ],
            ],
        ],
        'finance/refund-log' => [
            'title' => '退款记录',
            'alias' => 'finance.refund_log',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list','type-list'],
                ],
            ],
        ],
        'finance/red-package' => [
            'title' => '红包记录',
            'alias' => 'finance.red_package',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list'],
                ],
            ],
        ],


    ];
    
}