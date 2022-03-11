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

namespace shopstar\exceptions\sysset;

use shopstar\bases\exception\BaseException;

/**
 * 商城交易设置异常
 * Class MallException
 * @package shopstar\bases\exception
 */
class TradeException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置
     * 80 交易设置
     * 01 错误码
     */
    
    /**
     * @Message("未付款关闭时间不能为空")
     */
    const CLOSE_TIME_EMPTY = 138001;
    
    /**
     * @Message("未付款关闭时间范围错误")
     */
    const CLOSE_TIME_ERROR = 138002;
    
    /**
     * @Message("订单关闭通知时间不能为空")
     */
    const CLOSE_NOTICE_TIME_EMPTY = 138003;
    /**
     * @Message("订单关闭通知时间不能大于自动关闭时间")
     */
    const CLOSE_NOTICE_TIME_ERROR = 138004;
    
    /**
     * @Message("自动收货天数不能为空")
     */
    const AUTO_RECEIVE_TIME_EMPTY = 138005;
    
    /**
     * @Message("不能大于30天或小于0天")
     */
    const AUTO_RECEIVE_TIME_ERROR = 138006;
    
    /**
     * @Message("库存预警通知库存数不能为空")
     */
    const STOCK_WANING_DAY_EMPTY = 138007;
    
    /**
     * @Message("库存预警通知库存数必须为正整数")
     */
    const STOCK_WANING_DAY_ERROR = 138008;
    
    /**
     * @Message("保存失败")
     */
    const TRADE_SAVE_FAIL = 138009;
    
    /**
     * @Message("自动评价时间错误")
     */
    const TRADE_AUTO_COMMENT_TIME_ERROR = 138010;
    
    /**
     * @Message("自动评价内容不能为空")
     */
    const TRADE_AUTO_COMMENT_CONTENT_ERROR = 138011;
    
    
    /*************业务端异常结束*************/
    
    /*************客户端异常开始*************/
    /**
     * 应该木有客户端
     */
    /*************客户端异常结束*************/
    
}