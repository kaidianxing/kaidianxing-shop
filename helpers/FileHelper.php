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

use yii\helpers\FileHelper as BaseFileHelper;

/**
 * 文件助手类
 * Class FileHelper
 * @package shopstar\helpers
 */
class FileHelper extends BaseFileHelper
{

    /**
     *
     * 写入文件
     * @param string $path 要写入的图片路径
     * @param string $body 要写入的内容
     * @param null $flags 附加内容 例如 FILE_APPEND
     * @return bool|array
     */
    public static function write($path, $body = '', $flags = null)
    {
        if (is_dir($path)) {
            return error('有同名的文件夹存在, 写入失败!');
        }

        $dir = dirname($path);

        try {
            self::createDirectory($dir);
        } catch (\yii\base\Exception $exception) {
            return error($exception->getMessage());
        }
        return file_put_contents($path, $body, $flags);
    }

    /**
     * 覆盖写入
     * @param string $path
     * @param string $targetPath
     * @return array|bool|int
     * @author likexin
     */
    public static function cover(string $path, string $targetPath)
    {
        if (!is_file($path)) {
            return error('源文件不存在');
        }
        return self::write($targetPath, file_get_contents($path));
    }

    /**
     * 复制文件或目录
     * @param string $path
     * @param string $targetPath
     * @return mixed|void
     * @throws \yii\base\Exception
     * @author likexin
     */
    public static function copy(string $path, string $targetPath)
    {
        if (is_dir($path)) {
            self::copyDirectory($path, $targetPath);
            return;
        }

        // 先创建目录
        self::createDirectory(dirname($targetPath));

        return @copy($path, $targetPath);
    }

    /**
     * 获取文件扩展名
     * @param string $file
     * @return string
     */
    public static function getExtension($file = '')
    {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * 将文件移动至目标位置，如果目标位置目录不存在，则创建
     * @param string $source 要移动的文件
     * @param string $target 移动的目标位置
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function move($source, $target)
    {
        self::createDirectory(dirname($target));
        if (is_uploaded_file($source)) {
            move_uploaded_file($source, $target);
        } else {
            rename($source, $target);
        }
        return is_file($target);
    }

    /**
     * 下载文件
     * @param $name
     * @param $path
     * @return array|void
     */
    public static function outputFile($name, $path)
    {
        //避免中文文件名出现检测不到文件名的情况，进行转码utf-8->gbk
        $path = iconv('utf-8', 'gb2312', $path);
        if (!file_exists($path)) {//检测文件是否存在
            return error('文件不存在!');
        }

        $fp = fopen($path, 'r');//只读方式打开
        $filesize = filesize($path);//文件大小


        //返回的文件(流形式)
        header("Content-type: application/octet-stream");
        //按照字节大小返回
        header("Accept-Ranges: bytes");
        //返回文件大小
        header("Accept-Length: $filesize");
        //这里客户端的弹出对话框，对应的文件名
        header("Content-Disposition: attachment; filename=" . $name);


        ob_clean();
        flush();
        //=================重点===================
        //设置分流
        $buffer = 1024;
        //来个文件字节计数器
        $count = 0;
        while (!feof($fp) && ($filesize - $count > 0)) {
            $data = fread($fp, $buffer);

            $count += strlen($data);//计数
            echo $data;//传数据给浏览器端
        }

        fclose($fp);

    }

    /**
     * 检测远程文件是否存在
     * @param string $url 远程文件链接
     * @return bool
     */
    public static function checkRemoteFileExists($url)
    {
        $curl = curl_init($url);
        //不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        //发送请求
        $result = curl_exec($curl);
        $found = false;
        if ($result !== false) {
            //检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    }

    /**
     * @param string $path 路径地址
     * @param array $option 额外参数
     * @return array
     */
    public static function fileReaddir($path, $option = [])
    {
        $option = array_merge([
            'md5' => false,
            'lasttime' => false,
            'only_dir' => false, //是否只返回文件夹
            'recursive' => true, //是否递归查找
            'filter_dir' => [], //过滤文件夹
        ], $option);

        $handle = opendir($path);
        $res = [];
        if (substr($path, -1) !== '/') {
            $path = $path . '/';
        }

        foreach ((array)$option['filter_dir'] as $dir) {
            if (StringHelper::exists($path, $dir)) {
                return [];
            }
        }

        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $real_file = $path . $file;

                    $relative_path = str_replace(SHOP_STAR_PATH . "/", '', $real_file);

                    if (is_dir($real_file)) {
                        if ($option['recursive']) {
                            $res = array_merge($res, self::fileReaddir($real_file . '/', $option));
                        }
                        if ($option['only_dir']) {
                            $res[] = $real_file;
                        }
                    } elseif ($option['only_dir'] === false) {

                        if ($option['lasttime'] !== false) {
                            $time = filemtime($real_file);
                            if ($time > $option['lasttime']) {
                                $res[$relative_path] = $option['md5'] ? md5_file($real_file) : $real_file;
                            }
                        } else {
                            $res[$relative_path] = $option['md5'] ? md5_file($real_file) : $real_file;
                        }
                    }
                }
            }
            closedir($handle);
            return $res;
        }
        return [];
    }

    /**
     * @param string $path 必须是以 /或者 /*结尾
     * @param array $options
     * @return array
     */
    public static function fileGlob(string $path, array $options = [])
    {
        $options = array_merge([
            'md5' => false,
            'lastTime' => false,
            'onlyDir' => false, //是否只返回文件夹
            'recursive' => true, //是否递归查找
            'relativePath' => true, //是否相对路径
            'basePath' => SHOP_STAR_PATH,
            'filterDir' => [], //过滤文件夹
        ], $options);

        $res = [];
        if (substr($path, -1) !== '*') {
            $path = $path . '*';
        }

        // 过滤文件夹
        foreach ((array)$options['filterDir'] as $dir) {
            if (StringHelper::exists($path, $dir)) {
                return [];
            }
        }

        foreach (glob($path) as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $file;
                if ($options['relativePath']) {
                    $filePath = str_replace($options['basePath'], '', $file);
                }
                if (is_dir($file)) {
                    if ($options['recursive']) {
                        $res = array_merge($res, self::fileGlob($file . '/*', $options));
                    }
                    if ($options['onlyDir']) {
                        $res[$filePath] = $file;
                    }
                } elseif ($options['onlyDir'] === false) {
                    if ($options['lastTime'] !== false) {
                        $time = filemtime($file);
                        if ($time > $options['lastTime']) {
                            $res[$filePath] = $options['md5'] ? md5_file($file) : $filePath;;
                        }
                    } else {
                        $res[$filePath] = $options['md5'] ? md5_file($file) : $filePath;;
                    }
                }
            }
        }

        if (!$options['md5']) {
            return array_values($res);
        }

        return $res;
    }

    /**
     * 检测文件完整性
     * @param string $path
     * @param string $md5
     * @return bool
     * @author likexin
     */
    public static function checkCompletion(string $path, string $md5)
    {
        if (empty($path) || empty($md5)) {
            return false;
        }
        return is_file($path) && md5_file($path) == $md5;
    }

}
