<?php

namespace shopstar\constants\creditSign;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分签到记录常量
 * Class CreditSignRecordConstant
 * @package shopstar\constants\creditSign
 * @author yuning
 */
class CreditSignRecordConstant extends BaseConstant
{
    /**
     * @Text("日常签到")
     */
    public const RECORD_STATUS_DAY = 0;

    /**
     * @Text("补签")
     */
    public const RECORD_STATUS_SUPPLEMENTARY = 1;

    /**
     * @Text("未删除")
     */
    public const RECORD_IS_DELETE_NO = 0;

    /**
     * @Text("已删除")
     */
    public const RECORD_IS_DELETE_YES = 1;
}