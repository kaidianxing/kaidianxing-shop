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

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\dispatch\bases\DadaOrderStatusConstant;
use shopstar\components\dispatch\bases\DispatchDriverConstant;
use shopstar\constants\order\OrderPackageCityDistributionTypeConstant;
use shopstar\helpers\FileHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\order\DispatchNotifyLogModel;
use shopstar\models\order\DispatchOrderModel;
use yii\base\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 第三方配送回调
 * Class DispatchController
 * @package shop\client\notify
 * @author 青岛开店星信息技术有限公司
 */
class DispatchController extends BaseMobileApiController
{
    /**
     * @var array
     */
    public $configActions = [
        'allowSessionActions' => ['*'],
        'allowActions' => ['*'],
        'allowClientActions' => ['*'],
        'allowShopCloseActions' => ['index'],
    ];

    /**
     * @return array|int[]|Response
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        FileHelper::createDirectory(SHOP_STAR_TMP_PATH . '/logs/');
        $notify = file_get_contents('php://input');

        file_put_contents(SHOP_STAR_TMP_PATH . '/logs/dispatch_notify_' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' > ' . $notify . PHP_EOL, FILE_APPEND);

        $notifyArr = Json::decode($notify);

        // 达达配送
        $this->dada($notify, $notifyArr);

        return $this->result(['status' => 'ok']);
    }

    private function dada(bool $notify, $notifyArr)
    {
        // 添加配送订单表
        $dispatchOrder = DispatchOrderModel::findOne(['order_no' => $notifyArr['order_id'], 'type' => OrderPackageCityDistributionTypeConstant::DADA]);

        $dispatchOrderAttribute = [
            'status' => $notifyArr['order_status']
        ];

        switch ($notifyArr['order_status']) {
            case DadaOrderStatusConstant::WAIT_LIST:
                $dispatchOrderAttribute['payed_time'] = date('Y-m-d H:i:s', $notifyArr['update_time']);
                break;
            case DadaOrderStatusConstant::WAIT_PICK_UP:
                $dispatchOrderAttribute['accepted_time'] = date('Y-m-d H:i:s', $notifyArr['update_time']);
                break;
            case DadaOrderStatusConstant::DELIVERY:
                $dispatchOrderAttribute['delivery_time'] = date('Y-m-d H:i:s', $notifyArr['update_time']);
                break;
            case DadaOrderStatusConstant::COMPLETED:
                $dispatchOrderAttribute['completed_time'] = date('Y-m-d H:i:s', $notifyArr['update_time']);
                break;
            case DadaOrderStatusConstant::CANCELED:
                $dispatchOrderAttribute['cancel_time'] = date('Y-m-d H:i:s', $notifyArr['update_time']);
                break;
        }

        $dispatchOrder->setAttributes($dispatchOrderAttribute);

        if (!$dispatchOrder->save()) {
            LogHelper::error('Dispatch Order Save ' . $dispatchOrder->getErrorMessage(), $notifyArr);
        }
        // 记录日志
        $dispatchLog = new DispatchNotifyLogModel();

        $attribute = [
            'type' => DispatchDriverConstant::getCode(DispatchDriverConstant::DRIVE_DADA),
            'order_id' => $dispatchOrder->order_id,
            'notify' => $notify,
            'status' => $notifyArr['order_status'],
            'create_time' => date('Y-m-d H:i:s', $notifyArr['update_time'])
        ];

        $dispatchLog->setAttributes($attribute);

        if (!$dispatchLog->save()) {
            LogHelper::error('Dispatch Log Save ' . $dispatchLog->getErrorMessage(), $notifyArr);
        }

        return true;
    }


}
