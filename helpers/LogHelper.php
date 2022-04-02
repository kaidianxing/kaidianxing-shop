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
 * @author 青岛开店星信息技术有限公司
 */
class LogHelper
{

    /**
     * @param $message
     * @param $data
     * @param string $file
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function info($message, $data, $file = '')
    {
        empty($file) && $file = SHOP_STAR_TMP_PATH . '/logs/info-' . date('Y-m-d') . '.log';

        FileHelper::createDirectory(dirname($file));

        file_put_contents($file,
            date('Y-m-d H:i:s') . ',' .
            'msg:' . $message . ',' .
            'data:' . json_encode($data) . PHP_EOL,
            FILE_APPEND);
    }

    /**
     * @param $message
     * @param $data
     * @param string $file
     * @return array|void
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function error($message, $data, $file = '')
    {
        empty($file) && $file = SHOP_STAR_TMP_PATH . '/logs/error-' . date('Y-m-d') . '.log';

        FileHelper::createDirectory(dirname($file));

        try {
            file_put_contents($file,
                date('Y-m-d H:i:s') . ',' .
                'msg:' . $message . ',' .
                'data:' . json_encode($data) . PHP_EOL,
                FILE_APPEND);
        } catch (\Exception $exception) {
            return error('文件权限异常');
        }
    }

}