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

namespace shopstar\components\byteDance\helpers;

use shopstar\components\byteDance\ByteDanceComponent;
use EasyWeChat\Kernel\Http\StreamResponse;
use yii\base\InvalidConfigException;

class ByteDanceQrcodeHelper
{
    /**
     * @var \Moonpie\Macro\ByteMiniProgram\Application
     */
    public static $wxappInstance = null;
    
    /**
     * 获取小程序实例
     * @param string $channel
     * @return \Moonpie\Macro\ByteMiniProgram\Application|null |null |null
     * @throws InvalidConfigException
     */
    private static function getInstance(string $channel)
    {
        if (self::$wxappInstance == null) {
            self::$wxappInstance = ByteDanceComponent::getInstance($channel)->factory;
        }
        
        return self::$wxappInstance;
    }
    
    /**
     * 获取二维码
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array|\EasyWeChat\Kernel\Http\StreamResponse|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCode( string $path, array $params,  array $options = [])
    {
        try {
            $response = self::getInstance($params['appname'])->app_code->get($path, $params);
            $content = $response->getBody()->getContents();
            // 直接返回
            if ($options['get_content']) {
                return $content;
            }
            // 存
            if ($options['directory'] && $response instanceof StreamResponse) {
                $response->saveAs($options['directory'], $options['file_name']);
            }
        } catch (\Throwable $exception) {
            return error('请检查配置');
        }
        
        return true;
    }
}