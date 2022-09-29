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

namespace shopstar\admin\utility;

use shopstar\bases\KdxAdminUtilityController;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

/**
 * 链接选择器
 * Class LinkController
 * @package shopstar\admin\utility
 * @author 青岛开店星信息技术有限公司
 */
class LinkController extends KdxAdminUtilityController
{

    /**
     * @var array 商城基础链接
     */
    private array $basicLink = [
        /**
         * @var array 商城页面相关
         */
        [
            'name' => '商城页面',
            'links' => [
                [
                    'name' => '商城首页',
                    'url' => '/pages/index/index',
                ],
                [
                    'name' => '分类导航',
                    'url' => '/kdxGoods/categoryList/index',
                ],
                [
                    'name' => '全部商品',
                    'url' => '/kdxGoods/goodList/index',
                ],
                [
                    'name' => '购物车',
                    'url' => '/kdxCart/index',
                ],
            ],
        ],

        /**
         * @var array 会员中心相关
         */
        [
            'name' => '会员中心',
            'links' => [
                [
                    'name' => '会员中心',
                    'url' => '/kdxMember/index/index',
                ],
                [
                    'name' => '会员等级说明',
                    'url' => '/kdxMember/level/index',
                ],
                [
                    'name' => '我的订单(全部)',
                    'url' => '/kdxOrder/list',
                ],
                [
                    'name' => '待付款订单',
                    'url' => '/kdxOrder/list?status=pay',
                ],
                [
                    'name' => '待发货订单',
                    'url' => '/kdxOrder/list?status=send',
                ],
                [
                    'name' => '待收货订单',
                    'url' => '/kdxOrder/list?status=pick',
                ],
                [
                    'name' => '已完成订单',
                    'url' => '/kdxOrder/list?status=finish',
                ],
                [
                    'name' => '维权订单',
                    'url' => '/kdxOrder/refund/list',
                ],
                [
                    'name' => '我的收藏',
                    'url' => '/kdxMember/mine/collection/index',
                ],
                [
                    'name' => '我的足迹',
                    'url' => '/kdxMember/mine/pug/index',
                ],
                [
                    'name' => '会员充值',
                    'url' => '/kdxMember/balance/index',
                ],
                [
                    'name' => '余额明细',
                    'url' => '/kdxMember/detail/index?page=balance',
                ],
                [
                    'name' => '积分明细',
                    'url' => '/kdxMember/credit/index',
                ],
                [
                    'name' => '余额提现',
                    'url' => '/kdxMember/withdraw/index',
                ],
                [
                    'name' => '我的资料',
                    'url' => '/kdxMember/memberInfo/index',
                ],

                [
                    'name' => '收货地址管理',
                    'url' => '/kdxMember/address/list',
                ],
                [
                    'name' => '小程序客服',
                    'url' => 'wx_service',
                ],
                [
                    'name' => '红包领取明细',
                    'url' => '/kdxMember/detail/redpacketDetail',
                ],
            ],
        ],
    ];

    /**
     * @var array 优惠券
     */
    private array $couponLink = [
        [
            'name' => '优惠券',
            'links' => [
                [
                    'name' => '领取优惠券',
                    'url' => '/kdxMember/coupon/list/index?tabIndex=1',
                ],
                [
                    'name' => '我的优惠券',
                    'url' => '/kdxMember/coupon/list/index?tabIndex=2',
                ],
            ],
        ]
    ];

    /**
     * @var array 分销应用插件
     */
    private array $commissionLink = [
        [
            'name' => '分销',
            'links' => [
                [
                    'name' => '分销中心',
                    'url' => '/kdxCommission/index/index',
                ],
                [
                    'name' => '等级说明',
                    'url' => '/kdxCommission/level',
                ],
                [
                    'name' => '分销订单',
                    'url' => '/kdxCommission/order/list',
                ],
                [
                    'name' => '分销佣金',
                    'url' => '/kdxCommission/statistics',
                ],
                [
                    'name' => '我的下线',
                    'url' => '/kdxCommission/downLine',
                ],
                [
                    'name' => '提现明细',
                    'url' => '/kdxCommission/withdraw/list',
                ],
                [
                    'name' => '佣金排名',
                    'url' => '/kdxCommission/ranking',
                ],
            ],
        ],
    ];

    /**
     * @var array 积分商城应用插件
     */
    private array $creditShopLink = [
        [
            'name' => '积分商城',
            'links' => [
                [
                    'name' => '积分商城首页',
                    'url' => '/kdxCreditShop/index',
                ],
            ],
        ]
    ];

    /**
     * @var array[] 微信客服
     */
    public array $wechatCustomerServiceLink = [
        'name' => '微信客服',
        'url' => 'wechatCustomerService',
    ];

    /**
     * @var string[][] 积分签到奖励
     */
    public array $creditSignLink = [
        [
            'name' => '签到',
            'links' => [
                [
                    'name' => '积分签到',
                    'url' => '/kdxSignIn/index',
                ]
            ],
        ],
    ];

    /**
     * 获取列表
     * @author likexin
     */
    private function getLinks(): array
    {
        $links = $this->basicLink;
        $service = [];
        $appArray = [];
        $saleArray = [];

        // 判断有分销应用时合并分销的链接
        $appArray = array_merge($appArray, $this->commissionLink);
        // 积分商城
        $appArray = array_merge($appArray, $this->creditShopLink);
        // 积分签到
        $appArray = array_merge($appArray, $this->creditSignLink);

        $service['wx_service'] = [
            'name' => '小程序客服',
            'url' => 'wx_service',
        ];

        $service['wechat_customer_service'] = $this->wechatCustomerServiceLink;

        // 合并优惠券
        $saleArray = array_merge($saleArray, $this->couponLink);

        return ['links' => $links, 'service' => $service, 'apps' => $appArray, 'sale' => $saleArray];
    }

    /**
     * 返回
     * @return Response
     * @author likexin
     */
    public function actionIndex(): Response
    {
        $links = $this->getLinks();
        return $this->result([
            'list' => $links['links'],
            'service' => $links['service'],
            'apps' => $links['apps'],
            'sale' => $links['sale']
        ]);
    }

    /**
     * 获取小程序跳转appid列表
     * @return Response
     * @author likexin
     */
    public function actionGetNavigateList(): Response
    {
        return $this->result([
            'list' => ShopSettings::get('channel_setting.wxapp.navigate_appid_list', []),
        ]);
    }

}
