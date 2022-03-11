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

namespace shopstar\helpers;

/**
 * Xml助手类
 * Class XmlHelper
 * @package shopstar\helpers
 */
class XmlHelper
{

    /**
     * 从字符串获取XML
     * @param $string
     * @param string $className
     * @param int $options
     * @param string $ns
     * @param bool $isPrefix
     * @return bool|\SimpleXMLElement
     * @author likexin
     */
    public static function fromString($string, $className = 'SimpleXMLElement', $options = 0, $ns = '', $isPrefix = false)
    {
        libxml_disable_entity_loader(true);
        if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
            return false;
        }
        return simplexml_load_string($string, $className, $options, $ns, $isPrefix);
    }

}