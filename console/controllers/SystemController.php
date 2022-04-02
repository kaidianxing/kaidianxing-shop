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


namespace shopstar\console\controllers;

use shopstar\helpers\CacheHelper;
use shopstar\helpers\LogHelper;
use yii\console\Controller;
use Yii;
use yii\helpers\FileHelper;

/**
 * 重置操作密码
 * Class ResetPasswordController
 * @package modules\console\controllers
 * @author likexin
 */
class SystemController extends Controller
{

    /**
     * 命令行刷新缓存
     * @throws \yii\db\Exception
     */
    public function actionFlushCache()
    {
        CacheHelper::flush();

        echo "flush cache success \n";
    }
    public function exceptionFile()
    {
        $baseDir = Yii::getAlias('@shopstar');
        $exceptionDir = $baseDir .'/exceptions/';
        $files = FileHelper::findFiles($exceptionDir);
        $arr = [];
        $endStr = 'Exception';
        foreach ($files as $file) {
            $file = str_replace($baseDir,'', $file);
            $file = str_replace('/','\\', $file);
            $file = str_replace('.php','', $file);
            $file = '\shopstar' . $file;
            if (substr( $file, -strlen( $endStr ) ) == $endStr) {
                $arr[] = $file;
            }
        }

        return $arr;
    }


    public function actionException()
    {
        $exceptionClasss = $this->exceptionFile();
        foreach ($exceptionClasss as $exceptionClass) {

            if ($exceptionClass) {
                $exArr[] = call_user_func_array([$exceptionClass, 'getConstList'], []);
            }
        }

        $resultArr = [];
        $repeatArr = [];
        foreach ($exArr as $arr) {
            foreach ($arr as $code => $info) {
                if (!isset($resultArr[$code])) {
                    $resultArr[$code] = $info;
                } else {
                    if (!isset($repeatArr[$code])) {
                        $repeatArr[$code][] = $resultArr[$code];
                    }
                    $repeatArr[$code][] = $info;
                }
            }
        }
        // 打印输出重复
        //var_dump($repeatArr);
        $this->printException($resultArr);
    }

    public function printException($resultArr)
    {
        $str = "\n\n";
        $str .= "| 状态码 | 描述 | 文件地址 | \n";
        $str .= "| :-----| :---- | :---- | \n";
        sort($resultArr);
        foreach ($resultArr as $code => $info) {
            $str .= "| " . $info['code'] . " | " . $info['message'] . " | " . $info['file'] . " | \n";
        }

        LogHelper::info($str, []);
    }


}