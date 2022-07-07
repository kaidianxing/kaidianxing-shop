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

namespace shopstar\constants\groups;

use shopstar\bases\constant\BaseConstant;

/**
 * 拼团日志常量类
 * Class GroupsLogConstant
 * @package shopstar\constants\groups
 * @author likexin
 */
class GroupsLogConstant extends BaseConstant
{

    /**
     * @Text("拼团-设置-修改")
     */
    public const CHANGE_SETTING = 560000;

    /**
     * @Text("拼团-活动-删除")
     */
    public const DELETE = 560001;

    /**
     * @Text("拼团-活动-手动停止")
     */
    public const STOP = 560002;

    /**
     * @Text("拼团-活动-编辑")
     */
    public const EDIT = 560003;

    /**
     * @Text("拼团-活动-新增")
     */
    public const ADD = 560004;

}