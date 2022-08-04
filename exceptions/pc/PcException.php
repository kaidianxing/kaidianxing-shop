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

namespace shopstar\exceptions\pc;

use shopstar\bases\exception\BaseException;

class PcException extends BaseException
{
    /**
     * @Message("菜单类型传递不合法")
     */
    public const PC_MENU_TYPE_ERROR = 710001;

    /**
     * @Message("菜单图片为空")
     */
    public const PC_MENU_IMG_EMPTY = 710002;

    /**
     * @Message("客服设置保存失败")
     */
    public const SERVICE_SAVE_FAIL = 710003;
    /**
     * @Message("版权设置保存失败")
     */
    public const COPYRIGHT_SAVE_FAIL = 710004;
    /**
     * @Message("基本设置保存失败")
     */
    public const BASIC_SAVE_FAIL = 710005;

    /**
     * @Message("菜单ID为空")
     */
    public const MENUS_ID_EMPTY = 710006;

    /**
     * @Message("商品组ID为空")
     */
    public const GOODS_GROUP_ID_EMPTY = 710007;

    /**
     * @Message("广告组ID为空")
     */
    public const HOME_ADVERTISE_ID_EMPTY = 710008;

    /**
     * @Message("商品组ID错误")
     */
    public const GOODS_GROUP_ID_ERROR = 710009;

    /**
     * @Message("广告组最大数为20")
     */
    public const HOME_ADVERTISE_MAX_COUNT_20 = 710010;
}
