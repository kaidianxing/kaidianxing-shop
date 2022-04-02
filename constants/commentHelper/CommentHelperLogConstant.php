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

namespace shopstar\constants\commentHelper;

use shopstar\bases\constant\BaseConstant;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CommentHelperLogConstant extends BaseConstant
{
    /**
     * @Text("评价助手-评价奖励发放")
     */
    public const COMMENT_HELPER_REWARD = 640000;

    /**
     * @Text("评价助手-基础设置")
     */
    public const COMMENT_HELPER_SET = 640100;

    /**
     * @Text("评价助手-创建评价")
     */
    public const COMMENT_HELPER_ADD = 640101;

    /**
     * @Text("评价助手-编辑评价")
     */
    public const COMMENT_HELPER_EDIT = 640102;
}