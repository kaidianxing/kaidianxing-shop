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



namespace shopstar\components\notice\bases;


use shopstar\components\notice\config\NoticeMakeTypeMap;
 

/**
 * 转化
 * Class NoticeMakeType
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\notice\makes
 */
class NoticeMakeType
{
    /**
     * 获取构建数据分类
     * @param string $identify
     * @param string $pluginName
     * @return bool|int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMakeTypeClass(string $identify, string $pluginName = '')
    {
        //默认消息类型
        $noticeMap = NoticeMakeTypeMap::getNoticeMap();

        //检测是否有插件，如果插件存在则合并map
        if ($pluginName) {
            $filePath = \Yii::getAlias('@shopstar') . '/config/apps/' . $pluginName . '/NoticeSceneGroup.php';
            if (is_file($filePath)) {

                $className = "\\shopstar\\config\\apps\\{$pluginName}\\NoticeMakeMap";

                $pluginNoticeMap = (new $className)::getNoticeMap();
            }
            !empty($pluginNoticeMap) && $noticeMap = array_merge($noticeMap, $pluginNoticeMap);
        }
        foreach ($noticeMap as $key => $item) {
            if (in_array($identify, $item['item'])) {
                return $item['class'];
            }
        }

        return false;
    }
}
