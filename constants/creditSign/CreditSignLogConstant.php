<?php

namespace shopstar\constants\creditSign;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分签到日志常量
 * Class CreditSignLogConstant
 * @package shopstar\constants\creditSign
 * @author yuning
 */
class CreditSignLogConstant extends BaseConstant
{

    /**
     * @Text("签到-添加活动")
     */
    public const CREDIT_SIGN_ACTIVITY_ADD_LOG = 750000;

    /**
     * @Text("签到-编辑活动")
     */
    public const CREDIT_SIGN_ACTIVITY_EDIT_LOG = 750001;

    /**
     * @Text("签到-删除活动")
     */
    public const CREDIT_SIGN_ACTIVITY_STOP_LOG = 750002;

    /**
     * @Text("签到-手动停止活动")
     */
    public const CREDIT_SIGN_ACTIVITY_DELETED_LOG = 750003;
}