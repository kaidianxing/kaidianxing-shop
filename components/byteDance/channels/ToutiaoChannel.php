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

namespace shopstar\components\byteDance\channels;

use shopstar\components\byteDance\bases\BaseByteDanceChannel;
use shopstar\models\shop\ShopSettings;
use Moonpie\Macro\Factory;

/**
 * 头条渠道
 * Class ToutiaoChannel
 * @package shopstar\components\byteDance\channels
 */
class ToutiaoChannel extends BaseByteDanceChannel
{
    /**
     * @var string 字节跳动appid
     */
    public $appid;
    
    /**
     * @var string 抖音app secret
     */
    public $app_secret;

    /**
     * @var \Moonpie\Macro\ByteMiniProgram\Application
     */
    public $factory;
    
    /**
     * @author 青岛开店星信息技术有限公司
     */
    public function autoloadConfig()
    {
        // 读取店铺设置
        $settings = ShopSettings::get('channel_setting.byte_dance');
        $this->appid = $settings['appid'];
        $this->app_secret = $settings['app_secret'];
    }
    
    /**
     * @author 青岛开店星信息技术有限公司
     */
    public function makeFactory()
    {
        $this->factory = Factory::byteMiniProgram([
            'app_id' => $this->appid,
            'secret' => $this->app_secret,
        ]);
        
    }
}