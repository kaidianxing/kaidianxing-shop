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

namespace shopstar\constants\log\verify;

use shopstar\bases\constant\BaseConstant;

/**
 * 核销日志
 * Class VerifyLogConstant
 * @package shopstar\constants\log\verify
 */
class VerifyLogConstant extends BaseConstant
{
    /**********核销*********/
    /**
     * @Text("核销设置-核销点-删除")
     */
    public const VERIFY_POINT_DELETE = 501001;

    /**
     * @Text("核销设置-核销点-编辑")
     */
    public const VERIFY_POINT_EDIT = 501002;

    /**
     * @Text("核销设置-核销点-添加")
     */
    public const VERIFY_POINT_ADD = 501003;

    /**
     * @Text("核销设置-基础设置")
     */
    public const VERIFY_OPEN = 502002;


}
