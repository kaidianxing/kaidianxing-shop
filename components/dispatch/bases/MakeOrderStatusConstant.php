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

namespace shopstar\components\dispatch\bases;


use shopstar\bases\constant\BaseConstant;

/**
 * 码科配送订单状态
 * Class MakeOrderStatusConstant
 * @method getMessage($code) static
 * @method getCode($code) static
 * @package shopstar\components\dispatch\bases
 */
class MakeOrderStatusConstant extends BaseConstant
{
    // loading = 待付款', cancel='订单已取消', payed='待接单', accepted='待取件', geted='待收件',gotoed= '待评价', completed='订单已完成');
    // 0待支付,1已取消,2待接单,3待取件,4配送中，5待评价 6已完成

    /**
     * @Message("待付款")
     * @Code("0")
     */
    public const WAIT_PAY = 'loading';

    /**
     * @Message("待接单")
     * @Code("1")
     */
    public const WAIT_LIST = 'payed';

    /**
     * @Message("待取件")
     * @Code("2")
     */
    public const WAIT_PICK_UP = 'accepted';

    /**
     * @Message("待收件")
     * @Code("3")
     */
    public const DELIVERY = 'geted';

    /**
     * @Message("待评价")
     * @Code("4")
     */
    public const WAIT_COMMENT = 'gotoed';

    /**
     * @Message("已完成")
     * @Code("5")
     */
    public const COMPLETED = 'completed';

    /**
     * @Message("已取消")
     * @Code("6")
     */
    public const CANCELED = 'canceled';
}