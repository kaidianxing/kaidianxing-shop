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

/**
 * 公众号二维码   demo https://www.easywechat.com/docs/4.1/basic-services/qrcode
 * Class OfficialAccountQrCodeHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountQrCodeHelper
{
    /**
     * 获取临时二维码
     * @param array $qrContent
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getQrCode(array $qrContent, string $temporary = 'temporary')
    {
        try {
            /**
             * @var $instance \EasyWeChat\BasicService\QrCode\Client
             */
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;
            // 永久
            if ($qrContent['expire'] == '-86400') {
                $temporary = 'forever';
                $result = $instance->qrcode->$temporary($qrContent['value']);

            }else{
                // 获取二维码
                $result = $instance->qrcode->$temporary($qrContent['value'], $qrContent['expire']);
            }

        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }
}
