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
 * 小程序临时素材上传
 * Class MiniProgramMediaHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class MiniProgramMediaHelper
{
    /**
     * 上传图片
     * @param string $path
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadImage(string $path)
    {
        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->media;
            $result = $instance->uploadImage($path);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 微信自定义交易组件专用上传图片
     * @param string $path
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadWxImage(string $path)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/shop/img/upload?access_token=' . $token['access_token'], Json::encode($path), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }
}
