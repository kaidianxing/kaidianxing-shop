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


namespace shopstar\constants\log\goods;

use shopstar\bases\constant\BaseConstant;

class GoodsLogConstant extends BaseConstant
{
    /**********商品*********/
    /**
     * @Text("商品-添加")
     */
    public const GOODS_ADD = 200000;

    /**
     * @Text("商品-修改")
     */
    public const GOODS_EDIT = 200001;

    /**
     * @Text("商品-删除")
     */
    public const GOODS_DELETE = 200002;

    /**
     * @Text("商品-彻底删除")
     */
    public const GOODS_REAL_DELETE = 200004;

    /**
     * @Text("商品-设置商品属性")
     */
    public const GOODS_CHANGE_PROPERTY = 200005;

    /**
     * @Text("商品-操作-设置价格库存")
     */
    public const GOODS_OPERATION_SET_PRICE_AND_STOCK = 200010;

    /**
     * @Text("商品-操作-批量下架")
     */
    public const GOODS_OPERATION_BATCH_UNSHELVE = 200011;

    /**
     * @Text("商品-操作-下架")
     */
    public const GOODS_OPERATION_UNSHELVE = 200012;

    /**
     * @Text("商品-操作-批量上架")
     */
    public const GOODS_OPERATION_BATCH_PUTAWAY = 200013;

    /**
     * @Text("商品-操作-上架")
     */
    public const GOODS_OPERATION_PUTAWAY = 200014;

    /**
     * @Text("商品-操作-批量删除")
     */
    public const GOODS_OPERATION_BATCH_DELETE = 200015;

    /**
     * @Text("商品-操作-批量恢复")
     */
    public const GOODS_OPERATION_BATCH_RECOVER = 200016;

    /**
     * @Text("商品-操作-恢复")
     */
    public const GOODS_OPERATION_RECOVER = 200017;

    /**
     * @Text("商品-操作-批量彻底删除")
     */
    public const GOODS_OPERATION_BATCH_REAL_DELETE = 200018;

    /**
     * @Text("商品-操作-设置商品分类")
     */
    public const GOODS_OPERATION_SET_CATEGORY = 200019;


    /**********分类*********/
    /**
     * @Text("商品-分类-保存")
     */
    public const GOODS_CATEGORY_SAVE = 200020;
    /**
     * @Text("商品-分类-单个修改")
     */
    public const GOODS_CATEGORY_ONE_EDIT = 200021;
    /**
     * @Text("商品-分类-删除")
     */
    public const GOODS_CATEGORY_DELETE = 200022;
    /**
     * @Text("商品-分类-设置")
     */
    public const GOODS_CATEGORY_SETTING = 200023;
    /**
     * @Text("商品-分类-开关")
     */
    public const GOODS_CATEGORY_SWITCH = 200024;

//    /**
//     * @Text("商品-标签-添加")
//     */
//    public const GOODS_LABEL_ADD = 200020;
//    /**
//     * @Text("商品-标签-修改")
//     */
//    public const GOODS_LABEL_EDIT = 200021;
//    /**
//     * @Text("商品-标签-删除")
//     */
//    public const GOODS_LABEL_DELETE = 200022;

    /**
     * @Text("商品-标签分组-添加")
     */
    public const GOODS_LABEL_GROUP_ADD = 200030;
    /**
     * @Text("商品-标签分组-修改")
     */
    public const GOODS_LABEL_GROUP_EDIT = 200031;
    /**
     * @Text("商品-标签分组-删除")
     */
    public const GOODS_LABEL_GROUP_DELETE = 200032;
    /**
     * @Text("商品-标签分组-开关")
     */
    public const GOODS_LABEL_GROUP_SWITCH = 200033;
    /**
     * @Text("商品-分组-添加")
     */
    public const GOODS_GROUP_ADD = 200040;
    /**
     * @Text("商品-分组-修改")
     */
    public const GOODS_GROUP_EDIT = 200041;
    /**
     * @Text("商品-分组-删除")
     */
    public const GOODS_GROUP_DELETE = 200042;

    /**
     * @Text("商品-分组-开关")
     */
    public const GOODS_GROUP_SWITCH = 200043;

}
