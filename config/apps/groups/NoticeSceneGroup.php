<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\config\apps\groups;

use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\interfaces\NoticeSceneGroupInterface;

/**
 * 拼团初始化消息数据
 * Class NoticeSceneGroup
 * @package shopstar\config\apps\groups
 * @author likexin
 */
class NoticeSceneGroup implements NoticeSceneGroupInterface
{

    /**
     * @return \array[][][]
     * @author likexin
     */
    public static function getSceneGroupMap(): array
    {
        /**
         * 初始化消息数据
         * @author Jason
         */
        return [
            'buyer_notice' => [
                'buyer_notice' => [
                    NoticeTypeConstant::GROUPS_SUCCESS => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::GROUPS_SUCCESS),
                        'item' => ['wechat', 'subscribe', 'wxapp', 'sms']
                    ],
                    NoticeTypeConstant::GROUPS_JOIN => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::GROUPS_JOIN),
                        'item' => ['wechat', 'subscribe', 'sms']
                    ],
                    NoticeTypeConstant::GROUPS_DEFEATED => [
                        'title' => NoticeTypeConstant::getText(NoticeTypeConstant::GROUPS_DEFEATED),
                        'item' => ['wechat', 'subscribe', 'sms']
                    ],
                ]
            ]
        ];
    }

}