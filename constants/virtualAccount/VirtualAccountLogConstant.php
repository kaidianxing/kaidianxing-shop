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
 * @author 青岛开店星信息技术有限公司
 */
class VirtualAccountLogConstant extends BaseConstant
{
    /**
     * @Text("卡密库-操作-添加")
     */
    public const VIRTUAL_ACCOUNT_EDIT_ADDRESS = 670001;


    /**
     * @Text("卡密库-操作-编辑")
     */
    public const VIRTUAL_ACCOUNT_EDIT_EDIT = 670002;

    /**
     * @Text("卡密库-操作-添加卡密数据")
     */
    public const VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA = 670003;

    /**
     * @Text("卡密库-操作-删除卡密数据")
     */
    public const VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_DATA = 670004;

    /**
     * @Text("卡密库-操作-删除")
     */
    public const VIRTUAL_ACCOUNT_DATA_EDIT_DELETE = 670005;

    /**
     * @Text("卡密库设置-操作-未付款订单关闭时间")
     */
    public const VIRTUAL_ACCOUNT_SETTING_EDIT_CLOSE_TIME = 670006;

    /**
     * @Text("卡密库-操作-彻底删除")
     */
    public const VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_COMPLETE = 670007;

    /**
     * @Text("卡密库-操作-恢复")
     */
    public const VIRTUAL_ACCOUNT_DATA_EDIT_RESTORE = 670008;


}
