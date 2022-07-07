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

namespace shopstar\exceptions\groups;

use shopstar\bases\exception\BaseException;

/**
 * 拼团异常类
 * Class GroupsException
 * @package exceptions\groups
 * @author likexin
 */
class GroupsException extends BaseException
{

    /**
     * @Message("添加失败")
     */
    public const GROUPS_ADD_FAIL = 560100;

    /**
     * @Message("缺少参数")
     */
    public const GROUPS_EDIT_PARAMS_ERROR = 560101;

    /**
     * @Message("活动不存在")
     */
    public const GROUPS_EDIT_ACTIVITY_NOT_EXIST_ERROR = 560102;

    /**
     * @Message("活动修改失败")
     */
    public const GROUPS_EDIT_FAIL = 560103;


    /**
     * @Message("参数错误")
     */
    public const GROUPS_MANUAL_STOP_PARAMS_ERROR = 560104;

    /**
     * @Message("手动停止活动失败")
     */
    public const GROUPS_MANUAL_STOP_FAIL = 560105;

    /**
     * @Message("参数错误")
     */
    public const GROUPS_DELETE_PARAMS_ERROR = 560106;

    /**
     * @Message("删除失败")
     */
    public const GROUPS_DELETE_ERROR = 560107;

    /**
     * @Message("参数错误")
     */
    public const GROUPS_DETAIL_PARAMS_ERROR = 560108;

    /**
     * @Message("活动不存在")
     */
    public const GROUPS_DETAIL_ACTIVITY_NOT_EXISTS = 560109;


    /**
     * @Message("团队不存在")
     */
    public const GROUPS_TEAM_ID_NOT_EXISTS = 560110;


    /**
     * @Message("未查询到团员或订单")
     */
    public const GROUPS_MEMBER_OR_ORDER_IS_EMPTY = 560111;


    /**
     * @Message("活动未找到")
     */
    public const GROUPS_INVITE_TEAM_IS_EMPTY = 560112;

    /**
     * @Message("修改时间不能小于当前结束时间")
     */
    public const NEW_EDIT_EMD_TIME_NOT_LESS_THAN_OLD_END_TIME_ERROR = 560113;

}