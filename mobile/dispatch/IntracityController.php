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

namespace shopstar\mobile\dispatch;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\dispatch\bases\DispatchDriverConstant;
use shopstar\constants\order\OrderPackageCityDistributionTypeConstant;
use shopstar\exceptions\order\DispatchException;
use shopstar\helpers\RequestHelper;
use shopstar\services\shop\ShopSettingIntracityLogic;
use shopstar\models\shop\ShopSettings;

class IntracityController extends BaseMobileApiController
{

    /**
     * 获取配送区域
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetDispatchArea ()
    {
        $dispatchArea = ShopSettingIntracityLogic::getDispatchArea();

        $dispatchArea['shop_address'] = ['address' => ShopSettings::get('contact')];

        return $this->result(['data' => $dispatchArea]);
    }

    /**
     * 查询第三方配送订单详情
     * @return array|\yii\web\Response
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionQueryOrderStatus()
    {
        $dispatchType = RequestHelper::post('dispatch_type');

        // check dispatch type
        $checkDispatchType = OrderPackageCityDistributionTypeConstant::getIdentify($dispatchType);
        if (is_null($checkDispatchType)) {
            throw new DispatchException(DispatchException::DISPATCH_INTRACITY_QUERY_ORDER_STATUS_TYPE_INVALID);
        }

        $orderId = RequestHelper::post('order_id');
        if (empty($orderId)) {
            throw new DispatchException(DispatchException::DISPATCH_INTRACITY_QUERY_ORDER_STATUS_ORDER_INVALID);
        }

        $result = ShopSettingIntracityLogic::queryOrderStatus($dispatchType, $orderId);

        if (is_error($result)) {
            return $this->result($result['message'], DispatchException::DISPATCH_INTRACITY_QUERY_ORDER_STATUS_INVALID);
        }

        return $this->result(['data' => $result]);
    }

}