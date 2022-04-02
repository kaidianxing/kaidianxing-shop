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

namespace shopstar\constants\log\sale;

use shopstar\bases\constant\BaseConstant;

/**
 * 优惠券日志
 * Class CouponLogConstant
 * @package shopstar\constants\log\sale
 * @author 青岛开店星信息技术有限公司
 */
class CouponLogConstant extends BaseConstant
{
    /************* 优惠券 **************/
    /**
     * @Text("营销-优惠券-修改发放状态")
     */
    public const COUPON_CHANGE_STATE = 243000;
    
    /**
     * @Text("营销-优惠券-新增优惠券")
     */
    public const COUPON_ADD = 243001;
    
    /**
     * @Text("营销-优惠券-修改优惠券")
     */
    public const COUPON_EDIT = 243002;
    
    /**
     * @Text("营销-优惠券-删除优惠券")
     */
    public const COUPON_DELETE = 243003;
    
    
    /************* 手动发券 **************/
    /**
     * @Text("营销-优惠券-手动发券")
     */
    public const COUPON_BATCH_SEND = 243200;
    
    
    /************* 其他设置 **************/
    /**
     * @Text("营销-优惠券-其他设置")
     */
    public const COUPON_SET = 243300;
    
    
}