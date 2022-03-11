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

/**
 * 获取用户
 * Class OfficialAccountMessageTextHelp
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountMenuHelper
{

    /**
     * 获取实例
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司.
     */
    private static function getInstance()
    {
        return WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;
    }

    /**
     * 创建
     * @param array $buttons
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public static function create(array $buttons)
    {
        try {
            /**
             * @var OfficialAccountChannel $instance
             */
            $instance = self::getInstance();
            $result = $instance->menu->create($buttons);
        } catch (\Exception $result) {
        }

        return Wechat::apiError($result);
    }

    /**
     * 创建
     * @param string|null $menuId
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public static function delete(string $menuId = null)
    {
        try {
            /**
             * @var OfficialAccountChannel $instance
             */
            $instance = self::getInstance();
            $result = $instance->menu->delete($menuId);
        } catch (\Exception $result) {
        }

        return Wechat::apiError($result);
    }


}