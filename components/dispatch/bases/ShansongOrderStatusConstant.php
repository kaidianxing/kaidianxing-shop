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
 * 闪送配送状态
 * Class ShansongOrderStatusConstant
 * @package shopstar\components\dispatch\bases
 * @author 青岛开店星信息技术有限公司
 */
class ShansongOrderStatusConstant extends BaseConstant
{

    /**
     * @Message("派单中")
     */
    public const ORDER_DISTRIBUTE = 20;

    /**
     * @Message("取货中")
     */
    public const ORDER_PICK_UP = 30;

    /**
     * @Message("闪送中")
     */
    public const ORDER_DISTRIBUTION = 40;


    /**
     * @Message("已完成")
     */
    public const ORDER_COMPLETE = 50;


    /**
     * @Message("已取消")
     */
    public const ORDER_CANCELED = 60;
}