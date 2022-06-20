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

namespace shopstar\services\shop;

use shopstar\components\dispatch\bases\DispatchDriverConstant;
use shopstar\components\dispatch\bases\MakeOrderStatusConstant;
use shopstar\components\dispatch\DispatchComponent;
use shopstar\components\dispatch\driver\DadaDriver;
use shopstar\constants\order\OrderPackageCityDistributionTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\sysset\IntracityException;
use shopstar\helpers\StringHelper;
use shopstar\models\order\DispatchModel;
use shopstar\models\order\DispatchOrderModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use Throwable;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class ShopSettingIntracityLogic
{
    /**
     * 同城配送配送状态
     * @param $args
     * @return bool
     * @throws IntracityException
     * @author 青岛开店星信息技术有限公司
     */
    public static function enable($args): bool
    {
        // 开启前判断是否设置参数,平台不需要校验
        if ($args['enable'] == 1) {
            // 判断店铺地址
            if (!self::checkShopAddress()) {
                throw new IntracityException(IntracityException::SHOP_SETTINGS_SHOP_ADDRESS_NOT_EMPTY);
            }
        }

        // 关闭前判断是否有开启配送方式
        if ($args['enable'] == 0) {

            $dispatchEnable = ShopSettings::get('dispatch.express.enable');

            if (empty($dispatchEnable)) {
                throw new IntracityException(IntracityException::SHOP_SETTINGS_DISPATCH_INTRACITY_ENABLE_INVALID);
            }
        }

        ShopSettings::set('dispatch.intracity.enable', $args['enable']);

        // 配送方式排序处理
        DispatchModel::updateSort($args['enable'], 30);

        return true;
    }

    /**
     * 检测店铺地址
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkShopAddress(): bool
    {
        $shopAddress = ShopSettings::get('contact.address');
        array_map(function ($value) {
            if (empty($value)) {
                return false;
            }
        }, $shopAddress);

        return true;
    }

    /**
     * 读取设置
     * @param bool $onlySettings 只返回配置
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function get(bool $onlySettings = false): array
    {
        $settings = ShopSettings::get('dispatch.intracity');
        // 获取店铺地址
        $address = ShopSettings::get('contact');

        if ($onlySettings) {
            return $settings;
        }

        return [
            'data' => $settings,
            'address' => !empty($address) ? $address : null
        ];
    }

    /**
     * 同城配送保存设置
     * @param array $args
     * @return bool
     * @throws IntracityException
     * @author 青岛开店星信息技术有限公司
     */
    public static function set(array $args): bool
    {
        // 初始化参数
        $args = self::init($args);

        // 至少开启一种配送方式
        if (empty($args['merchant']) && empty($args['dada']['enable'])) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_INTRACITY_DISPATCH_INVALID);
        }

        // 检测达达参数
        if ($args['dada']['enable']) {
            if (empty($args['dada']['app_key']) || empty($args['dada']['app_secret']) ||
                empty($args['dada']['shop_no']) || empty($args['dada']['source_id'] || empty($args['dada']['city_code']))) {
                throw new IntracityException(IntracityException::SHOP_SETTINGS_INTRACITY_DADA_DISPATCH_PARAMS_INVALID);
            }
        }

        // 检测店铺地址
        if (!self::checkShopAddress()) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_SHOP_ADDRESS_NOT_EMPTY);
        }

        //检测高德key
        if (empty($args['amap_key'])) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_AMAP_KEY_NOT_EMPTY);
        }

        // 默认值
        if (is_null($args['delivery_area'])) {
            $args['delivery_area'] = 0;
        }

        if (is_null($args['division_way'])) {
            $args['division_way'] = 0;
        }

        if (is_null($args['over_scope'])) {
            $args['over_scope'] = 0;
        }

        // 检测配送区域
        if (($args['delivery_area'] == 0 || $args['delivery_area'] == 1) && empty($args['dispatch_area'])) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_DISPATCH_AREA_INVALID);
        }
        if ($args['delivery_area'] == 2 && (empty($args['dispatch_barrio']) || empty($args['barrio_rule']))) {
            throw new IntracityException(IntracityException::SHOP_SETTINGS_DISPATCH_DISPATCH_BARRIO_INVALID);
        }

        // 检测配送参数
        $deliveryMode = self::getDeliveryModel($args['delivery_area'], $args['division_way']);
        switch ($deliveryMode) {
            case 1://按不同区域-半径
                foreach ($args['dispatch_area'] as $dispatchAreaItem) {
                    if (!isset($dispatchAreaItem['initial_price']) || !isset($dispatchAreaItem['dispatch_price']) ||
                        !isset($dispatchAreaItem['center_lng']) || !isset($dispatchAreaItem['center_lat']) ||
                        !isset($dispatchAreaItem['is_free']) || !isset($dispatchAreaItem['free_price']) ||
                        !isset($dispatchAreaItem['radius'])) {
                        throw new IntracityException(IntracityException::SHOP_SETTINGS_AREA_RADIO_WAY_INVALID);

                    }
                }
                break;
            case 2://按不同区域-自定义
                foreach ($args['dispatch_area'] as $dispatchAreaItem) {
                    if (!isset($dispatchAreaItem['initial_price']) || !isset($dispatchAreaItem['dispatch_price']) ||
                        !isset($dispatchAreaItem['is_free']) || !isset($dispatchAreaItem['free_price']) ||
                        !isset($dispatchAreaItem['location']) || count($dispatchAreaItem['location']) > 50) {
                        throw new IntracityException(IntracityException::SHOP_SETTINGS_AREA_DIY_WAY_INVALID);

                    }
                }
                break;
            case 3://按不同距离-半径
                foreach ($args['dispatch_area'] as $dispatchAreaItem) {
                    if (!isset($dispatchAreaItem['initial_price']) || !isset($dispatchAreaItem['radius']) ||
                        !isset($dispatchAreaItem['center_lng']) || !isset($dispatchAreaItem['center_lat']) ||
                        !isset($dispatchAreaItem['is_free']) || !isset($dispatchAreaItem['free_price'])) {
                        throw new IntracityException(IntracityException::SHOP_SETTINGS_DISTANCE_RADIO_WAY_INVALID);

                    }
                }
                break;
            case 4://按不同距离-自定义
                foreach ($args['dispatch_area'] as $dispatchAreaItem) {
                    if (!isset($dispatchAreaItem['initial_price']) || !isset($dispatchAreaItem['is_free']) ||
                        !isset($dispatchAreaItem['free_price']) ||
                        !isset($dispatchAreaItem['location']) || count($dispatchAreaItem['location']) > 50) {
                        throw new IntracityException(IntracityException::SHOP_SETTINGS_DISTANCE_DIY_WAY_INVALID);
                    }
                }
                break;
            case 5://按照行政区域
                if (empty($args['dispatch_barrio']) || empty($args['barrio_rule'])) {
                    throw new IntracityException(IntracityException::SHOP_SETTINGS_BARRIO_WAY_INVALID);
                }

        }

        // 检测配送规则
        if ($args['delivery_area'] == 0 || $args['delivery_area'] == 1) {
            /**** 按区域 *****/
            if ($args['delivery_area'] == 0) {
                if (!isset($args['dispatch_rule']['initial_weight']) ||
                    !isset($args['dispatch_rule']['increase_weight']) ||
                    !isset($args['dispatch_rule']['increase_weight_price'])) {
                    throw new IntracityException(IntracityException::SHOP_SETTINGS_DISTRICT_DISPATCH_RULE_INVALID);
                }
            }
            /**** 按距离 *****/
            if ($args['delivery_area'] == 1) {
                if (!isset($args['dispatch_rule']['initial_weight']) ||
                    !isset($args['dispatch_rule']['increase_weight']) ||
                    !isset($args['dispatch_rule']['increase_weight_price']) ||
                    !isset($args['dispatch_rule']['initial_distance']) ||
                    !isset($args['dispatch_rule']['initial_dispatch_price']) ||
                    !isset($args['dispatch_rule']['increase_distance']) ||
                    !isset($args['dispatch_rule']['increase_distance_price']) ||
                    !isset($args['dispatch_rule']['over_distance']) ||
                    !isset($args['dispatch_rule']['over_distance_fix_price'])) {
                    throw new IntracityException(IntracityException::SHOP_SETTINGS_DISTANCE_DISPATCH_RULE_INVALID);
                }
            }
        } else {
            /**** 按行政区域 *****/
            if (!isset($args['barrio_rule']['initial_price']) ||
                !isset($args['barrio_rule']['dispatch_price'])) {
                throw new IntracityException(IntracityException::SHOP_SETTINGS_BARRIO_RULE_INVALID);
            }
        }

        $settings = ShopSettings::get('dispatch.intracity');
        $args['enable'] = (int)$settings['enable'];
        ShopSettings::set('dispatch.intracity', $args);

        return true;
    }

    /**
     * 初始化参数
     * @author 青岛开店星信息技术有限公司
     */
    private static function init($params)
    {
        foreach ($params as &$item) {
            StringHelper::isJson($item) && $item = Json::decode($item, true);
        }

        return $params;
    }

    /**
     * 获取配送组合模式
     * @param $deliveryArea
     * @param $divisionWay
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private static function getDeliveryModel($deliveryArea, $divisionWay): int
    {
        // 配送区域 0: 按不同区域 1: 按不同距离 2: 按行政区域
        // 划分方式 0: 半径 1: 自定义
        $mode = 0;
        if ($deliveryArea == 0 && $divisionWay == 0) {
            $mode = 1;
        }

        if ($deliveryArea == 0 && $divisionWay == 1) {
            $mode = 2;
        }

        if ($deliveryArea == 1 && $divisionWay == 0) {
            $mode = 3;
        }

        if ($deliveryArea == 1 && $divisionWay == 1) {
            $mode = 4;
        }

        if ($deliveryArea == 2) {
            $mode = 5;
        }

        return $mode;
    }

    /**
     * 获取配送费
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDispatchPrice()
    {
        $intracity = ShopSettings::get('dispatch.intracity');

        if ($intracity['delivery_area'] == 0) {
            // 配送区域 0: 按不同区域 1: 按不同距离
            $dispatchPrice = empty($intracity['dispatch_area']) ? 0 : (min(array_column($intracity['dispatch_area'] ?: [], 'dispatch_price')));
        } elseif ($intracity['delivery_area'] == 1) {
            // 配送区域 1: 按不同距离
            $dispatchPrice = $intracity['dispatch_rule']['initial_dispatch_price'];
        } else {
            // 配送区域 2: 按行政区域
            $dispatchPrice = $intracity['barrio_rule']['dispatch_price'];
        }

        return $dispatchPrice;
    }

    /**
     * 获取配送区域
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDispatchArea(): array
    {
        $intracity = ShopSettings::get('dispatch.intracity');

        if ($intracity['delivery_area'] == 0 || $intracity['delivery_area'] == 1) {
            // 配送区域 0: 按不同区域 1: 按不同距离
            $dispatchArea = $intracity['dispatch_area'];
        } else {
            // 配送区域 2: 按行政区域
            $dispatchArea = $intracity['dispatch_barrio'];
        }

        return [
            'delivery_area' => $intracity['delivery_area'],
            'division_way' => $intracity['division_way'],
            'dispatch_area' => $dispatchArea
        ];
    }

    /**
     * 获取达达配送城市code
     * @param $config
     * @return array|bool
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDadaCity($config): ?array
    {
        /** @var DadaDriver $driver */
        $driver = DispatchComponent::getInstance(DispatchDriverConstant::DRIVE_DADA, $config);

        return $driver->cityCode();
    }

    /**
     * 查询第三方配送订单详情
     * @param $dispatchType
     * @param $orderId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function queryOrderStatus($dispatchType, $orderId): array
    {
        // 订单追踪
        $orderTrace = [];
        // 配送详情
        $dispatch = [];
        $orderInfo = OrderModel::getOrderAndOrderGoods($orderId);
        if ($orderInfo['status'] < OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            return [
                'order_trace' => $orderTrace,
                'dispatch' => $dispatch,
                'order_status' => $orderInfo['status']
            ];
        }

        $orderTrace = [
            [
                'status_text' => '订单已提交',
                'status_time' => $orderInfo['created_at'],
            ],
            [
                'status_text' => '支付成功',
                'status_time' => $orderInfo['pay_time'],
            ],
            [
                'status_text' => '商家已接单',
                'status_time' => $orderInfo['pay_time'],
            ],
            [
                'status_text' => '配送开始调度，商家正在拣货',
                'status_time' => $orderInfo['pay_time'],
            ]
        ];

        // 待发货 没有配送信息 直接返回
        if ($orderInfo['status'] == OrderStatusConstant::ORDER_STATUS_WAIT_SEND) {
            return [
                'order_trace' => $orderTrace,
                'dispatch' => $dispatch,
                'order_status' => $orderInfo['status']
            ];
        }

        // 商家已发货
        if ($dispatchType == OrderPackageCityDistributionTypeConstant::DADA) {
            $config = ShopSettings::get('dispatch.intracity.dada');
            $orderItem = DispatchOrderModel::find()
                ->where([
                    'order_id' => $orderId,
                    'type' => DispatchDriverConstant::getCode(DispatchDriverConstant::DRIVE_DADA)
                ])->first();
            $orderNo = $orderItem['order_no'];
            // 拼接订单跟踪
            if (strtotime($orderItem['accepted_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已接单',
                    'status_time' => $orderItem['accepted_time'],
                ];
            }
            if (strtotime($orderItem['delivery_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已到店',
                    'status_time' => $orderItem['delivery_time'],
                ];
            }
            if (strtotime($orderItem['completed_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '商品已送达',
                    'status_time' => $orderItem['completed_time'],
                ];
            }
            if (strtotime($orderItem['cancel_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '订单已取消',
                    'status_time' => $orderItem['cancel_time'],
                ];
            }
        } elseif ($dispatchType == OrderPackageCityDistributionTypeConstant::MAKE) {
            $config = ShopSettings::get('dispatch.intracity.make');
            $orderItem = DispatchOrderModel::find()
                ->where([
                    'order_id' => $orderId,
                    'type' => DispatchDriverConstant::getCode(DispatchDriverConstant::DRIVER_MAKE)
                ])->first();
            $orderNo = $orderItem['out_order_no'];
            // 拼接订单跟踪
            if (strtotime($orderItem['accepted_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已接单',
                    'status_time' => $orderItem['accepted_time'],
                ];
            }
            if (strtotime($orderItem['delivery_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已到店',
                    'status_time' => $orderItem['delivery_time'],
                ];
            }
            if (strtotime($orderItem['gotoed_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '商品已送达',
                    'status_time' => $orderItem['gotoed_time'],
                ];
            }
            if (strtotime($orderItem['cancel_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '订单已取消',
                    'status_time' => $orderItem['cancel_time'],
                ];
            }
        } elseif ($dispatchType == OrderPackageCityDistributionTypeConstant::SF) {
            $config = ShopSettings::get('dispatch.intracity.shunfeng');

            $orderItem = DispatchOrderModel::find()
                ->where([
                    'order_id' => $orderId,
                    'type' => DispatchDriverConstant::getCode(DispatchDriverConstant::DRIVER_SF)
                ])->first();

            $orderNo = $orderItem['out_order_no'];

            if (strtotime($orderItem['accepted_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已接单',
                    'status_time' => $orderItem['accepted_time'],
                ];
            }
            if (strtotime($orderItem['delivery_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已到店',
                    'status_time' => $orderItem['delivery_time'],
                ];
            }
            if (strtotime($orderItem['completed_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '商品已送达',
                    'status_time' => $orderItem['completed_time'],
                ];
            }
            if (strtotime($orderItem['cancel_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '订单已取消',
                    'status_time' => $orderItem['cancel_time'],
                ];
            }


        } elseif ($dispatchType == OrderPackageCityDistributionTypeConstant::SHANSONG) {

            $config = ShopSettings::get('dispatch.intracity.shansong');

            //去除不需要的值
            $config = [
                'client_id' => $config['client_id'],
                'app_secret' => $config['app_secret'],
                'enable' => $config['enable']
            ];

            $orderItem = DispatchOrderModel::find()
                ->where([
                    'order_id' => $orderId,
                    'type' => DispatchDriverConstant::getCode(DispatchDriverConstant::DRIVER_SHANSONG)
                ])->first();

            //闪送特殊格式
            $orderNo = [
                'issOrderNo' => $orderItem['out_order_no']
            ];

            if (strtotime($orderItem['accepted_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已接单',
                    'status_time' => $orderItem['accepted_time'],
                ];
            }
            if (strtotime($orderItem['delivery_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '骑手已到店',
                    'status_time' => $orderItem['delivery_time'],
                ];
            }
            if (strtotime($orderItem['completed_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '商品已送达',
                    'status_time' => $orderItem['completed_time'],
                ];
            }
            if (strtotime($orderItem['cancel_time']) > 0) {
                $orderTrace[] = [
                    'status_text' => '订单已取消',
                    'status_time' => $orderItem['cancel_time'],
                ];
            }

        } else {
            return error('同城配送方式错误');
        }

        if (empty($config) || !isset($config['enable'])) {
            return error('同城配送参数错误');
        }
        unset($config['enable']);

        try {
            $driver = DispatchComponent::getInstance(OrderPackageCityDistributionTypeConstant::getIdentify($dispatchType), $config);

            $result = $driver->queryStatus($orderNo);

        } catch (Throwable $e) {
            return error($e->getMessage());
        }

        // 拼装达达配送信息
        if ($dispatchType == OrderPackageCityDistributionTypeConstant::DADA) {
            $dispatch = [
                'order_status' => $result['statusCode'],
                'order_status_msg' => $result['statusMsg'],
                'transporter_name' => $result['transporterName'] ?? '',
                'transporter_phone' => $result['transporterPhone'] ?? '',
                'transporter_lat' => $result['transporterLat'] ?? '',
                'transporter_lng' => $result['transporterLng'] ?? '',
            ];
        }

        // 拼装码科配送信息
        if ($dispatchType == OrderPackageCityDistributionTypeConstant::MAKE) {
            $dispatch = [
                'order_status' => MakeOrderStatusConstant::getCode($result['status']),
                'order_status_msg' => MakeOrderStatusConstant::getMessage($result['status']),
                'transporter_name' => $result['order_rider']['real_name'] ?? '',
                'transporter_phone' => $result['order_rider']['mobile'] ?? '',
                'transporter_lat' => $result['order_rider']['latitude'] ?? '',
                'transporter_lng' => $result['order_rider']['longitude'] ?? '',
            ];
        }


        //拼装顺丰配送信息
        if ($dispatchType == OrderPackageCityDistributionTypeConstant::SF) {
            $dispatch = [
                'transporter_name' => $result['rider_name'] ?? '',
                'transporter_phone' => $result['rider_phone'] ?? '',
                'transporter_lat' => $result['rider_lat'] ?? '',
                'transporter_lng' => $result['rider_lng'] ?? '',
            ];
        }


        //拼装闪送配送信息
        if ($dispatchType == OrderPackageCityDistributionTypeConstant::SHANSONG) {
            $dispatch = [
                'transporter_name' => $result['data']['name'] ?? '',
                'transporter_phone' => $result['data']['mobile'] ?? '',
                'transporter_lat' => $result['data']['latitude'] ?? '',
                'transporter_lng' => $result['data']['longitude'] ?? '',
            ];
        }

        // 店铺地址
        $contactAddress = ShopSettings::get('contact.address');


        $dispatch['shop_lat'] = $contactAddress['lat'];
        $dispatch['shop_lng'] = $contactAddress['lng'];

        // 买家地址
        $buyerAddress = Json::decode($orderInfo['address_info']);
        $dispatch['buyer_lat'] = $buyerAddress['lat'];
        $dispatch['buyer_lng'] = $buyerAddress['lng'];

        return [
            'order_trace' => $orderTrace,
            'dispatch' => $dispatch,
            'order_status' => $orderInfo['status']
        ];
    }


}
