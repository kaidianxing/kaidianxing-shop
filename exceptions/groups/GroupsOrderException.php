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
 * 拼团订单异常
 * Class GroupsOrderException
 * @package shopstar\exceptions\groups
 * @author likexin
 */
class GroupsOrderException extends BaseException
{
    /**
     * @Message("缺少订单商品")
     */
    public const GROUPS_ORDER_PROCESSOR_ORDER_GOODS_NOT_EXIST_ERROR = 561000;

    /**
     * @Message("拼团价格不存在")
     */
    public const GROUPS_ORDER_PROCESSOR_ORDER_GOODS_PRICE_NOT_EXIST_ERROR = 561001;

    /**
     * @Message("阶梯拼团价格不存在")
     */
    public const GROUPS_ORDER_PROCESSOR_ORDER_GOODS_LADDER_PRICE_NOT_EXIST_ERROR = 561002;

    /**
     * @Message("开团失败")
     */
    public const GROUPS_ORDER_PROCESSOR_ORDER_CREATE_TEAM_ERROR = 561003;

    /**
     * @Message("开团失败(1)")
     */
    public const GROUPS_ORDER_PROCESSOR_ORDER_CREATE_CREW_ERROR = 561004;

    /**
     * @Message("活动商品异常")
     */
    public const GROUPS_ORDER_PROCESSOR_ACTIVITY_GOODS_ERROR = 561005;

    /**
     * @Message("活动库存不足")
     */
    public const GROUPS_ORDER_PROCESSOR_GOODS_ACTIVITY_STOCK_ERROR = 561006;

    /**
     * @Message("阶梯团数据异常")
     */
    public const GROUPS_ORDER_PROCESSOR_LADDER_PRICE_ERROR = 561007;

    /**
     * @Message("商品不是付款减库存")
     */
    public const GROUPS_ORDER_PROCESSOR_REDUCTION_TYPE_ERROR = 561008;

    /**
     * @Message("商品达到限购数量")
     */
    public const GROUPS_ORDER_PROCESSOR_BUY_LIMIT = 561009;

    /**
     * @Message("拼团同时只能购买一个商品")
     */
    public const GROUPS_ORDER_PROCESSOR_GOODS_COUNT_ERROR = 561011;


    /**
     * @Message("参团的团队ID不正确")
     */
    public const GROUPS_ORDER_JOIN_TEAM_ID_IS_ERROR = 561012;

    /**
     * @Message("未知团")
     */
    public const GROUPS_ORDER_JOIN_TEAM_IS_EMPTY_ERROR = 561013;

    /**
     * @Message("团状态无效")
     */
    public const GROUPS_ORDER_JOIN_TEAM_STATUS_ERROR = 561014;


    /**
     * @Message("您已有正在进行的订单，不可参团")
     */
    public const GROUPS_ORDER_JOIN_TEAM_IS_REPEAT = 561015;

    /**
     * @Message("请选择拼团正确选项")
     */
    public const GROUPS_ORDER_PROCESSOR_PARAMS_ERROR = 561016;


}