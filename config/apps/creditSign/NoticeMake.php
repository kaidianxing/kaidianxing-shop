<?php

namespace shopstar\config\apps\creditSign;

use shopstar\components\notice\bases\BaseMake;

class NoticeMake extends BaseMake
{

    /**
     * 预留字段,用于转化
     * @var string[]
     * @author yuning
     */
    public array $reserveField = [
        'activity_name' => '[业务名称]',
        'now_sign_day' => '[当前进度]',
        'nickname' => '[执行人]',
        'activity_time' => '[执行时间]',
        'remark' => '[备注]',
        'day_num' => '[签到天数]',
        'sign_reward' => '[签到奖励]',
        'sign_tips' => '[温馨提示]',
        'wxapp_activity_name' => '[活动名称]',
        'wxapp_sign_day' => '[累计签到]'
    ];
}