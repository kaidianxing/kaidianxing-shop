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

namespace shopstar\components\notice\config;

use shopstar\models\core\CoreSettings;
use Overtrue\EasySms\Strategies\OrderStrategy;

class SmsConfig
{
    /**
     * 获取短信配置
     * @param $sms
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getConfig($sms)
    {
        $smsSet = CoreSettings::get('sms');
        if (empty($smsSet) || empty($smsSet[$sms['type']])) {
            return error('短信模板设置错误');
        }

        $config = [
            'timeout' => 5.0,
            'default' => [
                'strategy' => OrderStrategy::class,
                'gateways' => [$sms['type']]
            ]
        ];

        // 根据类型配置参数
        switch ($sms['type']) {
            case 'aliyun':
                $config['gateways'] = [
                    'aliyun' => [
                        'access_key_id' => $smsSet[$sms['type']]['access_key_id'],
                        'access_key_secret' => $smsSet[$sms['type']]['access_key_secret'],
                        'sign_name' => $sms['sms_sign']
                    ]
                ];
                break;
            case 'juhe':
                $config['gateways'] = [
                    'juhe' => [
                        'app_key' => $smsSet[$sms['type']]['app_key']
                    ],
                ];
                break;
        }

        return $config;
    }
}
