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
 * 公众号模板消息   demo https://www.easywechat.com/docs/4.1/official-account/template_message
 * Class OfficialAccountMessageHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountMessageHelper
{
    /**
     * 获取支持的行业列表
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getIndustry()
    {
        try {
            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->template_message;
            $result = $instance->getIndustry();
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 添加模板
     * @param string $shortId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addTemplate(string $shortId)
    {
        try {
            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */

            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->template_message;
            $result = $instance->addTemplate($shortId);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 获取所有模板列表
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPrivateTemplates()
    {
        try {
            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->template_message;
            $result = $instance->getPrivateTemplates();
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * @param string $templateId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deletePrivateTemplate(string $templateId)
    {
        try {

            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->template_message;
            $result = $instance->deletePrivateTemplate($templateId);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);
    }

    /**
     * @param array $messageData
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function send(array $messageData)
    {
        try {

            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->template_message;
            $result = $instance->send($messageData);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 添加一次性订阅消息模板
     * @param int $tid
     * @param array $kidList
     * @param string $sceneDesc
     * @return array|bool|mixed
     * @author likexin
     */
    public static function addTemplateSubscribe(int $tid, array $kidList, string $sceneDesc)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=' . $token['access_token'], Json::encode([
                'tid' => $tid,
                'kidList' => $kidList,
                'sceneDesc' => $sceneDesc,
            ]), [
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
     * 删除一次性订阅消息模板
     * @param string $templateId 用户的模板id
     * @return array|bool|mixed
     * @author likexin
     */
    public static function deleteTemplateSubscribe(string $templateId)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token=' . $token['access_token'], Json::encode([
                'priTmplId' => $templateId,
            ]), [
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
     * 发送一次性订阅消息
     * @param array $messageData
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author likexin
     */
    public static function sendSubscription(array $messageData)
    {
        try {
            /**
             * @var $instance \EasyWeChat\OfficialAccount\TemplateMessage\Client
             */
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token=' . $token['access_token'], Json::encode($messageData), [
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
