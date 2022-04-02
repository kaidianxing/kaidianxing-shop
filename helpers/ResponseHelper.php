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
 * 响应助手类
 * Class Response
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class ResponseHelper
{

    /**
     * 生成错误信息
     * @param mixed $message 信息
     * @param int $error 错误码
     * @param bool $exit 是否转为json并exit
     * @return array
     * @author likexin
     */
    public static function error($message, int $error = -1, bool $exit = false): array
    {
        $result = [
            'error' => $error
        ];
        if (!empty($message)) {
            if (\yii\helpers\ArrayHelper::isTraversable($message)) {
                $result = \yii\helpers\ArrayHelper::merge($result, $message);
            } else if (!empty($message)) {
                $result['message'] = $message;
            }
        }

        if ($exit) {
            self::result($error, $result);
        }

        return $result;
    }

    /**
     * 生成成功信息
     * @param string $message 成功消息
     * @return array|void
     * @author likexin
     */
    public static function success($message = '')
    {
        return self::error($message, 0);
    }

    /**
     * 判断是否错误
     * @param string|array $value
     * @return bool
     * @author likexin
     */
    public static function isError($value)
    {
        return is_array($value) && (isset($value['error']) && !empty($value['error']));
    }

    /**
     * 返回数据
     * @param int $error
     * @param null $return
     * @param int $options
     * @author likexin
     */
    public static function result($error = 0, $return = null, int $options = JSON_UNESCAPED_UNICODE)
    {
        header('Content-type: application/json; charset=utf-8');

        $ret = array(
            'error' => $error
        );

        if (is_string($return)) {
            $ret['message'] = $return;
        } else if (is_array($return)) {
            unset($return['error']);
            $ret = array_merge($ret, $return);
        }

        exit(json_encode($ret, $options));
    }

}