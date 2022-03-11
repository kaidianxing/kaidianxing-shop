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

namespace shopstar\components\payment\base;

use shopstar\bases\constant\BaseConstant;

/**
 * 获取支付方式
 * Class PayTypeConstance
 * @package shopstar\constants
 * @method getMessage($code) static 获取文案
 * @method getIdentify($code) static 获取标识
 */
class PayTypeConstant extends BaseConstant
{
    /**
     * @message("余额支付")
     * @Identify("balance")
     */
    public const PAY_TYPE_BALANCE = 2;

    /**
     * @message("货到付款")
     * @Identify("delivery")
     */
    public const PAY_TYPE_DELIVERY = 3;

    /**
     * @message("微信支付")
     * @Identify("wechat")
     */
    public const PAY_TYPE_WECHAT = 20;

    /**
     * @message("支付宝支付")
     * @Identify("alipay")
     */
    public const PAY_TYPE_ALIPAY = 30;

    /**
     * @message("抖音支付-微信")
     * @Identify("bytedance")
     */
    public const PAY_TYPE_BYTEDANCE_WECHAT = 40;

    /**
     * @message("抖音支付-支付宝")
     * @Identify("bytedance")
     */
    public const PAY_TYPE_BYTEDANCE_ALIPAY = 41;

    /**
     * 支付方式映射
     * @param $type
     * @return int
     */
    public static function getPayTypeCodeByType($type)
    {
        switch ($type) {
            case('wechat'):
                return self::PAY_TYPE_WECHAT;
            case 'alipay':
                return self::PAY_TYPE_ALIPAY;
            case 'balance':
                return self::PAY_TYPE_BALANCE;
            case 'delivery':
                return self::PAY_TYPE_DELIVERY;
            case 'byte_dance':
                return self::PAY_TYPE_BYTEDANCE_WECHAT;
        }
    }
}