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

/**
 * 商品数据异常
 * Class GoodsException
 * @package shopstar\exceptions\statistics
 */
class GoodsException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 25 数据
     * 20商品异常
     * 01错误码
     */
    
    /**
     * @Message("详情不存在")
     */
    const GOODS_DETAIL_NOT_EXISTS = 252001;
    
    /**
     * @Message("参数错误")
     */
    const GOODS_DETAIL_PARAMS_ERROR = 252002;
    
    
    /*************业务端异常结束*************/
}