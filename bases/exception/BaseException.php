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


namespace shopstar\bases\exception;

use shopstar\bases\traits\ConstantTrait;
use yii\base\Exception;

/**
 * 异常基类
 * Class BaseException
 * @method getMessages($code) static string 获取错误信息
 * @method getMessageWithCode($code) static string 获取错误信息携带code
 * @package shopstar\bases\exception
 * @author 青岛开店星信息技术有限公司
 */
class BaseException extends Exception
{

    // 引用相特性
    use ConstantTrait;

    /**
     * BaseException constructor.
     * @param $code
     * @param string|null $message
     * @param \Throwable|null $previous
     */
    public function __construct($code, string $message = null, \Throwable $previous = null)
    {
        if (static::class !== __CLASS__ && is_int($code)) {

            if (is_null($message)) {
                $message = static::getMessages($code);
            }

            if (!empty(static::getMessageWithCode($code))) {
                $message .= "({$code})";
            }

        }

        parent::__construct($message, $code, $previous);
    }

    public static function getConstList()
    {
        $objClass = new \ReflectionClass(static::class);
        $arrConst = $objClass->getConstants();
        $codeArr = [];
        foreach ($arrConst as $key => $code) {
            $message = static::getMessages($code);
            $codeArr[$code] = [
                'code' => $code,
                'message' => $message,
                'file' => static::class,
            ];

        }

        return $codeArr;
    }

}