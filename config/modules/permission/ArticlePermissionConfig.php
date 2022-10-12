<?php

namespace shopstar\config\modules\permission;

use shopstar\components\permission\BasePermissionConfig;

/**
 * 文章营销权限
 * Class ArticlePermissionConfig
 * @package shopstar\config\modules\permission
 * @author yuning
 */
class ArticlePermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'article';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/article/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '文章营销';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'article' => [
            'title' => '文章列表',
            'alias' => 'article.article',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list', 'get', 'statistics', 'get-sell-data']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['save', 'check-title', 'change-status', 'delete', 'change-top', 'import-wx-article', 'promote', 'get-join-activity-list']
                ],

            ]
        ],
        'group' => [
            'title' => '分组管理',
            'alias' => 'article.group',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['list']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['save', 'change-status', 'delete']
                ],

            ]
        ],
        'settings' => [
            'title' => '基础设置',
            'alias' => 'article.settings',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get']
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['set', 'clear-data']
                ],

            ]
        ],
    ];
}