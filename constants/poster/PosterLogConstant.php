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

namespace shopstar\constants\poster;


use shopstar\bases\constant\BaseConstant;

/**
 * 海报日志
 * Class PosterLogConstant
 * @package shopstar\constants\poster
 */
class PosterLogConstant extends BaseConstant
{
    /**
     * @Text("海报-新增")
     */
    public const POSTER_ADD = 430100;

    /**
     * @Text("海报-修改")
     */
    public const POSTER_SAVE = 430101;

    /**
     * @Text("海报-删除")
     */
    public const POSTER_DELETE = 430102;

    /**
     * @Text("海报-禁用")
     */
    public const POSTER_FORBIDDEN = 430103;

    /**
     * @Text("海报-启用")
     */
    public const POSTER_ACTIVE = 430104;
}