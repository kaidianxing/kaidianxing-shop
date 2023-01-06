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

use install\models\CoreSettingsModel;
use yii\helpers\Json;

/**
 * kdx-cloud调用助手
 * Class KdxCloudHelper
 * @package shopstar\helpers
 * @author likexin
 */
class KdxCloudHelper
{

    /**
     * @var array 本地授权信息
     */
    private static array $localAuthInfo = [];

    /**
     * 获取本地授权信息
     * @return array
     * @author likexin
     */
    private static function getLocalAuthInfo(): array
    {
        // 类缓存为空时并且未安装的情况
        if (empty(self::$localAuthInfo) && SHOP_STAR_IS_INSTALLED) {
            self::$localAuthInfo = CoreSettingsModel::get('licence', [
                'site' => 0,
                'licence_code' => '',
            ]);
        }

        return self::$localAuthInfo;
    }

    /**
     * GET请求
     * @param string $route 路由
     * @param array $params 请求参数
     * @param array $options 附件选项
     * @return array|string
     */
    public static function get(string $route, array $params = [], array $options = [])
    {
        $options = ArrayHelper::merge($options, [
            'headers' => [
                'Client-Type' => 33,
            ],
        ]);

        try {
            // 拼接请求地址
            $url = self::getRequestUrl($route);

            // 生成请求参数
            $params = self::buildRequestParams($route, $params, true);

            // 执行请求
            $response = HttpHelper::getJson($url . $params, $options);
            if (is_error($response)) {
                unset($response['sign'], $response['nonce_str']);

                // 拼接说明
                if (isset($response['message']) && is_string($response['message'])) {
                    $response['message'] = '云服务返回错误(0): ' . $response['message'];
                }

                return $response;
            }

            // 检测返回签名
            return self::checkResponseSign($route, $response);

        } catch (\Throwable $exception) {
            return error('调用云服务失败, curl_get失败: ' . $exception->getMessage());
        }
    }

    /**
     * POST请求
     * @param string $route 路由
     * @param array $data 请求数据
     * @param array $options 附加选项
     * @return array
     */
    public static function post(string $route, array $data = [], array $options = []): array
    {
        $options = ArrayHelper::merge($options, [
            'headers' => [
                'Client-Type' => 33,
            ],
        ]);

        try {
            // 拼接请求地址
            $url = self::getRequestUrl($route, false);

            // 生成请求数据
            $data = self::buildRequestParams($route, $data);

            // 执行请求
            $response = HttpHelper::postJson($url, $data, $options);
            if (is_error($response)) {
                unset($response['sign'], $response['nonce_str']);

                // 拼接说明
                if (isset($response['message']) && is_string($response['message'])) {
                    $response['message'] = '云服务返回错误(1): ' . $response['message'];
                }

                return $response;
            }

            // 检测返回签名
            return self::checkResponseSign($route, $response);

        } catch (\Throwable $exception) {
            return error('调用云服务失败, curl_post失败: ' . $exception->getMessage());
        }
    }

    /**
     * 获取请求地址
     * @param string $route 路由
     * @param bool $joint 是否拼接连接符
     * @return string
     */
    private static function getRequestUrl(string $route, bool $joint = true): string
    {
        // 组成链接
        $url = 'https://cloud-2022.api.kaidianxing.com/' . ltrim($route, '/');

        // 拼接连接符
        if ($joint) {
            $url .= strpos($route, '?') ? '&' : '?';
        }

        return $url;
    }

    /**
     * 生成请求参数
     * @param string $route 云端路由
     * @param array $params 请求参数
     * @param bool $returnString 返回字符串
     * @return array|string
     */
    private static function buildRequestParams(string $route, array $params, bool $returnString = false)
    {
        // 追加客户端信息
        $params['client_ip'] = gethostbyname($_SERVER['HTTP_HOST']);
        $params['client_domain'] = $_SERVER['HTTP_HOST'];

        // 上报本地版本
        $params['local_version'] = SHOP_STAR_VERSION;
        $params['local_release'] = SHOP_STAR_RELEASE;

        // 追加站点ID、授权码
        $localAuthInfo = self::getLocalAuthInfo();
        $params['site_id'] = (int)$localAuthInfo['site_id'];
        $params['auth_code'] = (string)$localAuthInfo['licence_code'];

        return self::buildRequestSign($route, $params, $returnString);
    }

    /**
     * 生成请求签名
     * @param string $cloudRoute
     * @param array $params 请求参数
     * @param bool $returnString 返回字符串
     * @return array|string
     */
    private static function buildRequestSign(string $cloudRoute, array $params, bool $returnString = false)
    {
        // 新增请求时间戳
        $params['timestamp'] = time();

        // 新增随机字符串
        $params['nonce_str'] = StringHelper::random('32');

        // 排序并转为字符串
        $queryString = self::kSortAndToString($params);

        // 计算验证签名
        $params['sign'] = hash('sha256', self::getSignKey($cloudRoute, 'cloud2install.kaidianxing.cn') . md5('res' . $queryString . $params['site_id'] . base64_encode($params['nonce_str'] . $params['auth_code'])) . $params['timestamp'] . 'res' . $params['nonce_str']);

        return $returnString ? http_build_query($params) : $params;
    }

    /**
     * 检测返回值签名
     * @param string $cloudRoute 云端路由
     * @param array $response 返回数据
     * @return array
     * @throws \Exception
     */
    private static function checkResponseSign(string $cloudRoute, array $response): array
    {
        if (!isset($response['nonce_str'])) {
            throw new \Exception('请求返回参数错误 nonce_str为空');
        }
        if (!isset($response['sign']) || empty($response['sign'])) {
            throw new \Exception('请求返回参数错误 sign为空');
        }
        $responseSign = $response['sign'];
        unset($response['sign']);

        // 计算签名
        $sign = hash('sha256', self::getSignKey($cloudRoute, 'cloud-res.kaidianxing.com') . md5('resp' . self::kSortAndToString($response) . '91370203MA3TD4PG0P' . $response['nonce_str']) . $response['nonce_str'] . 'resp');
        if ($sign !== $responseSign) {
            throw new \Exception('请求返回签名验证错误');
        }
        unset($response['nonce_str']);

        // 拼接说明
        if (isset($response['message']) && is_string($response['message'])) {
            $response['message'] = '云服务返回错误(3): ' . $response['message'];
        }

        return $response;
    }

    /**
     * 排序并转为字符串
     * @param array $params 参数
     * @return false|string
     */
    private static function kSortAndToString(array $params)
    {
        ksort($params);

        $queryString = '';
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            } elseif (is_array($value) && empty($value)) {
                continue;
            } elseif (is_array($value)) {
                $value = Json::encode($value);
            }
            $queryString .= '&' . ($key . '=' . $value);
        }
        return substr($queryString, 1);
    }

    /**
     * 获取签名key
     * @param string $route
     * @param string $dom
     * @return string
     */
    private static function getSignKey(string $route, string $dom): string
    {
        return $route . '&' . $dom;
    }

}
