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

namespace shopstar\components\dispatch\driver;

use shopstar\components\dispatch\bases\BaseDispatchDriver;
use shopstar\components\dispatch\bases\DispatchDriverInterface;
use shopstar\helpers\ShopUrlHelper;
use yii\helpers\Json;

/**
 * 达达驱动类
 * Class DadaDriver
 * @package shopstar\components\dispatch\driver
 * @author 青岛开店星信息技术有限公司
 */
class DadaDriver extends BaseDispatchDriver implements DispatchDriverInterface
{

//    private $url = 'http://newopen.qa.imdada.cn';
    private $url = 'http://newopen.imdada.cn';
    private $APP_KEY = '';
    private $version = '1.0';
    private $APP_SECRET = '';
    private $API_ADDORDER = '/api/order/addOrder';
    private $API_FETCHORDER = '/api/order/fetch';
    private $API_CITY_LIST = "/api/cityCode/list";
    private $API_FINISHORDER = '/api/order/finish';
    private $API_CANCELORDER = '/api/order/cancel';
    private $API_EXPIREORDER = '/api/order/expire';
    private $API_FORMALCANCEL = '/api/order/formalCancel';
    private $API_CANCELREASONS = '/api/order/cancel/reasons';
    private $API_ACCEPTORDER = '/api/order/accept';
    private $API_ADDTIP = '/api/order/addTip';
    private $API_READDORDER = '/api/order/reAddOrder';

    private $API_QUERYDELIVERFEE = '/api/order/queryDeliverFee';
    private $API_ADDAFTERQUERY = '/api/order/addAfterQuery';
    private $API_ADDSHOP = '/api/shop/add';
    private $API_QUERYSTATUS = '/api/order/status/query';

    /**
     * @var string app_key
     */
    public $app_key;

    /**
     * @var string app_secret
     */
    public $app_secret;

    /**
     * @var string $shop_no 门店编码
     */
    public $shop_no;

    /**
     * @var string $source_id 商户ID
     */
    public $source_id;

    /**
     * @var string $city_code 城市编号
     */
    public $city_code;


    public function connect()
    {
        parent::connect(); // TODO: Change the autogenerated stub
    }

    /** 新增订单
     * @return bool
     */
    public function addOrder($data)
    {

        $data['shop_no'] = $this->shop_no;
        $data['city_code'] = $this->city_code;
        $data['callback'] = ShopUrlHelper::build('dispatch/notify.php', [], true);
        return self::getResult($this->API_ADDORDER, $data);
    }

    /**
     * 重新发布订单
     * 在调用新增订单后，订单被取消、过期或者投递异常的情况下，调用此接口，可以在达达平台重新发布订单。
     * @return bool
     */
    public function reAddOrder($data)
    {
        return self::getResult($this->API_READDORDER, $data);
    }

    /**
     * 订单详情查询
     * 查询订单的相关信息以及骑手的相关信息
     * @return bool
     */
    public function queryStatus($orderId)
    {
        $data['order_id'] = $orderId;
        return self::getResult($this->API_QUERYSTATUS, $data);
    }


    /** 获取城市信息
     * @return bool
     */
    public function cityCode()
    {
        return self::getResult($this->API_CITY_LIST);
    }

    /**
     * 查询订单运费接口
     * @return bool
     */
    public function queryDeliverFee($data)
    {
        return self::getResult($this->API_QUERYDELIVERFEE, $data);
    }

    /**
     * 查询运费后发单接口
     */
    public function addAfterQuery($data)
    {
        return self::getResult($this->API_ADDAFTERQUERY, $data);
    }

    /**
     *
     * @param $param
     * @param $time
     * @return string
     */
    private function sign($param, $time)
    {
        $tmpArr = array(
            "app_key" => $this->app_key,
            "body" => $param,
            "format" => "json",
            "source_id" => $this->source_id,
            "timestamp" => $time,
            "v" => $this->version,
        );
        if (empty($this->source_id)) {
            unset($tmpArr['source_id']);
        }
        $str = '';
        foreach ($tmpArr as $k => $v) {
            $str .= $k . $v;
        }
        $str = $this->app_secret . $str . $this->app_secret;
        $signature = md5($str);
        return strtoupper($signature);
    }

    private function getParam($data = '')
    {
        if (empty($data)) {
            $param = '';
        } else {
            $param = json_encode($data);
        }
        $time = time();
        $sign = self::sign($param, $time);
        $tmpArr = array(
            "app_key" => $this->app_key,
            "body" => $param,
            "format" => "json",
            "signature" => $sign,
            "source_id" => $this->source_id,
            "timestamp" => $time,
            "v" => $this->version,
        );
        if (empty($this->source_id)) {
            unset($tmpArr['source_id']);
        }
        return json_encode($tmpArr);
    }

    /** 根据参数获取结果信息
     * @param $api
     * @param string $data
     * @return bool
     */
    private function getResult($api, $data = '')
    {
        $param = self::getParam($data);
        $url = $this->url . $api;
        $response = self::http_post($url, $param);

        //$response = HttpHelper::post($url, $param);
        $response = Json::decode($response);

        if ($response['status'] == 'success' && $response['code'] == 0) {
            // success
            return $response['result'];
        } else {
            if ($response['code'] == 2103) {
                // cargo_weight必须大于0
                return error('商品重量必须大于0');
            }
            return error($response['msg']);
        }


    }

    /**
     * POST 请求
     * @param string $url
     * @param array|string $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $header = array(
            'Content-Type: application/json',
        );
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

}