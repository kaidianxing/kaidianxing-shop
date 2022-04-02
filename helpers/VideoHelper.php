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
 * @author 青岛开店星信息技术有限公司
 */
class VideoHelper
{

    /**
     * 获取腾讯视频URL
     * @param string $url
     * @return mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTententVideo(string $url)
    {
        $cacheKey = '{prefix}_' . md5($url);
        // 读取缓存
        $cache = CacheHelper::get($cacheKey);
        if (!empty($cache)) {
            return $cache;
        }
        // 重新获取
        if (strpos($url, 'v.qq')) {
            $url = str_replace('.html', '', trim($url));
            $url = explode('/', $url);
            $vid = end($url);
            // 视频访问参数获取地址
            $url = 'https://vv.video.qq.com/getinfo?vids=' . $vid . '&platform=101001&charge=0&otype=json';
            $json = file_get_contents($url);
            preg_match('/^QZOutputJson=(.*?);$/', $json, $json2);
            $tempStr = json_decode($json2[1], true);
            // 拼接真实视频地址
            $realUrl = 'https://ugcws.video.gtimg.com/' . $tempStr['vl']['vi'][0]['fn'] . "?vkey=" . $tempStr['vl']['vi'][0]['fvkey'];
            CacheHelper::set($cacheKey, $realUrl, 6400);
            return $realUrl;
        }
        return false;
    }

    /**
     * 匹配富文本中的腾讯视频
     * @param string $text
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function parseRichTextTententVideo(string $text): string
    {
        return preg_replace_callback('/<video((?!data-url).)*data-url[\s]*=[\s]*[\'\"](?<src>(.*):[^\'\"]*)[\'\"]/i', function ($matchs) {
            if (!empty($matchs['src'])) {
                return str_replace('src="' . $matchs['src'] . '"', 'src="' . self::getTententVideo($matchs['src']) . '"', $matchs[0]);
            }
        }, $text);
    }

}