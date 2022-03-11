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

namespace shopstar\constants\member;


use shopstar\bases\constant\BaseConstant;
use shopstar\models\member\MemberCreditRecordModel;

/**
 * 积分余额类型
 * Class MemberCreditRecordStatusConst
 * @package shopstar\constants\member
 * @method getMessage($code) static 获取文案
 */
class MemberCreditRecordStatusConstant extends BaseConstant
{
    /*
     * 余额
     */
    
    /**
     * 注意: 新添加类型一定要去 MemberCreditRecordModel 下的 $balanceSendType 等 变量里添加上
     * @var MemberCreditRecordModel
     */
    
    /**
     * @Message("余额充值")
     */
    public const BALANCE_STATUS_RECHARGE = 10;

    /**
     * @Message("后台余额充值")
     */
    public const BALANCE_STATUS_BACKGROUND = 11;

    /**
     * @Message("余额提现")
     */
    public const BALANCE_STATUS_WITHDRAW = 12;

    /**
     * @Message("余额抵扣")
     */
    public const BALANCE_STATUS_DEDUCTION = 13;

    /**
     * @Message("余额支付")
     */
    public const BALANCE_STATUS_PAY = 14;

    /**
     * @Message("余额退款")
     */
    public const BALANCE_STATUS_REFUND = 15;

    /**
     * @Message("余额赠送")
     */
    public const BALANCE_STATUS_SEND = 16;

    /**
     * @Message("佣金提现")
     */
    public const COMMISSION_STATUS_WITHDRAW = 17;

    /**
     * @Message("新人送礼")
     */
    public const NEW_MEMBER_SEND_BALANCE = 18;

    /**
     * @Message("充值奖励")
     */
    public const RECHARGE_REWARD_SEND_BALANCE = 19;

    /**
     * @Message("关注海报余额赠送")
     */
    public const BALANCE_STATUS_POSTER_SEND = 30;

    /**
     * @Message("消费奖励")
     */
    public const CONSUME_REWARD_SEND_BALANCE = 31;
 
    /**
     * @Message("购物奖励")
     */
    public const SHOPPING_REWARD_SEND_BALANCE = 32;
 
    /**
     * @Message("消费奖励退回")
     */
    public const CONSUME_REWARD_REFUND_BALANCE = 33;
 
    /**
     * @Message("购物奖励退回")
     */
    public const SHOPPING_REWARD_REFUND_BALANCE = 34;
 
    /**
     * @Message("评价奖励")
     */
    public const COMMENT_REWARD_SEND_BALANCE = 35;
    
    /**
     * @Message("拼团返利")
     */
    public const BALANCE_STATUS_GROUPS_REBATE = 44;
    
    /**
     * @Message("分销商达标奖")
     */
    public const PERFORMANCE_AWARD_BALANCE = 36;
    
    
    /**
     * 积分
     */
    
    
    /**
     * 注意: 新添加类型一定要去 MemberCreditRecordModel 下的 $balanceSendType 等 变量里添加上
     * @var MemberCreditRecordModel
     */
    
    /**
     * @Message("后台充值")
     */
    public const CREDIT_STATUS_BACKGROUND = 20;

    /**
     * @Message("积分赠送")
     */
    public const CREDIT_STATUS_SEND = 21;

    /**
     * @Message("积分抵扣")
     */
    public const CREDIT_STATUS_DEDUCTION = 22;

    /**
     * @Message("取消订单返还积分")
     */
    public const CREDIT_STATUS_ORDER_CANCEL = 23;

    /**
     * @Message("售后商品退还积分")
     */
    public const CREDIT_STATUS_REFUND = 24;
    
    /**
     * 无用了  没有充值送积分了 只有积分奖励
     * @Message("积分赠送退还")
     */
    public const CREDIT_STATUS_SEND_BACK = 25;
    
    /**
     * @Message("新人送礼")
     */
    public const NEW_MEMBER_SEND_CREDIT = 26;
    
    /**
     * @Message("充值奖励")
     */
    public const RECHARGE_REWARD_SEND_CREDIT = 27;
    
    /**
     * @Message("消费奖励")
     */
    public const CONSUME_REWARD_SEND_CREDIT = 28;
    
    /**
     * @Message("关注海报积分赠送")
     */
    public const CREDIT_STATUS_SEND_POSTER = 29;

    /**
     * @Message("消费奖励退回")
     */
    public const CONSUME_REWARD_REFUND_CREDIT = 40;
    
    /**
     * @Message("购物奖励退回")
     */
    public const SHOPPING_REWARD_REFUND_CREDIT = 41;
    
    /**
     * @Message("购物奖励")
     */
    public const SHOPPING_REWARD_SEND_CREDIT = 42;

    /**
     * @Message("拼团返利")
     */
    public const CREDIT_STATUS_GROUPS_REBATE = 43;
    
    /**
     * @Message("评价奖励")
     */
    public const COMMENT_REWARD_SEND_CREDIT = 45;
    
    /**
     * @Message("积分商城支付")
     */
    public const CREDIT_SHOP_PAY = 46;

    
    /**
     * @Message("购物送积分")
     */
    public const ORDER_GIVE_CREDIT = 47;
    
    /**
     * @Message("积分商城售后退还积分")
     */
    public const CREDIT_STATUS_CREDIT_SHOP_REFUND = 48;
    
    /**
     * @Message("分销商达标奖")
     */
    public const PERFORMANCE_AWARD_CREDIT = 51;
    
    
    

    /**
     * @Message("店铺笔记奖励")
     */
    public const ARTICLE_REWARD_SEND_CREDIT = 49;

    /**
     * @Message("店铺笔记奖励")
     */
    public const ARTICLE_REWARD_SEND_BALANCE = 50;



    
  
    /**
     * 注意: 新添加类型一定要去 MemberCreditRecordModel 下的 $balanceSendType 等 变量里添加上
     * @var MemberCreditRecordModel
     */
}