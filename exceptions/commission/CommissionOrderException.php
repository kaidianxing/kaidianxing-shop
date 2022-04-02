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

namespace shopstar\exceptions\commission;

use shopstar\bases\exception\BaseException;

/**
 * 分销订单异常
 * Class CommissionOrderException
 * @package shopstar\exceptions\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionOrderException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 33 分销
     * 51 订单异常
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const GET_COMMISSION_PARAMS_ERROR = 335101;

    /**
     * @Message("分销信息不存在")
     */
    const GET_COMMISSION_INFO_NOT_EXISTS = 335102;

    /**
     * @Message("参数错误")
     */
    const CHANGE_COMMISSION_PARAMS_ERROR = 335103;

    /**
     * @Message("该订单不可修改佣金")
     */
    const CHANGE_COMMISSION_NOT_ALLOW_CHANGE_COMMISSION = 335104;

    /**
     * @Message("修改佣金失败")
     */
    const CHANGE_COMMISSION_FAIL = 335105;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 33 分销
     * 52 等级 客户端
     * 01 错误码
     */


    /*************客户端异常结束*************/
}