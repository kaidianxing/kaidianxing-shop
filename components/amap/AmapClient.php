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

namespace shopstar\components\amap;

use shopstar\helpers\HttpHelper;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 高德地图
 * Class AmapClient
 * @package shopstar\components\amap
 * @author 青岛开店星信息技术有限公司
 */
class AmapClient
{
    /**
     * @var float 圆周率
     */
    protected const PI = 3.14159265358979324;

    protected const API_GEOCODE_GEO = 'http://restapi.amap.com/v3/geocode/geo';

    // 获取距离
    protected const API_DISTANCE = 'http://restapi.amap.com/v3/distance';

    // 行政区
    protected const API_CONFIG_DISTANCE = 'http://restapi.amap.com/v3/config/district';

    // 骑行规划路径
    protected const API_DIRECTION_BICYCLING = 'https://restapi.amap.com/v4/direction/bicycling';

    //根据经纬度获取详细地址
    protected const API_GET_ADDRESS = 'https://restapi.amap.com/v3/geocode/regeo';

    /**
     * 获取接口Key
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    protected static function getApiKey(): string
    {

        return ShopSettings::get('dispatch.intracity.amap_key', '');
    }

    /**
     * 获取高德地图经纬度信息
     * @param string $address 详细地址
     * @return array $result 位置信息
     * @author likexin
     */
    public static function getLocation(string $address): array
    {
        //过滤地址信息的空格
        $address = str_replace(' ', '', $address);

        // 请求数据
        $response = HttpHelper::get(self::API_GEOCODE_GEO . '?' . http_build_query([
                'address' => $address,
                'key' => self::getApiKey()
            ]));

        $result = Json::decode($response);
        if ($result['status'] == 0) {
            return error('地图API错误: ' . $result['info']);
        }

        return $result;
    }

    /**
     * 获取坐标点
     * @param string $address
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getLocationPoint(string $address): array
    {
        $locationArray = self::getLocation($address);
        if (is_error($locationArray)) {
            return $locationArray;
        }

        $location = explode(',', $locationArray['geocodes'][0]['location']);

        return [
            'lng' => $location[0],
            'lat' => $location[1],
        ];
    }

    /**
     * 获取两点间直线距离
     * @param string $startLocation 出发点坐标经纬度
     * @param string $destinationLocation 目的地坐标经纬度
     * @return array|mixed|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActualDistance(string $startLocation, string $destinationLocation)
    {
        // 请求数据
        $response = HttpHelper::get(self::API_DISTANCE . '?' . http_build_query([
                'origins' => $startLocation,
                'destination' => $destinationLocation,
                'key' => self::getApiKey()
            ]));

        //response distance(米) duration(秒)

        $result = Json::decode($response);
        if ($result['status'] == 0) {
            return error('地图API错误: ' . $result['info']);
        }

        return $result;
    }

    /**
     * 返回行政区
     * @param string $keywords 关键词 行政区名称、citycode、adcode
     * @param int $subdistrict 设置显示下级行政区级数 0，1，2，3
     * @param string $extensions 可选值：base、all
     * @param string $apiKey api key
     */
    public static function getConfigDistance(string $keywords, string $apiKey = '', int $subdistrict = 1, $extensions = 'base')
    {
        // 请求数据
        $response = HttpHelper::get(self::API_CONFIG_DISTANCE . '?' . http_build_query([
                'keywords' => $keywords,
                'subdistrict' => $subdistrict,
                'extensions' => $extensions,
                'key' => !empty($apiKey) ? $apiKey : self::getApiKey()
            ]));

        $result = Json::decode($response);
        if ($result['status'] == 0) {
            return error('获取行政区错误: ' . $result['info']);
        }

        return $result;

    }

    /**
     * 骑行路径规划
     * @param string $origin
     * @param string $destination
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDirectionBicycling(string $origin, string $destination)
    {
        // 请求数据
        $response = HttpHelper::get(self::API_DIRECTION_BICYCLING . '?' . http_build_query([
                'origin' => $origin,
                'destination' => $destination,
                'key' => self::getApiKey()
            ]));

        $result = Json::decode($response);

        if ($result['errcode'] != 0 || $result['errmsg'] != 'OK') {
            return error('获取骑行路径规划错误: ' . $result['errdetail']);
        }

        return $result['data']['paths'][0]['duration'];
    }

    /**
     * @param string $startLocation
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAddressByLngAndLat(string $startLocation)
    {
        $response = HttpHelper::get(self::API_GET_ADDRESS . '?' . http_build_query([
                'key' => self::getApiKey(),
                'location' => $startLocation,
            ]));

        $result = Json::decode($response);

        if ($result['error'] != 0 || $result['info'] != 'OK') {
            return error('获取用户地址错误');
        }

        return $result['regeocode'];
    }

}