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

use shopstar\components\notice\interfaces\NoticeMakeTypeMapInterface;
use shopstar\constants\components\notice\NoticeTypeConstant;

/**
 * 拼团消息通知映射
 * Class NoticeMakeMap
 * @package shopstar\config\apps\groups
 * @author likexin
 */
class NoticeMakeMap implements NoticeMakeTypeMapInterface
{

    /**
     * 消息类型映射
     * @author Jason
     */
    public const NOTICE_MAP = [
        'groups' => [
            'class' => 'plugins\groups\config\NoticeMake',
            'item' => [
                NoticeTypeConstant::GROUPS_SUCCESS, //买家拼团成功通知
                NoticeTypeConstant::GROUPS_JOIN, //买家参与拼团通知
                NoticeTypeConstant::GROUPS_DEFEATED, //买家拼团失败通知
            ],
        ],
    ];

    /**
     * @return array[]
     * @author Jason
     */
    public static function getNoticeMap(): array
    {
        return self::NOTICE_MAP;
    }

}