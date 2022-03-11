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


namespace shopstar\helpers;

/**
 * 客户端助手类
 * Class LiYangClientHelper
 * @package shopstar\helpers
 * @author likexin
 */
class LiYangClientHelper
{

    public const ROUTE_HTTP_CLIENT_UPGRADE_INIT = '/upgrade/init';
    public const ROUTE_HTTP_CLIENT_UPGRADE_START = '/upgrade/start';
    public const ROUTE_HTTP_CLIENT_UPGRADE_GET_STATUS = '/upgrade/get_status';
    public const ROUTE_HTTP_CLIENT_UPGRADE_EXECUTE = '/upgrade/execute';
    public const ROUTE_HTTP_CLIENT_UPGRADE_REPORT_STATUS = '/upgrade/report_status';
    public const ROUTE_HTTP_CLIENT_UPGRADE_RESET = '/upgrade/reset';

    public const ROUTE_HTTP_CLIENT_APP_INSTALL_START = '/app/install/start';
    public const ROUTE_HTTP_CLIENT_APP_INSTALL_GET_STATUS = '/app/install/get_status';
    public const ROUTE_HTTP_CLIENT_APP_INSTALL_RESET = '/app/install/reset';

}