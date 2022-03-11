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

/**
 * 适用于非CLI模式打印
 * @param mixed ...$args
 * @author likexin
 */
if (!function_exists('dd')) {
    function dd(...$args)
    {
        call_user_func_array('dump', $args);
        die();
    }
}

/**
 * 适用于普通模式打印
 * @param mixed ...$args
 * @author likexin
 */
function d(...$args)
{
    echo '<pre style="color: red">';
    call_user_func_array('var_dump', $args);
    echo '</pre>';
}

/**
 * 生成成功信息
 * @param mixed $message
 * @return array|void
 * @author likexin
 */
function success($message = '')
{
    return \shopstar\helpers\ResponseHelper::success($message);
}

/**
 * 生成错误信息
 * @param mixed $message 错误信息
 * @param int $error 错误码
 * @return array
 * @author likexin
 */
function error($message, int $error = -1): array
{
    return \shopstar\helpers\ResponseHelper::error($message, $error);
}

/**
 * 判断是否错误
 * @param mixed $value
 * @return bool
 * @author likexin
 */
function is_error($value): bool
{
    return \shopstar\helpers\ResponseHelper::isError($value);
}

/**
 * 价格格式化
 * @param float $price
 * @return mixed|string
 * @author likexin
 */
function price_format(float $price)
{
    $prices = explode('.', $price);
    if (intval($prices[1]) <= 0) {
        $price = $prices[0];
    } else {
        if (isset($prices[1][1]) && $prices[1][1] <= 0) {
            $price = $prices[0] . '.' . $prices[1][0];
        }
    }
    return $price;
}

/**
 * 保留小数点后两位小数
 * @param $val int 值
 * @param int $precision 精度
 * @return float
 * @author likexin
 */
function round2($val, $precision = 2)
{
    switch ($precision) {
        case 1:
            return (float)sprintf("%.1f", substr(sprintf("%.4f", $val), 0, -3));
            break;
        case 2:
            return (float)sprintf("%.2f", substr(sprintf("%.4f", $val), 0, -2));
            break;
        case 3:
            return (float)sprintf("%.3f", substr(sprintf("%.4f", $val), 0, -1));
            break;
        default:
            return round($val, $precision);
            break;
    }
}
