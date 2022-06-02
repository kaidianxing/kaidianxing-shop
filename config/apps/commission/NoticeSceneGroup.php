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

namespace shopstar\config\apps\commission;

use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\interfaces\NoticeSceneGroupInterface;

class NoticeSceneGroup implements NoticeSceneGroupInterface
{

    /**
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSceneGroupMap()
    {
        return [
            'buyer_notice' => [
                'buyer_notice' => [
                    NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME => [ // 买家成为分销商通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_UPGRADE => [ // 买家分销等级升级通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_UPGRADE),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD => [ // 买家新增下级通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_CHILD_PAY => [ // 买家下级支付通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_CHILD_PAY),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY_FAIL => [ // 买家申请提现失败通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY_FAIL),
                        'item' => ['wechat', 'subscribe', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_PAY => [ // 买家佣金打款通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_PAY),
                        'item' => ['wechat', 'subscribe', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD_LINE => [ // 买家新增下线通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD_LINE),
                        'item' => ['wechat', 'sms']
                    ],
                ],
            ],
            'seller_notice' => [
                'seller_notice' => [
                    NoticeTypeConstant::COMMISSION_SELLER_APPLY => [ // 卖家申请成为分销商通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_SELLER_APPLY),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_SELLER_WITHDRAW => [ // 卖家申请提现通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_SELLER_WITHDRAW),
                        'item' => ['wechat', 'sms']
                    ],
                    NoticeTypeConstant::COMMISSION_SELLER_ADD_COMMISSION_ORDER => [ // 卖家新增分销订单通知
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::COMMISSION_SELLER_ADD_COMMISSION_ORDER),
                        'item' => ['wechat', 'sms']
                    ],
                ],
            ]
        ];
    }
}