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
 * 权限配置
 * Class PermissionConfig
 * @package apps\assistant\config
 * @author likexin
 */
class AssistantPermissionConfig extends BasePermissionConfig
{

    /**
     * @var string 权限模块标识
     */
    protected $identity = 'assistant';

    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/assistant/';

    /**
     * @var string 分组名称
     */
    protected $groupName = '店铺助手';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = true;

    /**
     * @var array[] 权限配置
     */
    public $config = [
        'index' => [
            'title' => '店铺助手',
            'alias' => 'assistant',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['index', 'get-statistics', 'get-qrcode']
                ]
            ]
        ],
    ];
}