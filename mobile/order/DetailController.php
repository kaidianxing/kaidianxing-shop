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

namespace shopstar\mobile\order;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\amap\AmapClient;
use shopstar\constants\form\FormTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderPackageCityDistributionTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\exceptions\order\CommentException;
use shopstar\exceptions\order\OrderException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\form\FormModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\order\DispatchOrderModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;
use shopstar\services\creditShop\CreditShopOrderService;
use shopstar\services\groups\GroupsTeamService;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 手机端订单详情
 * Class DetailController
 * @author 青岛开店星信息技术有限公司
 * @package shop\client\order
 */
class DetailController extends BaseMobileApiController
{
    /**
     * 订单详情
     * @return Response
     * @throws OrderException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): Response
    {
        $orderId = RequestHelper::getInt('order_id');
        if (empty($orderId)) {
            throw new OrderException(OrderException::ORDER_DETAIL_INDEX_PARAMS_ERROR);
        }

        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId);
        if (empty($orderInfo)) {
            throw new OrderException(OrderException::ORDER_DETAIL_INDEX_ORDER_NOT_FOUND_ERROR);
        }

        //获取订单包括信息
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS && $orderInfo['status'] >= OrderStatusConstant::ORDER_STATUS_WAIT_PICK) { //快递订单
            //订单详情取首个包裹id
            $packageId = current(array_unique(array_filter(array_column($orderInfo['orderGoods'], 'package_id'))));
            $express = [];
            if (!empty($packageId)) {
                $package = OrderPackageModel::getPackageById($packageId);
                if (!empty($package) && $package['no_express'] == 0) {
                    $packageExpress = CoreExpressModel::getExpressById($package['express_id']);
                    $expressData = CoreExpressModel::queryExpress($package['express_sn'], $packageExpress['code'], $packageExpress['key'], [
                        'buyer_mobile' => $orderInfo['buyer_mobile']
                    ]);
                    if (!empty($expressData)) {
                        $expressData = CoreExpressModel::decodeExpressDate($expressData);
                        $express = $expressData['data'][0];
                        $express['state_text'] = $expressData['state_text'];
                    }
                }
            }

            $orderInfo['express'] = $express;
            $orderInfo['package_id'] = $packageId;
        }

        // 同城配送获取订单包裹信息
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY && $orderInfo['status'] >= OrderStatusConstant::ORDER_STATUS_WAIT_PICK) {
            $packageId = current(array_unique(array_filter(array_column($orderInfo['orderGoods'], 'package_id'))));
            $express = [];
            if (!empty($packageId)) {
                $package = OrderPackageModel::getPackageById($packageId);
                $express['city_distribution_type'] = $package['city_distribution_type'];
            }

            $orderInfo['express'] = $express;
            $orderInfo['package_id'] = $packageId;
        }
        // 虚拟卡密不需要配送 并获取相关卡密信息展示
        if ($orderInfo['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_NOT_DELIVERY && $orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT) {
            $orderVirtualAccountDataMap = VirtualAccountOrderMapModel::getDetails($orderId);
            if ($orderInfo['status'] == OrderStatusConstant::ORDER_STATUS_SUCCESS) {
                $orderInfo['virtual_account_data'] = $orderVirtualAccountDataMap;
                $orderInfo['to_mailer'] = $orderVirtualAccountDataMap[0]['to_mailer'];
            } else {
                $orderInfo['to_mailer'] = $orderVirtualAccountDataMap[0]['to_mailer'];
                $orderInfo['virtual_account_data'] = [];
            }
            // 获取卡密库邮箱是否开启
            $orderInfo['virtual_account_mailer_setting'] = VirtualAccountModel::checkMailer($orderInfo['goods_info'][0]['virtual_account_id']) ? 1 : 0;

        }

        //处理订单评论状态
        $orderInfo['comment_status'] = 0; //0是可评价
        $commentStatus = array_column($orderInfo['orderGoods'], 'comment_status');
        $commentStatus = array_unique(array_filter($commentStatus));
        if (count($commentStatus) >= 1) {
            $orderInfo['comment_status'] = 1; //不可评价
        }

        //评价设置
        $setting = ShopSettings::get('sysset.trade');
        $orderInfo['comment_setting'] = [
            'order_comment' => $setting['order_comment'],
            'show_comment' => $setting['show_comment'],
            'comment_audit' => $setting['comment_audit']
        ];

        //附加商品信息
        $this->getGoods($orderInfo);
        //维权信息
        $this->getRefund($orderInfo);
        //预计送达时间 骑行时长+30min 24小时制
        $this->getExpectedDeliveryTime($orderInfo);
        //同城配送显示文案
        $this->getIntracityDispatchText($orderInfo);

        // 拼团
        if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_GROUPS) {
            $groupsTeamInfo = GroupsTeamService::getGroupsInfo($orderId);
            $orderInfo['groups_data'] = $groupsTeamInfo[$orderId]['team'] ?? [];
        }

        //表单
        $orderInfo['form'] = FormModel::get(FormTypeConstant::FORM_TYPE_ORDER, $this->memberId, false, $orderId);


        return $this->success($orderInfo);
    }

    /**
     * 额外的商品信息
     * @param $orderInfo
     * @author 青岛开店星信息技术有限公司
     */
    private function getGoods(&$orderInfo)
    {
        $goodsInfo = $orderInfo['goods_info'];

        foreach ($goodsInfo as $goodsInfoItem) {
            StringHelper::isJson($goodsInfoItem['ext_field']) && $goodsInfoItem['ext_field'] = Json::decode($goodsInfoItem['ext_field']);
            foreach ($orderInfo['orderGoods'] as &$orderGoodsInfo) {
                if ($goodsInfoItem['goods_id'] == $orderGoodsInfo['goods_id']) {
                    //商品维权规则
                    $orderGoodsInfo['refund_rule'] = [
                        'refund' => $goodsInfoItem['ext_field']['refund'],//是否可退款
                        'return' => $goodsInfoItem['ext_field']['return'],//是否可退货
                        'exchange' => $goodsInfoItem['ext_field']['exchange'],//是否可换货
                    ];
                    $orderGoodsInfo['type'] = $goodsInfoItem['type'];
                    $orderGoodsInfo['auto_deliver'] = $goodsInfoItem['auto_deliver'];
                    $orderGoodsInfo['auto_deliver_content'] = $goodsInfoItem['auto_deliver_content'];

                    if ($orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON) {
                        // 积分商城优惠券订单 单独判断
                        $res = CreditShopOrderService::checkRefund($orderInfo['id']);

                        // 不可维权
                        if (is_error($res)) {
                            $orderGoodsInfo['refund_rule']['refund'] = '0';
                        }
                    }

                }
            }
        }
    }

    /**
     * 获取维权
     * @param $orderInfo
     * @author 青岛开店星信息技术有限公司
     */
    private function getRefund(&$orderInfo)
    {
        $orderInfo['refund_setting'] = ShopSettings::get('sysset.refund');

        //是否可以申请整单维权
        $orderInfo['refund_setting']['refund_time_status'] = 0;

        // 判断积分商城订单的设置
        if ($orderInfo['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            // 获取积分商城设置
            $creditSet = ShopSettings::get('credit_shop');
            // 走系统默认
            if ($creditSet['refund_type'] == 0) {
                if ($orderInfo['refund_setting']['apply_type'] == 2) {

                    $refundTime = strtotime($orderInfo['created_at']) + intval($orderInfo['refund_setting']['apply_days'] * 86400);

                    $orderInfo['refund_setting']['refund_time_status'] = $refundTime <= time() ? 0 : 1;
                }
            } else {
                // 已完成的允许售后
                if ($creditSet['finish_order_refund_type'] == 1) {
                    // 计算天数
                    $refundTime = strtotime($orderInfo['created_at']) + intval($creditSet['finish_order_refund_days'] * 86400);
                    $orderInfo['refund_setting']['refund_time_status'] = $refundTime <= time() ? 0 : 1;
                }
            }

        } else {
            if ($orderInfo['refund_setting']['apply_type'] == 2) {

                $refundTime = strtotime($orderInfo['created_at']) + intval($orderInfo['refund_setting']['apply_days'] * 86400);

                $orderInfo['refund_setting']['refund_time_status'] = $refundTime <= time() ? 0 : 1;
            }
        }


    }

    /**
     * 获取预计送达时间
     * @param $orderInfo
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function getExpectedDeliveryTime(&$orderInfo)
    {
        // 未发货不显示送达时间
        if (empty($orderInfo['send_time'])) {
            $orderInfo['expect_delivery_time'] = '';
            return true;
        }

        //TODO
        $dispatchOrderInfo = DispatchOrderModel::find()
            ->where([
                'order_no' => $orderInfo['order_no'],
            ])
            ->first();

        // 买家坐标
        $buyerAddress = Json::decode($orderInfo['address_info']);
        $buyerLat = $buyerAddress['lat'] ?? '';
        $buyerLng = $buyerAddress['lng'] ?? '';
        if (empty($buyerLat) || empty($buyerLng)) {
            $orderInfo['expect_delivery_time'] = '';
            return true;
        }
        $destination = $buyerLng . ',' . $buyerLat;

        // 卖家坐标
        $shopAddress = ShopSettings::get('contact.address');

        $shopLat = $shopAddress['lat'] ?? '';
        $shopLng = $shopAddress['lng'] ?? '';
        if (empty($shopLat) || empty($shopLng)) {
            $orderInfo['expect_delivery_time'] = '';
            return true;
        }
        $origin = $shopLng . ',' . $shopLat;

        $result = AmapClient::getDirectionBicycling($origin, $destination);

        if (is_error($result)) {
            $orderInfo['expect_delivery_time'] = '';
            return true;
        }

        $orderInfo['expect_delivery_time'] = date('H:i', strtotime($orderInfo['send_time']) + $result + 1800);
    }

    private function getIntracityDispatchText(&$originInfo)
    {
        // 判断是否是同城配送
        if ($originInfo['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {
            return true;
        }

        // 待发货
        if ($originInfo['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            $originInfo['intracity_status_text'] = '订单准备完成，正在为您发货';
            return true;
        }

        // 待收货
        if ($originInfo['status'] > OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            // 获取配送订单信息
            //TODO
            $dispatchOrderInfo = DispatchOrderModel::find()
                ->where([
                    'order_no' => $originInfo['order_no'],
                ])
                ->first();

            if (empty($dispatchOrderInfo)) {
                $originInfo['intracity_status_text'] = '';
                return true;
            }

            // 达达
            if ($dispatchOrderInfo['type'] == OrderPackageCityDistributionTypeConstant::DADA) {
                switch ($dispatchOrderInfo['status']) {
                    case 1:
                        $originInfo['intracity_status_text'] = '商家已接单';
                        break;
                    case 2:
                        $originInfo['intracity_status_text'] = '骑手已接单';
                        break;
                    case 3:
                    case 100:
                        $originInfo['intracity_status_text'] = '骑手已到店';
                        break;
                    case 4:
                        $originInfo['intracity_status_text'] = '商品已送达';
                        break;
                    case 5:
                        $originInfo['intracity_status_text'] = '订单已取消';
                        break;
                    default:
                        $originInfo['intracity_status_text'] = '';
                }
            }
        }

        return true;
    }


    /**
     * 包裹查询
     * @return Response
     * @throws Exception
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetExpress()
    {
        $orderId = RequestHelper::getInt('order_id');
        $packageId = RequestHelper::getInt('package_id');
        if (empty($orderId) || $packageId <= 0) {
            throw new CommentException(CommentException::ORDER_CLIENT_GET_EXPRESS_PARAMS_ORDER);
        }

        $orderInfo = OrderModel::findOne(['id' => $orderId]);
        if (empty($orderInfo)) {
            throw new CommentException(CommentException::ORDER_CLIENT_GET_EXPRESS_ORDER_NOT_FOUND_ORDER);
        }

        //包裹信息
        $package = OrderPackageModel::find()->where([
            'id' => $packageId,
            'order_id' => $orderId
        ])->asArray(['*', 'express_name'])->one();

        if (empty($package)) {
            return $this->success();
        }

        //设置物流公司
        OrderPackageModel::setPackage($package);

        //包裹内第一个商品信息
        $orderGoodsIds = explode(',', $package['order_goods_ids']);
        $orderGoods = OrderGoodsModel::findAll([
            'id' => $orderGoodsIds,
            'order_id' => $orderId
        ]);

        $express = [];
        //查询包裹物流信息
        if (!$package['no_express']) {
            $express = CoreExpressModel::queryExpress($package['express_sn'], $package['express_code'], $package['express_encoding'], [
                'buyer_mobile' => $orderInfo->buyer_mobile
            ]);
            $express = CoreExpressModel::decodeExpressDate($express);
        }

        $return = [
            'id' => $packageId,
            'express_name' => $package['express_name'],
            'express_sn' => $package['express_sn'],
            'no_express' => $package['no_express'],
            'finish_time' => $package['finish_time'],
            'order_goods' => $orderGoods,
            'express' => !empty($express) ? $express : null, //适配安卓 ios
            'address' => $orderInfo['address_state'] . $orderInfo['address_city'] . $orderInfo['address_area'] . $orderInfo['address_detail']
        ];

        return $this->success($return);
    }

    /**
     * 包裹列表
     * @return Response
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExpressPackageList()
    {
        $orderId = RequestHelper::getInt('order_id');
        if (empty($orderId)) {
            return $this->success(['list' => []]);
        }

        //包裹信息
        $packages = OrderPackageModel::find()
            ->select(['id', 'order_goods_ids', 'no_express', 'express_id', 'express_sn', 'remark', 'send_time', 'finish_time'])
            ->where(['order_id' => $orderId])
            ->indexBy('id')
            ->asArray()
            ->all();

        if (empty($packages)) {
            return $this->success();
        }

        $firstPackageOrderGoodsIds = [];
        $orderGoodsIdMap = []; //为了把订单商品信息填充到包裹内

        foreach ($packages as &$package) {
            OrderPackageModel::setPackage($package);
            $orderGoodsIds = explode(',', $package['order_goods_ids']);
            $package['goods_count'] = count($orderGoodsIds);

            //包裹内第一个订单商品id
            $firstPackageOrderGoodsIds = array_merge($firstPackageOrderGoodsIds, $orderGoodsIds);
            foreach ($orderGoodsIds as $orderGoodsIdsIndex => $orderGoodsIdsItem) {
                $orderGoodsIdMap[$orderGoodsIdsItem] = $package['id'];
            }

            //物流信息查询
            if (!$package['no_express']) {
                $package['express'] = CoreExpressModel::queryExpress($package['express_sn'], $package['express_code'], $package['express_encoding']);
                $package['express'] = CoreExpressModel::decodeExpressDate($package['express']);
            }

            unset($package['order_goods_ids'], $package['express_id'], $package['express_code']);
        }

        //商品信息
        if (!empty($firstPackageOrderGoodsIds)) {
            OrderGoodsModel::getColl([
                'alias' => 'og',
                'leftJoin' => [GoodsModel::tableName() . ' g', 'g.id=og.goods_id'],
                'select' => 'og.id, og.thumb, g.thumb as thumb_2,og.title, og.option_title,og.price,og.total',
                'where' => [
                    'og.id' => $firstPackageOrderGoodsIds,
                    'og.member_id' => $this->memberId
                ],
                'indexBy' => 'id'
            ], [
                'onlyList' => true,
                'pager' => false,
                'callable' => function ($item) use ($orderGoodsIdMap, &$packages) {
                    $packages[$orderGoodsIdMap[$item['id']]]['goods_list'][] = $item;
                }
            ]);
        }

        return $this->success(['list' => array_values($packages)]);
    }
}
