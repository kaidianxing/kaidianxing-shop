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

use shopstar\bases\exception\BaseException;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\helpers\Json;

/**
 * HTTP网络请求辅助类
 * Class HttpHelper
 * @package shopstar\helpers
 */
class HttpHelper
{
    /**
     * @var $headers array 所有header信息
     */
    protected static $headers;

    /**
     * @var $guzzleClient \GuzzleHttp\Client
     */
    protected static $guzzleClient;

    public static function getHeaders()
    {
        return self::$headers;
    }

    /**
     * 获取GuzzleClient
     * @return object
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getGuzzleClient()
    {
        if (is_null(self::$guzzleClient)) {
            $httpProxy = getenv('http_proxy') ?? getenv('HTTP_PROXY');
            if (empty($httpProxy)) {
                $httpProxy = null;
            }
            $httpsProxy = getenv('https_proxy') ?? getenv('HTTPS_PROXY');
            if (empty($httpsProxy)) {
                $httpsProxy = null;
            }
            self::$guzzleClient = Yii::createObject('GuzzleHttp\Client', [
                [
                    'timeout' => 10,
                    'connect_timeout' => 10,
                    'proxy' => [
                        'http' => $httpProxy,
                        'https' => $httpsProxy,
                        'no' => [],
                    ],
                ]
            ]);
        }

        return self::$guzzleClient;
    }

    /**
     * GET|POST请求
     * @param $method
     * @param string $uri
     * @param array $options
     * @return array|string|void
     * @throws null
     * @author 青岛开店星信息技术有限公司
     */
    public static function request($method, $uri = '', array $options = [])
    {
        try {
            $response = self::getGuzzleClient()->request($method, $uri, $options);
            self::$headers = $response->getHeaders();
            if ($response->getStatusCode() === 200) {
                return (string)$response->getBody();
            }
            throw new BaseException($response->getStatusCode(), $response->getReasonPhrase());
        } catch (RequestException $exception) {
            throw new BaseException($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @param $uri
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public static function get($uri, $options = [])
    {
        return self::request("GET", $uri, $options);
    }

    /**
     * GET请求, 返回JSON格式
     * @param $uri
     * @param array $options
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getJson($uri, $options = [])
    {
        $resp = self::get($uri, $options);
        return (array)Json::decode($resp);
    }

    /**
     * GET请求, 返回XML格式
     * @param $uri
     * @param array $options
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public static function getXml($uri, $options = [])
    {
        $resp = self::get($uri, $options);
        if (is_error($resp)) {
            return $resp;
        }
        if (!stripos($resp, '<xml')) {
            return false;
        }
        return ArrayHelper::fromXML($resp);
    }

    /**
     * POST请求
     * @param $uri
     * @param string|array $data
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public static function post($uri, $data = '', array $options = [])
    {
        if (!empty($data)) {
            if (is_string($data)) {
                $options = ArrayHelper::merge($options, [
                    'body' => $data
                ]);
            } else if (is_array($data)) {
                $options = ArrayHelper::merge($options, [
                    'form_params' => $data
                ]);
            }

        }

        return self::request("POST", $uri, $options);
    }

    /**
     * POST请求, 返回JSON格式
     * @param $uri
     * @param string|array $data
     * @param array $options
     * @return array
     */
    static function postJson($uri, $data = '', array $options = [])
    {
        $resp = self::post($uri, $data, $options);
        return (array)Json::decode($resp);
    }

    /**
     * POST请求, 返回XML格式
     * @param $uri
     * @param string|array $data
     * @param array $options
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|\SimpleXMLElement
     */
    public static function postXml($uri, $data = '', array $options = [])
    {
        $resp = self::post($uri, $data, $options);
        if (stripos($resp, '<xml') === false) {
            return false;
        }
        return ArrayHelper::fromXML($resp);
    }


    /**
     * 上传文件或者多表单
     * @param $uri
     * @param string $data = ['name'=>'上传表单名','contents'=>'上传内容']
     * 选填内容 : headers'  => ['X-Baz' => 'bar'] , 'filename' => 'custom_filename.txt'
     * @param array $options
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public static function upload($uri, array $data = [], $options = [])
    {

        if (isset($data[0])) {
            foreach ($data as $key => $val) {
                $options['multipart'][$key] = [
                    'name' => $val['name'],
                    'contents' => $val['contents'],
                ];
                if (isset($val['headers'])) {
                    $options['multipart'][$key]['headers'] = $val['headers'];
                }
                if (isset($val['filename'])) {
                    $options['multipart'][$key]['filename'] = $val['filename'];
                }

            }
        } else {
            $options['multipart'] = [
                [
                    'name' => $data['name'],
                    'contents' => is_file($data['contents']) ? fopen($data['contents'], 'r') : $data['contents']
                ]
            ];
        }
        $resp = self::post($uri, '', $options);
        if (stripos($resp, '<xml') === false) {
            return false;
        }
        return ArrayHelper::fromXML($resp);
    }

}
