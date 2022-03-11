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

namespace shopstar\exceptions\statistics;

use shopstar\bases\exception\BaseException;

class TradeException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 25 数据
     * 10 交易异常
     * 01 错误码
     */
    
    /**
     * @Message("起始时间能大于结束时间")
     */
    const TRADE_DATE_START_MORE_THAN_END = 251001;
    
    /**
     * @Message("参数错误")
     */
    const INDEX_MANAGE_OVERVIEW_PARAMS_ERROR = 251002;
    
    /**
     * @Message("参数错误")
     */
    const INDEX_MANAGE_PARAMS_ERROR = 251003;
    
    /**
     * @Message("类型错误")
     */
    const INDEX_TYPE_ERROR = 251004;
    
    
    
    /*************业务端异常结束*************/
    
}