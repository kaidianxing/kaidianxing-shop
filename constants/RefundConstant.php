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

namespace shopstar\constants;

/**
 * 维权
 * Class RefundConst
 * @package shopstar\constants
 */
class RefundConstant
{
    /**
     * @Message("取消维权")
     */
    public const REFUND_STATUS_CANCEL = -2;
    
    /**
     * @Message("拒绝")
     */
    public const REFUND_STATUS_REJECT = -1;
    
    /**
     * @Message("申请")
     */
    public const REFUND_STATUS_APPLY = 0;
    
    /**
     * @Message("完成")
     */
    public const REFUND_STATUS_SUCCESS = 10;
    
    /**
     * @Message("手动完成退款")
     */
    public const REFUND_STATUS_MANUAL = 11;
    
    /**
     * @Message("用户填写快递单号")
     */
    public const REFUND_STATUS_MEMBER = 1;
    
    /**
     * @Message("店家填写快递单号")
     */
    public const REFUND_STATUS_SHOP = 2;
    
    /**
     * @Message("等待完成")
     */
    public const REFUND_STATUS_WAIT = 3;
    
    /**
     * @Message("退款")
     */
    public const TYPE_REFUND = 1;
    
    /**
     * @Message("退货退款")
     */
    public const TYPE_RETURN = 2;
    
    /**
     * @Message("换货")
     */
    public const TYPE_EXCHANGE = 3;

    /**
     * @Message("其他快递")
     */
    public const EXPRESS_CODE_QITA = 'qita';
}