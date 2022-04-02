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
 * 营销基础设置日志
 * Class BasicLogConstant
 * @package shopstar\constants\log\sale
 */
class BasicLogConstant extends BaseConstant
{
    /**************** 抵扣设置 *******************/
    /**
     * @Text("营销-抵扣设置-修改")
     */
    public const SALE_DEDUCT_EDIT = 241000;
    
    
    /**************** 满额立减 *******************/
    /**
     * @Text("营销-满额立减-修改")
     */
    public const SALE_ENOUGH_EDIT = 241100;
    
    
    /**************** 满额包邮 *******************/
    /**
     * @Text("营销-满额包邮-修改")
     */
    public const SALE_ENOUGH_FREE_EDIT = 241200;
    
}