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
use shopstar\components\wechat\WechatComponent;
use shopstar\helpers\HttpHelper;
use yii\helpers\Json;

/**
 * 小程序UrlScheme助手类
 * Class MiniProgramUrlSchemeHelper
 * @package shopstar\components\wechat\helpers
 * @author 青岛开店星信息技术有限公司
 */
class MiniProgramUrlSchemeHelper
{

    /**
     * 获取小程序scheme码
     * @param array $data 数据
     * @return array|bool|mixed
     * @link https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/url-scheme/urlscheme.generate.html
     */
    public static function generate(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxa/generatescheme?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }


    /**
     * 获取小程序跳转的URLScheme链接
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function skip(array $data)
    {
        try {

            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();

            $result = HttpHelper::postJson("https://api.weixin.qq.com/wxa/generatescheme?access_token=" . $token['access_token'], Json::encode($data));


        } catch (\Throwable $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

}