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

namespace shopstar\exceptions\newGifts;

use shopstar\bases\exception\BaseException;

/**
 * 新人送礼异常
 * Class NewGiftsException
 * @package shopstar\exceptions\newGifts
 */
class NewGiftsException extends BaseException
{
    /************ 商家端异常 ************/
    /**
     * 37 新人送礼
     * 01 业务端
     * 00 错误码
     */
    
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 370100;
    
    /**
     * @Message("活动不存在")
     */
    const DETAIL_ACTIVITY_NOT_EXISTS = 370101;
    
    /**
     * @Message("添加失败")
     */
    const ADD_FAIL = 370102;
    
    /**
     * @Message("修改失败")
     */
    const EDIT_ACTIVITY_NOT_EXISTS = 370103;
    
    /**
     * @Message("活动已停止,不可修改")
     */
    const EDIT_ACTIVITY_IS_STOP = 370104;
    
    /**
     * @Message("该时间段已存在活动")
     */
    const EDIT_ACTIVITY_TIME_IS_EXISTS = 370105;
    
    /**
     * @Message("修改失败")
     */
    const EDIT_ACTIVITY_FAIL = 370106;
    
    /**
     * @Message("修改时间不能小于当前时间")
     */
    const EDIT_ACTIVITY_TIME_ERROR = 370107;
    
    /**
     * @Message("未修改数据")
     */
    const EDIT_ACTIVITY_NOT_CHANGE = 370108;
    
    /**
     * @Message("活动不存在")
     */
    const MANUAL_STOP_ACTIVITY_NOT_EXISTS = 370109;
    
    /**
     * @Message("活动状态错误")
     */
    const MANUAL_STOP_ACTIVITY_STATUS_ERROR = 370110;
    
    /**
     * @Message("活动停止失败")
     */
    const MANUAL_STOP_ACTIVITY_FAIL = 370111;
    
    /**
     * @Message("参数错误")
     */
    const DELETE_ACTIVITY_PARAMS_ERROR = 370112;
    
    /**
     * @Message("活动不存在")
     */
    const DELETE_ACTIVITY_NOT_EXISTS = 370113;
    
    /**
     * @Message("删除活动失败")
     */
    const DELETE_ACTIVITY_FAIL = 370114;
    
    
    
    /**************** 新人送礼移动端 *****************/
    /**
     * 37 新人送礼
     * 02 移动端
     * 00 错误码
     */
    /**
     * @Message("无活动")
     */
    const CHECK_NEW_ACTIVITY_NO_EXISTS = 370200;
    
    /**
     * @Message("无活动")
     */
    const UN_LOGIN_ACTIVITY_NO_EXISTS = 370201;
    
    /**
     * @Message("不符合条件")
     */
    const SEND_NEW_MEMBER_IS_EXPEND = 370202;
    
    /**
     * @Message("不符合条件")
     */
    const SEND_NEW_MEMBER_IS_NOT_NEW = 370203;
    
    /**
     * @Message("已参与过活动")
     */
    const SEND_NEW_MEMBER_IS_JOIN = 370204;
    
    /**
     * @Message("发送失败")
     */
    const SEND_NEW_MEMBER_FAIL = 370205;
    
    
}