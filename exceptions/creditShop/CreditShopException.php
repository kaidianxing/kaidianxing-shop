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

namespace shopstar\exceptions\creditShop;

use shopstar\bases\exception\BaseException;

/**
 * 积分商城异常类
 * Class CreditShopException.
 * @package shopstar\exceptions\creditShop
 */
class CreditShopException extends BaseException
{
    /**
     * @Message("商品保存失败")
     */
    public const ADD_GOODS_SAVE_FAIL = 520000;

    /**
     * @Message("商品不存在")
     */
    public const DETAIL_GOODS_NOT_EXISTS = 520001;

    /**
     * @Message("商品不存在")
     */
    public const EDIT_GOODS_NOT_EXISTS = 520002;

    /**
     * @Message("保存失败")
     */
    public const EDIT_GOODS_SAVE_FAIL = 520003;

    /**
     * @Message("状态错误")
     */
    public const CHANGE_STATUS_STATUS_ERROR = 520004;

    /**
     * @Message("商品不存在")
     */
    public const MOBILE_DETAIL_GOODS_NOT_EXISTS = 520005;

    /**
     * @Message("积分商城未开启")
     */
    public const CREDIT_SHOP_STATUS_ERROR = 520006;

    /**
     * @Message("参数错误")
     */
    public const GET_CODE_PARAMS_ERROR = 520007;
}
