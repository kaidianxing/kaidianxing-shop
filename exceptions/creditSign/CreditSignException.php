<?php

namespace shopstar\exceptions\creditSign;

use shopstar\bases\exception\BaseException;

/**
 * 积分签到异常类
 * Class CreditSignException
 * @package shopstar\exceptions\creditSign
 * @author yuning
 */
class CreditSignException extends BaseException
{
    /**
     * @Message("签到记录保存失败")
     */
    const CREDIT_SIGN_ADD_RECORD_ERROR = 750200;

    /**
     * @Message("签到统计保存失败")
     */
    const CREDIT_SIGN_SAVE_TOTAL_ERROR = 750201;

    /**
     * @Message("发送积分失败")
     */
    const CREDIT_SIGN_SEND_INTEGRAL_ERROR = 750202;

    /**
     * @Message("奖励记录保存失败")
     */
    const CREDIT_SIGN_ADD_REWARD_RECORD_ERROR = 750203;

    /**
     * @Message("补签功能未开启")
     */
    const CREDIT_SIGN_SUPPLEMENTARY_STATUS_NOT_OPEN_ERROR = 750204;

    /**
     * @Message("补签次数不足")
     */
    const CREDIT_SIGN_SUPPLEMENTARY_NUM_INSUFFICIENT_ERROR = 750205;

    /**
     * @Message("补签扣除积分错误")
     */
    const CREDIT_SIGN_SUPPLEMENTARY_INTEGRAL_INSUFFICIENT_ERROR = 750206;

    /**
     * @Message("签到时间不在活动时间内")
     */
    const CREDIT_SIGN_SIGN_TIME_NOT_ACTIVITY_ERROR = 750207;

    /**
     * @Message("签到活动ID不可为空")
     */
    const CREDIT_SIGN_SIGN_ACTIVITY_ID_NOT_EMPTY_ERROR = 750208;

    /**
     * @Message("奖励ID不可为空")
     */
    const CREDIT_SIGN_SIGN_REWARD_ID_NOT_EMPTY_ERROR = 750209;

    /**
     * @Message(“发送优惠券失败”)
     */
    const CREDIT_SIGN_SEND_COUPON_ERROR = 750210;

    /**
     * @Message("领取奖励失败")
     */
    const CREDIT_SIGN_RECEIVE_REWARD_ERROR = 750211;

    /**
     * @Message("请先签到")
     */
    const CREDIT_SIGN_ERROR = 750212;

    /**
     * @Message("保存统计失败")
     */
    const CREDIT_SIGN_SAVE_MEMBER_TOTAL_ERROR = 750213;

    /**
     * @Message("活动不存在")
     */
    const CREDIT_SIGN_ACTIVITY_NOT_ERROR = 750214;

    /**
     * @Message("活动不支持此渠道")
     */
    const CREDIT_SIGN_ACTIVITY_CLIENT_TYPE_ERROR = 750215;

}