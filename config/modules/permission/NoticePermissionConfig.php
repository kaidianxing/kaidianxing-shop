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
 * 消息通知
 * Class PermissionConfig
 * @package apps\notice\config
 */
class NoticePermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'notice';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/notice/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '消息通知';
    
    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;
    
    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'title' => '消息通知',
            'alias' => 'notice',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['init', 'get-wechat-notice', 'wechat-notice', 'get-wxapp-notice', 'wxapp-notice', 'get-sms-notice', 'sms-notice'],
                ],
            ],
        ],
        'sms' => [
            'title' => '消息通知',
            'alias' => 'notice',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => [
                        'get-plan',
                        'index',
                        'get-scene',
                        'get-num',
                        'detail',
                        'add',
                        'edit',
                        'change-state',
                        'delete',
                        'send-data',
                        'set',
                        'edit-set',
                        'code',
                        'edit-code',
                        'get-access-key',
                        'set-access-key',
                    ],
                    'depends' => [
                        'notice.manage'
                    ]
                ],
            ],
        ],
        'sms-signature' => [
            'title' => '消息通知',
            'alias' => 'notice',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['list', 'enabled', 'apply'],
                    'depends' => [
                        'notice.manage'
                    ],
                ],
            ],
        ],
        'wechat-template' => [
            'title' => '消息通知',
            'alias' => 'notice',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['index', 'add-template', 'delete', 'detail'],
                    'depends' => [
                        'notice.manage'
                    ]
                ],
            ],
        ],
        'mailer' => [
            'title' => '消息通知',
            'alias' => 'notice',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['get', 'set', 'test-send'],
                    'depends' => [
                        'notice.manage'
                    ],
                ],
            ],
        ],
    ];
    
}