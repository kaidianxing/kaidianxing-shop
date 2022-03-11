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

/**
 * @var array 初始注册的模块
 */
$modules = [
    /**
     * @var array 商城
     */
    'kdx' => [
        'class' => 'shopstar\config\modules\KdxModule',
    ],
];


// 如果没安装注册安装模块
if (!SHOP_STAR_IS_INSTALLED) {
    $modules['install'] = [
        'class' => 'modules\install\config\InstallModule',
    ];
}

return $modules;