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

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\amap\AmapClient;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\goods\GoodsVirtualConstant;
use shopstar\constants\log\order\OrderLogConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderPaymentTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\virtualAccount\VirtualAccountDataConstant;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderChangePriceLogModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\order\refund\OrderRefundModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\shoppingReward\ShoppingRewardLogModel;
use shopstar\models\user\UserModel;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;
use shopstar\services\commission\CommissionOrderService;
use shopstar\services\commission\CommissionService;
use shopstar\services\consumeReward\ConsumeRewardLogService;
use shopstar\services\goods\GoodsService;
use shopstar\services\order\OrderService;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * 订单操作
 * Class OpController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\order
 */
class OpController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'update-seller-remark'
        ]
    ];

    /**
     * 同城配送映射map
     * @var string[]
     */
    public $dispatchMap = [
        '0' => '商家配送',
        '1' => '达达配送',
        '2' => '码科配送',
        '3' => '闪送',
        '4' => '顺丰同城',
    ];

    /**
     * @不允许修改运费的订单类型: 虚拟商品, 虚拟卡密, 到店核销
     * @var mixed
     */
    private $disableChangeDispatchPrice = [
        OrderTypeConstant::ORDER_TYPE_VIRTUAL,
        OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT,
    ];

    /**
     * 修改收货地址
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEditAddress(): \yii\web\Response
    {
        $orderId = RequestHelper::isPost() ? RequestHelper::postInt('order_id') : RequestHelper::getInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_PARAMS_ORDER);
        }

        $order = OrderModel::getOrderAndOrderGoods($orderId, 0);
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_ORDER_NOT_FOUND_ORDER);
        }

        if (RequestHelper::isPost()) {
            $post = RequestHelper::post();
            //修改收货地址
            if (empty($post['buyer_name'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '收货人姓名不能为空');
            }

            if (empty($post['buyer_mobile'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '收货人手机不能为空');
            }

            if (empty($post['address_detail'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '详细地址不能为空');
            }

            if (empty($post['province'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '请选择省');
            }

            if (empty($post['city'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '请选择城市');
            }

            if (empty($post['area'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '请选择区县');
            }

            if (empty($post['area_code'])) {
                throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_POST_PARAMS_ORDER, '缺少地区码');
            }

            //释放之前的地址信息
            unset($order['address_info']);
            $order['address_info']['dispatch_type'] = OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS;
            $order['address_info']['province'] = $post['province'];
            $order['address_info']['city'] = $post['city'];
            $order['address_info']['area'] = $post['area'];
            $order['address_info']['area_code'] = $post['area_code'];
            $order['address_info']['address_detail'] = $post['address_detail'];
            $order['address_info']['postcode'] = $post['postcode'];

            $province = $post['province'];
            $city = $post['city'];
            $area = $post['area'];
            $address = $post['address'];

            $addressDetail = $province . $city . $area . $address;

            $location = AmapClient::getLocationPoint($addressDetail);

            if (is_error($location)) {
                $location = [
                    'lng' => '',
                    'lat' => '',
                ];
            }

            $order['address_info']['lng'] = $location['lng'];
            $order['address_info']['lat'] = $location['lat'];

            $data = [
                'buyer_name' => $post['buyer_name'],
                'buyer_mobile' => $post['buyer_mobile'],
                'address_state' => $post['province'],
                'address_city' => $post['city'],
                'address_area' => $post['area'],
                'address_detail' => $post['address_detail'],
                'address_info' => Json::encode($order['address_info']),
                'address_code' => $post['area_code'],
            ];

            $result = OrderModel::updateAll($data, ['id' => $orderId]);

            $model = new OrderModel();
            if ($result) {
                //添加操作日志
                LogModel::write(
                    $this->userId,
                    OrderLogConstant::ORDER_OP_EDIT_ADDRESS,
                    OrderLogConstant::getText(OrderLogConstant::ORDER_OP_EDIT_ADDRESS),
                    $orderId,
                    [
                        'log_primary' => $model->getLogAttributeRemark([
                            'change_address' => [
                                'order_no' => $order['order_no'],
                                'buyer_name' => $post['buyer_name'],
                                'buyer_mobile' => $post['buyer_mobile'],
                                'address_info' => $data['address_state'] . '-' . $data['address_city'] . '-' . $data['address_area'],
                                'address_detail' => $data['address_detail']
                            ]
                        ]),
                    ]
                );
                return $this->success();
            }

            throw new OrderException(OrderException::ORDER_MANAGE_EDIT_ADDRESS_ORDER);
        }

        //返回收货信息
        $return = [
            'id' => $order['id'],
            'buyer_name' => $order['buyer_name'],
            'buyer_mobile' => $order['buyer_mobile'],
            'address_state' => $order['address_state'],
            'address_city' => $order['address_city'],
            'address_area' => $order['address_area'],
            'address_code' => $order['address_code'],
            'address_detail' => $order['address_detail'],
        ];

        return $this->success($return);
    }

    /**
     * 发货
     * @return \yii\web\Response
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSendPackage(): \yii\web\Response
    {
        $orderId = RequestHelper::isPost() ? RequestHelper::postInt('order_id') : RequestHelper::getInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_PACKAGE_PARAMS_ERROR);
        }

        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId, 0);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_PACKAGE_ORDER_NOT_FOUND_ERROR);
        }

        //如果订单商品不存在
        if (empty($orderInfo['orderGoods'])) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_PACKAGE_ORDER_GOODS_NOT_FOUND_ERROR);
        }

        //状态不正确
        if ($orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_SEND && $orderInfo['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PART_SEND) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_PACKAGE_STATUS_ERROR);
        }

        if (!in_array($orderInfo['dispatch_type'], [OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS, OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY])) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_PACKAGE_DISPATCH_TYPE_ERROR);
        }

        //如果是post请求
        if (RequestHelper::isPost()) {
            $post = RequestHelper::post();
            $post['transaction'] = true;
            $result = OrderService::ship($orderInfo, $post);
            if (is_error($result)) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_SEND_ERROR, $result['message']);
            }

            //添加操作日志
            $model = new OrderModel();
            $express = CoreExpressModel::findOne($post['express_id']);
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_OP_SEND_PACKAGE,
                OrderLogConstant::getText(OrderLogConstant::ORDER_OP_SEND_PACKAGE),
                $orderId,
                [
                    'log_data' => $post,
                    'log_primary' => $model->getLogAttributeRemark([
                        'send' => [
                            'order_no' => $orderInfo['order_no'],
                            'dispatch_type' => OrderDispatchExpressConstant::getText($orderInfo['dispatch_type']),
                            'express' => $express->name,
                            'express_sn' => $post['express_sn'],
                            'order_goods_id' => $post['order_goods_id'],
                            'send_type' => RequestHelper::postInt('more_package') == 1 ? '分包裹' : "整单"
                        ]
                    ])
                ]
            );

            return $this->success($result);
        }

        //包裹
        array_walk($orderInfo['orderGoods'], function (&$orderGoods) {
            //查找包裹
            $orderGoods['package'] = false;
            $packageId = $orderGoods['package_id'];
            $orderGoods['can_send'] = 1;
            $orderGoods['package_status'] = '未发货';

            if ($packageId > 0) {
                $orderGoods['package'] = OrderPackageModel::getPackageById($packageId);
                $orderGoods['can_send'] = 0;
                $orderGoods['package_status'] = $orderGoods['package']['no_express'] == 1 ? '无需物流' : '已发货';
            } else {
                if (!empty($orderGoods['refund_type']) && $orderGoods['refund_status'] >= 0) {
                    $orderGoods['can_send'] = 0;
                    $orderGoods['package_status'] = '维权中';
                }
            }
        });

        $return = [
            'buyer_name' => $orderInfo['buyer_name'],
            'buyer_mobile' => $orderInfo['buyer_mobile'],
            'address' => $orderInfo['address_state'] . ' ' . $orderInfo['address_city'] . ' ' . $orderInfo['address_area'] . ' ' . $orderInfo['address_detail'],
            'order_goods' => array_values($orderInfo['orderGoods']),
            'express' => CoreExpressModel::getAll(false),
            'buyer_remark' => $orderInfo['buyer_remark'],
            'remark' => $orderInfo['remark'],
        ];

        // 获取支持同城配送方式
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {
            $intracitySetting = ShopSettings::get('dispatch.intracity');
            $dispatchInfo = [
                'enable' => $intracitySetting['enable'],
                'merchant' => $intracitySetting['enable'] && $intracitySetting['merchant'] ? 1 : 0,
            ];
            $return['dispatch'] = $dispatchInfo;
        }

        return $this->success($return);
    }

    /**
     * 批量发货
     * @return \yii\web\Response
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBatchSend(): \yii\web\Response
    {
        $post = [
            'no_express' => RequestHelper::postInt('no_express', 0),
            'express_id' => RequestHelper::postInt('express_id'),
            'express_sn' => RequestHelper::post('express_code'),
            'remark' => RequestHelper::post('remark')
        ];

        $orderIds = RequestHelper::postArray('order_ids');
        if (empty($orderIds)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_BATCH_SEND_ORDER_ID_EMPTY_ERROR);
        }

        if ($post['no_express'] == 0) {
            if ((empty($post['express_id']) || empty($post['express_sn']))) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_BATCH_SEND_EXPRESS_ID_EMPTY_ERROR);
            }

            $express = CoreExpressModel::getAll();
            if (!isset($express[$post['express_id']])) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_BATCH_SEND_EXPRESS_ERROR);
            }
        }

        //所有待发货订单商品信息
        $params = [
            'select' => 'o.status, o.order_no, o.dispatch_type, o.address_state, o.address_city, o.address_area, o.address_info, o.is_refund, o.send_time,
                        og.id, og.order_id, refund.status as refund_status, refund.refund_type, og.member_id, og.package_id,o.order_type',
            'where' => [
                'o.id' => $orderIds,
                'o.status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND,
            ],
            'andWhere' => [
                ['<>', 'o.is_refund', OrderConstant::IS_REFUND_YES] //维权中的订单不能发货
            ],
            'alias' => 'og',
            'leftJoins' => [
                [OrderModel::tableName() . ' o', 'o.id = og.order_id'],
                [OrderRefundModel::tableName() . 'refund', 'refund.order_id = o.id']
            ]
        ];

        //需要发货的订单
        $orders = [];
        //组合订单信息
        OrderGoodsModel::getColl($params, [
            'callable' => function ($row) use (&$orders) {
                if (isset($orders[$row['order_id']])) {
                    $orders[$row['order_id']]['order_goods_id'][] = $row['id'];
                    $orders[$row['order_id']]['order_goods'][] = [
                        'id' => $row['id'],
                        'package_id' => $row['package_id'],
                        'refund_status' => $row['refund_status'],
                        'refund_type' => $row['refund_type']
                    ];

                    //未发货订单商品id
                    if ($row['package_id'] <= 0) {
                        if (!empty($orders[$row['order_id']]['no_package_order_goods_id'])) {
                            $orders[$row['order_id']]['no_package_order_goods_id'][] = $row['id'];
                        } else {
                            $orders[$row['order_id']]['no_package_order_goods_id'] = [$row['id']];
                        }
                    }
                } else {
                    $orders[$row['order_id']] = [
                        'id' => $row['order_id'],
                        'member_id' => $row['member_id'],
                        'order_type' => $row['order_type'],
                        'order_no' => $row['order_no'],
                        'status' => $row['status'],
                        'dispatch_type' => $row['dispatch_type'],
                        'is_refund' => $row['is_refund'],
                        'address_state' => $row['address_state'],
                        'address_city' => 'address_city',
                        'address_area' => 'address_area',
                        'address_detail' => 'address_detail',
                        'send_time' => $row['send_time'],
                        'order_goods_id' => [$row['id']],
                        'orderGoods' => [
                            [
                                'id' => $row['id'],
                                'package_id' => $row['package_id'],
                                'refund_status' => $row['status'] ?: 0,
                                'refund_type' => $row['refund_type'] ?: 0
                            ]
                        ]
                    ];

                    if ($row['package_id'] <= 0) {
                        $orders[$row['order_id']]['no_package_order_goods_id'] = [$row['id']];
                    }
                }
            }]);

        if (empty($orders)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_BATCH_SEND_NO_SEND_ORDER_ERROR);
        }

        //批量发货
        $failIds = '';
        foreach ($orders as $order) {
            try {
                if (empty($order['no_package_order_goods_id'])) {
                    throw new Exception();
                }
                $post['order_goods_id'] = $order['no_package_order_goods_id'];
                $result = OrderService::ship(order, $post);
                if (is_error($result)) {
                    throw new \Exception($result['message']);
                }
            } catch (Exception $e) {
                $failIds .= "{$order['order_no']}, ";
            }
        }

        if (!empty($failIds)) {
            $failIds = rtrim($failIds, ', ');
            throw new OrderException(OrderException::ORDER_MANAGE_OP_BATCH_SEND_FAIL_ORDER_NO_ERROR, "发货失败的订单编号：{$failIds}");
        }

        return $this->success();
    }

    /**
     * 修改物流
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeSend(): \yii\web\Response
    {
        $packageData = RequestHelper::post('package_data');
        if (empty($packageData)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_SEND_POST_PARAMS_ERROR);
        }

        $packageIds = array_column($packageData, 'package_id');
        if (empty($packageIds)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_SEND_POST_PACKAGE_ID_EMPTY_PARAMS_ERROR);
        }

        foreach ($packageData as $index => $value) {
            $packageId = $value['package_id'];
            $packageIndex = $index + 1;

            $data = [
                'no_express' => (int)$value['no_express'],
                'remark' => $value['remark'] ?: '',
                'express_name' => $value['express_name'] ?: '',
            ];

            if (!$data['no_express']) {
                $data['express_id'] = (int)$value['express_id'];
                $data['express_sn'] = $value['express_sn'];

                if (!$data['express_id']) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_SEND_POST_PLACE_SELECT_EXPRESS_COMPANY_ERROR, "请选择包裹-{$packageIndex}中的物流公司！");
                }

                if (empty($data['express_sn'])) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_SEND_POST_PLACE_SELECT_EXPRESS_NUMBER_ERROR, "请填写包裹-{$packageIndex}中的物流单号！");
                }
            }

            OrderPackageModel::updateAll($data, ['id' => $packageId]);
            //添加操作日志
            $package = OrderPackageModel::findOne(['id' => $packageId]);
            $orderInfo = OrderModel::findOne($package->order_id);
            $express = CoreExpressModel::findOne($value['express_id']);

            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_OP_CHANGE_SEND,
                OrderLogConstant::getText(OrderLogConstant::ORDER_OP_CHANGE_SEND),
                $value['package_id'],
                [
                    'log_primary' => $orderInfo->getLogAttributeRemark([
                        'change_express' => [
                            'order_no' => $orderInfo->order_no,
                            'express' => $express->name,
                            'express_sn' => $value['express_sn'],
                        ]
                    ])
                ]
            );
            //TODO 青岛开店星信息技术有限公司 消息通知
        }

        return $this->success();
    }

    /**
     * 获取已发货包裹列表
     * @return \yii\web\Response
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetPackage(): \yii\web\Response
    {
        $orderId = RequestHelper::getInt('order_id');
        $packageId = RequestHelper::getInt('package_id');

        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_GET_PACKAGE_LIST_GET_PARAMS_ERROR);
        }

        $order = OrderModel::getOrderAndOrderGoods($orderId, 0);
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_GET_PACKAGE_LIST_ORDER_NOT_FOUND_ERROR);
        }

        //已发货的包裹
        $packages = OrderModel::getPackages($orderId, $packageId, $order);
        if (empty($packages)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_GET_PACKAGE_LIST_NOT_SEND_GOODS_ERROR);
        }

        //所有物流公司
        $express = CoreExpressModel::getALL(false);

        $return = [
            'packages' => [],
            'express' => $express,
            'buyer_name' => $order['buyer_name'],
            'buyer_mobile' => $order['buyer_mobile'],
            'address_state' => $order['address_state'],
            'address_city' => $order['address_city'],
            'address_area' => $order['address_area'],
            'address' => $order['address_detail'],
        ];

        foreach ($packages as $package) {

            $packageInfo = [
                'id' => $package['id'],
                'order_id' => $package['order_id'],
                'no_express' => $package['no_express'],
                'express_id' => $package['express_id'],
                'express_sn' => $package['express_sn'],
                'express_name' => $package['express_name'],
                'remark' => $package['remark'],
                'goods_count' => substr_count($package['order_goods_ids'], ',') + 1,
                'send_time' => $package['send_time'],
                'is_city_distribution' => $package['is_city_distribution'],
                'city_distribution_type' => $this->dispatchMap[$package['city_distribution_type']],
                'order_goods' => []
            ];

            //组合订单商品
            foreach ($order['orderGoods'] as $orderGoodsIndex => $orderGoodsItem) {
                if ($orderGoodsItem['package_id'] == $package['id']) {
                    $orderGoods = [
                        'thumb' => $orderGoodsItem['thumb'],
                        'title' => $orderGoodsItem['title'],
                        'option_title' => $orderGoodsItem['option_title'],
                        'total' => $orderGoodsItem['total'],
                        'status' => $orderGoodsItem['status']
                    ];
                    $packageInfo['order_goods'][] = $orderGoods;
                }
            }

            $return['packages'][] = $packageInfo;
        }

        return $this->success($return);
    }

    /**
     * 取消发货
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCancelSend(): \yii\web\Response
    {
        $packageId = RequestHelper::post('package_id');
        $orderId = RequestHelper::postint('order_id');
        if ($packageId <= 0 || $orderId <= 0) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CANCEL_SEND_PARAMS_ERROR);
        }

        $reason = RequestHelper::post('reason');
        $result = OrderService::cancelShip($orderId, $packageId, $reason);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CANCEL_SEND_ERROR, $result['message']);
        }

        return $this->success();
    }

    /**
     * 确认收货
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionFinish(): \yii\web\Response
    {
        $orderId = RequestHelper::postInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_FINISH_PARAMS_ERROR);
        }

        $order = OrderModel::getOrderAndOrderGoods($orderId, 0);
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_FINISH_ORDER_NOT_FOUND_PARAMS_ERROR);
        }

        if ($order['status'] != OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_FINISH_ORDER_STATUS_ERROR);
        }

        $result = OrderService::complete($order);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_FINISH_ERROR, $result['message']);
        }

        //添加操作日志
        $model = new OrderModel();

        LogModel::write(
            $this->userId,
            OrderLogConstant::ORDER_OP_FINISH,
            OrderLogConstant::getText(OrderLogConstant::ORDER_OP_FINISH),
            $orderId,
            [
                'log_primary' => $model->getLogAttributeRemark([
                    'finish' => [
                        'order_no' => $order['order_no'],
                        'goods_info' => $order['orderGoods'],
                        'time' => DateTimeHelper::now()
                    ]
                ])
            ]
        );

        return $this->success();
    }

    /**
     * 后台确认付款
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay(): \yii\web\Response
    {
        $id = RequestHelper::postInt('order_id');
        $presellPayType = RequestHelper::post('presell_pay_type'); // 预售支付类型  0||'' 非  1定金 2尾款 3全款
        if (empty($id)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_PARAMS_ERROR);
        }

        // 外层开启事务
        $tr = \Yii::$app->db->beginTransaction();
        try {

            // 不是支付定金
            if ($presellPayType != 1) {
                $order = OrderModel::findOne(['id' => $id]);
                if (empty($order)) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_NOT_FOUND_ERROR);
                }

                if ($order->status != OrderStatusConstant::ORDER_STATUS_WAIT_PAY) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_STATUS_ERROR);
                }

                $order->setAttributes([
                    'pay_time' => DateTimeHelper::now(),
                    'pay_type' => OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_ADMIN_CONFIRM,
                    'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND
                ]);

                //修改订单状态
                if (!$order->save()) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR);
                }

                //修改订单商品
                OrderGoodsModel::updateAll([
                    'status' => OrderStatusConstant::ORDER_STATUS_WAIT_SEND
                ], [
                    'order_id' => $id,
                ]);

                $goodsInfo = $order->goods_info;
                if (empty($goodsInfo)) {
                    // 订单商品不存在
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_EDIT_STATUS_ERROR);
                }

                // 修改虚拟卡密数据表状态
                if ($order->order_type == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT) {
                    $orderVirtualAccountData = VirtualAccountOrderMapModel::getMapList($id);
                    if ($orderVirtualAccountData) {
                        VirtualAccountDataModel::updateStatus($orderVirtualAccountData, VirtualAccountDataConstant::ORDER_VIRTUAL_ACCOUNT_DATA_SUCCESS);
                    }
                }

                $goodsInfo = Json::decode($goodsInfo);

                // 判断虚拟商品
                // 判断核销
                if ($order->dispatch_type == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
                    // 核销的不发货,直接待收货
                    OrderService::ship($order, [
                        'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                        'no_express' => 1 // 不需要快递
                    ]);

                } else {
                    // 不是核销的判断是否自动发货
                    if (GoodsService::checkOrderGoodsVirtualType($order)) {
                        if ($goodsInfo[0]['auto_deliver'] == GoodsVirtualConstant::GOODS_VIRTUAL_AUTO_DELIVERY) {
                            // 虚拟商品&&自动发货 自动完成
                            $virtualRes = OrderService::ship($order, [
                                'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                                'no_express' => 1 // 不需要快递
                            ]);
                            if (is_error($virtualRes)) {
                                throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_VIRTUAL_SEND_FAIL, $virtualRes['message']);
                            }
                            $virtualRes = OrderService::complete($order, 2);
                        } else {
                            // 虚拟商品&&不自动发货
                            $virtualRes = OrderService::ship($order, [
                                'order_goods_id' => array_column($goodsInfo, 'order_goods_id'),
                                'no_express' => 1 // 不需要快递
                            ]);
                        }

                        if (is_error($virtualRes)) {
                            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_VIRTUAL_SEND_FAIL, $virtualRes['message']);
                        }
                    }
                }

                // 分销
                $isCommission = true;

                // 订单参加的活动
                if (StringHelper::isJson($order['extra_price_package'])) {
                    $order['extra_price_package'] = Json::decode($order['extra_price_package']);
                }
                // 订单参加的活动
                $orderActivity = array_keys($order['extra_price_package']);
                if (StringHelper::isJson($order['extra_discount_rules_package'])) {
                    $order['extra_discount_rules_package'] = Json::decode($order['extra_discount_rules_package']);
                }
                $discountRules = $order['extra_discount_rules_package'];
                // 需要检查是否参与分销的活动
                $checkCommissionActivity = [
                    'presell', // 预售
                    'seckill', // 秒杀
                    'groups',//拼团
                ];

                // 取活动交集
                $activityIntersect = array_intersect($orderActivity, $checkCommissionActivity);

                // 如果有活动  检测活动是否支持分销
                if (!empty($activityIntersect)) {
                    // 如果包含预售
                    if (in_array('presell', $activityIntersect)) {
                        // 不支持
                        $discountRules[0]['presell']['is_commission'] == 0 && $isCommission = false;
                    } else {
                        // 其他活动
                        // 取订单参与的活动
                        $activity = array_column($discountRules, $activityIntersect[0]);
                        // 不支持
                        if ($activity[0]['rules']['is_commission'] == 0) {
                            $isCommission = false;
                        }
                    }
                }

                // 积分商城不支持分销
                if ($order->activity_type == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                    $isCommission = false;
                }

                if ($isCommission) {
                    CommissionService::orderPay($id, $order->member_id);
                }


                // 消费奖励
                $res = ConsumeRewardLogService::sendReward($order->member_id, $order->id, 1);

                // 购物奖励
                $res = ShoppingRewardLogModel::sendReward($order->member_id, $order->id, 0);

                $extraPackage = Json::decode($order->extra_package) ?? [];

                //添加操作日志
                $orderGoods = OrderGoodsModel::findAll(['order_id' => $id]);

                LogModel::write(
                    $this->userId,
                    OrderLogConstant::ORDER_OP_PAY,
                    OrderLogConstant::getText(OrderLogConstant::ORDER_OP_PAY),
                    $id,
                    [
                        'log_primary' => $order->getLogAttributeRemark([
                            'pay' => [
                                'order_no' => $order['order_no'],
                                'goods_info' => $orderGoods
                            ]
                        ]),
                    ]
                );
            }

            // 不是尾款
            if ($presellPayType != 2) {
                //减库存
                $result = GoodsService::updateQty(true, $id, [], GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT, [
                    'transaction' => false,
                    'reason' => '商家手动付款减库存',
                    'presell_activity_id' => $presellActivity['id'] ?? 0, // 预售活动id
                ]);

                if (is_error($result)) {
                    throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_GOODS_STOCK_ERROR, $result['message']);
                }
            }

            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new OrderException(OrderException::ORDER_MANAGE_OP_PAY_ORDER_ERROR, $throwable->getMessage());
        }

        return $this->success();
    }

    /**
     * 关闭订单
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionClose()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw  new OrderException(OrderException::ORDER_MANAGE_OP_CLOSE_PARAMS_ERROR);
        }
        $isRefundFront = RequestHelper::post('is_refund_front', 0); // 是否退还定金
        $closePresellActivity = RequestHelper::post('close_presell_activity', 0); // 停止预售关闭订单

        $remark = RequestHelper::post('remark');

        $order = OrderModel::findOne(['id' => $id]);
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CLOSE_ORDER_NOT_FOUND_ERROR);
        }

        //判断状态是否正确
        if ($order['status'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CLOSE_ORDER_STATUS_ERROR);
        }

        //关闭订单
        $result = OrderService::closeOrder($order, OrderConstant::ORDER_CLOSE_TYPE_SELLER_CLOSE, $this->userId, [
            'cancel_reason' => $remark,
            'is_refund_front' => $isRefundFront, // 预售订单关闭 是否退还定金
            'close_presell_activity' => $closePresellActivity, // 预售订单关闭 是否退还定金
        ]);

        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CLOSE_ORDER_ERROR, $result['message']);
        }

        $orderGoods = OrderGoodsModel::findAll(['order_id' => $id]);

        $logPrimary = [
            'close' => [
                'id' => $id,
                'order_no' => $order['order_no'],
                'goods_info' => $orderGoods
            ]
        ];

        //添加操作日志
        LogModel::write(
            $this->userId,
            OrderLogConstant::ORDER_OP_CLOSE,
            OrderLogConstant::getText(OrderLogConstant::ORDER_OP_CLOSE),
            $id,
            [
                'log_primary' => $order->getLogAttributeRemark($logPrimary),
            ]
        );

        return $this->result($result);
    }

    /**
     * 关闭订单并退款
     * @return \yii\web\Response
     * @throws OrderException
     * @throws \yii\db\Exception|\Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCloseAndRefund(): \yii\web\Response
    {
        $orderId = RequestHelper::post('order_id');
        $password = RequestHelper::post('password');
        $refundReason = RequestHelper::post('refund_reason', '');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_PARAMS_ERROR);
        }

        //验证密码
        if (!UserModel::checkUserPassword($this->userId, $password)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_PASSWORD_ORDER);
        }

        $order = OrderModel::getOrderAndOrderGoods($orderId, 0);
        if (empty($order)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_ORDER_NOT_FOUND_ORDER, "订单#{$orderId}不存在");
        }

        // 虚拟卡密不能退款
        if ($order['order_type'] == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT) {
            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_ORDER_NOT_FOUND_ORDER_VIRTUAL_ACCOUNT);
        }

        //货到付款不能退款
//        if ($order['pay_type'] == OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_DELIVERY) {
//            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_DELIVERY_NOT_CLOSE_REFUND_ORDER);
//        }

        $result = OrderService::closeAndRefund($order, $this->userId, ['refund_reason' => $refundReason]);
        if (is_error($result)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CLOSE_AND_REFUND_ORDER, $result['message']);
        }


        // 消费奖励
        ConsumeRewardLogService::refundBack($order['member_id'], $orderId);


        //添加操作日志
        $model = new OrderModel();
        LogModel::write(
            $this->userId,
            OrderLogConstant::ORDER_OP_CLOSE_AND_REFUND,
            OrderLogConstant::getText(OrderLogConstant::ORDER_OP_CLOSE_AND_REFUND),
            $orderId,
            [
                'log_primary' => $model->getLogAttributeRemark([
                    'close_and_refund' => [
                        'order_no' => $order['order_no'],
                        'refund_price' => $order['pay_price']
                    ]
                ])
            ]
        );

        return $this->success();
    }

    /**
     * 订单改价
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangePrice(): \yii\web\Response
    {
        $post = RequestHelper::post();
        if (empty($post['order_id'])) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_PARAMS_ERROR);
        }

        //获取订单信息
        $orderInfo = OrderModel::getOrderAndOrderGoods($post['order_id'], 0);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_NOT_FOUND_ERROR);
        }

        //判断订单状态
        if ($orderInfo['status'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_STATUS_ERROR);
        }

        //改价次数过多
        if ($orderInfo['change_price_count'] >= 10) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_ORDER_CHANGE_NUMBER_ERROR);
        }

        //是否修改过
        $changed = false;

        //整单变动金额
        $priceChangeTotal = 0;

        //有效改价商品
        $validChangeItems = [];

        //主订单需要修改的数据
        $orderUpdate = [];

        //日志商品
        $logGoodsInfo = [];

        //存在商品价格修改
        if (!empty($post['change_item'])) {
            $orderGoods = ArrayHelper::index($orderInfo['orderGoods'], 'id');

            foreach ($post['change_item'] as $itemIndex => $item) {
                if (empty($item['price_change'])) continue;
                //上一次改价变动金额
                $oldPriceChange = $orderGoods[$item['id']]['price_change'];

                //判断价格有变动
                if ($oldPriceChange != $item['price_change']) {
                    //商品改价后价格
                    $goodsPrice = max(round2($orderGoods[$item['id']]['price_original'] + $item['price_change']), 0);
                    $item['price'] = $orderGoods[$item['id']]['price'] = $goodsPrice;

                    //商品价格变动金额
                    $goodsPriceChange = round2($item['price'] - $orderGoods[$item['id']]['price_original'], 2);

                    $item['price_change'] = $orderGoods[$item['id']]['price_change'] = $goodsPriceChange;

                    //累计整单变动金额
                    $priceChangeTotal = round2($priceChangeTotal + ($item['price_change'] - $oldPriceChange), 2);

                    $validChangeItems[] = $item;

                    !$changed && $changed = true;
                    $logGoodsInfo[] = [
                        'title' => $orderGoods[$item['id']]['title'],
                        'price' => $goodsPrice
                    ];
                }
            }
        }

        //存在运费修改
        if ($post['dispatch_price'] != '' && (float)$post['dispatch_price'] != (float)$orderInfo['dispatch_price']) {
            // 虚拟商品/虚拟卡密/到店核销. 不支持改运费
            if (in_array($orderInfo['order_type'], $this->disableChangeDispatchPrice)) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_ORDER_TYPE_DISABLE_CHANGE_DISPATCH_PRICE);
            }

            // 到店核销物流方式, 不支持修改运费
            if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
                throw new OrderException(OrderException::ORDER_MANAGE_OP_DISPATCH_TYPE_DISABLE_CHANGE_DISPATCH_PRICE);
            }

            //当前运费
            $orderUpdate['dispatch_price'] = $post['dispatch_price'];
            //原始运费减掉当前运费就是改变的运费
            $orderUpdate['change_dispatch'] = round2($post['dispatch_price'] - $orderInfo['original_dispatch_price']);

            //和上次实际支付的运费比较差值
            $actualDispatchChangePrice = round2($post['dispatch_price'] - $orderInfo['dispatch_price']);

            //累积整单变动金额
            $priceChangeTotal = round2($priceChangeTotal + $actualDispatchChangePrice);
            !$changed && $changed = true;
        }

        //没有任何改变
        if (!$changed) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_NO_CHANGE_ERROR);
        }

        //订单改价后金额
        $orderUpdate['pay_price'] = round2($orderInfo['pay_price'] + $priceChangeTotal, 2);

        if ($orderUpdate['pay_price'] <= 0) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_PRICE_TOO_SMALL_ERROR);
        }

        if ($post['total_price'] != $orderUpdate['pay_price']) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_PRICE_ERROR);
        }

        //订单改价变动金额
        $orderUpdate['change_price'] = round2($orderInfo['change_price'] + $priceChangeTotal, 2);
        //订单改价次数
        $orderUpdate['change_price_count'] = new Expression('change_price_count + 1');

        $tr = \Yii::$app->db->beginTransaction();
        try {

            $result = OrderModel::updateAll($orderUpdate, ['id' => $post['order_id']]);
            if (!$result) {
                throw new \Exception('修改失败');
            }

            //订单商品改价
            if (!empty($validChangeItems)) {
                foreach ($validChangeItems as $v) {
                    $updateGoods = [
                        'price' => $v['price'],
                        'price_change' => $v['price_change']
                    ];

                    $result = OrderGoodsModel::updateAll($updateGoods, ['id' => $v['id']]);
                    if (!$result) {
                        throw new \Exception('操作失败[订单商品保存失败]');
                    }
                }
            }
            $extInfo = [];
            if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
                $extInfo['pay_credit'] = $orderInfo['pay_credit'];
            }

            //改价记录
            $recordModel = new OrderChangePriceLogModel();
            $recordModel->setAttributes([
                'uid' => $this->userId ?: 0,
                'order_id' => $post['order_id'],
                'change_price' => $orderUpdate['change_price'],
                'before_price' => $orderInfo['pay_price'],
                'after_price' => $orderUpdate['pay_price'],
                'created_at' => DateTimeHelper::now(),
                'ext_info' => Json::encode($extInfo),
            ]);

            if (!$recordModel->save()) {
                throw new \Exception('改价记录同步失败');
            }

            // 重新计算佣金
            CommissionOrderService::calculate($post['order_id'], true);

            //添加操作日志
            $model = new OrderModel();
            LogModel::write(
                $this->userId,
                OrderLogConstant::ORDER_OP_CHANGE_PRICE,
                OrderLogConstant::getText(OrderLogConstant::ORDER_OP_CHANGE_PRICE),
                $post['order_id'],
                [
                    'log_primary' => $model->getLogAttributeRemark([
                        'change_price' => [
                            'order_no' => $orderInfo['order_no'],
                            'change_dispatch_price' => $post['dispatch_price'],
                            'goods_info' => $logGoodsInfo
                        ]
                    ])
                ]
            );
            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_ERROR, $throwable->getMessage());
        }

        return $this->success();
    }

    /**
     * 获取物流
     * @return \yii\web\Response
     * @throws OrderException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetExpress(): \yii\web\Response
    {
        $orderId = RequestHelper::getInt('order_id');
        $packageId = RequestHelper::getInt('package_id');
        if (empty($orderId) || empty($packageId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_GET_EXPRESS_PARAMS_ORDER);
        }

        $orderInfo = OrderModel::findOne(['id' => $orderId]);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_GET_EXPRESS_ORDER_NOT_FOUND_ORDER);
        }

        //包裹信息
        $package = OrderPackageModel::find()
            ->select('package.*, express.name')
            ->where([
                'package.id' => $packageId,
                'package.order_id' => $orderId
            ])
            ->alias('package')
            ->leftJoin(CoreExpressModel::tableName() . ' express', 'express.id=package.express_id')
            ->asArray()->one();

        if (empty($package)) {
            return $this->success();
        }

        //设置物流公司
        OrderPackageModel::setPackage($package);

        $express = [];
        //查询包裹物流信息
        if (!$package['no_express']) {
            $express = CoreExpressModel::queryExpress($package['express_sn'], $package['express_code'], $package['express_encoding'], [
                'buyer_mobile' => $orderInfo->buyer_mobile
            ]);
            $express = CoreExpressModel::decodeExpressDate($express);
        }

        $express['express_com'] = $package['express_name'] ?: $package['name'];
        $express['express_sn'] = $package['express_sn'];
        $express['address_detail'] = $orderInfo->address_detail;

        return $this->success(['data' => $express]);
    }

    /**
     * 获取修改内容
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangePriceDetail()
    {
        $orderId = RequestHelper::get('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_PARAMS_ERROR);
        }

        //获取订单信息
        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_ORDER_NOT_FOUND_ERROR);
        }

        //判断订单状态
        if ($orderInfo['status'] != OrderPaymentTypeConstant::ORDER_PAYMENT_TYPE_NON) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_DETAIL_ORDER_STATUS_ERROR);
        }

        //组装数据
        $data = [
            'dispatch_price' => $orderInfo['dispatch_price'], //运费
            'change_price_count' => $orderInfo['change_price_count'],//改价次数
            'order_goods' => $orderInfo['orderGoods'],//订单商品
            'pay_price' => $orderInfo['pay_price'],//订单需要支付的价格
            'activity_type' => $orderInfo['activity_type'],//订单需要支付的价格
            'dispatch_type' => $orderInfo['dispatch_type'],//运费类型
        ];

        // 积分商城
        if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            $data['pay_credit'] = $orderInfo['pay_credit'];
        }

        return $this->success($data);
    }

    /**
     * 获取订单改价操作记录
     * @return \yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangePriceLog(): \yii\web\Response
    {
        $orderId = RequestHelper::get('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_OP_CHANGE_PRICE_LOG_PARAMS_ERROR);
        }

        $params = [
            'where' => [
                'order_id' => $orderId
            ],
            'orderBy' => 'id desc'
        ];

        //操作员id
        $uids = [];
        $result = OrderChangePriceLogModel::getColl($params, [
            'callable' => function (&$row) use (&$uids) {
                $uids[$row['uid']] = $row['uid'];
                $row['ext_info'] = Json::decode($row['ext_info']);
            }
        ]);

        return $this->success($result);
    }

    /**
     * 修改发票状态
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeInvoiceStatus()
    {
        $orderId = RequestHelper::postInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CHANGE_INVOICE_STATUS_PARAMS_ERROR);
        }

        $orderInfo = OrderModel::findOne($orderId);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_MANAGE_CHANGE_INVOICE_STATUS_ORDER_NOT_FOUND_ERROR);
        }

        $invoice = $orderInfo->invoice_info ?: '';
        if (empty($invoice) || $invoice == '[]') {
            throw new OrderException(OrderException::ORDER_MANAGE_CHANGE_INVOICE_STATUS_INVOICE_INFO_ERROR);
        }
        $invoice = Json::decode($invoice);
        $invoice['status'] = 1;

        $orderInfo->invoice_info = Json::encode($invoice);

        if (!$orderInfo->save()) {
            throw new OrderException(OrderException::ORDER_MANAGE_CHANGE_INVOICE_STATUS_ERROR);
        }

        return $this->success();
    }

    /**
     * 更改订单商家备注
     * @return array|\yii\web\Response
     * @throws OrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdateSellerRemark()
    {
        $orderId = RequestHelper::postInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_MANAGE_DETAIL_PARAMS_ERROR);
        }
        $sellerRemark = RequestHelper::post('seller_remark', '');
        OrderModel::updateAll(['seller_remark' => $sellerRemark], ['id' => $orderId]);

        return $this->result();
    }

}
