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
 * 达达配送订单状态
 * Class DadaOrderStatusConstant
 * @package shopstar\components\dispatch\bases
 * @author 青岛开店星信息技术有限公司
 */
class DadaOrderStatusConstant extends BaseConstant
{
    //订单状态(待接单＝1,待取货＝2,配送中＝3,已完成＝4,已取消＝5, 指派单=8,妥投异常之物品返回中=9, 妥投异常之物品返回完成=10, 骑士到店=100,创建达达运单失败=1000 可参考文末的状态说明）

    /**
     * @Message("待接单")
     */
    public const WAIT_LIST = 1;

    /**
     * @Message("待取货")
     */
    public const WAIT_PICK_UP = 2;

    /**
     * @Message("配送中")
     */
    public const DELIVERY = 3;

    /**
     * @Message("已完成")
     */
    public const COMPLETED = 4;

    /**
     * @Message("已取消")
     */
    public const CANCELED = 5;
}