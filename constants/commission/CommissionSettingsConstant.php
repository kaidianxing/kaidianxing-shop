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


namespace shopstar\constants\commission;

use shopstar\bases\constant\BaseConstant;

/**
 * 分销设置常量
 * Class CommissionLogConstant
 * @package shopstar\constants\commission
 */
class CommissionSettingsConstant extends BaseConstant
{

    /*** 分销类型  ***/
    /**
     * @Message("传统模式")
     */
    public const TYPE_NORMAL = 1;

    /**
     * @Message("竞争模式")
     */
    public const TYPE_COMPETE = 2;

    /*** 保护期类型 ***/
    /**
     * @Message("无保护期")
     */
    public const COMPETE_SAFE_TYPE_NONE = 0;

    /**
     * @Message("自定义保护期")
     */
    public const COMPETE_SAFE_TYPE_CUSTOM = 1;


}