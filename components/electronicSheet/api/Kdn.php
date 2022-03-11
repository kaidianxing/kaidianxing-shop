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


namespace shopstar\components\electronicSheet\api;

use shopstar\components\electronicSheet\bases\ElectronicSheetApiInterface;
use shopstar\helpers\HttpHelper;
use shopstar\models\shop\ShopSettings;

/**
 * 快递鸟
 * Class Kdn
 * @package shopstar\components\electronicSheet\api
 * @author 青岛开店星信息技术有限公司
 */
class Kdn implements ElectronicSheetApiInterface
{
    /**
     * 接口地址
     * @var string
     */
    public $apiUrl = 'https://api.kdniao.com/api/EOrderService';

//    public $apiUrl = 'http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json';

//    public $apiUrl = 'http://testapi.kdniao.com:8081/api/EOrderService';

    /**
     * 应用id
     * @var
     * @author 青岛开店星信息技术有限公司
     */
    public $eBusinessId;

    /**
     * key
     * @var
     * @author 青岛开店星信息技术有限公司
     */
    public $appKey;

    /**
     * 初始化
     * @param array $config
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function init(array $config = [])
    {
        $setting = $config;
        if (empty($config)) {
            $setting = ShopSettings::get('plugin_express_helper.express.kdn');
        }

        $this->eBusinessId = $setting['appid'];
        $this->appKey = $setting['key'];

        if (empty($this->eBusinessId) || empty($this->appKey)) {
            return false;
        }

        return true;
    }

    /**
     * Json方式 调用电子面单接口
     * @param $params
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function submitEOrder(array $params)
    {
        //兼容中文处理
        $requestData = json_encode($params, JSON_UNESCAPED_UNICODE);

        //返回请求
        return HttpHelper::postJson($this->apiUrl, [
            'EBusinessID' => $this->eBusinessId,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
            'DataSign' => $this->encrypt($requestData)
        ], [
            'Content-Type' => ''
        ]);
    }

    /**
     * 电商Sign签名生成
     * @param $data
     * @return string DataSign签名
     * @author 青岛开店星信息技术有限公司
     */
    public function encrypt($data)
    {
        return urlencode(base64_encode(md5($data . $this->appKey)));
    }
}