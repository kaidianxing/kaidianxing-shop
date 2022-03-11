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
 * 商品权限
 * Class GoodsPermissionConfig
 * @package shop\config\permission
 */
class GoodsPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'goods';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '商品';


    /**
     * @var array[] 权限配置
     */
    public $config = [
        'goods/index' => [
            'title' => '商品管理',
            'alias' => 'goods.index',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get', 'get-goods-qrcode'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'property', 'delete', 'forever-delete','get-virtual-account']
                ],
            ],
        ],
        'goods/category' => [
            'title' => '商品分类',
            'alias' => 'goods.category',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-setting', 'get-one'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update', 'save', 'forever-delete', 'set-setting', 'switch'],
                ],
            ],
        ],
        'goods/group' => [
            'title' => '商品组',
            'alias' => 'goods.group',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-one'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['create', 'update', 'forever-delete', 'switch'],
                ],
            ],
        ],
        'goods/label-group' => [
            'title' => '标签管理',
            'alias' => 'goods.label_group',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['add', 'edit', 'get-one'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'forever-delete', 'switch', 'create', 'update', 'get-list-and-label'],
                ],
            ],
        ],
        'goods/operation' => [
            'title' => '商品操作',
            'alias' => 'goods.operation',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-price-and-stock'],
                    'depends' => [
                        'goods.index.view'
                    ]
                ],
                'manage' => [
                    'title' => '操作',
                    'actions' => ['recover', 'putaway', 'unshelve', 'set-price-and-stock', 'set-category', 'delete', 'forever-delete'],
                    'depends' => [
                        'goods.index.manage'
                    ]
                ],
            ]
        ],

    ];
    
}