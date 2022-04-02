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

namespace shopstar\constants;

use shopstar\bases\constant\BaseConstant;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsConstant extends BaseConstant
{
    //状态
    /**
     * @Message("下架")
     */
    public const GOODS_STATUS_UNSHELVE = 0;

    /**
     * @Message("上架")
     */
    public const GOODS_STATUS_PUTAWAY = 1;

    /**
     * @Message("上架不显示")
     */
    public const GOODS_STATUS_PUTAWAY_NOT_DISPLAY = 2;

    //删除
    /**
     * @Message("已删除")
     */
    public const GOODS_IS_DELETE_YES = 1;

    /**
     * @Message("未删除")
     */
    public const GOODS_IS_DELETE_NO = 0;

    //减库存方式


    /**
     * @Message("下单减库存")
     */
    public const GOODS_REDUCTION_TYPE_ORDER = 0;

    /**
     * @Message("付款减库存")
     */
    public const GOODS_REDUCTION_TYPE_PAYMENT = 1;

    /**
     * @Message("永不减库存")
     */
    public const GOODS_REDUCTION_TYPE_NOT_REDUCE = 2;


    /******运费类型*******/
    /**
     * @Message("包邮")
     */
    public const GOODS_DISPATCH_TYPE_FREE = 0;

    /**
     * @Message("运费模板")
     */
    public const GOODS_DISPATCH_TYPE_TEMPLATE = 1;

    /**
     * @Message("固定运费")
     */
    public const GOODS_DISPATCH_TYPE_FIXED = 2;

    /******商品类型*******/
    /**
     * @Message("实体商品")
     */
    public const GOODS_TYPE_ENTITY = 0;

    /**
     * @Message("虚拟商品")
     */
    public const GOODS_TYPE_VIRTUAL = 1;

    /**
     * @Message("虚拟卡密")
     */
    public const GOODS_TYPE_VIRTUAL_ACCOUNT = 2;

    /******虚拟商品物流设置*******/
    /**
     * @Message("虚拟商品不自动发货")
     */
    public const GOODS_VIRTUAL_NO_AUTO_DELIVERY = 0;

    /**
     * @Message("虚拟商品自动发货")
     */
    public const GOODS_VIRTUAL_AUTO_DELIVERY = 1;
}
