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
 * 虚拟卡密
 * Class PermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class VirtualAccountPermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'virtualAccount';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/virtualAccount/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '虚拟卡密';

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
                    'title' => '卡密库',
                    'alias' => 'virtualAccount.index',
                    'perm' => [
                        'view' => [
                            'title' => '查看',
                            'actions' => ['index', 'get-statistics']
                        ],
                        'manage' => [
                            'title' => '管理',
                            'actions' => ['add', 'edit', 'delete', 'check-name']
                        ],
                    ],
                ],
            ]
        ],
        'virtual-account-data' => [
            'title' => '卡密库数据',
            'alias' => 'virtualAccountData.index',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index', 'get-data']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'update', 'download', 'import', 'update-data']
                ],
            ],
        ],
        'setting' => [
            'title' => '设置',
            'alias' => 'virtualAccount.setting',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set']
                ],
            ],
        ],
        'virtual-account-recycle-bin' => [
            'title' => '回收站',
            'alias' => 'virtualAccount.recycleBin',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['delete', 'restore']
                ],
            ],
        ],
    ];
}