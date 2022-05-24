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

class WxTransactionComponentConstant extends BaseConstant
{
    /**
     * @Text("审核撤销")
     */
    public const NOT_STATUS = 10;

    /**
     * @Text("审核中")
     */
    public const STATUS_IN = 20;

    /**
     * @Text("审核成功")
     */
    public const STATUS_SUCCESS = 30;

    /**
     * @Text("审核失败")
     */
    public const STATUS_ERROR = 40;

    /**
     * @Text("下架状态")
     */
    public const REMOTE_STATUS_DOWN = 1;

    /**
     * @Text("上架状态")
     */
    public const REMOTE_STATUS_UP = 2;
}
