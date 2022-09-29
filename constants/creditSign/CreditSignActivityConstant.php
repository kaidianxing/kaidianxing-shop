<?php

namespace shopstar\constants\creditSign;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分签到状态常量
 * Class CreditSignActivityConstant
 * @package shopstar\constants\creditSign
 * @author yuning
 */
class CreditSignActivityConstant extends BaseConstant
{
    /**
     * @Text("未开始")
     */
    public const STATUS_WAIT = 0;

    /**
     * @Text("进行中")
     */
    public const STATUS_UNDERWAY = 1;

    /**
     * @Text("自动停止")
     */
    public const STATUS_MANUAL_END = -1;

    /**
     * @Text("手动停止")
     */
    public const STATUS_MANUAL_STOP = -2;

    /**
     * @Text("未删除")
     */
    public const IS_DELETE_NO = 0;

    /**
     * @Text("已删除")
     */
    public const IS_DELETE_YES = 1;
}