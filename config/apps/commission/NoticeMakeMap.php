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

use shopstar\components\notice\interfaces\NoticeMakeTypeMapInterface;
use shopstar\constants\components\notice\NoticeTypeConstant;

class NoticeMakeMap implements NoticeMakeTypeMapInterface
{
    public const NOTICE_MAP = [
        // 分销
        'commission' => [
            'class' => 'shopstar\config\apps\commission\NoticeMake',            //处理类名
            'item' => [                                                                   //子项
                NoticeTypeConstant::COMMISSION_SELLER_APPLY, // 卖家申请成为分销商通知
                NoticeTypeConstant::COMMISSION_SELLER_WITHDRAW, // 卖家申请提现通知
                NoticeTypeConstant::COMMISSION_SELLER_ADD_COMMISSION_ORDER, // 新增分销订单通知
                NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME, // 买家成为分销商通知
                NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD, // 买家新增下级通知
                NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD_LINE, // 买家新增下线通知
                NoticeTypeConstant::COMMISSION_BUYER_CHILD_PAY, // 买家下级支付通知
                NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY_FAIL, // 买家申请提现失败通知
                NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_PAY, // 买家佣金打款通知
                NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_UPGRADE, // 买家分销等级升级通知
            ]
        ],
    ];

    /**
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNoticeMap(): array
    {
        return self::NOTICE_MAP;
    }
}