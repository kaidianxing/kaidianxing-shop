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

use shopstar\bases\controller\BaseController;
use shopstar\helpers\RequestHelper;
use shopstar\services\tradeOrder\TradeOrderService;
use yii\helpers\Json;

/**
 * 支付回调控制器
 * Class PayController
 * @package shop\client\notify
 * @author likexin
 */
class PayController extends BaseController
{
    
    /**
     * @var bool 关闭csrf
     */
    public $enableCsrfValidation = false;
    
    /**
     * @author likexin
     */
    public function actionIndex()
    {
        $params = [
            'type' => 'alipay',
            'raw' => RequestHelper::post(),
        ];
        // 非支付宝
        if (!RequestHelper::post('gmt_create')) {
            if (empty(RequestHelper::get('bytedance'))) {
                $params['type'] = 'wechat';
            } else  {
                $params['type'] = 'byte_dance';
            }
            $params['raw'] = file_get_contents('php://input');
        }

        // 调用交易订单服务处理
        return TradeOrderService::notify($params)->handler();
    }
    
}