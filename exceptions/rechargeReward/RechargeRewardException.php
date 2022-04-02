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

namespace shopstar\exceptions\rechargeReward;

use shopstar\bases\exception\BaseException;

/**
 * 充值奖励异常
 * Class RechargeRewardException
 * @package shopstar\exceptions\rechargeReward
 * @author 青岛开店星信息技术有限公司
 */
class RechargeRewardException extends BaseException
{
    /************** 充值奖励 ****************/
    /**
     * 39 充值奖励
     * 01 商家端
     * 00 错误码
     */
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 390100;

    /**
     * @Message("活动不存在")
     */
    const DETAIL_REWARD_NOT_EXISTS = 390101;

    /**
     * @Message("添加失败")
     */
    const ADD_FAIL = 390102;

    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 390103;

    /**
     * @Message("活动不存在")
     */
    const DELETE_REWARD_NOT_EXISTS = 390104;

    /**
     * @Message("删除失败")
     */
    const DELETE_FAIL = 390105;

    /**
     * @Message("活动不存在不")
     */
    const EDIT_REWARD_NOT_EXISTS = 390106;

    /**
     * @Message("活动已停止,不可修改")
     */
    const EDIT_ACTIVITY_IS_STOP = 390107;

    /**
     * @Message("未修改数据")
     */
    const EDIT_ACTIVITY_NOT_CHANGE = 390108;

    /**
     * @Message("修改时间不能小于当前时间")
     */
    const EDIT_ACTIVITY_TIME_ERROR = 390109;

    /**
     * @Message("该时间段已存在活动")
     */
    const EDIT_ACTIVITY_TIME_IS_EXISTS = 390110;

    /**
     * @Message("修改失败")
     */
    const EDIT_ACTIVITY_FAIL = 390111;

    /**
     * @Message("参数错误")
     */
    const MANUAL_STOP_PARAMS_ERROR = 390112;

    /**
     * @Message("活动不存在")
     */
    const MANUAL_STOP_ACTIVITY_NOT_EXISTS = 390113;

    /**
     * @Message("活动状态错误")
     */
    const MANUAL_STOP_ACTIVITY_STATUS_ERROR = 390114;

    /**
     * @Message("活动停止失败")
     */
    const MANUAL_STOP_ACTIVITY_FAIL = 390115;


    /***************** 充值送礼 ********************/
    /**
     * 39 充值送礼
     * 02 是移动端
     * 00 错误码
     */
    /**
     * @Message("无活动")
     */
    const ACTIVITY_NOT_EXISTS = 390200;

}