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

namespace shopstar\components\notice\config;

use shopstar\components\notice\interfaces\NoticeMakeTypeMapInterface;
use shopstar\constants\components\notice\NoticeTypeConstant;

/**
 * Class NoticeMakeTypeMap
 * @package shopstar\components\notice\config
 * @author 青岛开店星信息技术有限公司
 */
class NoticeMakeTypeMap implements NoticeMakeTypeMapInterface
{
    /**
     * 类型
     */
    public const NOTICE_MAP = [
        //验证码
        'base_verify_code' => [
            'class' => 'shopstar\components\notice\bases\noticeMakes\BaseVerifyCodeMake',            //处理类名
            'item' => [                                                                   //子项
                NoticeTypeConstant::VERIFY_CODE_USER_REG,//用户注册
                NoticeTypeConstant::VERIFY_CODE_RETRIEVE_PWD,//找回密码
                NoticeTypeConstant::VERIFY_CODE_CHANGE_BIND,//修改手机号
                NoticeTypeConstant::VERIFY_CODE_LOGIN_CODE,//验证码登录
                NoticeTypeConstant::VERIFY_CODE_BIND,//绑定手机号
                NoticeTypeConstant::VERIFY_CODE_BIND,//绑定手机号
                NoticeTypeConstant::SHOP_VERIFY_CODE_USER_REG,//商家用户注册
                NoticeTypeConstant::SHOP_VERIFY_CODE_RETRIEVE_PWD,//商家找回密码
            ]
        ],

        //订单
        'base_order' => [
            'class' => 'shopstar\config\apps\notice\noticeMakes\BaseOrderMake',
            'item' => [
                NoticeTypeConstant::SELLER_ORDER_PAY, //订单付款通知
                NoticeTypeConstant::SELLER_ORDER_RECEIVE, //订单收货通知
                NoticeTypeConstant::SELLER_STOCK_WARNING,//库存预警通知
                NoticeTypeConstant::SELLER_GOODS_PAY,//商品付款通知
                NoticeTypeConstant::BUYER_ORDER_CANCEL,//买家订单取消通知
                NoticeTypeConstant::BUYER_ORDER_CANCEL_AND_REFUND,//订单取消通知
                NoticeTypeConstant::BUYER_ORDER_PAY,//订单支付通知
                NoticeTypeConstant::BUYER_ORDER_SEND,//订单发货通知
                NoticeTypeConstant::BUYER_ORDER_STATUS,//订单状态更新通知
            ]
        ],

        //维权
        'base_order_refund' => [
            'class' => 'shopstar\config\apps\notice\noticeMakes\BaseOrderRefundMake',
            'item' => [
                NoticeTypeConstant::SELLER_ORDER_REFUND,//卖家订单维权通知
                NoticeTypeConstant::BUYER_REFUND_MONEY,//买家退款成功通知
                NoticeTypeConstant::BUYER_REFUND_EXCHANGE,//买家换货成功通知
                NoticeTypeConstant::BUYER_REFUND_SEND,//买家换货发货通知
                NoticeTypeConstant::BUYER_REFUND_REJECT,//买家退款申请拒绝通知
            ]
        ],

        //会员
        'base_member_account' => [
            'class' => 'shopstar\config\apps\notice\noticeMakes\BaseMemberAccountMake',
            'item' => [
                NoticeTypeConstant::BUYER_PAY_RECHARGE,//买家充值成功通知
                NoticeTypeConstant::BUYER_PAY_WITHDRAW,//买家提现成功通知
                NoticeTypeConstant::BUYER_PAY_RECHARGE_ADMIN,//买家后台充值通知
                NoticeTypeConstant::BUYER_PAY_CREDIT,//买家积分变动
                NoticeTypeConstant::BUYER_MEMBER_UPDATE,//买家会员升级通知
                NoticeTypeConstant::BUYER_COUPON_SEND //买家优惠券发放通知
            ]
        ],


    ];

    /**
     * 获取消息类型
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNoticeMap(): array
    {
        return self::NOTICE_MAP;
    }
}
