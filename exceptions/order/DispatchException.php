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
 * 配送方式
 * Class DispatchException
 * @package shopstar\exceptions\order
 */
class DispatchException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 22 订单相关
     * 60 配送方式
     * 01 错误码
     */
    
    /**
     * @Message("配送方式保存失败")
     */
    const DISPATCH_EDIT_SAVE_FAIL = 226001;
    
    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAMS_ERROR = 226002;
    
    /**
     * @Message("配送方式不存在")
     */
    const DISPATCH_NOT_EXISTS = 226003;
    
    /**
     * @Message("修改配送方式状态失败")
     */
    const CHANGE_DISPATCH_STATE_FAIL = 226004;
    
    /**
     * @Message("删除配送方式失败")
     */
    const DELETE_DISPATCH_FAIL = 226005;
    
    /**
     * @Message("参数错误")
     */
    const EDIT_PARAMS_ERROR = 226006;
    
    /**
     * @Message("参数错误")
     */
    const DELETE_PARAMS_ERROR = 226007;
    
    /**
     * @Message("参数错误")
     */
    const CHANGE_STATE_PARAMS_ERROR = 226008;
    
    /**
     * @Message("参数错误")
     */
    const CHANGE_DEFAULT_PARAMS_ERROR = 226009;
    
    /**
     * @Message("配送方式保存失败")
     */
    const DISPATCH_ADD_SAVE_FAIL = 226010;
    
    /**
     * @Message("修改默认状态失败")
     */
    const CHANGE_DEFAULT_FAIL = 226011;

    /**
     * @Message("至少需要开启一种配送方式才可以正常营业")
     */
    const SHOP_SETTINGS_DISPATCH_EXPRESS_ENABLE_INVALID = 226012;
    
    /**
     * @Message("保存失败")
     */
    const DISPATCH_SORT_SAVE_FAIL = 226013;
    
    
    
    /*************业务端异常结束*************/
    
    
    /*************客户端异常开始*************/

    /**
     * @Message("同城配送方式错误")
     */
    const DISPATCH_INTRACITY_QUERY_ORDER_STATUS_TYPE_INVALID = 226501;

    /**
     * @Message("同城配送订单错误")
     */
    const DISPATCH_INTRACITY_QUERY_ORDER_STATUS_ORDER_INVALID = 226502;

    /**
     * @Message("同城配送查询订单详情错误")
     */
    const DISPATCH_INTRACITY_QUERY_ORDER_STATUS_INVALID = 226503;

    /*************客户端异常结束*************/
    
}