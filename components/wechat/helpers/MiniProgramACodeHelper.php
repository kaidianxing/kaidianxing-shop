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

use EasyWeChat\Kernel\Http\StreamResponse;
use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\helpers\StringHelper;
use yii\helpers\Json;

class MiniProgramACodeHelper
{
    /**
     *
     * @author 青岛开店星信息技术有限公司
     * @var \EasyWeChat\MiniProgram\Application|\Moonpie\Macro\Factory|null
     */
    public static $wxappInstance = null;

    /**
     * 获取小程序实例
     * @param array $config 配置项
     * @return \EasyWeChat\MiniProgram\Application|\Moonpie\Macro\Factory|null
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    private static function getInstance(array $config = [])
    {
        if (self::$wxappInstance == null) {
            self::$wxappInstance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, $config)->factory;
        }

        return self::$wxappInstance;
    }

    /**
     * 获取小程序码，适用于需要的码数量极多的业务场景。通过该接口生成的小程序码，永久有效，数量暂无限制。 更多用法详见https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/qr-code.html 获取二维码。
     * @param string $sceneValue
     * @param array $optional
     * @param array $config 小程序配置
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function getUnlimited(string $sceneValue, array $optional = [], array $config = [])
    {
        return self::getCode('getUnlimit', $sceneValue, $optional, $config);
    }

    /**
     * 获取小程序二维码
     * 接口C：适用于需要的码数量较少的业务场景
     * @param string $path 小程序页面路径
     * @param array $optional [fileName]     二维码存储文件名，不传则由系统自动命名
     * @return array|bool|void|StreamResponse
     * @author 青岛开店星信息技术有限公司
     * @link    @link https://developers.weixin.qq.com/miniprogram/dev/api/qrcode.html 获取二维码文档：接口C
     */
    public static function getQrCode(string $path, array $optional = [])
    {
        return self::getCode('getQrCode', $path, $optional);
    }

    /**
     * 获取小程序码
     * 接口A: 适用于需要的码数量较少的业务场景
     * -----------------------
     * 通过该接口生成的小程序码，永久有效
     * 数量限制见文末说明，请谨慎使用。
     * 用户扫描该码进入小程序后，将直接进入 path 对应的页面。
     * -----------------------
     * @param string $path 扫描小程序码后访问的路径
     * @param array $optional [fileName]     二维码存储文件名，不传则由系统自动命名
     * @return array|bool|void|StreamResponse
     * @author 青岛开店星信息技术有限公司
     * @link    @link https://developers.weixin.qq.com/miniprogram/dev/api/qrcode.html 获取二维码文档：接口A
     */
    public static function get(string $path, array $optional = [])
    {
        return self::getCode('get', $path, $optional);
    }

    /**
     * 通用小程序二维码 or 小程序码
     * @param string $func
     * @param string $scene_or_path
     * @param array $optional
     * @param array $config 小程序配置
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    private static function getCode(string $func, string $scene_or_path, array $optional = [], array $config = [])
    {
        try {
            $func === 'getQrCode' && ($width = $optional['width']);

            $directory = $optional['directory'];

            unset($optional['directory']);

            $fileName = $optional['fileName'];

            unset($optional['fileName']);

            $getContent = $optional['get_content'];

            unset($optional['get_content']);

            $optional['page'] = trim($optional['page'], '/');

            $response = self::getInstance($config)->app_code->$func($scene_or_path, $func === 'getQrCode' ? $width : $optional);

            $response = Wechat::apiError($response);

            if (is_error($response)) {
                return $response;
            }

            $content = $response->getBody()->getContents(); //by 青椒
            if (is_object($content)) {
                return error($content->message);
            }

            if (StringHelper::exists($content, ['errmsg', 'errcode'], 'OR')) {
                if (is_array(Json::decode($content))) {
                    $content = Json::decode($content);
                    return error($content['errmsg'], $content['errcode']);
                }
            }

            if ($getContent) {
                return $content;
            }

            if ($directory && $response instanceof StreamResponse) {
                $response->saveAs($directory, $fileName ?? '');
            }
        } catch (\Exception $exception) {
            $response = $exception;
        }


        return Wechat::apiError($response);
    }
}
