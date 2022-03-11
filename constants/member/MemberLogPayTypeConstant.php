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

namespace shopstar\constants\member;

use shopstar\bases\constant\BaseConstant;

/**
 * 用户提现支付类型
 * Class MemberLogPayTypeConst
 * @package shopstar\constants
 * @method getIdentify($code) static 获取英文标识
 */
class MemberLogPayTypeConstant extends BaseConstant
{

    /**
     * @Message("后台")
     * @Identify("background")
     */
    public const ORDER_PAY_TYPE_BACKGROUND = 10;

    /**
     * @Message("微信")
     * @Identify("wechat")
     */
    public const ORDER_PAY_TYPE_WECHAT = 20;

    /**
     * @Message("支付宝")
     * @Identify("alipay")
     */
    public const ORDER_PAY_TYPE_ALIPAY = 30;

}