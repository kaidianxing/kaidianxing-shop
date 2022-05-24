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

namespace shopstar\constants\wxTransactionComponent;

use shopstar\bases\constant\BaseConstant;

class WxTransactionComponentLogConstant extends BaseConstant
{
    /********日志********/
    /**
     * @Text("小程序交易组件（视频号商城）-操作-自定义交易组件上传商品")
     */
    public const WX_TRANSACTION_COMPONENT_ADD_GOODS = 621000;

    /**
     * @Text("小程序交易组件（视频号商城）-操作-提交商品审核")
     */
    public const WX_TRANSACTION_COMPONENT_UPDATE_GOODS = 621001;

    /**
     * @Text("小程序交易组件（视频号商城）-操作-删除商品")
     */
    public const WX_TRANSACTION_COMPONENT_DELETE_GOODS = 621002;

    /**
     * @Text("小程序交易组件（视频号商城）-操作-下架商品")
     */
    public const WX_TRANSACTION_COMPONENT_UPDATE_STATUS_DOWN_GOODS = 621003;
}
