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

namespace shopstar\exceptions\commentHelper;

use shopstar\bases\exception\BaseException;

/**
 * 评价助手异常
 * Class CommentHelperException
 * @package shopstar\exceptions\commentHelper
 */
class CommentHelperException extends BaseException
{
    /**
     * @Message("评价不能为空")
     */
    const MANUAL_CONTENT_EMPTY = 640001;
    
    /**
     * @Message("评价时间不能为空")
     */
    const MANUAL_TIME_EMPTY = 640002;
    
    /**
     * @Message("评价时间错误")
     */
    const MANUAL_TIME_ERROR = 640003;
    
    /**
     * @Message("自定义评价者信息错误")
     */
    const MANUAL_CUSTOMER_MEMBER_INFO_ERROR = 640004;
    
    /**
     * @Message("请选择会员")
     */
    const MANUAL_MEMBER_EMPTY = 640005;
    
    /**
     * @Message("会员等级不存在")
     */
    const MANUAL_MEMBER_LEVEL_NOT_EXISTS = 640006;
    
    /**
     * @Message("评价内容不能为空")
     */
    const EDIT_CONTENT_EMPTY = 640007;
    
    /**
     * @Message("评价时间不能为空")
     */
    const EDIT_TIME_EMPTY = 640008;
    
    /**
     * @Message("会员信息错误")
     */
    const EDIT_MEMBER_INFO_ERROR = 640009;
    
    /**
     * @Message("评价不存在")
     */
    const EDIT_COMMENT_NOT_EXISTS = 640010;
    
    /**
     * @Message("评价修改失败")
     */
    const EDIT_FAIL = 640011;
    
    /**
     * @Message("评价助手抓取剩余次数不足，请联系管理员")
     */
    const GRAB_QUANTITY_NOT_ENOUGH = 640012;
    
    /**
     * @Message("抓取地址不能为空")
     */
    const GRAB_URL_EMPTY = 640013;
    
    /**
     * @Message("抓取数量错误")
     */
    const GRAB_NUM_EMPTY = 640014;
    
    /**
     * @Message("评价时间错误")
     */
    const GRAB_TIME_ERROR = 640015;
    
    /**
     * @Message("接口调用错误")
     */
    const GRAB_API_ERROR = 640016;
    
    /**
     * @Message("商品不存在")
     */
    const GRAB_API_GOODS_NOT_EXISTS = 640017;
    
    /**
     * @Message("商品不存在")
     */
    const MANUAL_CREATE_GOODS_NOT_EXISTS = 640018;
    
    /**
     * @Message("无法获取商品id")
     */
    const GRAB_API_GOODS_ID_EMPTY = 640019;
    
    /**
     * @Message("余额不足, 请联系管理员")
     */
    const API_NO_MONEY = 640020;
    
    
}