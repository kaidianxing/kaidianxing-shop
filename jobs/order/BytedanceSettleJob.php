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

namespace shopstar\jobs\order;

use shopstar\helpers\HttpHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\order\PayOrderModel;
use shopstar\models\shop\ShopSettings;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * 字节跳动支付结算任务
 * 字节跳动要求主动调用结算接口才能提现
 * T + 7 天结算
 * Class BytedanceSettleJob
 * @package shopstar\jobs\order
 */
class BytedanceSettleJob extends BaseObject implements JobInterface
{
    /**
     * @var string 结算api
     */
    private $settleApi = 'https://developer.toutiao.com/api/apps/ecpay/v1/settle';

    /**
     * @var string 外部交易单号
     */
    public $outTradeNo = '';
    
    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        // appid
        $appid = ShopSettings::get('channel_setting.byte_dance.appid');
        // salt
        $salt = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance.salt');
        
        // 参数
        $params = [
            'out_order_no' => $this->outTradeNo,
            'settle_desc' => '结算',
            'out_settle_no' => time().mt_rand(1000, 9999)
        ];
        // 计算签名
        $paramArray = [];
        foreach ($params as $param) {
            $paramArray[] = trim($param);
        }
        $paramArray[] = trim($salt);
        sort($paramArray, 2);
        $signStr = trim(implode('&', $paramArray));
        $params['sign'] = md5($signStr);
        
        $params['app_id'] = $appid;
        $params = Json::encode($params);
        $res = HttpHelper::post($this->settleApi, $params);
        $res = Json::decode($res);
        if ($res['err_no'] != 0) {
            LogHelper::error('[BYTEDANCE SETTLE ERROR ERROR]:' . $res['err_tips'], Json::decode($params));
            echo "字节跳动结算失败";
            return ;
        }
        echo "字节跳动结算成功";
    }
}