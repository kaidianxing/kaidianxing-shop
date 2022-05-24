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

namespace shopstar\mobile\wxTransactionComponent;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\wxTransactionComponent\WxTransactionComponentException;
use shopstar\helpers\RequestHelper;
use shopstar\services\wxTransactionComponent\WxTransactionComponentService;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 微信自定义交易组件
 * Class IndexController.
 * @package shopstar\mobile\wxTransactionComponent
 */
class IndexController extends BaseMobileApiController
{
    /**
     * 微信自定义交易组件
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckScene()
    {
        $scene = RequestHelper::post('scene');
        $orderId = RequestHelper::post('order_id');
        $payInfo = RequestHelper::post('pay_info');

        if (empty($scene) || empty($orderId) || empty($payInfo)) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $result = WxTransactionComponentService::checkScene($scene, $orderId, $payInfo);

        return $this->success($result);
    }
}
