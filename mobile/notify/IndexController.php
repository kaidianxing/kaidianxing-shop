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
use shopstar\components\payment\base\PayOrderTypeConstant;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\FileHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\order\BytedanceSettleJob;
use shopstar\models\core\CoreSettings;
use shopstar\models\order\PayOrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\sysset\PaymentModel;
use yii\helpers\Json;

class IndexController extends BaseMobileApiController
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
     * 微信回调
     * @return bool
     * @throws PaymentException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        FileHelper::createDirectory(SHOP_STAR_TMP_PATH . '/logs/');
        $array = $_POST;

        // alipay
        if (isset($array['gmt_create'])) {
            return $this->actionAlipay($array);
        }

        // wechat
        $notify = ArrayHelper::fromXML(file_get_contents('php://input'));
        file_put_contents(SHOP_STAR_TMP_PATH . '/logs/wechat_notify_' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' > ' .
            Json::encode($notify) . PHP_EOL, FILE_APPEND);

        // 交易状态校验
        if ($notify['result_code'] !== $notify['return_code'] && $notify['return_code'] !== 'SUCCESS') {
            LogHelper::info('[wechat-notify]-交易状态不正确', $notify);
            return false;
        }

        // 订单校验
        $callback = Json::decode($notify['attach']);
        if (empty($callback)) {
            // 二次回调不带回调参数，不作处理
            return true;
        }

        // 支付订单类型，新接口用type旧接口使用code
        $payOrderType = $callback['type'] ?? $callback['code'];

        $order = PayOrderModel::find()->where([
            'order_id' => $callback['order_id'],
            'type' => $payOrderType,
            'status' => 0
        ])->all();

        if (empty($order)) {
            LogHelper::info('[wechat-notify]-订单不存在', $notify);
            return false;
        }

        foreach ($order as $orderIndex => $orderItem) {

            // 订单状态校验
            if ((int)$orderItem['status'] !== 0) {
                LogHelper::info('[wechat-notify]-订单状态不正确', $notify);
                return false;
            }
        }

        // 支付价格校验
        if (number_format(array_sum(array_column($order, 'pay_price')) * 100, 0, ".", "") != $notify['total_fee']) {

            LogHelper::info('[wechat-notify]-价格不正确', ['notify' => $notify, 'pay_price' => number_format(array_sum(array_column($order, 'pay_price')) * 100, 0, ".", "")]);
            return false;
        }

        // 验证价格通过之后将价格赋值
        $callback['pay_price'] = array_sum(array_column($order, 'pay_price'));

        //判断是否是商家端订单
        if (PayOrderTypeConstant::ORDER_TYPE_MANAGE_ORDER == $payOrderType) {

            // 获取支付模板
            $paySettings = CoreSettings::get('payment.wechat');
            if (empty($paySettings)) {
                LogHelper::info('[manage-wechat-notify]-支付配置不存在', $notify);
                throw new PaymentException(PaymentException::MANAGE_WECHAT_NOTIFY_PAYSET_IS_NOT_ALLOWED);
            }

            $paymentModel = new PaymentModel();
            $paymentModel->setAttributes([
                'sub_appid' => $paySettings['app_id'],
                'sub_mch_id' => $paySettings['mch_id'],
                'api_key' => $paySettings['key'],
                'wechat_key' => '',
                'wechat_cert' => '',
            ]);

        } else {
            $paymentId = ShopSettings::get('sysset.payment.typeset.' . ClientTypeConstant::getIdentify($callback['client_type']) . '.wechat.id', 0);

            // 获取支付模板
            $paymentModel = PaymentModel::find()
                ->where([
                        'and',
                        ['id' => $paymentId],
                        ['is_deleted' => 0],
                    ]

                )->one();

            if ($paymentModel === null) {
                LogHelper::info('[manage-wechat-notify]-支付配置不存在', [ClientTypeConstant::getIdentify($callback['client_type']), $notify]);
                throw new PaymentException(PaymentException::NOTIFY_PAYSET_IS_NOT_ALLOWED);
            }
        }

        //判断订单来源，使用参数不同
        $driver = PayComponent::getInstance([
            'client_type' => $callback['client_type'],
        ]);
        if ($callback['client_type'] == ClientTypeConstant::CLIENT_WECHAT || $callback['client_type'] == ClientTypeConstant::MANAGE_PC) {
            $config = $driver->setWechatConfig($paymentModel);
        } else {
            $config = $driver->setWxappConfig($paymentModel);
        }

        if ($config->verify(ArrayHelper::toXML($notify)) === false || empty($config->verify(ArrayHelper::toXML($notify)))) {
            LogHelper::info('wechat-notify]-verify error', $notify);
            return false;
        }


        foreach ((array)$order as $orderIndex => $orderItem) {

            //开启事务
            $trans = \Yii::$app->db->beginTransaction();
            try {
                $orderItem->trade_no = $notify['transaction_id'];
                $orderItem->out_trade_no = $notify['out_trade_no'];
                $orderItem->pay_time = DateTimeHelper::now();
                $orderItem->raw_data = Json::encode($notify);
                $orderItem->status = 10;
                if ($orderItem->save() === false) {
                    throw new \Exception($orderItem->getErrorMessage());
                }

                $class = PayOrderTypeConstant::getModel($payOrderType);
                $callback['trans_id'] = $notify['transaction_id'];
                $callback['out_trade_no'] = $notify['out_trade_no'];

                //paysuccess结构体
                $orderPaySuccessStruct = \Yii::createObject([
                    'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                    'accountId' => $orderItem->account_id,
                    'orderId' => $orderItem->order_id,
                    'payType' => PayTypeConstant::PAY_TYPE_WECHAT,
                    'callBack' => $callback,
                ]);

                //调用paysuccess
                $ret = ($class::paySuccess($orderPaySuccessStruct));
                $ret === true && $ret = null;
                $orderItem->error_info = Json::encode($ret);
                $orderItem->save();
                //完成后提交
                $trans->commit();

            } catch (\Exception $exception) {
                LogHelper::info('wechat-notify]-' . $exception->getMessage(), $notify);

                //如果有错误信息则记录错误信息
                $trans->rollBack();

                //重新赋值错误信息后commit提交保存
                $orderItem->status = 0;
                $orderItem->error_info = Json::encode($exception->getMessage());
                $orderItem->save();
                return false;
            }
        }

        return $config->success()->send();
    }

    /**
     * 支付宝回调
     * @param $notify
     * @return false
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function actionAlipay($notify)
    {
        // 记录日志
        file_put_contents(SHOP_STAR_TMP_PATH . '/logs/alipay_notify_' . date('Y-m-d') . '.log', $notify['gmt_create'] . ' > ' . Json::encode($notify) . PHP_EOL, FILE_APPEND);

        //解析回调
        if ($notify['passback_params']) {
            $callback = Json::decode($notify['passback_params']);
        } else {
            $callback = Json::decode(urldecode($notify['extend_params']));
            unset($notify['extend_params']);
        }

        // 交易状态校验
        if (trim($notify['trade_status']) !== 'TRADE_SUCCESS') {
            LogHelper::info('[alipay-notify]-交易状态不正确', $notify);
            return false;
        }

        // 支付订单类型，新接口用type旧接口使用code
        $payOrderType = $callback['type'] ?? $callback['code'];

        // 订单校验
        $order = PayOrderModel::find()->where([
            'order_id' => $callback['order_id'],
            'type' => $payOrderType,
            'status' => 0
        ])->all();

        if (empty($order)) {
            LogHelper::info('[alipay-notify]-订单不存在', $notify);
            return false;
        }

        // 订单状态校验
        foreach ($order as $orderIndex => $orderItem) {
            if ((int)$orderItem['status'] !== 0) {
                LogHelper::info('[alipay-notify]-订单状态不正确', $notify);
                return false;
            }
        }

        // 支付价格校验
        $payPrice = array_sum(array_column($order, 'pay_price'));

        if ($payPrice != $notify['buyer_pay_amount']) {
            LogHelper::info('[alipay-notify]-价格不正确', $notify);
            return false;
        }

        // 验证价格通过之后将价格赋值
        $callback['pay_price'] = $payPrice;

        //是否是商家端订单
        if (PayOrderTypeConstant::ORDER_TYPE_MANAGE_ORDER == $payOrderType) {

            // 获取支付模板
            $paySettings = CoreSettings::get('payment.alipay');
            if (empty($paySettings)) {
                LogHelper::info('[manage-alipay-notify]-支付配置不存在', $notify);
                throw new PaymentException(PaymentException::MANAGE_WECHAT_NOTIFY_PAYSET_IS_NOT_ALLOWED);
            }

            $paymentModel = new PaymentModel();
            $paymentModel->setAttributes([
                'appid' => $paySettings['app_id'],
                'ali_private_key' => $paySettings['private_key'],
                'alipay_cert_public_key_rsa2' => $paySettings['alipay_cert_public_key_rsa2'],
                'app_cert_public_key' => $paySettings['app_cert_public_key'],
                'alipay_root_cert' => $paySettings['alipay_root_cert'],
                'pay_type' => PayTypeConstant::PAY_TYPE_ALIPAY,
                'pay_price' => $payPrice,
            ]);
        } else {

            $paymentId = ShopSettings::get('sysset.payment.typeset.' . ClientTypeConstant::getIdentify($callback['client_type']) . '.alipay.id', 0);

            // 获取支付模板
            $paymentModel = PaymentModel::findOne(['id' => $paymentId, 'appid' => $notify['app_id'], 'is_deleted' => 0]);
            if (empty($paymentModel)) {
                LogHelper::info('[alipay-notify]-支付配置不存在', $notify);
                throw new PaymentException(PaymentException::NOTIFY_PAYSET_IS_NOT_ALLOWED);
            }
        }

        $driver = PayComponent::getInstance([
            'client_type' => $callback['client_type'],
        ]);

        $config = $driver->setAlipayConfig($paymentModel);

        try {
            if ($config->verify($notify) === false || empty($config->verify($notify))) {
                throw new \Exception('验签失败');
            }
        } catch (\Exception $exception) {
            $driver->clearCert();
            LogHelper::info('alipay-notify]-verify error', $notify);
            return false;
        }

        //开启事务
        $trans = \Yii::$app->db->beginTransaction();

        foreach ((array)$order as $orderIndex => $orderItem) {

            try {
                $orderItem->trade_no = $notify['trade_no'];
                $orderItem->out_trade_no = $notify['out_trade_no'];
                $orderItem->pay_time = DateTimeHelper::now();
                $orderItem->raw_data = Json::encode($callback);
                $orderItem->status = 10;
                if ($orderItem->save() === false) {
                    throw new \Exception($orderItem->getErrorMessage());
                }

                $class = PayOrderTypeConstant::getModel($payOrderType);
                $callback['trans_id'] = $notify['trade_no'];
                $callback['out_trade_no'] = $notify['out_trade_no'];

                //paysuccess结构体
                $orderPaySuccessStruct = \Yii::createObject([
                    'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                    'accountId' => $orderItem->account_id,
                    'orderId' => $orderItem->order_id,
                    'payType' => PayTypeConstant::PAY_TYPE_ALIPAY,
                    'callBack' => $callback,
                ]);

                $ret = ($class::paySuccess($orderPaySuccessStruct));
                $orderItem->error_info = Json::encode($ret ?? null);
                $orderItem->save();

            } catch (\Exception $exception) {
                LogHelper::info('alipay-notify]-' . $exception->getMessage(), $notify);

                //如果有错误信息则记录错误信息
                $trans->rollBack();

                //重新赋值错误信息后commit提交保存
                $orderItem->status = 0;
                $orderItem->error_info = Json::encode($exception->getMessage());
                $orderItem->save();
                return false;
            }
        }

        //循环完成后提交
        $trans->commit();
        return $config->success()->send();
    }
    
    /**
     * 抖音回调
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBytedance()
    {
        // 接收数据
        $notify = file_get_contents('php://input');
        file_put_contents(SHOP_STAR_TMP_PATH . '/logs/bytedance_notify_' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' > ' . Json::encode($notify) . PHP_EOL, FILE_APPEND);
        $notify = Json::decode($notify);
    
        // 交易状态校验
        if ($notify['type'] !== 'payment') {
            LogHelper::info('[bytedance_notify]-交易状态不正确', $notify);
            return false;
        }
        
        // 订单信息的 json 字符串
        $msg = Json::decode($notify['msg']);
        // 附加参数
        $extData = Json::decode($msg['cp_extra']);
        // 验签
        // 取设置的token
        $token = ShopSettings::get('sysset.payment.typeset.byte_dance.byte_dance.token');
        $signData = [
            $token,
            $notify['timestamp'],
            $notify['nonce'],
            $notify['msg']
        ];
        sort($signData,2);
        $sign = sha1(implode('', $signData));
        if ($sign != $notify['msg_signature']) {
            LogHelper::info('[bytedance_notify]-验签失败', $notify);
            return false;
        }
        
        // 处理订单
        $order = PayOrderModel::find()->where([
            'order_id' => $extData['order_id'],
            'type' => $extData['type'],
            'status' => 0
        ])->all();
    
        if (empty($order)) {
            LogHelper::info('[bytedance-notify]-订单不存在', $notify);
            return false;
        }
    
        foreach ($order as $orderItem) {
            // 订单状态校验
            if ((int)$orderItem['status'] !== 0) {
                LogHelper::info('[bytedance-notify]-订单状态不正确', $notify);
                return false;
            }
        }
    
        //开启事务
        $trans = \Yii::$app->db->beginTransaction();
    
        foreach ((array)$order as $orderItem) {
        
            try {
                $orderItem->trade_no = $msg['payment_order_no'];
                $orderItem->out_trade_no = $msg['cp_orderno'];
                $orderItem->pay_time = DateTimeHelper::now();
                $orderItem->raw_data = Json::encode($notify);
                $orderItem->status = 10;
                if ($orderItem->save() === false) {
                    throw new \Exception($orderItem->getErrorMessage());
                }
            
                $class = PayOrderTypeConstant::getModel($extData['type']);
                $callback['trans_id'] = $msg['payment_order_no'];
                $callback['out_trade_no'] = $msg['cp_orderno'];
                $callback['pay_type'] = $msg['way'];
                $callback['pay_price'] = bcdiv($msg['total_amount'], 100, 2);
                //paysuccess结构体
                $orderPaySuccessStruct = \Yii::createObject([
                    'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                    'accountId' => $orderItem->account_id,
                    'orderId' => $orderItem->order_id,
                    'payType' => $msg['way'] == 1 ? PayTypeConstant::PAY_TYPE_BYTEDANCE_WECHAT : PayTypeConstant::PAY_TYPE_BYTEDANCE_ALIPAY,
                    'callBack' => $callback,
                ]);
            
                //调用paysuccess
                $ret = ($class::paySuccess($orderPaySuccessStruct));
                $ret === true && $ret = null;
                $orderItem->error_info = Json::encode($ret);
                $orderItem->save();
    
                // 创建结算任务 字节跳动支付
                if ($orderItem['pay_type'] == PayTypeConstant::PAY_TYPE_BYTEDANCE_WECHAT) {
                    QueueHelper::push(new BytedanceSettleJob([
                        'payOrderId' => $orderItem->id
                    ]), 1296000); // 15 天结算
                }
            
            } catch (\Exception $exception) {
                LogHelper::info('bytedance-notify]-' . $exception->getMessage(), $notify);
            
                //如果有错误信息则记录错误信息
                $trans->rollBack();
            
                //重新赋值错误信息后commit提交保存
                $orderItem->status = 0;
                $orderItem->error_info = Json::encode($exception->getMessage());
                $orderItem->save();
                return false;
            }
        }
    
        //循环完成后提交
        $trans->commit();
        
        $data = [
            'err_no' => 0,
            'err_tips' => 'success'
        ];
        echo Json::encode($data);
        die;
    }
}
