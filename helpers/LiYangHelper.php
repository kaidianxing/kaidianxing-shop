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

use shopstar\bases\exception\BaseApiException;

/**
 * Class LiYangHelper
 * @package shopstar\helpers
 */
class LiYangHelper
{

    /**
     * 检测安装
     * @param bool $isView
     * @throws BaseApiException
     * @author likexin
     */
    public static function checkInstall(bool $isView)
    {
        $installLock = SHOP_STAR_PATH . '/config/install.lock';
        if (is_file($installLock)) {
            return;
        }

        // 视图进来时跳转
        if ($isView) {
            header('Location: ' . '/install');
            exit;
        }

        // api抛出异常
        throw new BaseApiException(BaseApiException::SYSTEM_NOT_INSTALL);
    }

    public const PRODUCT_ID = 2;

    /**
     * 系统修复
     */
    public const ROUTE_SYSTEM_REPAIR_INIT = '/system/repair/init';
    public const ROUTE_SYSTEM_REPAIR_CHECK = '/system/repair/check';
    public const ROUTE_SYSTEM_REPAIR_UPDATE_CHECK_DIFF = '/system/repair/update-check-diff';
    public const ROUTE_SYSTEM_REPAIR_REQUEST = '/system/repair/request';
    public const ROUTE_SYSTEM_REPAIR_DOWNLOAD_FILE = '/system/repair/download-file';
    public const ROUTE_SYSTEM_REPAIR_UPDATE_REPAIR_STATUS = '/system/repair/update-repair-status';
    public const ROUTE_SYSTEM_REPAIR_REPORT_EXCEPTION = '/system/repair/report-exception';
    public const ROUTE_SYSTEM_REPAIR_COMPLETE = '/system/repair/complete';

    /**
     * 系统升级
     */
    public const ROUTE_SYSTEM_UPGRADE_GET_LOG_LIST = '/system/upgrade/get-log-list';
    public const ROUTE_SYSTEM_UPGRADE_GET_VERSION_LOG = '/system/upgrade/get-version-log';

    /**
     * 系统授权
     */
    public const ROUTE_SYSTEM_AUTH_CHECK = '/auth/index/check';
    public const ROUTE_SYSTEM_AUTH_SERVICE_GET_FUNCTION = '/auth/service/get-function';
    public const ROUTE_SYSTEM_AUTH_SHOP_WECHAT = '/auth/shop/update-wechat';

    /**
     * 应用相关
     */
    public const ROUTER_APP_LIST_NOT_INSTALL = '/app/list/not-install';
    public const ROUTER_APP_INSTALL_CHECK = '/app/install/check';
    public const ROUTER_APP_INSTALL_REQUEST = '/app/install/request';
    public const ROUTER_APP_INSTALL_GET_REQUEST_STATUS = '/app/install/get-request-status';
    public const ROUTER_APP_INSTALL_DOWNLOAD_FILE = '/app/install/download-file';
    public const ROUTER_APP_INSTALL_COMPLETE = '/app/install/complete';

    /**
     * 微信小程序上传
     */
    public const ROUTE_WX_APP_UPLOAD_INIT = '/miniApp/wechat-upload/init';
    public const ROUTE_WX_APP_UPLOAD_UPLOAD = '/miniApp/wechat-upload/upload';
    public const ROUTE_WX_APP_UPLOAD_GET_STATUS = '/miniApp/wechat-upload/get-status';
    public const ROUTE_WX_APP_UPLOAD_LOGIN_GET_QR_CODE = '/miniApp/wechat-upload-login/get-qr-code';
    public const ROUTE_WX_APP_UPLOAD_LOGIN_GET_SCAN_STATUS = '/miniApp/wechat-upload-login/get-scan-status';
    public const ROUTE_WX_APP_UPLOAD_LOGIN_GET_TICKET = '/miniApp/wechat-upload-login/get-ticket';

    /**
     * 字节跳动小程序上传
     */
    public const ROUTE_BD_APP_UPLOAD_INIT = '/miniApp/byte-dance-upload/init';
    public const ROUTE_BD_APP_UPLOAD_UPLOAD = '/miniApp/byte-dance-upload/upload';
    public const ROUTE_BD_APP_UPLOAD_UPLOAD_GET_STATUS = '/miniApp/wechat-upload/get-status';
    public const ROUTE_BD_APP_UPLOAD_LOGIN_GET_CAPTCHA = '/miniApp/byte-dance-upload-login/get-captcha';
    public const ROUTE_BD_APP_UPLOAD_LOGIN_SEND_SMS_CODE = '/miniApp/byte-dance-upload-login/send-sms-code';
    public const ROUTE_BD_APP_UPLOAD_LOGIN_SMS_LOGIN = '/miniApp/byte-dance-upload-login/sms-login';
    public const ROUTE_BD_APP_UPLOAD_LOGIN_EMAIL_LOGIN = '/miniApp/byte-dance-upload-login/email-login';

    /**
     * 开放接口
     */
    public const ROUTE_OPEN_API_GOODS_HELPER_GET = '/goodsHelper/index/index';

}