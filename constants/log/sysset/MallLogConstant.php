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
 * 商城设置日志
 * Class MallLogConstant
 * @package shopstar\constants\log\sysset
 * @author 青岛开店星信息技术有限公司
 */
class MallLogConstant extends BaseConstant
{
    /************** 基础设置 *****************/
    /**
     * @Text("设置-商城设置-基础设置")
     */
    public const MALL_BASIC_SET = 133000;
    
    
    /************** 分享设置 *****************/
    /**
     * @Text("设置-商城设置-分享设置")
     */
    public const MALL_SHARE_SET = 133100;
    
    
    /************** 公告管理 *****************/
    /**
     * @Text("设置-公告管理-添加公告")
     */
    public const MALL_NOTICE_ADD = 133200;
    
    /**
     * @Text("设置-公告管理-编辑公告")
     */
    public const MALL_NOTICE_EDIT = 133201;
    
    /**
     * @Text("设置-公告管理-修改状态")
     */
    public const MALL_NOTICE_CHANGE_STATUS = 133202;
    
    /**
     * @Text("设置-公告管理-删除")
     */
    public const MALL_NOTICE_DELETE = 133203;
    
    
}