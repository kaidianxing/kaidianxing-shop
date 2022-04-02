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

use Matrix\Exception;
use shopstar\components\printer\bases\BasePrinterDriver;
use shopstar\components\printer\bases\PrinterDriverInterface;
use shopstar\helpers\HttpHelper;
use shopstar\helpers\StringHelper;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;

/**
 * 易联云驱动类
 * Class YlyDriver
 * @author 青岛开店星信息技术有限公司
 */
class YlyDriver extends BasePrinterDriver implements PrinterDriverInterface
{
    /**
     * 获取Access Token
     */
    protected const OAUTH = 'https://open-api.10ss.net/oauth/oauth';

    protected const ADD_PRINTER = 'https://open-api.10ss.net/printer/addprinter';

    protected const DELETE_PRINTER = 'https://open-api.10ss.net/printer/deleteprinter';

    protected const PRINTER_INDEX = 'https://open-api.10ss.net/print/index';

    protected const SET_ICON = 'https://open-api.10ss.net/printer/seticon';

    protected const DELETE_ICON = 'https://open-api.10ss.net/printer/deleteicon';

    protected const PRINTER_INFO = 'https://open-api.10ss.net/printer/printinfo';

    protected const CANCEL_ALL = 'https://open-api.10ss.net/printer/cancelall';

    /**
     * @var string access_token
     */
    public $access_token;

    /**
     * @var string sign
     */
    public $sign;

    /**
     * @var string 用户ID
     */
    public $client_id;

    /**
     * @var string 应用秘钥
     */
    public $client_secret;

    /**
     * @var string 终端号
     */
    public $machine_code;

    /**
     * @var string 打印机密钥
     */
    public $msign;

    /**
     * @var string 订单号
     */
    public $origin_id;

    /**
     * @var string 时间戳
     */
    public $current_time;

    public function connect()
    {
        /**
         * 添加授权 自有应用授权模式
         */

        // 获取时间戳
        $this->current_time = $this->getCurrentTime();

        // 获取access_token
        $this->access_token = $this->getAccessToken();

        // 获取签名
        $this->sign = $this->getSign();
    }

    public function addPrinter()
    {
        return $this->send(
            self::ADD_PRINTER,
            [
                'client_id' => $this->client_id,
                'machine_code' => $this->machine_code,
                'msign' => $this->msign,
                'access_token' => $this->access_token,
                'sign' => $this->sign,
                'id' => StringHelper::guid(),
                'timestamp' => $this->current_time,
            ]
        );
    }

    public function printIndex($content, $times = 1)
    {
        return $this->send(
            self::PRINTER_INDEX,
            [
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
                'machine_code' => $this->machine_code,
                'origin_id' => time(),
                'sign' => $this->sign,
                'id' => $this->uuid4(),
                'timestamp' => $this->current_time,
                'content' => $content

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
            self::CANCEL_ALL,
            [
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
                'machine_code' => $this->machine_code,
                'sign' => $this->sign,
                'id' => $this->uuid4(),
                'timestamp' => $this->current_time,
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
            self::DELETE_PRINTER,
            [
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
                'machine_code' => $this->machine_code,
                'sign' => $this->sign,
                'id' => $this->uuid4(),
                'timestamp' => $this->current_time,

            ]
        );
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function setIcon($image)
    {
        return $this->send(
            self::SET_ICON,
            [
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
                'machine_code' => $this->machine_code,
                'img_url' => CoreAttachmentService::getUrl($image),
                'sign' => $this->sign,
                'id' => $this->uuid4(),
                'timestamp' => $this->current_time,
            ]
        );
    }

    public function deleteIcon()
    {
        return $this->send(
            self::DELETE_ICON,
            [
                'client_id' => $this->client_id,
                'access_token' => $this->access_token,
                'machine_code' => $this->machine_code,
                'sign' => $this->sign,
                'id' => $this->uuid4(),
                'timestamp' => $this->current_time,
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

        //全局错误码
        /**
         * {'error':'0', 'error_description':'success'},
         * {'error':'1', 'error_description':'response_type非法'},
         * {'error':'2', 'error_description':'client_id不存在'},
         * {'error':'3', 'error_description':'redirect_uri不匹配'},
         * {'error':'4', 'error_description':'client_id、response_type、state均不允许为空'},
         * {'error':'5', 'error_description':'client_id或client_secret错误'},
         * {'error':'6', 'error_description':'code错误或已过期'},
         * {'error':'7', 'error_description':'账号密码错误'},
         * {'error':'8', 'error_description':'打印机信息错误,参数有误'},
         * {'error':'9', 'error_description':'连接打印机失败,参数有误'},
         * {'error':'10', 'error_description':'权限不足'},
         * {'error':'11', 'error_description':'sign验证失败'},
         * {'error':'12', 'error_description':'缺少必要参数'},
         * {'error':'13', 'error_description':'打印失败,参数有误'},
         * {'error':'14', 'error_description':'access_token错误'},
         * {'error':'15', 'error_description':'权限不能大于初次授权的权限'},
         * {'error':'16', 'error_description':'不支持k1,k2,k3机型'},
         * {'error':'17', 'error_description':'该打印机已被他人绑定'},
         * {'error':'18', 'error_description':'access_token过期或错误,请刷新access_token或者重新授权'},
         * {'error':'19', 'error_description':'应用未上架或已下架'},
         * {'error':'20', 'error_description':'refresh_token已过期,请重新授权'}
         * {'error':'21', 'error_description':'关闭或重启失败'},
         * {'error':'22', 'error_description':'声音设置失败'},
         * {'error':'23', 'error_description':'获取机型和打印宽度失败'},
         * {'error':'24', 'error_description':'操作失败，没有订单可以被取消'},
         * {'error':'25', 'error_description':'未找到机型的硬件和软件版本'},
         * {'error':'26', 'error_description':'取消logo失败'},
         * {'error':'27', 'error_description':'请设置scope,权限默认为all'},
         * {'error':'28', 'error_description':'设置logo失败'},
         * {'error':'29', 'error_description':'client_id,machine_code,qr_key不能为空'},
         * {'error':'30', 'error_description':'machine_code,qr_key错误'},
         * {'error':'31', 'error_description':'接口不支持自有应用服务模式'},
         * {'error':'32', 'error_description':'订单确认设置失败'},
         * {'error':'33', 'error_description':'Uuid不合法'},
         * {'error':'34', 'error_description':'非法操作'},
         * {'error':'35', 'error_description':'machine_code或msign错误'},
         * {'error':'36', 'error_description':'按键打印开启或关闭失败'},
         * {'error':'37', 'error_description':'添加应用菜单失败'},
         * {'error':'38', 'error_description':'应用菜单内容错误,content必须是Json数组'},
         * {'error':'39', 'error_description':'应用菜单个数超过最大个数'},
         * {'error':'40', 'error_description':'应用菜单内容错误,content中的name值重名'},
         * {'error':'41',  'error_description':'获取或更新access_token的次数,已超过最大限制次数!'},
         * {'error':'42',  'error_description':'该机型不支持面单打印'},
         * {'error':'43',  'error_description':'shipper_type错误'},
         * {'error':'45',  'error_description':'系统错误!请立即反馈'},
         * {'error':'46', 'error_description': 'picture_url错误或格式错误'},
         * {'error':'47',  'error_description':'参数错误',"body":"xxxxx"},
         * {'error':'48', 'error_description': '无法设置,该型号版本不支持!'},
         * {'error':'49', 'error_description': '错误',"body":"xxxxx"},
         */

        $response = Json::decode($response);

        if ($response['error'] != 0 && $response['error_description'] != 'success') {

            if ($response['error'] == 2) {
                $message = '应用ID不存在';
            } elseif ($response['error'] == 5) {
                $message = '应用ID或者应用秘钥错误';
            } elseif ($response['error'] == 11) {
                $message = '应用秘钥错误';
            } elseif ($response['error'] == 35) {
                $message = '终端号或者打印机秘钥错误';
            } elseif ($response['error'] == 41) {
                $message = '获取或更新打印机授权的次数,已超过最大限制次数!，请24小时后重试';
            } else {
                $message = $response['error_description'];
            }

            return error($message);
        }

        return $response;
    }

    private function getAccessToken()
    {
        // 自由型授权token 永久有效， 开放性授权30天有效期

        if ($this->access_token) {
            return $this->access_token;
        }

        $accessTokenInfo = $this->send(
            self::OAUTH,
            [
                'client_id' => $this->client_id,
                'grant_type' => 'client_credentials',
                'sign' => $this->getSign(),
                'scope' => 'all',
                'timestamp' => $this->current_time,
                'id' => $this->uuid4()
            ]
        );

        if (is_error($accessTokenInfo)) {
            throw new Exception($accessTokenInfo['message']);
        }

        return $accessTokenInfo['body']['access_token'] ?? '';
    }

    private function getSign()
    {
        /**
         * client_id+timestamp+client_secret
         */

        return md5(
            $this->client_id .
            $this->current_time .
            $this->client_secret
        );
    }

    private function getCurrentTime()
    {
        return time();
    }

    private function uuid4()
    {
        mt_srand((double)microtime() * 10000);
        $charId = strtolower(md5(uniqid(rand(), true)));
        $hyphen = '-';
        $uuidV4 =
            substr($charId, 0, 8) . $hyphen .
            substr($charId, 8, 4) . $hyphen .
            substr($charId, 12, 4) . $hyphen .
            substr($charId, 16, 4) . $hyphen .
            substr($charId, 20, 12);
        return $uuidV4;
    }


}