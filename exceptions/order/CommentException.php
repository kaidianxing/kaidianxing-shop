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

namespace shopstar\exceptions\order;

use shopstar\bases\exception\BaseException;

/**
 * 评价异常
 * Class CommentException
 * @package shopstar\exceptions\order
 */
class CommentException extends BaseException
{
    
    /*************业务端异常开始*************/
    /**
     * 22 订单相关
     * 71 评价
     * 01 错误码
     */
    
    /**
     * @Message("参数错误")
     */
    const EDIT_PARAMS_ERROR = 227101;
    
    /**
     * @Message("评论保存失败")
     */
    const COMMENT_ADD_SAVE_FAIL = 227102;
    
    /**
     * @Message("评论保存失败")
     */
    const COMMENT_EDIT_SAVE_FAIL = 227103;
    
    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 227104;
    
    /**
     * @Message("评论删除失败")
     */
    const COMMENT_DELETE_FAIL = 227105;
    
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 227106;
    
    /**
     * @Message("评价不存在")
     */
    const COMMENT_NOT_EXISTS = 227107;
    
    /**
     * @Message("参数错误")
     */
    const AUDIT_PARAMS_ERROR = 227108;
    
    /**
     * @Message("审核失败")
     */
    const COMMENT_AUDIT_FAIL = 227109;
    
    /**
     * @Message("参数错误")
     */
    const REPLY_PARAMS_ERROR = 227110;
    
    /**
     * @Message("回复失败")
     */
    const COMMENT_REPLY_FAIL = 227111;
    
    
    /*************业务端异常结束*************/
    
    
    /*************客户端异常开始*************/
    /**
     * 22 订单相关
     * 72 评价方式
     * 01 错误码
     */
    
    /**
     * @Message("参数错误")
     */
    const ORDER_GOODS_COMMENT_WRITE_COMMENT_PARAMS_ERROR = 227201;
    
    /**
     * @Message("订单商品不存在")
     */
    const ORDER_GOODS_COMMENT_WRITE_COMMENT_ORDER_GOODS_NOT_FOUND_ERROR = 227202;
    
    /**
     * @Message("评价失败")
     */
    const ORDER_GOODS_COMMENT_WRITE_COMMENT_ERROR = 227203;
    
    /**
     * @Message("评价已关闭")
     */
    const ORDER_GOODS_COMMENT_WRITE_COMMENT_SWITCH_CLOSE_ERROR = 227204;
    
    /**
     * @Message("参数错误")
     */
    const ORDER_GOODS_COMMENT_LIST_PARAMS_FOUND_ERROR = 227205;
    
    /**
     * @Message("评价已关闭")
     */
    const ORDER_GOODS_COMMENT_WAIT_LIST_SWITCH_CLOSE_ERROR = 227206;
    
    /**
     * @Message("参数错误")
     */
    const ORDER_CLIENT_GET_EXPRESS_PARAMS_ORDER = 227207;
    
    /**
     * @Message("参数错误")
     */
    const ORDER_CLIENT_GET_EXPRESS_ORDER_NOT_FOUND_ORDER = 227208;
    
    /**
     * @Message("已经评价过")
     */
    const ORDER_CLIENT_IS_EXISTS = 227209;
    
    
    /*************客户端异常结束*************/

}