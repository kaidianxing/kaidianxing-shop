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

use ReflectionClassConstant;

/**
 * 常量助手
 * Class ConstantHelper
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class ConstantHelper
{

    /**
     * 手机指定类中的注解常量
     * @param string|object $className
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function collectClass($className): array
    {
        try {
            if ($className instanceof \ReflectionClass) {
                $ref = $className;
            } else {
                $ref = new \ReflectionClass($className);
            }
            $classConstants = $ref->getReflectionConstants();
            return self::getAnnotations($classConstants);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    /**
     * 获取注解
     * @param array $classConstants
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected static function getAnnotations(array $classConstants): array
    {
        $result = [];
        /** @var ReflectionClassConstant $classConstant */
        foreach ($classConstants as $classConstant) {
            $code = $classConstant->getValue();
            $docComment = $classConstant->getDocComment();
            if ($docComment) {
                $result[$code] = self::parse($docComment);
            }
        }

        return $result;
    }

    /**
     * 解析注解
     * @param string $doc
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected static function parse(string $doc): array
    {
        $pattern = '/\\@(\\w+)\\(\\"(.+)\\"\\)/U';
        if (preg_match_all($pattern, $doc, $result)) {
            if (isset($result[1], $result[2])) {
                $keys = $result[1];
                $values = $result[2];

                $result = [];
                foreach ($keys as $i => $key) {
                    if (isset($values[$i])) {
                        $result[strtolower($key)] = $values[$i];
                    }
                }
                return $result;
            }
        }

        return [];
    }

}