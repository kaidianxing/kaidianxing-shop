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

class MiniProgramSubscriptionNoticeHelper
{
    /**
     * @author 青岛开店星信息技术有限公司
     * @var \EasyWeChat\MiniProgram\Application|\Moonpie\Macro\Factory|null
     */
    public static $wxappInstance = null;

    /**
     * 获取小程序实例
     * @return \EasyWeChat\MiniProgram\Application|\Moonpie\Macro\Factory|null
     * @author 青岛开店星信息技术有限公司
     */
    private static function getInstance()
    {
        if (self::$wxappInstance == null) {
            self::$wxappInstance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory;
        }
        return self::$wxappInstance;
    }

    /**
     * 添加账号下的个人模板
     * @param int $tid
     * @param array $kidList
     * @param string $sceneDesc
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addTemplate(int $tid, array $kidList, string $sceneDesc)
    {
        try {
            $result = self::getInstance()->subscribe_message->addTemplate($tid, $kidList, $sceneDesc);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);
    }

    /**
     * 删除帐号下的个人模板
     * @param string $templateId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteTemplate(string $templateId)
    {
        try {
            $result = self::getInstance()->subscribe_message->deleteTemplate($templateId);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);

    }

    /**
     * 获取小程序账号的类目
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCategory()
    {
        try {
            $result = self::getInstance()->subscribe_message->getCategory();
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);

    }

    /**
     * 获取当前帐号下的个人模板列表
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTemplates()
    {
        try {
            $result = self::getInstance()->subscribe_message->getTemplates();
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);

    }

    /**
     * 获取帐号所属类目下的公共模板标题
     * @param array $ids 类目 id
     * @param int $start 用于分页，表示从 start 开始。从 0 开始计数。
     * @param int $limit 用于分页，表示拉取 limit 条记录。最大为 30。
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTemplateTitles(array $ids, int $start, int $limit)
    {
        try {
            $result = self::getInstance()->subscribe_message->getTemplateTitles($ids, $start, $limit);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);
    }

    /**
     * 发送
     * @param $data
     * @return array|bool|mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function send($data)
    {
        try {
            $result = self::getInstance()->subscribe_message->send($data);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result);
    }
}
