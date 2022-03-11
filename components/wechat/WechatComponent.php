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

namespace shopstar\components\wechat;

use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\bases\WechatChannelInterface;
use Yii;
use yii\base\Component;

/**
 * 微信组件
 * Class WechatComponent
 * @package shopstar\components\wechat
 */
class WechatComponent extends Component
{

    public static $wechat;

    /**
     * @var WechatChannelInterface 存储驱动接口
     */
    private static $instance = null;

    /**
     * @var string 实例存储驱动类型
     */
    private static $channel = null;

    /**
     * 获取实例
     * @param string $channel 渠道类型
     * @param array $config 自动注入设置
     * @return array|WechatChannelInterface|object|null
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getInstance(string $channel, array $config = [])
    {
        if (is_null(self::$instance) || self::$channel != $channel) {
            $channel = strtolower($channel);

            // 获取实现类
            $class = WechatChannelConstant::getClass($channel);
            if (!$class) {
                return error("`{$channel}` Wechat Channel not Found.");
            }

            // 注入固定参数
            $config = array_merge($config, [
                'class' => $class,
            ]);

            // 注入实现类
            self::$instance = Yii::createObject($config);
            unset($config['class']);
        }

        return self::$instance;
    }
}