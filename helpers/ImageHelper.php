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

use yii\web\UploadedFile;

/**
 * 图片助手
 * Class ImageHelper
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class ImageHelper
{

    /**
     * 压缩图片
     * @param UploadedFile|null $file
     * @param array $params
     * @return UploadedFile|null
     * @author likexin
     */
    public static function compress(UploadedFile $file = null, array $params = []): ?UploadedFile
    {
        $params = array_merge([
            'fixed' => false //是否固定高宽
        ], $params);

        if (!is_object($file)) {
            return $file;
        }

        if (!is_file($file->tempName)) {
            return $file;
        }

        list($width, $height) = getimagesize($file->tempName);

        $oldWidth = $width;
        $oldHeight = $height;

        //处理图片缩放 by lgt
        if ($params['width'] > 0 && !$params['fixed']) {
            $newWidth = $params['width'];
            //原尺寸超过设定值开始处理压缩尺寸
            if ($width > $newWidth) {
                if ($height > 0) {
                    $rate = round2($width / $height, 2);
                    if ($rate > 0) {
                        $height = round2($newWidth / $rate, 2);
                        $width = $newWidth;
                    }
                }
            }
        }

        $img_string = file_get_contents($file->tempName);
        $src_im = imagecreatefromstring($img_string);

        //是否固定高宽
        if (!$params['fixed']) {

            $dst_im = imagecreatetruecolor($width, $height);
            imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $width, $height, $width, $height);
        } else {

            $dst_im = imagecreatetruecolor($params['width'], $params['height']);
            imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $params['width'], $params['height'], $oldWidth, $oldHeight);
        }

        if ($file->type == 'image/jpg' || $file->type == 'image/jpeg') {
            imagejpeg($dst_im, $file->tempName);
        } elseif ($file->type == 'image/png') {
            imagepng($dst_im, $file->tempName);
        }

        return new UploadedFile([
            'tempName' => $file->tempName,
            'name' => $file->name,
            'type' => $file->type,
            'size' => filesize($file->tempName),
        ]);
    }


    /**
     * 通过base64创建
     * @param string $base64
     * @param string $path
     * @return bool
     * @throws \yii\base\Exception
     * @author likexin
     */
    public static function createFromBase64(string $base64, string $path)
    {
        if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            return false;
        }

        // 创建目录
        FileHelper::createDirectory(dirname($path));

        if (!file_put_contents($path, base64_decode(str_replace($result[1], '', $base64)))) {
            return false;
        }

        return true;
    }

    /**
     * 保存网络图片
     * @param string $url
     * @param string $path
     * @return bool|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getImg(string $url, string $path = '')
    {

        if (empty($path)) {
            $path = SHOP_STAR_PATH . '/data/image/' . date('Y') . '/' . date('m');
        }
        if (!is_dir($path)) {
            FileHelper::createDirectory($path);
        }

        $state = @file_get_contents($url, 0, null, 0, 1);//获取网络资源的字符内容

        if ($state) {

            $filename = $path . '/' . md5($url . time()) . '.jpg';//文件名称与路径

            ob_start();//打开输出

            readfile($url);//输出图片文件

            $img = ob_get_contents();//得到浏览器输出

            ob_end_clean();//清除输出并关闭

            $size = strlen($img);//得到图片大小

            $fp2 = @fopen($filename, "a");

            fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名

            fclose($fp2);

            return $filename;

        } else {

            return false;

        }

    }

    /**
     * 下载图片
     * @author 青岛开店星信息技术有限公司
     * @return void
     */
    public static function downloadImage($filename,$newname = '核销员邀请码')
    {
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $newname .'.jpg');
        readfile($filename);
        die;
    }

}