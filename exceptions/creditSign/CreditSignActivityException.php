<?php

namespace shopstar\exceptions\creditSign;

use shopstar\bases\exception\BaseException;

/**
 * 积分签到活动异常类
 * Class CreditSignActivityException
 * @package shopstar\exceptions\creditSign
 * @author yuning
 */
class CreditSignActivityException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const PARAMETER_NOT_FOUND = 750100;

    /**
     * @Message("新增失败")
     */
    const CREDIT_SIGN_ADD_ACTIVITY_FAIL = 750101;

    /**
     * @Message("活动不存在")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_NOT_EXISTS = 750102;

    /**
     * @Message("活动已停止,不可修改")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_IS_STOP = 750103;

    /**
     * @Message("未修改数据")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_NOT_CHANGE = 750104;

    /**
     * @Message("修改时间不能小于当前结束时间")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_TIME_ERROR = 750105;

    /**
     * @Message("该时间段已存在活动")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_TIME_IS_EXISTS = 750106;

    /**
     * @Message("校验参数错误")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_PARAMS_ERROR = 750107;

    /**
     * @Message("修改失败")
     */
    const CREDIT_SIGN_EDIT_ACTIVITY_FAIL = 750108;

    /**
     * @Message("参数错误")
     */
    const CREDIT_SIGN_DELETE_ACTIVITY_PARAMS_ERROR = 750109;

    /**
     * @Message("活动不存在")
     */
    const CREDIT_SIGN_DELETE_ACTIVITY_REWARD_NOT_EXISTS = 750110;

    /**
     * @Message("删除失败")
     */
    const CREDIT_SIGN_DELETE_ACTIVITY_FAIL = 750111;

    /**
     * @Message("活动不存在")
     */
    const CREDIT_SIGN_MANUAL_STOP_ACTIVITY_NOT_EXISTS = 750112;

    /**
     * @Message("活动状态错误")
     */
    const CREDIT_SIGN_MANUAL_STOP_ACTIVITY_STATUS_ERROR = 750113;

    /**
     * @Message("活动停止失败")
     */
    const CREDIT_SIGN_MANUAL_STOP_ACTIVITY_FAIL = 750114;
}