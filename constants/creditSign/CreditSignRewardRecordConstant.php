<?php

namespace shopstar\constants\creditSign;

use shopstar\bases\constant\BaseConstant;

/**
 * 积分签到奖励
 * Class CreditSignRewardRecordConstant
 * @package shopstar\constants\creditSign
 * @author yuning
 */
class CreditSignRewardRecordConstant extends BaseConstant
{
    /**
     * @Text("日签奖励")
     */
    const REWARD_RECORD_TYPE_DAY = 0;

    /**
     * @Text("连签奖励")
     */
    const REWARD_RECORD_TYPE_CONTINUITY = 1;

    /**
     * @Text("递增奖励")
     */
    const REWARD_RECORD_TYPE_INCREASING = 2;

    /**
     * @Text("未领取奖励")
     */
    const REWARD_RECORD_STATUS_RECEIVE_NO = 0;

    /**
     * @Text("已领取奖励")
     */
    const REWARD_RECORD_STATUS_RECEIVE_YES = 1;

    /**
     * @Text("未删除")
     */
    const REWARD_RECORD_IS_DELETE_NO = 0;

    /**
     * @Text("已删除")
     */
    const REWARD_RECORD_IS_DELETE_YES = 1;
}