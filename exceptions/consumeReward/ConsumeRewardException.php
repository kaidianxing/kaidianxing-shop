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

namespace shopstar\exceptions\consumeReward;

use shopstar\bases\exception\BaseException;

/**
 * 消费奖励异常
 * Class ConsumeRewardException
 * @package shopstar\exceptions\consumeReward
 * @author 青岛开店星信息技术有限公司
 */
class ConsumeRewardException extends BaseException
{
    /******************* 消费奖励商家端 *****************/
    /**
     * 38 消费奖励
     * 01 商家端
     * 00 错误码
     */

    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 380100;

    /**
     * @Message("活动不存在")
     */
    const DETAIL_REWARD_NOT_EXISTS = 380101;

    /**
     * @Message("新增失败")
     */
    const ADD_REWARD_FAIL = 380102;

    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 380103;

    /**
     * @Message("活动不存在")
     */
    const DELETE_REWARD_NOT_EXISTS = 380104;

    /**
     * @Message("删除失败")
     */
    const DELETE_FAIL = 380105;

    /**
     * @Message("活动不存在")
     */
    const EDIT_ACTIVITY_NOT_EXISTS = 380106;

    /**
     * @Message("活动已停止,不可修改")
     */
    const EDIT_ACTIVITY_IS_STOP = 380107;

    /**
     * @Message("修改时间不能小于当前结束时间")
     */
    const EDIT_ACTIVITY_TIME_ERROR = 380108;

    /**
     * @Message("未修改数据")
     */
    const EDIT_ACTIVITY_NOT_CHANGE = 380109;

    /**
     * @Message("修改失败")
     */
    const EDIT_ACTIVITY_FAIL = 380110;

    /**
     * @Message("该时间段已存在活动")
     */
    const EDIT_ACTIVITY_TIME_IS_EXISTS = 380111;

    /**
     * @Message("活动不存在")
     */
    const MANUAL_STOP_ACTIVITY_NOT_EXISTS = 380112;

    /**
     * @Message("活动状态错误")
     */
    const MANUAL_STOP_ACTIVITY_STATUS_ERROR = 380113;

    /**
     * @Message("活动停止失败")
     */
    const MANUAL_STOP_ACTIVITY_FAIL = 380114;


    /**************** 移动端 *****************/
    /**
     * 38 充值送礼
     * 02 移动端
     * 00 错误码
     */
    /**
     * @Message("参数错误")
     */
    const SEND_REWARD_PARAMS_ERROR = 380200;

    /**
     * @Message("无记录")
     */
    const SEND_REWARD_LOG_NOT_EXISTS = 380201;

    /**
     * @Message("活动不存在")
     */
    const SEND_REWARD_ACTIVITY_NOT_EXISTS = 380202;

    /**
     * @Message("活动不存在")
     */
    const SEND_REWARD_ACTIVITY_CLIENT_TYPE_ERROR = 380203;

    /**
     * @Message("活动不存在")
     */
    const SEND_REWARD_ACTIVITY_NO_REWARD = 380204;

}