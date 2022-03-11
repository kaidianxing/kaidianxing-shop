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
 * 维权设置异常
 * Class CreditException
 * @package shopstar\bases\exception
 */
class RefundException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置
     * 60 维权设置
     * 01 错误码
     */
    
    /**
     * @Message("天数不能为空")
     */
    const REFUND_DAYS_EMPTY = 136001;
    
    /**
     * @Message("天数必须为正整数")
     */
    const REFUND_DAYS_ERROR = 136002;
    
    /**
     * @Message("保存失败")
     */
    const REFUND_SAVE_FAIL = 136003;
    
    
    /*************业务端异常结束*************/
    
    /*************客户端异常开始*************/
    /**
     * 应该木有客户端
     */
    /*************客户端异常结束*************/
    
}