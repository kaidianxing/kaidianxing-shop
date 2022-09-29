<?php

namespace shopstar\config\apps\creditSign;

use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\interfaces\NoticeSceneGroupInterface;

class NoticeSceneGroup implements NoticeSceneGroupInterface
{
    /**
     * @return \array[][][]
     * @author miaobowen
     */
    public static function getSceneGroupMap()
    {
        return [
            'buyer_notice' => [
                'buyer_notice' => [
                    NoticeTypeConstant::CREDIT_SIGN_NOTICE => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::CREDIT_SIGN_NOTICE),
                        'item' => ['wechat', 'wxapp', 'sms']
                    ],
                ]
            ]
        ];
    }
}