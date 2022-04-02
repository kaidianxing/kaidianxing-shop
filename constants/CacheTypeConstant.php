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
class CacheTypeConstant extends BaseConstant
{

    const CACHE_DEFINE = [
        CacheTypeConstant::USER_PROFILE => ['key' => 'user_profile', 'type' => 'string', 'expire' => 3600],
        CacheTypeConstant::MANAGE_PROFILE => ['key' => 'manage_profile', 'type' => 'string', 'expire' => 3600],
        CacheTypeConstant::ROLE_PERMS => ['key' => 'role_perms', 'type' => 'string', 'expire' => 3600],
        CacheTypeConstant::PRINTER_ACCESS_TOKEN => ['key' => 'printer_access_token', 'type' => 'string', 'expire' => 600],
        CacheTypeConstant::MAKE_TOKEN => ['key' => 'make_token', 'type' => 'string', 'expire' => 86400],
    ];

    /**
     * 用户详情
     */
    const USER_PROFILE = 1;

    /**
     * 管理员详情
     */
    const MANAGE_PROFILE = 2;

    /**
     * 角色权限
     */
    const ROLE_PERMS = 3;

    /**
     * 打印小票打印机access_token
     */
    const PRINTER_ACCESS_TOKEN = 4;

    /**
     * 码科跑腿token
     */
    const MAKE_TOKEN = 5;

    /**
     * 邀请核销员缓存key
     */
    public const CACHEKEY = 'verifier_qrcode_';
}
