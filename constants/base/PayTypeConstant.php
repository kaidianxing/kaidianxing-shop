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

namespace shopstar\constants\base;

use shopstar\bases\constant\BaseConstant;

/**
 * 系统基础-支付类型常量
 * Class PayTypeConstant
 * @method getIdentity($code) static 获取标识
 * @package shopstar\constants\base
 * @author likexin
 */
class PayTypeConstant extends BaseConstant
{

    /**
     * @Text("余额支付")
     * @Identity("balance")
     */
    public const TYPE_BALANCE = 2;

    /**
     * @Text("货到付款")
     * @Identity("delivery")
     */
    public const PAY_TYPE_DELIVERY = 3;

    /**
     * @Text("微信支付")
     * @Identity("wechat")
     */
    public const PAY_TYPE_WECHAT = 20;

    /**
     * @Text("支付宝支付")
     * @Identity("alipay")
     */
    public const PAY_TYPE_ALIPAY = 30;
    
    /**
     * @Text("字节跳动支付")
     * @Identity("byte_dance")
     */
    public const PAY_TYPE_BYTEDANCE = 40;
    
    /**
     * 根据支付标识获取支付类型
     * @param string $identity 支付标识
     * @return int
     * @author likexin
     */
    public static function getPayTypeCodeByIdentity(string $identity)
    {
        return self::getOneByIndex('identity', $identity, 'code');
    }

}