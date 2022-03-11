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

namespace shopstar\components\wechat\channels;

use shopstar\components\wechat\bases\BaseWechatChannel;
use shopstar\components\wechat\bases\WechatChannelInterface;
use shopstar\models\shop\ShopSettings;
use EasyWeChat\Factory;

/**
 * 微信组件小程序渠道
 * Class MiniProgramChannel
 * @package shopstar\components\wechat\channels
 */
class MiniProgramChannel extends BaseWechatChannel implements WechatChannelInterface
{

    /**
     * @var \EasyWeChat\MiniProgram\Application
     */
    public $factory;

    /**
     * @var string 小程序AppID
     */
    public $app_id;

    /**
     * @var string 小程序Secret
     */
    public $secret;

    /**
     * @var string 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
     */
    public $response_type = 'array';

    /**
     * @var string[] 日志参数
     */
    public $log = [
        'level' => YII_DEBUG ? 'debug' : 'error',
        'file' => SHOP_STAR_RUNTIME_PATH . '/wechat_logs/mini_program.log',
    ];

    /**
     * 自动加载店铺设置
     * @return mixed
     * @author likexin
     */
    public function autoloadConfig()
    {
        // 读取店铺设置
        $settings = ShopSettings::get('channel_setting.wxapp');
        // 将设置项挂载到类上
        $this->app_id = (string)$settings['appid'];
        $this->secret = (string)$settings['app_secret'];
    }

    /**
     * 创建工厂
     * @author likexin
     */
    public function makeFactory()
    {
        $this->factory = Factory::miniProgram([
            'app_id' => $this->app_id,
            'secret' => $this->secret,
            'response_type' => $this->response_type,
            'log' => $this->log,
        ]);
    }

}
