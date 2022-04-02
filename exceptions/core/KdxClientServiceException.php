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

namespace shopstar\exceptions\core;

use shopstar\bases\exception\BaseException;

/**
 * Class KdxClientServiceException
 * @package shopstar\exceptions\core
 * @author likexin
 */
class KdxClientServiceException extends BaseException
{

    /**
     * @Message("与client通讯异常")
     */
    public const CONNECT_GET_FAIL = 191800;

    /**
     * @Message("与client通讯异常")
     */
    public const CONNECT_POST_FAIL = 191001;

    /**
     * @Message("client返回数据参数缺失")
     */
    public const CHECK_RESPONSE_SIGN_PARAM_SIGN_EMPTY = 191110;

    /**
     * @Message("client返回数据不完整")
     */
    public const CHECK_RESPONSE_SIGN_FAIL = 191111;

    /**
     * @Message("client返回数据可能被修改")
     */
    public const CHECK_RESPONSE_USED_PROXY = 191112;

    /**
     * @Message("系统升级初始化失败")
     */
    public const SYSTEM_UPGRADE_INIT_FAIL = 191801;

    /**
     * @Message("系统升级请求升级失败")
     */
    public const SYSTEM_UPGRADE_START_FAIL = 191300;

    /**
     * @Message("系统升级获取升级进度失败")
     */
    public const SYSTEM_UPGRADE_GET_STATUS_FAIL = 191804;

    /**
     * @Message("系统升级执行升级失败")
     */
    public const SYSTEM_UPGRADE_EXECUTE_FAIL = 191500;

    /**
     * @Message("系统升级执行升级数据表失败")
     */
    public const SYSTEM_UPGRADE_EXECUTE_TABLE_FAIL = 191501;

}