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

namespace shopstar\constants\components\notice;

use shopstar\bases\constant\BaseConstant;

/**
 * Class NoticeTypeConstant
 * @author 青岛开店星信息技术有限公司
 * @package apps\notice\constant
 */
class NoticeTypeConstant extends BaseConstant
{
    //验证码
    /**
     * @Text("用户注册")
     */
    public const VERIFY_CODE_USER_REG = 'user_reg';

    /**
     * @Text("找回密码")
     */
    public const VERIFY_CODE_RETRIEVE_PWD = 'retrieve_pwd';

    /**
     * @Text("修改绑定手机号")
     */
    public const VERIFY_CODE_CHANGE_BIND = 'change_bind';

    /**
     * @Text("用户登录")
     */
    public const VERIFY_CODE_LOGIN_CODE = 'login_code';

    /**
     * @Text("绑定手机号")
     */
    public const VERIFY_CODE_BIND = 'bind';

    /**
     * @Text("卖家订单付款通知")
     */
    public const SELLER_ORDER_PAY = 'seller_order_pay'; //卖家订单付款通知

    /**
     * @Text("卖家订单收货通知")
     */
    public const SELLER_ORDER_RECEIVE = 'seller_order_receive';//卖家订单收货通知

    /**
     * @Text("卖家库存预警通知")
     */
    public const SELLER_STOCK_WARNING = 'seller_stock_warning';//卖家库存预警通知

    /**
     * @Text("卖家商品付款通知")
     */
    public const SELLER_GOODS_PAY = 'seller_goods_pay';//卖家商品付款通知

    /**
     * @Text("卖家订单维权通知")
     */
    public const SELLER_ORDER_REFUND = 'seller_order_refund';//卖家订单维权通知


    /**
     * @Text("买家优惠券发放通知")
     */
    public const BUYER_COUPON_SEND = 'buyer_coupon_send';//买家优惠券发放通知

    /**
     * @Text("买家订单发货通知")
     */
    public const BUYER_ORDER_SEND = 'buyer_order_send';//买家订单发货通知

    /**
     * @Text("买家订单取消通知")
     */
    public const BUYER_ORDER_CANCEL = 'buyer_order_cancel';//买家订单取消通知

    /**
     * @Text("订单手动退款通知")
     */
    public const BUYER_ORDER_CANCEL_AND_REFUND = 'buyer_order_cancel_and_refund';//卖家订单手动退款通知

    /**
     * @Text("买家订单支付通知")
     */
    public const BUYER_ORDER_PAY = 'buyer_order_pay';//买家订单支付通知

    /**
     * @Text("买家订单收货通知")
     */
    public const BUYER_ORDER_RECEIVE = 'buyer_order_receive';//买家订单收货通知

    /**
     * @Text("买家订单状态更新通知")
     */
    public const BUYER_ORDER_STATUS = 'buyer_order_status';//买家订单状态更新通知

    /**
     * @Text("买家退款成功通知")
     */
    public const BUYER_REFUND_MONEY = 'buyer_refund_money';//买家退款成功通知

    /**
     * @Text("买家换货成功通知")
     */
    public const BUYER_REFUND_EXCHANGE = 'buyer_refund_exchange';//买家换货成功通知

    /**
     * @Text("买家退款发货通知")
     */
    public const BUYER_REFUND_SEND = 'buyer_refund_send';//买家退款发货通知

    /**
     * @Text("买家退款申请拒绝通知")
     */
    public const BUYER_REFUND_REJECT = 'buyer_refund_reject';//买家退款申请拒绝通知


    /**
     * @Text("买家充值成功通知")
     */
    public const BUYER_PAY_RECHARGE = 'buyer_pay_recharge';//买家充值成功通知

    /**
     * @Text("买家提现成功通知")
     */
    public const BUYER_PAY_WITHDRAW = 'buyer_pay_withdraw';//买家提现成功通知

    /**
     * @Text("买家后台充值通知")
     */
    public const BUYER_PAY_RECHARGE_ADMIN = 'buyer_pay_recharge_admin';//买家后台充值通知

    /**
     * @Text("买家积分变动")
     */
    public const BUYER_PAY_CREDIT = 'buyer_pay_credit';//买家积分变动

    /**
     * @Text("买家会员升级通知")
     */
    public const BUYER_MEMBER_UPDATE = 'buyer_member_update';//买家会员升级通知



    // 分銷
    /**
     * @Text("卖家申请成为分销商通知")
     */
    public const COMMISSION_SELLER_APPLY = 'commission_seller_apply'; // 卖家申请成为分销商通知
    
    /**
     * @Text("卖家申请提现通知")
     */
    public const COMMISSION_SELLER_WITHDRAW = 'commission_seller_withdraw'; // 卖家申请提现通知
    
    /**
     * @Text("卖家新增分销订单通知")
     */
    public const COMMISSION_SELLER_ADD_COMMISSION_ORDER = 'commission_seller_add_commission_order'; // 新增分销订单通知
    
    /**
     * @Text("买家成为分销商通知")
     */
    public const COMMISSION_BUYER_AGENT_BECOME = 'commission_buyer_agent_become'; // 买家成为分销商通知
    
    /**
     * @Text("买家新增下级通知")
     */
    public const COMMISSION_BUYER_AGENT_ADD_CHILD = 'commission_buyer_agent_add_child'; // 买家新增下级通知
    
    /**
     * @Text("买家下级支付通知")
     */
    public const COMMISSION_BUYER_CHILD_PAY = 'commission_buyer_child_pay'; // 买家下级支付通知
    
    /**
     * @Text("买家下级收货通知")
     */
    public const COMMISSION_BUYER_CHILD_RECEIVE = 'commission_buyer_child_receive'; // 买家下级收货通知
    
    /**
     * @Text("买家申请提现通知")
     */
    public const COMMISSION_BUYER_WITHDRAW_APPLY = 'commission_buyer_withdraw_apply'; // 买家申请提现通知
    
    /**
     * @Text("买家申请提现失败通知")
     */
    public const COMMISSION_BUYER_WITHDRAW_APPLY_FAIL = 'commission_buyer_withdraw_apply_fail'; // 买家申请提现失败通知
    
    /**
     * @Text("买家提现完成通知")
     */
    public const COMMISSION_BUYER_WITHDRAW_FINISH = 'commission_buyer_withdraw_finish'; // 买家提现完成通知
    
    /**
     * @Text("买家佣金打款通知")
     */
    public const COMMISSION_BUYER_COMMISSION_PAY = 'commission_buyer_commission_pay'; // 买家佣金打款通知
    
    /**
     * @Text("买家分销等级升级通知")
     */
    public const COMMISSION_BUYER_COMMISSION_UPGRADE = 'commission_buyer_commission_upgrade'; // 买家分销等级升级通知
    
    /**
     * @Text("买家新增下线通知")
     */
    public const COMMISSION_BUYER_AGENT_ADD_CHILD_LINE = 'commission_buyer_agent_add_child_line'; // 买家新增下线通知
    
    
    
    // 商品预售
    /**
     * @Text("支付尾款通知")
     */
    public const PRESELL_BUYER_PAY_FINAL = 'presell_buyer_pay_final'; // 买家支付尾款通知

    /**
     * @Text("表单受理通知")
     */
    public const DIYFORM_SUBMIT_SEND = 'diyform_submit';//表单受理通知

    //拼团
    /**
     * @Text("拼团失败通知")
     */
    public const GROUPS_DEFEATED = 'groups_defeated';

    /**
     * @Text("拼团成功通知")
     */
    public const GROUPS_SUCCESS = 'groups_success';

    /**
     * @Text("参与拼团通知")
     */
    public const GROUPS_JOIN = 'groups_join';


    //拼团返利
    /**
     * @Text("拼团失败返利通知")
     */
    public const GROUPS_REBATE_DEFEATED = 'groups_rebate_defeated';

    /**
     * @Text("拼团成功返利通知")
     */
    public const GROUPS_REBATE_SUCCESS = 'groups_rebate_success';

    /**
     * @Text("参与拼团返利通知")
     */
    public const GROUPS_REBATE_JOIN = 'groups_rebate_join';

    // 核销
    /**
     * @Text("核销成功通知")
     */
    public const VERIFY_SUCCESS = 'verify_success'; // 买家核销成功通知

    /**
     * @Text("核销员扫码绑定成功通知")
     */
    public const VERIFY_QRCODE_BIND_SUCCESS = 'verify_qrcode_bind_success';

	//商家端
    /**
     * @Text("用户注册")
     */
    public const SHOP_VERIFY_CODE_USER_REG = 'shop_user_reg';

    /**
     * @Text("找回密码")
     */
    public const SHOP_VERIFY_CODE_RETRIEVE_PWD = 'shop_retrieve_pwd';

    // 人信云
    /**
     * @Text("人信云咨询提醒通知")
     */
    public const RXY_ADVISORY_REMINDER = 'rxy_advisory_reminder';

    /**
     * @Text("人信云咨询回复提醒通知")
     */
    public const RXY_ADVISORY_REMINDER_REPLY = 'rxy_advisory_reminder_reply';

    // 积分签到
    /**
     * @Text("积分签到定时通知")
     */
    public const CREDIT_SIGN_NOTICE = 'credit_sign_notice';
    
}
