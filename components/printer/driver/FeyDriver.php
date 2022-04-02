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

namespace shopstar\components\printer\driver;

use shopstar\components\printer\bases\BasePrinterDriver;
use shopstar\components\printer\bases\PrinterDriverInterface;
use shopstar\helpers\HttpHelper;
use yii\helpers\Json;

/**
 * 飞蛾云驱动类
 * Class FeyDriver
 * @package shopstar\components\printer\driver
 * @author 青岛开店星信息技术有限公司
 */
class FeyDriver extends BasePrinterDriver implements PrinterDriverInterface
{

    protected const URL = 'http://api.feieyun.cn/Api/Open/';

    /**
     * @var string $user
     */
    public $user;

    /**
     * @var string $ukey
     */
    public $ukey;

    /**
     * @var string $sign
     */
    public $sign;

    /**
     * @var string $sn
     */
    public $sn;

    /**
     * @var string $key
     */
    public $key;

    /**
     * @var string $current_time
     */
    public $current_time;

    public function connect()
    {
        // 获取时间戳
        $this->current_time = $this->getCurrentTime();

        // 获取签名
        $this->sign = $this->signature();
    }

    public function addPrinter()
    {
        return $this->send(
            self::URL,
            [
                'user' => $this->user,
                'stime' => $this->current_time,
                'sig' => $this->sign,
                'apiname' => 'Open_printerAddlist',
                'printerContent' => "$this->sn#$this->key"
            ]
        );

    }

    public function printIndex($content, $times = 1)
    {
        return $this->send(
            self::URL,
            [
                'user' => $this->user,
                'stime' => $this->current_time,
                'sig' => $this->sign,
                'apiname' => 'Open_printMsg',
                'sn' => $this->sn,
                'content' => $content,
                'times' => $times
            ]
        );
    }

    /**
     * 取消未打印任务
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public function cancelAll()
    {
        return $this->send(
            self::URL,
            [
                'user' => $this->user,
                'stime' => $this->current_time,
                'sig' => $this->sign,
                'apiname' => 'Open_delPrinterSqs',
                'sn' => $this->sn
            ]
        );
    }

    /**
     * 删除打印机
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public function deletePrinter()
    {
        return $this->send(
            self::URL,
            [
                'user' => $this->user,
                'stime' => $this->current_time,
                'sig' => $this->sign,
                'apiname' => 'Open_printerDelList',
                'snlist' => $this->sn
            ]
        );
    }

    /**
     * 发送请求
     * @author 青岛开店星信息技术有限公司
     */
    private function send($url, $data)
    {
        $response = HttpHelper::post($url, $data,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]
        );

        $response = Json::decode($response);
        if ($response['ret'] != 0 && $response['msg'] != 'ok') {
            if ($response['ret'] == -2) {
                /**
                 * msg => "参数错误 : 该帐号未注册."
                 * ret" => -2
                 */
                return error('USER不正确');
            }
            return error($response['msg']);
        }
        if (!empty($response['data']['no'])) {
            return error($response['data']['no'][0] ?? '');
        }

        return $response;
    }

    /**
     * [signature 生成签名]
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function signature(): string
    {
        return sha1($this->user . $this->ukey . $this->current_time);//公共参数，请求公钥
    }

    /**
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private function getCurrentTime(): int
    {
        return time();
    }
}