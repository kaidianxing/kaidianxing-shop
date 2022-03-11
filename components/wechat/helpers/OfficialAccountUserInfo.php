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
use shopstar\helpers\HttpHelper;
use yii\helpers\Json;
use shopstar\components\platform\Wechat;

class OfficialAccountUserInfo
{
    /**
     * 获取用户信息
     * @param string $openid
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getUserInfo(string $openid)
    {
        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;


//            获取access_token
//            $accessTokenArr = $instance->access_token->getToken();
//            $accessToken = $accessTokenArr['access_token'] ?? '';
//            $tokenRes = HttpHelper::get('https://api.weixin.qq.com/cgi-bin/token' . '?' .
//                http_build_query(['grant_type' => 'client_credential', 'appid' => $instance->app_id, 'secret' => $instance->secret]));
//            $tokenRes = Json::decode($tokenRes);
//            $accessToken = $tokenRes['access_token'];

//            获取用户信息
//            $result = HttpHelper::get('https://api.weixin.qq.com/cgi-bin/user/info' . '?' .
//                http_build_query(['access_token' => $accessToken, 'openid' => $openid]));
            $result = $instance->user->get($openid);

        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }
}