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

namespace install\services;

/**
 * 安装检测环境服务
 * Class CheckEnvService
 * @package install\services
 * @author likexin
 */
class CheckEnvService
{

    /**
     * @var string[] 提示列表
     */
    private static array $tipList = [
        '确保当前服务器操作系统为Linux',
        '最低服务器配置2核4M，5M带宽',
        '确保PHP版本7.4及以上',
        '数据库MySQL版本5.6或者5.7',
    ];

    /**
     * @var string[][] 检测列表
     */
    private static array $checkList = [
        [
            'type' => 'os',
            'text' => '服务器操作系统检测(是否为Linux)',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
        [
            'type' => 'domain',
            'text' => '检测当前域名是否可以公网访问',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
        [
            'type' => 'phpVersion',
            'text' => '检测PHP版本是否7.4以上',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
        [
            'type' => 'phpExtend',
            'text' => '检测必要PHP扩展',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
        [
            'type' => 'phpFunction',
            'text' => '检测PHP禁用函数',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
        [
            'type' => 'phpConfig',
            'text' => ' 检测PHP配置信息(上传大小限制、错误级别、时区)',
            'link_url' => 'http://wiki.kaidianxing.com/',
        ],
    ];

    /**
     * 开始检测
     * @return array
     * @author likexin
     */
    public static function check(): array
    {
        $list = [];
        $checked = 0;

        // 遍历执行
        foreach (self::$checkList as $row) {
            $method = 'check' . ucfirst($row['type']);
            // 判断是否有方法
            if (!method_exists(self::class, $method)) {
                continue;
            }
            // 调用方法
            $check = call_user_func_array([self::class, $method], []);

            $row['checked'] = !is_error($check);
            $row['message'] = $row['checked'] ? '' : $check['message'];

            // 检测成功++
            if ($row['checked']) {
                $checked++;
            }

            $list[] = $row;
        }

        return [
            'tip_list' => self::$tipList,
            'check_list' => $list,
            'check_success' => count($list) == $checked,
        ];
    }

    /**
     * 服务器操作系统检测(是否为Linux)
     * @return array
     * @author likexin
     */
    private static function checkOs(): array
    {
        if (PHP_OS !== 'Linux') {
            return error('请使用Linux系统服务器');
        }
        return success();
    }

    /**
     * 检测PHP版本
     * @return array
     * @author likexin
     */
    private static function checkPhpVersion(): array
    {
        if (version_compare(phpversion(), '7.4.0', '<')) {
            return error('PHP最低支持版本7.4');
        }
        return success();
    }

    /**
     * 检测PHP扩展
     * @return array
     * @author likexin
     */
    private static function checkPhpExtend(): array
    {
        $loadedExtend = get_loaded_extensions();

        // 必须安装扩展
        $extend = ['fileinfo', 'bcmath', 'redis', 'pdo_mysql', 'zip', 'curl', 'curl', 'gd', 'Reflection'];

        // 取出不同
        $diff = array_diff($extend, $loadedExtend);
        if ($diff) {
            return error('请检查并安装扩展:' . implode(',', $diff));
        }

        // 不可安装扩展
        $exclude = ['swoole_loader', 'xdebug'];
        $diff = array_diff($exclude, $loadedExtend);
        if (array_diff($exclude, $diff)) {
            return error('请检查并删除不兼容扩展: ' . implode(',', array_diff($exclude, $diff)));
        }

        return success();
    }

    /**
     * 检测PHP禁用函数
     * @return array
     * @author likexin
     */
    private static function checkPhpFunction(): array
    {
        // 读取禁用函数
        $disableFunctions = explode(',', ini_get('disable_functions'));

        // 需要检测的函数
        $functions = ['pcntl_signal', 'pcntl_signal_dispatch', 'proc_open', 'proc_close', 'proc_get_status'];

        // 取出交集
        $intersect = array_intersect($functions, $disableFunctions);
        if ($intersect) {
            return error('禁用函数错误，请检查并删除禁用函数: ' . implode(',', $intersect));
        }

        return success();
    }

    /**
     * 检测PHP配置信息(上传大小限制、错误级别、时区)
     * @return array
     * @author likexin
     */
    private static function checkPhpConfig(): array
    {
        if ((int)ini_get('post_max_size') < 50) {
            // 上传大小限制 post_max_size
            return error('POST数据限制设置错误，请修改php.init参数post_max_size为"50M"');

        } elseif ((int)ini_get('upload_max_filesize') < 50) {
            // 上传大小限制 upload_max_filesize
            return error('上传文件限制设置错误，请修改php.ini参数upload_max_filesize为"50M"');

        } elseif (ini_get('error_reporting') != '32759' && ini_get('error_reporting') != '32757') {
            // 错误级别
            return error('错误级别设置错误，请修改php.ini参数error_reporting为"E_ALL & ~E_NOTICE"或"E_ALL & ~E_NOTICE & ~E_WARNING"');

        } elseif (!in_array(ini_get('date.timezone'), ['PRC', 'Asia/Shanghai'])) {
            // 时区
            return error('时区设置错误，请修改php.ini参数date.timezone为"Asia/Shanghai"');
        }

        return success();
    }

}