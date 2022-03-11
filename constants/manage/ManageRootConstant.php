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

namespace shopstar\constants\manage;

use shopstar\bases\constant\BaseConstant;

/**
 * 管理员类型
 * Class ManageRootConst
 * @package shopstar\constants\manage
 */
class ManageRootConstant extends BaseConstant
{
    /**
     * @Message("普通操作员")
     */
    public const GENERAL_OPERATOR = 0;

    /**
     * @Message("公众号创建者")
     */
    public const OFFICIAL_ACCOUNT_OPERATOR = 1;

    /**
     * @Message("超级管理员")
     */
    public const SUPER_ADMIN_OPERATOR = 2;
}