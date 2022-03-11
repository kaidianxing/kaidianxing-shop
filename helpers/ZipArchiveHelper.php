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

use \ZipArchive;

/**
 * 压缩
 * Class ZipArchiveHelper
 * @package shopstar\helpers
 * @author likexin
 */
class ZipArchiveHelper
{
    
    /**
     * 压缩文件
     * @param string $filePath
     * @param string $targetPath
     * @param bool $replaceTarget 替换目标文件
     * @return array|bool
     * @throws \yii\base\Exception
     * @author likexin
     */
    public static function zip(string $filePath, string $targetPath, bool $replaceTarget = true)
    {
        $fileList = FileHelper::fileGlob($filePath, [
            'relativePath' => true,
            'basePath' => $filePath,
        ]);
        if (count($fileList) < 1) {
            return error('未找到文件');
        }
        
        // 如果替换目标文件，先进行删除
        if ($replaceTarget && is_file($targetPath)) {
            FileHelper::unlink($targetPath);
        }
        
        // 是否包含.zip后缀
        if (!StringHelper::exists($targetPath, '.zip')) {
            $targetPath .= '.zip';
        }
        
        // 先创建目录
        FileHelper::createDirectory(dirname($targetPath));
        
        $zip = new ZipArchive();
        $zip->open($targetPath, ZipArchive::CREATE);
        
        foreach ($fileList as $file) {
            $zip->addFile($filePath . $file, ltrim($file, "/"));
        }
        
        $zip->close();
        
        // 压缩完验证文件是否存在
        if (!file_exists($targetPath)) {
            return error('压缩失败');
        }
        
        return true;
    }
    
    /**
     * 打包并下载
     * @param string $filePath
     * @param string $targetPath
     * @param string $filename
     * @param bool $isDelete
     * @return array|bool
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function zipAndDownload(string $filePath, string $targetPath, string $filename, bool $isDelete = true)
    {
        self::zip($filePath, $targetPath);
        
        @header('Content-Encoding: none');
        @header('Content-Type: application/zip');
        @header('Content-Disposition: attachment ; filename=' . $filename . '.zip');
        @header('Pragma: no-cache');
        header('Content-Length: '. filesize($targetPath));
        @header('Expires: 0');
        readfile($targetPath);
        
        // 下载完删除打包目录 及 压缩包
        if ($isDelete) {
            FileHelper::removeDirectory($filePath);
            FileHelper::unlink($targetPath);
        }
        die;
        
    }
    
    
    /**
     * 解压文件
     * @param string $filePath
     * @param string $targetPath
     * @param string $password
     * @return bool|array
     * @throws \yii\base\Exception
     * @author likexin
     */
    public static function unzip(string $filePath, string $targetPath = '', string $password = '')
    {
        if (!file_exists($filePath)) {
            return error('文件不存在');
        }
        
        $zip = new ZipArchive();
        if (!empty($password)) {
            $zip->setPassword($password);
        }
        
        $re = $zip->open($filePath);
        if ($re !== true) {
            return error('解压失败');
        }
        
        // 创建目录
        FileHelper::createDirectory(dirname($targetPath));
        
        // 解压到指定目录
        $res = $zip->extractTo($targetPath);
        $zip->close();
        
        return $res;
    }
    
}
