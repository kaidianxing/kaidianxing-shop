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
 * 全局常量定义
 */

defined('SHOP_STAR_PATH') or define('SHOP_STAR_PATH', dirname(__DIR__));
defined('SHOP_STAR_IS_INSTALLED') or define('SHOP_STAR_IS_INSTALLED', is_file(SHOP_STAR_PATH . '/config/install.lock'));

defined('SHOP_STAR_PUBLIC_PATH') or define('SHOP_STAR_PUBLIC_PATH', SHOP_STAR_PATH . '/public');
defined('SHOP_STAR_VENDOR_PATH') or define('SHOP_STAR_VENDOR_PATH', SHOP_STAR_PATH . '/vendor');
defined('SHOP_STAR_RUNTIME_PATH') or define('SHOP_STAR_RUNTIME_PATH', SHOP_STAR_PATH . '/runtime');
defined('SHOP_STAR_TMP_PATH') or define('SHOP_STAR_TMP_PATH', SHOP_STAR_PATH . '/tmp');
defined('SHOP_STAR_PUBLIC_TMP_PATH') or define('SHOP_STAR_PUBLIC_TMP_PATH', SHOP_STAR_PUBLIC_PATH . '/tmp');
defined('SHOP_STAR_PUBLIC_DATA_PATH') or define('SHOP_STAR_PUBLIC_DATA_PATH', SHOP_STAR_PUBLIC_PATH . '/data');

/**
 * 引入系统版本定义文件
 */
require SHOP_STAR_PATH . '/config/version.php';

/**
 * 引入Yii
 */
require SHOP_STAR_VENDOR_PATH . '/autoload.php';
require SHOP_STAR_VENDOR_PATH . '/yiisoft/yii2/Yii.php';

/**
 * 别名定义
 */
Yii::setAlias('@shopstar', SHOP_STAR_PATH);
Yii::setAlias('@modules', SHOP_STAR_PATH . '/modules');
Yii::setAlias('@apps', SHOP_STAR_PATH . '/apps');
Yii::setAlias('@custom', SHOP_STAR_PATH . '/custom');
Yii::setAlias('@static', SHOP_STAR_PUBLIC_PATH . '/static');
