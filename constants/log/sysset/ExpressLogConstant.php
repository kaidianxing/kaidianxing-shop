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
 * 地址物流日志
 * Class ExpressLogConstant
 * @package shopstar\constants\log\sysset
 */
class ExpressLogConstant extends BaseConstant
{
    /*************** 地址设置 ******************/
    /**
     * @Text("设置-地址物流-修改地址设置")
     */
    public const ADDRESS_SET_EDIT = 132000;
    
    
    /*************** 物流配置 ******************/
    /**
     * @Text("设置-地址物流-修改物流配置")
     */
    public const EXPRESS_SET_EDIT = 132100;
    
    
    /**************** 退货地址 ********************/
    /**
     * @Text("设置-地址物流-添加退货地址")
     */
    public const REFUND_ADDRESS_ADD = 132200;
    
    /**
     * @Text("设置-地址物流-编辑退货地址")
     */
    public const REFUND_ADDRESS_EDIT = 132201;
    
    /**
     * @Text("设置-地址物流-修改退货地址默认状态")
     */
    public const REFUND_ADDRESS_CHANGE_DEFAULT = 132202;
    
    /**
     * @Text("设置-地址物流-删除退货地址")
     */
    public const REFUND_ADDRESS_DELETE = 132203;
    
}