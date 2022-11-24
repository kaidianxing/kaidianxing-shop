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

class OfficialAccountDraftsHelper
{
    /**
     * 新建草稿
     * 开发者可新增常用的素材到草稿箱中进行使用。上传到草稿箱中的素材被群发或发布后，该素材将从草稿箱中移除。新增草稿可在公众平台官网-草稿箱中查看和管理。
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function add(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/draft/add?access_token=' . $token['access_token'], Json::encode(['articles' => $data]), [
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
     * 获取草稿
     * 新增草稿后，开发者可以根据草稿指定的字段来下载草稿
     * @param string $mediaId 要获取的草稿的media_id
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function get(string $mediaId)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/draft/get?access_token=' . $token['access_token'], Json::encode(['media_id' => $mediaId]), [
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
     * 删除草稿
     * 新增草稿后，开发者可以根据本接口来删除不再需要的草稿，节省空间。此操作无法撤销，请谨慎操作。
     * @param string $mediaId 要删除的图文消息的id
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function delete(string $mediaId)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/draft/delete?access_token=' . $token['access_token'], Json::encode(['media_id' => $mediaId]), [
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
     * 修改草稿
     * 开发者可通过本接口对草稿进行修改
     * @param string $mediaId 要修改的图文消息的id
     * @param int $index 要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function update(string $mediaId, int $index, array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/draft/update?access_token=' . $token['access_token'], Json::encode([
                'media_id' => $mediaId,
                'index' => $index,
                'articles' => $data,
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
     * 获取草稿总数
     * 开发者可以根据本接口来获取草稿的总数。此接口只统计数量，不返回草稿的具体内容
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function count()
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::getJson('https://api.weixin.qq.com/cgi-bin/draft/count?access_token=' . $token['access_token'], [
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
     * 获取草稿列表
     * 新增草稿之后，开发者可以获取草稿的列表
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @param int $noContent 1 表示不返回 content 字段，0 表示正常返回，默认为 0
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function batchGet(int $offset, int $count = 20, int $noContent = 0)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson('https://api.weixin.qq.com/cgi-bin/draft/batchget?access_token=' . $token['access_token'], Json::encode([
                'offset' => $offset,
                'count' => $count,
                'no_content' => $noContent,
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
}
