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
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\wxapp\WxappUploadLogModel;

/**
 * 页面路径
 * Class PagesLinkController
 * @package shopstar\admin\utility
 * @author 青岛开店星信息技术有限公司
 */
class PageLinkController extends KdxAdminUtilityController
{
    /**
     * @var array 商城基础链接
     */
    private $basicLink = [
        /**
         * @var array 商城页面相关
         */
        'basic' => [
            'name' => '基础页面',
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
                [
                    'name' => '会员中心',
                    'url' => '/kdxMember/index/index',
                ],
                [
                    'name' => '会员等级说明',
                    'url' => '/kdxMember/level/index',
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
                    'name' => '我的资料',
                    'url' => '/kdxMember/memberInfo/index',
                ],
                [
                    'name' => '收货地址管理',
                    'url' => '/kdxMember/address/list',
                ],
            ],
        ],

        //订单相关
        'order' => [
            'name' => '订单页面',
            'links' => [
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
            ]
        ],

        //营销页面
        'marketing' => [
            'name' => '营销页面',
            'links' => [
                [
                    'name' => '领取优惠券',
                    'url' => '/kdxMember/coupon/list/index?tabIndex=1',
                ],
                [
                    'name' => '我的优惠券',
                    'url' => '/kdxMember/coupon/list/index?tabIndex=2',
                ],
                [
                    'name' => '会员充值',
                    'url' => '/kdxMember/balance/index'
                ],
                [
                    'name' => '余额明细',
                    'url' => '/kdxMember/detail/index?page=balance',
                ],
                [
                    'name' => '积分明细',
                    'url' => '/kdxMember/detail/index?page=credit',
                ],
                [
                    'name' => '余额提现',
                    'url' => '/kdxMember/withdraw/index',
                ],
                [
                    'name' => '积分排行',
                    'url' => '/kdxMember/ranking/integral/index',
                ],
            ]
        ],

        //插件页面
        'plugins' => [
            'name' => '插件页面',
            'links' => [

            ]
        ],

        //自定义页面
        'diy_page' => [
            'name' => '自定义页面',
            'links' => [

            ]
        ]
    ];

    /**
     * @var array 应用插件
     */
    private $pluginLinks = [
        [
            'name' => '开店星分销',
            'identify' => 'commission',
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
        [
            'name' => '积分商城',
            'identify' => 'creditShop',
            'links' => [
                [
                    'name' => '积分商城',
                    'url' => '/pagesCreditShop/index',
                ],
            ],
        ],
    ];

    /**
     * 获取列表
     * @author 青岛开店星信息技术有限公司
     */
    private function getLinks(): array
    {
        $links = $this->basicLink;

        // 判断插件权限 合并插件链接
        foreach ($this->pluginLinks as $pluginLinkIndex => $pluginLinkItem) {
            //合并插件权限链接
            $links['plugins']['links'] = array_merge($links['plugins']['links'], $pluginLinkItem['links']);
        }

        return $links;
    }

    /**
     * 返回
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): array
    {
        return $this->result([
            'list' => $this->getLinks(),
            'domain' => ShopUrlHelper::wap('/', [], true)
        ]);
    }

    /**
     * 获取小程序二维码
     * @return array|int[]|\yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetQrcode()
    {
        $page = RequestHelper::post('page');
        $params = RequestHelper::postArray('params');

        //获取小程序二维码
        $url = WxappUploadLogModel::getWxappQRcode($page, $params);

        return $this->result([
            'page' => $url
        ]);
    }

}