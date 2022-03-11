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

use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\channels\OfficialAccountChannel;
use shopstar\components\wechat\WechatComponent;
use EasyWeChat\Kernel\Exceptions\HttpException;

/**
 * 公众号JsSdk助手
 * Class OfficialAccountJsSdkHelper
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountJsSdkHelper
{

    /**
     * 生成配置
     * @param string $url 分享URL
     * @return array|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeExceptionOrderModel
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function buildConfig(string $url = '')
    {
        /**
         * @var OfficialAccountChannel $instance
         */
        $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, []);

        $jsSdk = $instance->factory->jssdk;
        if (!empty($url)) {
            $jsSdk->setUrl($url);
        }

        try {
            $result = $instance->factory->jssdk->buildConfig([
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'onMenuShareWeibo',
                'hideMenuItems',
            ], false, false, false);
        }catch (HttpException $exception){
            $result = $exception->formattedResponse;
        }

        return Wechat::apiError($result);
    }

}