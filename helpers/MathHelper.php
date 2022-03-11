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


class MathHelper
{
    /**
     * @var float 圆周率
     */
    protected const PI = 3.14159265358979324;

    /**
     * 判断坐标是否在圆内
     * @param array $point ['lng'=>'','lat'=>''] 指定点的坐标
     * @param array $circle array ['center'=>['lng'=>'','lat'=>''],'radius'=>'']  中心点和半径
     * @return bool false/true
     * @author 青岛开店星信息技术有限公司
     */
    public static function inTheCircle(array $point, array $circle)
    {
        if (empty($circle['center']['lng']) || empty($circle['center']['lat']) || empty($point['lat']) || empty($point['lng'])) {
            return false;
        }

        // 获取两点之间的距离
        $distance = static::distance($point['lat'], $point['lng'], $circle['center']['lat'], $circle['center']['lng']);

        // 和半径作比较，大于半径在圆外 否则在圆内
        return $distance <= $circle['radius'];
    }

    /**
     *  计算两个点之间的距离
     * @param float $centerLat 圆心点的纬度
     * @param float $centerLon 圆心点的经度
     * @param float $destinationLat 目的地点的纬度
     * @param float $destinationLon 目的地点的经度
     * @return float $distance 两点之间的距离
     * @author 青岛开店星信息技术有限公司
     */
    public static function distance($centerLat, $centerLon, $destinationLat, $destinationLon)
    {
        //地球半径
        $earthR = 6371000;

        $x = cos($centerLat * static::PI / 180) * cos($destinationLat * static::PI / 180) * cos(($centerLon - $destinationLon) * static::PI / 180);
        $y = sin($centerLat * static::PI / 180) * sin($destinationLat * static::PI / 180);
        $s = $x + $y;

        if ($s > 1) $s = 1;
        if ($s < -1) $s = -1;
        $alpha = acos($s);
        $distance = $alpha * $earthR;

        return $distance;
    }


    /**
     * 根据两点经纬度获取距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radLat1 = deg2rad(floatval($lat1));//deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad(floatval($lat2));
        $radLng1 = deg2rad(floatval($lng1));
        $radLng2 = deg2rad(floatval($lng2));
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6371;
        return round($s, 1);
    }

    /**
     * 判断一个坐标是否在一个多边形内（由多个坐标围成的）
     * @param array $point 指定点坐标
     * @param array $coordinateGroup 多边形坐标 顺时针方向
     * @return bool false/true
     * @author 青岛开店星信息技术有限公司
     */
    public static function InThePolygon(array $point, array $coordinateGroup)
    {
        //没有传入指定点坐标
        if (empty($point)) {
            return false;
        }
        //没有传入区域坐标
        if (empty($coordinateGroup)) {
            return false;
        }

        //统计多边形坐标数量
        $count = count($coordinateGroup);
        //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
        $boundOrVertex = true;
        //交叉点数
        $intersectCount = 0;
        //浮点类型计算时候与0比较时候的容差
        $precision = 2e-10;

        //顶点1赋值
        $vertex_1 = $coordinateGroup[0];
        //检查所有的线
        for ($i = 1; $i <= $count; ++$i) {
            if ($point['lng'] == $vertex_1['lng'] && $point['lat'] == $vertex_1['lat']) {
                //指定点在顶点上
                //指定点在顶点上
                return $boundOrVertex;
            }
            //顶点2赋值
            $vertex_2 = $coordinateGroup[$i % $count];
            //点不在第一条线上
            if ($point['lat'] < min($vertex_1['lat'], $vertex_2['lat']) || $point['lat'] > max($vertex_1['lat'], $vertex_2['lat'])) {
                //第二个顶点成为第一个顶点
                $vertex_1 = $vertex_2;
                continue;
            }

            //在两个顶点纬度之间
            if ($point['lat'] > min($vertex_1['lat'], $vertex_2['lat']) && $point['lat'] < max($vertex_1['lat'], $vertex_2['lat'])) {
                if ($point['lng'] <= max($vertex_1['lng'], $vertex_2['lng'])) {
                    //指定点在水平的线上
                    if ($vertex_1['lat'] == $vertex_2['lat'] && $point['lng'] >= min($vertex_1['lng'], $vertex_2['lng'])) {
                        return $boundOrVertex;
                    }

                    //垂直射线
                    if ($vertex_1['lng'] == $vertex_2['lng']) {
                        //指定点在垂直的线上
                        if ($vertex_1['lng'] == $point['lng']) {
                            return $boundOrVertex;
                        } else {
                            ++$intersectCount;
                        }
                    } else {
                        //经度的交点
                        $xinters = ($point['lat'] - $vertex_1['lat']) * ($vertex_2['lng'] - $vertex_1['lng']) / ($vertex_2['lat'] - $vertex_1['lat']) + $vertex_1['lng'];//cross point of lng
                        //指定点在线上
                        if (abs($point['lng'] - $xinters) < $precision) {
                            return $boundOrVertex;
                        }
                        //指定点在线前面
                        if ($point['lng'] < $xinters) {
                            ++$intersectCount;
                        }
                    }
                }
            } else {
                //指定点在除了顶点2以外的区域
                if ($point['lat'] == $vertex_2['lat'] && $point['lng'] <= $vertex_2['lng']) {
                    //下一个顶点
                    $vertex_3 = $coordinateGroup[($i + 1) % $count];
                    if ($point['lat'] >= min($vertex_1['lat'], $vertex_3['lat']) && $point['lat'] <= max($vertex_1['lat'], $vertex_3['lat'])) { //p.lat lies between p1.lat & p3.lat
                        ++$intersectCount;
                    } else {
                        $intersectCount += 2;
                    }
                }
            }
            //第二个顶点成为第一个顶点
            $vertex_1 = $vertex_2;
        }

        //计算指定地点在的区域
        if ($intersectCount % 2 == 0) {//结果为偶数时在多边形外
            return false;
        } else { //结果为奇数时在多边形内
            return true;
        }
    }
}