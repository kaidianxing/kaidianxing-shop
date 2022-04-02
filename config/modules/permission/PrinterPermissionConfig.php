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
 * 小票打印
 * Class PermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class PrinterPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'printer';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/printer/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '打印小票';
    
    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'title' => '打印机管理',
            'alias' => 'printer.index',
            'perm'  => [
                'view'   => [
                    'title'   => '查看',
                    'actions' => ['list']
                ],
                'manage' => [
                    'title'   => '管理',
                    'actions' => ['add', 'active', 'forbidden', 'delete',  'edit', 'save', 'cancel', 'test']
                ]
            ],
        ],
    
        'template' => [
            'title' => '模板管理',
            'alias' => 'printer.template',
            'perm'  => [
                'view'   => [
                    'title'   => '查看',
                    'actions' => ['list']
                ],
                'manage' => [
                    'title'   => '管理',
                    'actions' => ['add', 'delete',  'edit', 'save']
                ]
            ],
        ],
    
        'task' => [
            'title' => '打印任务',
            'alias' => 'printer.task',
            'perm'  => [
                'view'   => [
                    'title'   => '查看',
                    'actions' => ['list']
                ],
                'manage' => [
                    'title'   => '管理',
                    'actions' => ['add', 'delete',  'edit', 'save', 'execute']
                ]
            ],
        ]
    ];
    
}