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
 * 分销关系日志常量
 * Class CommissionLogConstant
 * @package shopstar\constants\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionRelationLogConstant extends BaseConstant
{
    /****** 日志类型 ******/
    /*** 绑定 ***/

    /**
     * 正常绑定上级
     * @Text("绑定关系")
     */
    public const TYPE_BIND = 10;

    /**
     * 竞争绑定上级
     * @Text("分销商抢客")
     */
    public const TYPE_COMPETE_BIND = 11;

    /**
     * 后台手动绑定上级
     * @Text("后台手动变更")
     */
    public const TYPE_MANUAL_BIND = 12;

    /**
     * 后台手动绑定换绑上级
     * @Text("后台手动变更")
     */
    public const TYPE_MANUAL_CHANGE_BIND = 13;

    /*** 解绑 ***/

    /**
     * 正常解绑关系
     * @Text("解绑关系")
     */
    public const TYPE_UNBIND = 20;

    /**
     * 竞争解绑上级
     * @Text("分销商抢客")
     */
    public const TYPE_COMPETE_UNBIND = 21;

    /**
     * 后台手动解绑上级
     * @Text("后台手动变更")
     */
    public const TYPE_MANUAL_UNBIND = 22;

    /**
     * 后台手动绑定换绑上级
     * @Text("后台手动变更")
     */
    public const TYPE_MANUAL_CHANGE_UNBIND = 23;

    /**
     * 取消分享商资格解绑上级
     * @Text("后台手动变更")
     */
    public const TYPE_CANCEL_COMMISSION_UNBIND = 24;

    /**
     * 删除会员解绑上级
     * @Text("后台手动变更")
     */
    public const TYPE_DELETE_MEMBER_UNBIND = 25;


}