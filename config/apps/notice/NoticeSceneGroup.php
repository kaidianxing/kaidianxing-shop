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

namespace shopstar\config\apps\notice;

use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\interfaces\NoticeSceneGroupInterface;

class NoticeSceneGroup implements NoticeSceneGroupInterface
{
    /**
     * 获取场景值映射
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSceneGroupMap()
    {
        return [
            'buyer_notice' => [
                'basic' => [
                    NoticeTypeConstant::BUYER_ORDER_PAY => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_ORDER_PAY),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ],//用户付款成功
                    NoticeTypeConstant::BUYER_ORDER_SEND => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_ORDER_SEND),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ], //订单发货
                    NoticeTypeConstant::BUYER_ORDER_CANCEL_AND_REFUND => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_ORDER_CANCEL_AND_REFUND),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ], //退款成功
                    NoticeTypeConstant::BUYER_PAY_WITHDRAW => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_PAY_WITHDRAW),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ], //提现成功
//                    NoticeTypeConstant::BUYER_MEMBER_UPDATE => [
//                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_MEMBER_UPDATE),
//                        'item' => ['wechat', 'wxapp']
//                    ], //会员升级
                    NoticeTypeConstant::BUYER_PAY_RECHARGE => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_PAY_RECHARGE),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ], //余额充值成功
                    NoticeTypeConstant::BUYER_PAY_CREDIT => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_PAY_CREDIT),
                        'item' => ['wechat', 'sms']
                    ], //积分变动
                ],
                'sale' => [
                    NoticeTypeConstant::BUYER_COUPON_SEND => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::BUYER_COUPON_SEND),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ]
                ],
                'verify_code' => [
                    NoticeTypeConstant::VERIFY_CODE_USER_REG => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::VERIFY_CODE_USER_REG),
                        'item' => ['sms']
                    ],                                                     //用户注册
                    NoticeTypeConstant::VERIFY_CODE_RETRIEVE_PWD => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::VERIFY_CODE_RETRIEVE_PWD),
                        'item' => ['sms']
                    ],                                                   //找回密码
                    NoticeTypeConstant::VERIFY_CODE_LOGIN_CODE => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::VERIFY_CODE_LOGIN_CODE),
                        'item' => ['sms']
                    ],//验证码登录
                    NoticeTypeConstant::VERIFY_CODE_BIND => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::VERIFY_CODE_BIND),
                        'item' => ['sms']
                    ],//修改手机号验证码
//                    NoticeTypeConstant::VERIFY_CODE_CHANGE_BIND => [
//                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::VERIFY_CODE_CHANGE_BIND),
//                        'item' => ['sms']
//                    ],
                ]
            ],
            'seller_notice' => [
                'seller_notice' => [
                    NoticeTypeConstant::SELLER_ORDER_PAY => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::SELLER_ORDER_PAY),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::SELLER_ORDER_RECEIVE => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::SELLER_ORDER_RECEIVE),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::SELLER_ORDER_REFUND => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::SELLER_ORDER_REFUND),
                        'item' => ['wechat', 'sms']
                    ],
                ]
            ]
        ];
    }
}
