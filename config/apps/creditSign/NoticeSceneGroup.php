<?php

namespace shopstar\config\apps\creditSign;

use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\interfaces\NoticeSceneGroupInterface;

class NoticeSceneGroup implements NoticeSceneGroupInterface
{
    /**
     * @return \array[][][]
     * @author 青岛开店星信息技术有限公司
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
