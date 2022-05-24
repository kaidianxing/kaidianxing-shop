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

namespace shopstar\mobile\notify;

use Exception;
use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\shop\ShopSettings;
use shopstar\services\wxApp\wxAppCallbackService;
use yii\helpers\Json;

/**
 * Class WxAppController.
 * @package shopstar\mobile\notify
 */
class WxAppController extends BaseMobileApiController
{
    public $configActions = [
        'allowSessionActions' => ['*'],
        'allowActions' => ['*'],
        'allowClientActions' => ['*'],
        'allowShopActions' => ['*'],
        'allowShopCloseActions' => ['index'],
    ];

    /**
     * @var bool 关闭csrf
     */
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $get = RequestHelper::get();
        LogHelper::info('[GET]', $get, SHOP_STAR_TMP_PATH . '/logs/wx-app' . date('Y-m-d') . '.log');

        // 验证消息是否来自微信服务器
        $checkWechat = $this->checkSignature($get['signature'] ?: '', $get['timestamp'] ?: 0, $get['nonce'] ?: '', $get['route_shop_id'] ?: 0);
        if (!$checkWechat) {
            return 'error';
        }

        // 调试验证
        if (!empty($get['echostr'])) {
            return $get['echostr'];
        }

        // 调用映射
        $input = file_get_contents('php://input');
        if (StringHelper::isJson($input)) {
            $inputData = Json::decode($input);
        } else {
            $inputData = ArrayHelper::fromXML($input);
        }

        LogHelper::info('[inputData]', $inputData, SHOP_STAR_TMP_PATH . '/logs/wx-app' . date('Y-m-d') . '.log');
        if (empty($inputData)) {
            return 'error';
        }

        // 开始处理回调
        try {
            (new wxAppCallbackService())->init($inputData);
        } catch (Exception $exception) {
            LogHelper::error('[WX APP ERROR]' . $exception->getMessage() . $exception->getCode(), $inputData, SHOP_STAR_TMP_PATH . '/logs/wx-app' . date('Y-m-d') . '.log');
            return 'error';
        }

        return 'success';
    }

    /**
     * 验证消息的确来自微信服务器
     * @param $signature string 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     * @param $timestamp int 时间戳
     * @param $nonce string 随机数
     * @return bool
     */
    private function checkSignature(string $signature, int $timestamp, string $nonce): bool
    {
        $token = ShopSettings::get('wxTransactionComponent.bases.token');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}
