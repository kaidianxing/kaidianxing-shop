<?php

namespace shopstar\constants\creditSign;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分签到通知常量
 * Class CreditSignMemberTotalConstant
 * @package shopstar\constants\creditSign
 * @author yuning
 */
class CreditSignMemberTotalConstant extends BaseConstant
{
    /**
     * @Text("关闭通知")
     */
    public const IS_REMIND_NO = 0;

    /**
     * @Text("开启通知")
     */
    public const IS_REMIND_YES = 1;
}