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
 * 云服务异常
 * Class CloudService
 * @package shopstar\exceptions\core
 * @author 青岛开店星信息技术有限公司
 */
class CloudServiceException extends BaseException
{

    // 1900xx 基础

    /**
     * @Message("与云服务通讯异常")
     */
    public const CONNECT_GET_FAIL = 190000;

    /**
     * @Message("与云服务通讯异常")
     */
    public const CONNECT_POST_FAIL = 190001;

    /**
     * @Message("与云服务通讯异常")
     */
    public const CONNECT_REQUEST_PARAM_AUTH_CODE_EMPTY = 190004;

    /**
     * @Message("请先验证授权")
     */
    public const BUILD_REQUEST_PARAMS_AUTH_FAIL = 190003;

    // 1901xx 检测授权

    /**
     * @Message("检测授权失败")
     */
    public const CHECK_AUTH_FAIL = 190100;

    /**
     * @Message("获取授权信息失败")
     */
    public const CHECK_AUTH_GET_INFO_FAIL = 190101;

    /**
     * @Message("返回数据参数缺失")
     */
    public const CHECK_RESPONSE_SIGN_PARAM_SIGN_EMPTY = 190110;

    /**
     * @Message("返回数据不完整")
     */
    public const CHECK_RESPONSE_SIGN_FAIL = 190111;

    /**
     * @Message("云服务返回数据可能被修改")
     */
    public const CHECK_RESPONSE_USED_PROXY = 190112;

    // 1902xx 系统修复

    /**
     * @Message("系统修复初始化失败")
     */
    public const REPAIR_INIT_FAIL = 190200;

    /**
     * @Message("系统修复检测失败")
     */
    public const REPAIR_CHECK_FAIL = 190300;
    /**
     * @Message("检测文件差异失败")
     */
    public const REPAIR_CHECK_UPLOAD_DIFF_FAIL = 190301;

    /**
     * @Message("请求更新需要先检测更新")
     */
    public const REPAIR_REQUEST_NEED_CHECKED = 190400;

    /**
     * @Message("无需请求云端下载")
     */
    public const REPAIR_REQUEST_NOT_NEED = 190402;

    /**
     * @Message("请求云端生成文件失败")
     */
    public const REPAIR_REQUEST_FAIL = 190403;

    /**
     * @Message("请先请求更新文件")
     */
    public const REPAIR_DOWNLOAD_NOT_REQUEST = 190500;

    /**
     * @Message("等待文件打包成功")
     */
    public const REPAIR_DOWNLOAD_WAIT_REQUESTED = 190501;

    /**
     * @Message("未加载修复包信息")
     */
    public const REPAIR_DOWNLOAD_INFO_ERROR = 190503;

    /**
     * @Message("当前文件包已经下载完成")
     */
    public const REPAIR_DOWNLOAD_FINISHED = 190504;

    /**
     * @Message("当前状态不可下载")
     */
    public const REPAIR_DOWNLOAD_STATUS_INVALID = 190505;

    /**
     * @Message("当前分片已经下载文件")
     */
    public const REPAIR_DOWNLOAD_SHARD_DOWNLOAD = 190506;

    /**
     * @Message("文件下载失败")
     */
    public const REPAIR_DOWNLOAD_FAIL = 190507;

    /**
     * @Message("保存文件失败")
     */
    public const REPAIR_DOWNLOAD_SAVE_FAIL = 190508;

    /**
     * @Message("修复文件包未下载完成或数据错误")
     */
    public const REPAIR_EXECUTE_NOT_DOWNLOAD = 190601;

    /**
     * @Message("请求更新需要先检测更新")
     */
    public const REPAIR_EXECUTE_NEED_CHECKED = 190602;

    /**
     * @Message("修复文件包不完整")
     */
    public const REPAIR_EXECUTE_PACKAGE_NOT_FULL = 190603;

    /**
     * @Message("修复文件包解压缩失败")
     */
    public const REPAIR_EXECUTE_UNZIP_FAIL = 190604;

    /**
     * @Message("文件未找到")
     */
    public const REPAIR_EXECUTE_FILE_NOT_EXITS = 190605;

    /**
     * @Message("文件移动失败")
     */
    public const REPAIR_EXECUTE_FILE_MOVE_FAIL = 190606;

    /**
     * @Message("修复执行失败")
     */
    public const REPAIR_EXECUTE_FAIL = 190607;

    /**
     * @Message("已经执行修复")
     */
    public const REPAIR_EXECUTE_ALREADY = 190609;

    /**
     * @Message("没有执行完基础操作")
     */
    public const REPAIR_EXECUTE_SCRIPT_NOT_EXECUTE = 190610;

    /**
     * @Message("没有脚本可执行")
     */
    public const REPAIR_EXECUTE_SCRIPT_NO_SCRIPT = 190611;

    /**
     * @Message("全部脚本已经执行完成")
     */
    public const REPAIR_EXECUTE_SCRIPT_ALL_FINISHED = 190612;

    /**
     * @Message("脚本不存在")
     */
    public const REPAIR_EXECUTE_SCRIPT_NOT_EXIST = 190613;

    /**
     * @Message("脚本已经执行完成")
     */
    public const REPAIR_EXECUTE_SCRIPT_FINISHED = 190614;

    /**
     * @Message("脚本写入失败")
     */
    public const REPAIR_EXECUTE_SCRIPT_SAVE_FAIL = 190615;

    /**
     * @Message("没有执行完基础操作")
     */
    public const REPAIR_COMPLETE_NOT_EXECUTE = 190700;


    /**
     * @Message("参数错误 identity不能为空")
     */
    public const APP_INSTALL_CHECK_PARAM_IDENTITY_EMPTY = 191000;

    /**
     * @Message("参数错误 app_id不能为空")
     */
    public const APP_INSTALL_CHECK_PARAM_APP_ID_EMPTY = 191301;

    /**
     * @Message("应用已经安装成功")
     */
    public const APP_INSTALL_CHECK_SHOP_STAR_IS_INSTALLED = 191002;

    /**
     * @Message("检测失败")
     */
    public const APP_INSTALL_CHECK_FAIL = 191003;

    /**
     * @Message("请先检测安装")
     */
    public const APP_INSTALL_REQUEST_NOT_CHECK = 191010;

    /**
     * @Message("请求安装失败")
     */
    public const APP_INSTALL_REQUEST_FAIL = 191011;

    /**
     * @Message("请先检测安装")
     */
    public const APP_INSTALL_GET_REQUEST_STATUS_NOT_CHECK = 191020;

    /**
     * @Message("获取安装状态失败")
     */
    public const APP_INSTALL_GET_REQUEST_STATUS_FAIL = 191021;

    /**
     * @Message("请先请求安装")
     */
    public const APP_INSTALL_DOWNLOAD_NOT_REQUEST = 191030;

    /**
     * @Message("当前分片已经下载文件")
     */
    public const APP_INSTALL_DOWNLOAD_SHARD_DOWNLOAD = 191031;

    /**
     * @Message("文件下载失败")
     */
    public const APP_INSTALL_DOWNLOAD_FAIL = 191032;

    /**
     * @Message("保存文件失败")
     */
    public const APP_INSTALL_DOWNLOAD_SAVE_FAIL = 191033;

    /**
     * @Message("请先请求安装")
     */
    public const APP_INSTALL_EXECUTE_NOT_REQUEST = 191040;

    /**
     * @Message("请先下载安装文件")
     */
    public const APP_INSTALL_EXECUTE_NOT_DOWNLOAD = 191041;

    /**
     * @Message("安装文件不完整")
     */
    public const APP_INSTALL_EXECUTE_FILE_NOT_FULL = 191043;

    /**
     * @Message("安装文件解压失败")
     */
    public const APP_INSTALL_EXECUTE_UNZIP_FAIL = 191044;

    /**
     * @Message("安装数据表为空")
     */
    public const APP_INSTALL_EXECUTE_TABLE_LIST_EMPTY = 191045;

    /**
     * @Message("安装文件为空")
     */
    public const APP_INSTALL_EXECUTE_DIFF_FILE_LIST_EMPTY = 191046;

    /**
     * @Message("安装文件不存在")
     */
    public const APP_INSTALL_EXECUTE_FILE_NOT_EXIST = 191047;

    /**
     * @Message("文件移动失败")
     */
    public const APP_INSTALL_EXECUTE_FILE_MOVE_FAIL = 191048;

    /**
     * @Message("执行安装脚本失败")
     */
    public const APP_INSTALL_EXECUTE_SCRIPT_FAIL = 191049;

    /**
     * @Message("安装失败")
     */
    public const APP_INSTALL_EXECUTE_FAIL = 191050;

    // 1912xx 系统升级

    /**
     * @Message("系统升级初始化失败")
     */
    public const UPGRADE_INIT_FAIL = 191200;

    /**
     * @Message("系统升级检测失败")
     */
    public const UPGRADE_CHECK_FAIL = 191802;

    /**
     * @Message("请先初始化升级")
     */
    public const UPGRADE_REQUEST_LATEST_VERSION_NOT_FOUND = 191400;

    /**
     * @Message("当前已经是最新版")
     */
    public const UPGRADE_REQUEST_ALREADY_LATEST_VERSION = 191401;

    /**
     * @Message("请求云端生成文件失败")
     */
    public const UPGRADE_REQUEST_FAIL = 191403;

    /**
     * @Message("请先请求更新文件")
     */
    public const UPGRADE_DOWNLOAD_NOT_REQUEST = 191805;

    /**
     * @Message("等待文件打包成功")
     */
    public const UPGRADE_DOWNLOAD_WAIT_REQUESTED = 191806;

    /**
     * @Message("未加载升级包信息")
     */
    public const UPGRADE_DOWNLOAD_INFO_ERROR = 191503;

    /**
     * @Message("当前文件包已经下载完成")
     */
    public const UPGRADE_DOWNLOAD_FINISHED = 191504;

    /**
     * @Message("当前状态不可下载")
     */
    public const UPGRADE_DOWNLOAD_STATUS_INVALID = 191505;

    /**
     * @Message("当前分片已经下载文件")
     */
    public const UPGRADE_DOWNLOAD_SHARD_DOWNLOAD = 191506;

    /**
     * @Message("文件下载失败")
     */
    public const UPGRADE_DOWNLOAD_FAIL = 191507;

    /**
     * @Message("保存文件失败")
     */
    public const UPGRADE_DOWNLOAD_SAVE_FAIL = 191508;

    /**
     * @Message("已经执行升级")
     */
    public const UPGRADE_EXECUTE_ALREADY = 191609;

    /**
     * @Message("升级文件包未下载完成或数据错误")
     */
    public const UPGRADE_EXECUTE_NOT_DOWNLOAD = 191601;

    /**
     * @Message("请求更新需要先检测更新")
     */
    public const UPGRADE_EXECUTE_NEED_CHECKED = 191602;

    /**
     * @Message("升级文件包不完整")
     */
    public const UPGRADE_EXECUTE_PACKAGE_NOT_FULL = 191603;

    /**
     * @Message("升级文件包解压缩失败")
     */
    public const UPGRADE_EXECUTE_UNZIP_FAIL = 191604;

    /**
     * @Message("升级文件包vendor解压缩失败")
     */
    public const UPGRADE_EXECUTE_UNZIP_VENDOR_FAIL = 191605;

    /**
     * @Message("文件移动失败")
     */
    public const UPGRADE_EXECUTE_FILE_MOVE_FAIL = 191606;

    /**
     * @Message("升级执行失败")
     */
    public const UPGRADE_EXECUTE_FAIL = 191607;

    /**
     * @Message("没有执行完基础操作")
     */
    public const UPGRADE_EXECUTE_SCRIPT_NOT_EXECUTE = 191610;

    /**
     * @Message("没有脚本可执行")
     */
    public const UPGRADE_EXECUTE_SCRIPT_NO_SCRIPT = 191611;

    /**
     * @Message("全部脚本已经执行完成")
     */
    public const UPGRADE_EXECUTE_SCRIPT_ALL_FINISHED = 191612;

    /**
     * @Message("脚本不存在")
     */
    public const UPGRADE_EXECUTE_SCRIPT_NOT_EXIST = 191613;

    /**
     * @Message("脚本已经执行完成")
     */
    public const UPGRADE_EXECUTE_SCRIPT_FINISHED = 191614;

    /**
     * @Message("没有执行完基础操作")
     */
    public const UPGRADE_COMPLETE_NOT_EXECUTE = 191700;

}