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
use yii\helpers\Json;

/**
 * 闪送驱动类
 * Class SfDriver
 * @package shopstar\components\dispatch\driver
 * @author 青岛开店星信息技术有限公司
 */
class ShansongDriver extends BaseDispatchDriver implements DispatchDriverInterface
{
    //测试环境地址
    //public $url = 'http://open.s.bingex.com';
    public $url = 'http://open.ishansong.com';

    public $client_id;

    public $app_secret;


    /**
     * 订单计费
     * @param $data
     * @return array|mixed|null
     */
    public function addOrder($data)
    {
        // TODO: Implement addOrder() method.
        return self::getResult($this->orderCalculate, $data);
    }

    /**
     * 订单提交
     * @param $data
     * @return array|mixed|null
     */
    public function orderPlace($data)
    {

        return self::getResult($this->orderPlace, $data);
    }

    /**
     * 查询闪送员位置
     * @param $orderId
     * @return mixed|void
     */
    public function queryStatus($orderId)
    {
        // TODO: Implement queryStatus() method.
        return self::getResult($this->queryStatus, $orderId);
    }

    private function getResult($url, $data)
    {
        $params = Json::encode($data);


        $sign = self::getSign($params);

        $postData = [
            'clientId' => $this->client_id,
            'sign' => $sign,
            'timestamp' => time(),
            'data' => $params
        ];

        $url = $this->url . $url;

        $response = self::httpPost($url, $postData);

        $response = Json::decode($response);

        if ($response['status'] == 200) {
            return $response;
        } else {
            return error($response['msg']);
        }

    }

    private function getSign($data)
    {
        $signData = [
            'clientId' => $this->client_id,
            'timestamp' => time(),
            'data' => $data
        ];

        $signPars = "";

        ksort($signData);

        foreach ($signData as $k => $v) {
            if ("" != $v && "sign" != $k) {
                $signPars .= $k . $v;
            }
        }
        $signPars = strtoupper(md5($this->app_secret . $signPars));
        return $signPars;
    }

    private function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

}