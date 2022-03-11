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
 * 订单权限
 * Class OrderPermissionConfig
 * @package shop\config\permission
 */
class OrderPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'order';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/order/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '订单';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'op' => [
            'title' => '订单管理',
            'alias' => 'order.op',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-package', 'get-express'],
                ],
                'pay' => [
                    'title' => '确认付款',
                    'actions' => ['pay'],
                ],
                'close_refund' => [
                    'title' => '退款',
                    'actions' => ['close-and-refund'],
                ],
                'send' => [
                    'title' => '发货',
                    'actions' => ['send-package', 'change-send'],
                ],

                'cancel_send' => [
                    'title' => '取消发货',
                    'actions' => ['cancel-send'],
                ],
                'edit_address' => [
                    'title' => '修改收货信息',
                    'actions' => ['edit-address'],
                ],
                'finish' => [
                    'title' => '确认收货',
                    'actions' => ['finish'],
                ],
                'change_price' => [
                    'title' => '订单改价',
                    'actions' => ['change-price', 'change-price-log', 'change-price-detail'],
                ],
                'close' => [
                    'title' => '关闭订单',
                    'actions' => ['close'],
                ],
                'diy_export' => [
                    'title' => '自定义导出',
                    'actions' => ['empty'],
                ],
                'change_invoice_status' => [
                    'title' => '修改发票状态',
                    'actions' => ['change-invoice-status'],
                ],
            ]
        ],
        'refund' => [
            'title' => '维权管理',
            'alias' => 'order.refund',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index'],
                ],
                'manage' => [
                    'title' => '处理维权',
                    'actions' => ['reject', 'return-accept', 'exchange-send', 'exchange-close', 'manual', 'refund-accept'],
                ]
            ]
        ],
        'list' => [
            'title' => '订单管理',
            'alias' => 'order.list',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'send', 'pay', 'pick', 'success', 'close', 'dispatch-type', 'activity-type', 'goods-type'],
                    'depends' => [
                        'order.op.view'
                    ]
                ],
                'refund_view' => [
                    'title' => '查看',
                    'actions' => ['refund'],
                    'depends' => [
                        'order.refund.view'
                    ]
                ],
            ]
        ],
        'detail' => [
            'title' => '订单详情',
            'alias' => 'order.detail',
            'perm' => [
                'view' => [
                    'title' => '查看详情',
                    'actions' => ['index'],
                    'depends' => [
                        'order.op.view'
                    ]
                ]
            ],
        ],

        'diy-export' => [
            'title' => '自定义导出',
            'alias' => 'order.diy_export',
            'perm' => [
                'diy_export' => [
                    'title' => '自定义导出',
                    'actions' => ['index', 'template-list', 'add-template', 'delete-template', 'get-template', 'del-template'],
                    'depends' => [
                        'order.op.diy_export'
                    ]
                ]
            ],
        ],
        'comment' => [
            'title' => '评价管理',
            'alias' => 'order.comment',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'detail']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'audit', 'reply']
                ]
            ]
        ],
        'dispatch' => [
            'title' => '配送方式',
            'alias' => 'order.dispatch',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'detail', 'get-dispatch-sort']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'change-state', 'change-default', 'enable', 'set-dispatch-sort']
                ]
            ]
        ],
        'intracity' => [
            'title' => '同城配送',
            'alias' => 'order.intracity',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'config-distance', 'get-dada-city'],
                    'depends' => [
                        'order.dispatch.view'
                    ]
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set', 'enable'],
                    'depends' => [
                        'order.dispatch.manage'
                    ]
                ]
            ]
        ]
    ];

}