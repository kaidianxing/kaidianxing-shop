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

namespace shopstar\exceptions\wxTransactionComponent;

use shopstar\bases\exception\BaseException;

class WxTransactionComponentException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    public const PARAMS_ERROR = 470100;

    /**
     * @Message("超出商品资质图片上传数量")
     */
    public const WX_TRANSACTION_COMPONENT_NUMBER_ERROR = 470101;

    /**
     * @Message("超出商品选取数量")
     */
    public const SHOP_GOODS_NUMBER_BEYOND_ERROR = 470102;

    /**
     * @Message("添加失败")
     */
    public const WX_TRANSACTION_COMPONENT_ADD_ERROR = 470103;

    /**
     * @Message("未获取该类目商品资质")
     */
    public const WX_TRANSACTION_COMPONENT_COMMODITY_QUALIFICATION_ERROR = 470104;

    /**
     * @Message("商品正在审核中不可重复提交")
     */
    public const WX_TRANSACTION_COMPONENT_GOODS_UPLOAD_STATUS_IN_ERROR = 470105;

    /**
     * @Message("交易组件中商品记录不存在")
     */
    public const WX_TRANSACTION_COMPONENT_GOODS_LOG_ERROR = 470106;

    /**
     * @Message("审核中不允许下架")
     */
    public const WX_TRANSACTION_COMPONENT_STATUS_IN_NOT_UPDATE_STATUS_ERROR = 470107;

    /**
     * @Message("审核中不允许删除")
     */
    public const WX_TRANSACTION_COMPONENT_STATUS_IN_NOT_DELETE_ERROR = 470108;

    /**
     * @Message("仅支持已审核成功的商品进行免审更新")
     */
    public const WX_TRANSACTION_COMPONENT_STATUS_IN_EXEMPTION_UPDATE_STATUS_ERROR = 470109;

    /**
     * @Message("不是审核中商品不允许撤回审核")
     */
    public const WX_TRANSACTION_COMPONENT_NOT_STATUS_IN_RESET_AUDIT_ERROR = 470110;
}
