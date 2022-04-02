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

namespace shopstar\components\paymentNew\bases;

use shopstar\bases\constant\BaseConstant;

/**
 * 支付驱动常量
 * Class PaymentNewDriverConstant
 * @method static getDriver(string $payType)
 * @package shopstar\components\paymentNew\bases
 * @author likexin
 */
class PaymentNewDriverConstant extends BaseConstant
{

    /**
     * @message("微信支付")
     * @Driver("shopstar\components\paymentNew\drivers\WechatDriver")
     * @Channel("10,20,0")
     */
    public const DRIVE_WECHAT = 'wechat';

    /**
     * @message("微信支付")
     * @Driver("shopstar\components\paymentNew\drivers\AlipayDriver")
     */
    public const DRIVE_ALIPAY = 'alipay';

    /**
     * @message("余额支付")
     * @Driver("shopstar\components\paymentNew\drivers\BalanceDriver")
     */
    public const DRIVE_BALANCE = 'balance';

    /**
     * @message("字节跳动支付")
     * @Driver("shopstar\components\paymentNew\drivers\ByteDanceDriver")
     */
    public const DRIVE_BYTEDANCE = 'byte_dance';

}