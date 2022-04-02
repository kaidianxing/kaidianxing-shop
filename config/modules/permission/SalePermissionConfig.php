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
 * 营销
 * Class SalePermissionConfig
 * @package shopstar\config\modules\permission
 * @author 青岛开店星信息技术有限公司
 */
class SalePermissionConfig extends BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = 'sale';
    
    /**
     * @var string 路由前缀
     */
    protected $prefix = 'manage/sale/';
    
    /**
     * @var string 分组名称
     */
    protected $groupName = '营销';
    
    /**
     * @var array[] 权限配置
     */
    public $config = [

        'basic/enough-free' => [
            'title' => '满额包邮',
            'alias' => 'sale.basic.free',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['edit']
                ]
            ],
        ],
        'coupon-list' => [
            'title' => '优惠券',
            'alias' => 'sale.coupon-list',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['detail'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['add', 'edit', 'delete', 'change-state'],
                ]
            ]
        ],
        'coupon/batch-send' => [
            'title' => '手动发券',
            'alias' => 'sale.coupon.batch_send',
            'perm' => [
                'manage' => [
                    'title' => '管理',
                    'actions' => ['get-info', 'send'],
                ],
            ]
        ],
        'coupon-log' => [
            'title' => '发券记录',
            'alias' => 'sale.coupon-log',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['index'],
                ],
            ]
        ],
        'coupon-set' => [
            'title' => '其他设置',
            'alias' => 'sale.coupon-set',
            'perm' => [
                'view' => [
                    'title' => '查看',
                    'actions' => ['get-info'],
                ],
                'manage' => [
                    'title' => '管理',
                    'actions' => ['update'],
                ],
            ]
        ],
    ];
}