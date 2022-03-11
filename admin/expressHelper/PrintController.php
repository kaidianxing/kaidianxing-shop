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


namespace shopstar\admin\expressHelper;


use shopstar\bases\KdxAdminApiController;
use shopstar\components\electronicSheet\bases\ElectronicSheetApiConstant;
use shopstar\components\electronicSheet\ElectronicSheetComponents;
use shopstar\constants\expressHelper\ExpressHelperLogConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\expressHelper\ExpressHelperException;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\expressHelper\ExpressHelperExpressTemplateModel;
use shopstar\models\expressHelper\ExpressHelperSendBillLogModel;
use shopstar\models\expressHelper\ExpressHelperSuccessRecordModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\services\expressHelper\PrintHandler;
use shopstar\services\order\OrderService;
use yii\helpers\Json;

/**
 * 打印
 * Class PrintController
 * @author 青岛开店星信息技术有限公司
 * @package apps\expressHelper\manage
 */
class PrintController extends KdxAdminApiController
{
    /**
     * 打印面单
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {

        $post = RequestHelper::post();

        if (empty($post['consigner_template_id']) || empty($post['express_template_id']) || empty($post['order_id']) || empty($post['order_goods_id'])) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_PARAMS_ERROR);
        }

        //获取助手类
        $printHandler = new PrintHandler();

        $orderList = $printHandler->getPrintList($post['order_id'], $post['order_goods_id']);
        if (is_error($orderList)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_LIST_ERROR, $orderList['message']);
        }

        //获取api实例  如果后期添加的菜鸟裹裹实体的话，直接添加实体并调用
        $instance = ElectronicSheetComponents::getInstance(ElectronicSheetApiConstant::API_KDN);
        if (is_error($instance)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_API_INSTANCE_ERROR, $instance['message']);
        }

        //注入配置参数
        $result = $instance->init();
        if (is_error($result)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_LACK_PARAMS_ERROR);
        }

        //获取模板
        list($consignerTemplate, $expressTemplate) = $printHandler->getTemplateData($post['consigner_template_id'], $post['express_template_id']);
        // 票单数量
        $expressTemplate['quantity'] = $post['number'] ?? 1;
        if ($expressTemplate['quantity'] > 300) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_NUMBER_ERROR);
        }
        //返回数据
        $returnData = [];

        //数据集合
        $allPrintData = [];

        //订单循环 多次提交快递鸟 快递鸟不支持批量提交，每秒限制20次请求
        foreach ((array)$orderList as $orderIndex => $orderItem) {
            //访问参数
            $params = $printHandler->getKdnParams($orderItem, $expressTemplate, $consignerTemplate);

            //判断参数是否组成错误
            if (is_error($params)) {
                throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_PARAMS_ERROR, $params['message']);
            }

            //提交请求
            $result = $instance->submitEOrder($params);

            if (is_error($result)) {
                throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_SUBMIT_ORDER_ERROR, $result['message']);
            }

            //判断请求是否返回错误
            if (($result['Success'] == false || $result['ResultCode'] != 100) && empty($result['PrintTemplate'])) {
                throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_GET_PRINT_SUBMIT_ORDER_RETURN_STATUS_ERROR, $result['Reason'] ?: '');
            }

            //追加参数记录使用
            $result['shop_order_id'] = $orderItem['id'];//商城订单id
            $result['shop_order_no'] = $orderItem['order_no'];//商城订单号
            $result['shop_order_goods_id'] = array_column($orderItem['order_goods'], 'id');//商城订单号

            //添加到数据集合
            $allPrintData[] = $result;

            //组装返回参数
            $returnData[] = [
                'Success' => $result['Success'],
                'Reason' => $result['Reason'],
                'ResultCode' => $result['ResultCode'],
                'PrintTemplate' => $result['PrintTemplate'],
                'logistic_code' => $result['Order']['LogisticCode'],
                'express_company' => $result['Order']['ShipperCode'],
                'shop_order_id' => $orderItem['id'],
                'shop_order_no' => $orderItem['order_no'],
                'shop_order_goods_id' => $orderItem['order_goods'],
                'SubPrintTemplates' => $result['SubPrintTemplates'],
            ];
        }

        //请求成功记录入库
        if (!empty($allPrintData)) {
            $printHandler->requestRecord($allPrintData);
        }

        return $this->result(['print_data' => $returnData]);
    }

    /**
     * 打印回调
     * @throws ExpressHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCallback()
    {
        $post = RequestHelper::post();
        if (empty($post)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_PARAMS_ERROR);
        }

        //开启事务
        $tr = \Yii::$app->db->beginTransaction();

        try {

            //获取电子面单模板
            $expressTemplate = ExpressHelperExpressTemplateModel::findOne(['id' => $post['express_template_id']]);

            if (empty($expressTemplate)) {
                throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_EXPRESS_TEMPLATE_NOT_FOUND_ERROR);
            }

            //批量入库
            $result = ExpressHelperSuccessRecordModel::insertData($post['data'], $post['express_template_id']);

            //循环解析数据
            foreach ((array)$post['data'] as $dataIndex => $dataItem) {

                //是否是自动发货
                if ($expressTemplate->auto_send) {

                    //获取未发货的订单商品id
                    $orderGoodsId = OrderGoodsModel::find()->where([
                        'and',
                        [
                            'id' => $dataItem['order_goods_id'],
                            'order_id' => $dataItem['order_id'],
                            'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND,
//                            'refund_status' => [RefundConstant::REFUND_STATUS_CANCEL, RefundConstant::REFUND_STATUS_REJECT],
                        ],
                    ])->column();

                    //如果为空则说明是重新打印的
                    if (empty($orderGoodsId)) {
                        continue;
                    }

                    //如果要发货的订单商品和传入的订单商品不一致
                    if (count($dataItem['order_goods_id']) !== count($orderGoodsId)) {
                        throw new \Exception('订单id(' . $dataItem['order_id'] . ';' . implode(',', $dataItem['order_goods_id']) . ') :' . '发货商品不一致');
                    }

                    //执行订单发货
                    $sendResult = OrderService::ship($dataItem['order_id'], [
                        'order_goods_id' => $orderGoodsId,
                        'no_express' => 0,
                        'express_sn' => $dataItem['logistic_code'],
                        'express_id' => CoreExpressModel::getIdByCode($dataItem['express_company'])
                    ]);

                    if (is_error($sendResult)) {
                        throw new \Exception('订单id(' . $dataItem['order_id'] . ';' . implode(',', $dataItem['order_goods_id']) . ') :' . $sendResult['message']);
                    }

                }

                //修改订单商品打印状态
                OrderGoodsModel::updateAll([
                    'is_print' => 1
                ], [
                    'and',
                    [
                        'id' => $dataItem['order_goods_id'],
                        'order_id' => $dataItem['order_id'],
                    ],
                    ['!=', 'is_print', 1]
                ]);
            }


            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_ERROR, $exception->getMessage());
        }


        return $this->result($result);
    }

    /**
     * 发货单回调
     * @return array|\yii\web\Response
     * @throws ExpressHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBillPrintCallback()
    {
        $post = RequestHelper::post();
        if (empty($post)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_PARAMS_ERROR);
        }
        $expressLog = ExpressHelperSendBillLogModel::find()->where(['id' => $post['express_log_id'], 'status' => ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_START])->asArray()->all();
        if (empty($expressLog)) {
            throw new ExpressHelperException(ExpressHelperException::SEND_BILL_PRINT_TEMPLATE_NOT_FOUND_ERROR);
        }

        //开启事务
        $tr = \Yii::$app->db->beginTransaction();

        try {
            foreach ($expressLog as $value) {
                //修改订单打印状态
                OrderModel::updateAll([
                    'is_bill_print' => 1
                ], [
                    'and',
                    [
                        'id' => $value['order_id'],
                    ],
                    ['!=', 'is_bill_print', 1]
                ]);
                // 修改发货单打印记录的状态
                ExpressHelperSendBillLogModel::updateAll([
                    'status' => ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_SUCCESS
                ], ['id' => $value['id'], 'status' => ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_START]);
            }

            // 修改发货单打印记录的状态
//            $expressLog->status = ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_SUCCESS;
//            $expressLog->save();
            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_ERROR, $exception->getMessage());
        }
        return $this->result();
    }

    /**
     * 订单打印之前检测是否与子母单冲突 暂时废弃
     * @return array|\yii\web\Response
     * @throws ExpressHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckSub()
    {
        $orderIds = RequestHelper::post('order_id');
        $orderGoodsIds = RequestHelper::post('order_goods_id');
        // 电子面单的模板
        $expressTemplateId = RequestHelper::post('express_template_id');
        if (!$orderIds || !$orderGoodsIds || empty($expressTemplateId)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_PRINT_INDEX_PARAMS_ERROR);
        }
        // 查询新模板数据
        $newExpressTemplateInfo = ExpressHelperExpressTemplateModel::findOne(['id' => $expressTemplateId]);
        // 查询所选订单的所有的打印成功记录
        $expressList = ExpressHelperSuccessRecordModel::find()
            ->where([
                'order_id' => $orderIds,
                'order_goods_id' => $orderGoodsIds,
            ])
            ->select(['express_template_info'])
            ->asArray()
            ->all();
        // 订单未有旧打印数据
        if (!$expressList) {
            return $this->result(['data' => ['status' => true]]);
        }
        // 判断旧订单的模板是否是子母单模板
        if ($expressList) {
            foreach ($expressList as $value) {
                // 此处特殊处理旧数据
                if (!$value['express_template_info']) {
                    // 旧订单此字段为空也属于普通打印 新订单是子母单打印
                    if ($newExpressTemplateInfo->is_sub != 0) {
                        return $this->result(['data' => ['status' => false, 'message' => '该订单已通过非子母单模板进行过打印,不再支持子母单模板']]);
                    }
                    break;
                }
                $item = Json::decode($value['express_template_info']);
                switch ($item['is_sub']) {
                    case 0:
                        // 旧订单是普通打印 新订单是子母单打印
                        if ($newExpressTemplateInfo->is_sub != 0) {
                            return $this->result(['data' => ['status' => false, 'message' => '该订单已通过非子母单模板进行过打印,不再支持子母单模板']]);
                        }
                        break;
                    case 1:
                        // 旧订单是子母打印 新订单是普通打印
                        if ($newExpressTemplateInfo->is_sub == 0) {
                            return $this->result(['data' => ['status' => false, 'message' => '该订单已通过子母单模板进行过打印,不再支持非子母单模板']]);
                        }
                        break;
                }
            }
        }

        return $this->result(['data' => ['status' => true]]);
    }
}
