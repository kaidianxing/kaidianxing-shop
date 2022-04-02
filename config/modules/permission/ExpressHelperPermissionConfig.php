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
 * 快递助手权限
 * Class PermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class ExpressHelperPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'expressHelper';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/expressHelper/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '快递助手';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'title' => '基础设置',
            'alias' => 'expressHelper',
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
        'consigner-template' => [
            'title' => '发件人模板',
            'alias' => 'expressHelper.consigner_template',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'edit'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'switch'],
                ],
            ],
        ],
        'express-template' => [
            'title' => '电子面单模板',
            'alias' => 'expressHelper.express_template',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'edit', 'test-print'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'switch'],
                ],
            ],
        ],
        'send-bill-template' => [
            'title' => '发货单模板',
            'alias' => 'expressHelper.send_bill_template',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['express', 'get-content-list'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'switch'],
                ],
            ],
        ],
        'order' => [
            'title' => '打印订单列表',
            'alias' => 'expressHelper.order',
            'perm' => [
                'manage' => [
                    'title' => '查看',
                    'actions' => ['list'],
                ],
            ],
        ],
        'print' => [
            'title' => '打印',
            'alias' => 'expressHelper.print',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['index', 'callback', 'check-sub'],
                ],
            ],
        ],
        'send-bill-print' => [
            'title' => '打印',
            'alias' => 'expressHelper.send_bill_print',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['index', 'get-template-data'],
                ],
            ],
        ]
    ];
}