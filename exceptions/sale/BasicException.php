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

namespace shopstar\exceptions\sale;

use shopstar\bases\exception\BaseException;

/**
 * 营销基础设置异常
 * Class BasicException
 * @package shopstar\exceptions\sale
 * @author 青岛开店星信息技术有限公司
 */
class BasicException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 24 营销
     * 10 基础设置
     * 01 错误码
     */

    /**
     * @Message("积分抵扣比例错误")
     */
    const DEDUCT_CREDIT_NUM_ERROR = 241001;

    /**
     * @Message("满额立减金额不能为空")
     */
    const FULL_DEDUCT_MONEY_NOT_EMPTY = 241002;

    /**
     * @Message("满额立减金额不能为负数")
     */
    const FULL_DEDUCT_MONEY_NOT_MINUS = 241003;

    /**
     * @Message("抵扣不能大于满减")
     */
    const FULL_DEDUCT_MONEY_BIG = 241004;

    /**
     * @Message("满额立减金额不能重复")
     */
    const FULL_DEDUCT_MONEY_NOT_REPEAT = 241005;

    /**
     * @Message("保存失败")
     */
    const FULL_DEDUCT_SAVE_FAIL = 241006;

    /**
     * @Message("满额包邮金额不能小于0")
     */
    const ENOUGH_FREE_MONEY_ERROR = 241007;

    /**
     * @Message("保存失败")
     */
    const ENOUGH_FREE_SAVE_FAIL = 241008;

    /**
     * @Message("保存失败")
     */
    const DEDUCT_SAVE_FAIL = 241009;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 应该木有客户端
     */
    /*************客户端异常结束*************/

}