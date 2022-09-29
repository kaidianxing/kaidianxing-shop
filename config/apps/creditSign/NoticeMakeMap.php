<?php

namespace shopstar\config\apps\creditSign;

use shopstar\components\notice\interfaces\NoticeMakeTypeMapInterface;
use shopstar\constants\components\notice\NoticeTypeConstant;

class NoticeMakeMap implements NoticeMakeTypeMapInterface
{
    public const NOTICE_MAP = [
        'creditSign' => [
            'class' => 'plugins\creditSign\config\NoticeMake',
            'item' => [
                NoticeTypeConstant::CREDIT_SIGN_NOTICE, // 提醒用户签到通知
            ],
        ],
    ];

    /**
     * @return array[]
     * @author miaobowen
     */
    public static function getNoticeMap(): array
    {
        return self::NOTICE_MAP;
    }
}