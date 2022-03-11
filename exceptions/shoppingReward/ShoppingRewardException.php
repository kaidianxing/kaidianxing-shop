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

namespace shopstar\exceptions\shoppingReward;

use shopstar\bases\exception\BaseException;

/**
 * 购物奖励异常
 * Class ShoppingRewardException
 * @package shopstar\exceptions\shoppingReward
 */
class ShoppingRewardException extends BaseException
{
    /***************** 购物奖励商家端 ******************/
    /**
     * 40 购物奖励
     * 01 商家端
     * 00 错误码
     */
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 400100;

    /**
     * @Message("活动不存在")
     */
    const DETAIL_ACTIVITY_NOT_EXISTS = 400101;
    
    /**
     * @Message("新增失败")
     */
    const ADD_ACTIVITY_FAIL = 400102;
    
    /**
     * @Message("活动不存在")
     */
    const EDIT_REWARD_NOT_EXISTS = 400103;
    
    /**
     * @Message("活动已停止")
     */
    const EDIT_ACTIVITY_IS_STOP = 400104;
    
    /**
     * @Message("未修改数据")
     */
    const EDIT_ACTIVITY_NOT_CHANGE = 400105;
    
    /**
     * @Message("时间错误")
     */
    const EDIT_ACTIVITY_TIME_ERROR = 400106;
    
    /**
     * @Message("该时间段已存在活动")
     */
    const EDIT_ACTIVITY_TIME_IS_EXISTS = 400107;
    
    /**
     * @Message("修改失败")
     */
    const EDIT_ACTIVITY_FAIL = 400108;
    
    /**
     * @Message("参数错误")
     */
    const MANUAL_STOP_PARAMS_ERROR = 400109;
    
    /**
     * @Message("活动不存在")
     */
    const MANUAL_STOP_ACTIVITY_NOT_EXISTS = 400110;
    
    /**
     * @Message("活动状态错误")
     */
    const MANUAL_STOP_ACTIVITY_STATUS_ERROR = 400111;
    
    /**
     * @Message("手动停止失败")
     */
    const MANUAL_STOP_ACTIVITY_FAIL = 400112;
    
    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 400113;
    
    /**
     * @Message("活动不存在")
     */
    const DELETE_REWARD_NOT_EXISTS = 400114;
    
    /**
     * @Message("删除失败")
     */
    const DELETE_FAIL = 400115;
    
    
    /***************** 购物奖励商家端 ******************/
    /**
     * 40 购物奖励
     * 02 移动端
     * 00 错误码
     */
    /**
     * @Message("参数错误")
     */
    const SEND_REWARD_PARAMS_ERROR = 400200;
    
    /**
     * @Message("记录不存在")
     */
    const SEND_REWARD_LOG_NOT_EXISTS = 400201;
    
    /**
     * @Message("活动不存在")
     */
    const SEND_REWARD_ACTIVITY_NOT_EXISTS = 400202;
    
    /**
     * @Message("客户端类型错误")
     */
    const SEND_REWARD_ACTIVITY_CLIENT_TYPE_ERROR = 400203;
    
    /**
     * @Message("无奖励")
     */
    const SEND_REWARD_ACTIVITY_NO_REWARD = 400204;
    
    
}