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

namespace shopstar\components\wechat\helpers;

use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\components\platform\Wechat;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;

/**
 * 文字消息回复   demo https://www.easywechat.com/docs/4.1/official-account/messages
 * Class OfficialAccountMessageTextHelp
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountMessageTextHelper
{
    /**
     * @param string $openId
     * @param string $message 发送文字
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendText(string $openId, string $message)
    {

        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;

            $text = new Text($message);

            $result = $instance->customer_service->message($text)->to($openId)->send();
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 发送图文消息
     * @param string $openid 目标用户OPENID
     * @param array $newList 图文消息列表二维数组
     * @param array $newList [][title] 标题
     * @param array $newList [][description] 描述
     * @param array $newList [][image] 图片地址
     * @param array $newList [][url] URL地址
     * @return array|bool
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/messages
     */
    public static function sendNews(string $openid, array $newsList)
    {
        if (count($newsList) > 8) {
            return error('图文消息最大数量为8');
        }
        $news = [];
        foreach ($newsList as $newsRow) {
            if (empty($newsRow) || !is_array($newsRow)) {
                continue;
            }
            $news[] = new NewsItem($newsRow);
        }

        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;

            $message = new News($news);

            $result = $instance->customer_service->message($message)->to($openid)->send();
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

}