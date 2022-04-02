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

namespace shopstar\exceptions\sysset;

use shopstar\bases\exception\BaseException;

/**
 * 同城配送
 * Class IntracityException
 * @package shopstar\exceptions\sysset
 * @author 青岛开店星信息技术有限公司
 */
class IntracityException extends BaseException
{

    /*************同城配送设置校验异常*************/

    /**
     * @Message("同城配送至少开启一种配送方式")
     */
    const SHOP_SETTINGS_INTRACITY_DISPATCH_INVALID = 139002;

    /**
     * @Message("未检测到店铺地址，请尽快设置")
     */
    const SHOP_SETTINGS_SHOP_ADDRESS_NOT_EMPTY = 139003;

    /**
     * @Message("高德KEY不能为空")
     */
    const SHOP_SETTINGS_AMAP_KEY_NOT_EMPTY = 139004;

    /**
     * @Message("配送区域不合法")
     */
    const SHOP_SETTINGS_DISPATCH_AREA_INVALID = 139005;

    /**
     * @Message("配送规则不合法")
     */
    const SHOP_SETTINGS_DISPATCH_RULE_INVALID = 139006;

    /**
     * @Message("可配送行政区域不合法")
     */
    const SHOP_SETTINGS_DISPATCH_DISPATCH_BARRIO_INVALID = 139007;

    /**
     * @Message("可配送行政区域规则参数不合法")
     */
    const SHOP_SETTINGS_DISPATCH_BARRIO_RULES_INVALID = 139008;

    /**
     * @Message("配送参数不合法")
     */
    const SHOP_SETTINGS_AREA_RADIO_WAY_INVALID = 139010;

    /**
     * @Message("配送参数不合法")
     */
    const SHOP_SETTINGS_AREA_DIY_WAY_INVALID = 139011;

    /**
     * @Message("配送参数不合法")
     */
    const SHOP_SETTINGS_DISTANCE_RADIO_WAY_INVALID = 139012;

    /**
     * @Message("配送参数不合法")
     */
    const SHOP_SETTINGS_DISTANCE_DIY_WAY_INVALID = 139013;

    /**
     * @Message("配送参数不合法")
     */
    const SHOP_SETTINGS_BARRIO_WAY_INVALID = 139014;

    /**
     * @Message("店铺区域编号错误")
     */
    const SHOP_SETTINGS_CITY_CODE_INVALID = 139015;

    /**
     * @Message("配送规则参数错误")
     */
    const SHOP_SETTINGS_DISTRICT_DISPATCH_RULE_INVALID = 139016;

    /**
     * @Message("配送规则参数错误")
     */
    const SHOP_SETTINGS_DISTANCE_DISPATCH_RULE_INVALID = 139017;

    /**
     * @Message("配送规则参数错误")
     */
    const SHOP_SETTINGS_BARRIO_RULE_INVALID = 139018;

    /**
     * @Message("至少开启一种第三方配送方式")
     */
    const SHOP_SETTINGS_INTRACITY_THIRD_DISPATCH_INVALID = 139019;

    /**
     * @Message("达达配送参数错误")
     */
    const SHOP_SETTINGS_INTRACITY_DADA_DISPATCH_PARAMS_INVALID = 139020;

    /**
     * @Message("码科配送参数错误")
     */
    const SHOP_SETTINGS_INTRACITY_MAKE_DISPATCH_PARAMS_INVALID = 139021;


    /**
     * @Message("顺丰配送参数错误")
     */
    const SHOP_SETTINGS_INTRACITY_SF_DISPATCH_PARAMS_INVALID = 139022;


    /**
     * @Message("闪送配送参数错误")
     */
    const SHOP_SETTINGS_INTRACITY_SHANSONG_DISPATCH_PARAMS_INVALID = 139023;


    /**
     * @Message("未查询到地区")
     */
    const SHOP_SETTINGS_CITY_CODE_FIND_INVALID = 139024;


    /*************同城配送开启关闭异常*************/

    /**
     * @Message("至少需要开启一种配送方式才可以正常营业")
     */
    const SHOP_SETTINGS_DISPATCH_INTRACITY_ENABLE_INVALID = 139050;


    /**
     * @Message("平台端未填写高德KEY")
     */
    const PLATFORM_SETTINGS_AMAP_KEY_NOT_EMPTY = 139051;

}