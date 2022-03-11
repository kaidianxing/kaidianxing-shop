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

namespace shopstar\constants\log\sysset;

use shopstar\bases\constant\BaseConstant;

/**
 * 支付设置日志
 * Class PaymentLogConstant
 * @package shopstar\constants\log\sysset
 */
class PaymentLogConstant extends BaseConstant
{
    /*************** 支付方式 *****************/
    /**
     * @Text("设置-支付设置-修改支付方式")
     */
    public const PAYMENT_TYPE_SET_EDIT = 135000;
    
    
    /*************** 打款设置 *****************/
    /**
     * @Text("设置-支付设置-修改打款设置")
     */
    public const PAYMENT_PAY_SET_EDIT = 135100;
    
    
    /*************** 支付设置 ******************/
    /**
     * @Text("设置-支付设置-新增支付模版")
     */
    public const PAYMENT_TEMPLATE_ADD = 135200;
    
    /**
     * @Text("设置-支付设置-修改支付模版")
     */
    public const PAYMENT_TEMPLATE_EDIT = 135201;
    
    /**
     * @Text("设置-支付设置-删除支付模版")
     */
    public const PAYMENT_TEMPLATE_DELETE = 135202;
    
    
}