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

namespace shopstar\constants\virtualAccount;

use shopstar\bases\constant\BaseConstant;

/**
 * Class VirtualAccountDataConstant
 * @package shopstar\constants\virtualAccount
 */
class VirtualAccountDataConstant extends BaseConstant
{
    /**
     * @Text("后台新增数据")
     */
    const CREATE_WAY_ADD = 1;

    /**
     * @Text("EXCEL导入")
     */
    const CREATE_WAY_IMPORT = 2;

    /**
     * @Text("待支付")
     */
    const ORDER_VIRTUAL_ACCOUNT_DATA_WAIT_PAY = 2;

    /**
     * @Text("已出售")
     */
    const ORDER_VIRTUAL_ACCOUNT_DATA_SUCCESS = 1;

    /**
     * @Text("普通状态")
     */
    const ORDER_VIRTUAL_ACCOUNT_DATA_NOT = 0;
}