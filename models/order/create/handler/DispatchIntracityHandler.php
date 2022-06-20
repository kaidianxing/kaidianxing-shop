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

namespace shopstar\models\order\create\handler;

use shopstar\components\amap\AmapClient;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\helpers\MathHelper;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

class DispatchIntracityHandler implements HandlerInterface
{
    private OrderCreatorKernel $orderCreatorKernel;

    /**
     * @var array 同城配送设置
     */
    protected array $settings = [];

    /**
     * @var array 收货地址
     */
    protected array $address = [];

    /**
     * @var array 店铺地址
     */
    protected array $shopContact = [];


    /**
     * HandlerInterface constructor.
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {

        $this->orderCreatorKernel = $orderCreatorKernel;
        // 同城配送设置
        $this->settings = $this->orderCreatorKernel->shopIntracity;

        // 买家地址
        $this->address = $this->orderCreatorKernel->address;

        // 店铺联系方式
        $this->shopContact = $this->orderCreatorKernel->shopContact;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return void
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 同城配送才进行计算
        if ($this->orderCreatorKernel->orderData['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {
            return;
        }
//        // 选择支付时如果是国外地址，并选择同城配送，抛错
//        if(!$this->orderCreatorKernel->isConfirm && $this->orderCreatorKernel->orderData['address_code'] == '990101' && $this->orderCreatorKernel->orderData['dispatch_type'] == '30'){
//            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_FOREIGN_ERROR);
//        }

        // 店铺同城配送开启状态
        $intracityEnable = ShopSettings::get('dispatch.intracity.enable');
        if (empty($intracityEnable)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_ENABLE_ERROR);
        }

        // 校验买家地址
        if (empty($this->address)) {
            $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_MEMBER_ADDRESS_ERROR);
        }

        // 获取商品价格、重量
        $goodsAllPrice = 0;
        $goodsAllWeight = 0;

        foreach ($this->orderCreatorKernel->orderGoodsData as $item) {
            $goodsAllPrice += (float)$item['price_original'];
            $goodsAllWeight += (float)$item['weight'] * $item['total'];
        }

        // 重量转化为千克
        $goodsAllWeight = round2($goodsAllWeight / 1000, 2);

        unset($item);

        // 判断买家地址
        if (empty($this->address['lng']) || empty($this->address['lat'])) {
            $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_MEMBER_ADDRESS_POINT_ERROR);
            return;
        }

        // 买家地址
        $location = $this->address['lng'] . ',' . $this->address['lat'];
        // 坐标
        $point = [
            'lng' => $this->address['lng'],
            'lat' => $this->address['lat'],
        ];

        // 获取店铺坐标店铺地址(判断店铺地址、坐标是否村存在)
        $shopAddress = $this->shopContact['address'];
        if (empty($shopAddress) || empty($shopAddress['lng']) || empty($shopAddress['lat'])) {
            $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_SHOP_ADDRESS_ERROR);
            return;
        }
        $shopLocation = $shopAddress['lng'] . ',' . $shopAddress['lat'];

        /**下面开始判断是否可配送、价格计算*******************************************/

        // 初始配送重量
        $initialWeight = $this->settings['dispatch_rule']['initial_weight'];
        // 每增加配送重量
        $increaseWeight = $this->settings['dispatch_rule']['increase_weight'];
        // 每增加重量增加的金额
        $increaseWeightPrice = $this->settings['dispatch_rule']['increase_weight_price'];

        // 满足配送的区域
        $areaIndexArray = [];
        // 满足配送区域的起送价集合
        $initialPriceArray = [];
        // 满足配送区域的配送价集合
        $deliveryPriceArray = [];

        // 最终配送费
        $dispatchPrice = 0;

        // 配送区域 0: 按不同区域 1: 按不同距离 2: 按行政区域
        if ($this->settings['delivery_area'] == 0 || $this->settings['delivery_area'] == 1) {

            // 配送区域为空
            if (empty($this->settings['dispatch_area'])) {
                $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_AREA_ERROR);
                return;
            }

            // 遍历查询出符合的配送区域
            foreach ($this->settings['dispatch_area'] as $index => $item) {
                if (empty($this->settings['division_way'])) {
                    // 按半径计算
                    $circle = [
                        'center' => [
                            'lng' => $item['center_lng'],
                            'lat' => $item['center_lat']
                        ],
                        'radius' => $item['radius'] * 1000 // 千米
                    ];
                    $result = MathHelper::InTheCircle($point, $circle);
                } else {
                    // 自定义计算(多边形)
                    $result = MathHelper::InThePolygon($point, $item['location']);
                }
                if (!$result) {
                    continue;
                }

                // 开启免配送费 并且商品价格大于起送价格
                if ($item['is_free'] && $goodsAllPrice >= $item['initial_price'] && $goodsAllPrice >= $item['free_price']) {
                    $dispatchPrice = 0;
                    $this->mountOrderData($dispatchPrice);
                    return;
                }

                // 塞入可配送区域
                $areaIndexArray[] = $index;
                // 塞入起送价格数组
                $initialPriceArray[] = $item['initial_price'];
                // 商品初始价格小于商品总价格，塞入配送费
                if ($item['initial_price'] <= $goodsAllPrice) {
                    $deliveryPriceArray[] = $item['dispatch_price'];
                }
            }

            // 没有符合的配送区域
            if (empty($areaIndexArray)) {
                $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_AREA_INVALID);
                return;
            }

            // 当前商品总价低于起送金额
            $minPrice = min($initialPriceArray);
            if ($goodsAllPrice < $minPrice) {
                $gap = round2($minPrice - $goodsAllPrice);
                $this->mountError
                (OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_GOODSPROCE_LOWER_INITPRICE, '还差￥' . $gap . '起送');
                return;
            }

            // 最低配送金额
            $minDispatchPrice = min($deliveryPriceArray);

            // 选择配送方案
            $areaIndex = null;
            foreach ($areaIndexArray as $index) {
                // 满足起送费&&最低配送费
                if (!empty($deliveryPriceArray)) {
                    if ($this->settings['dispatch_area'][$index]['dispatch_price'] == $minDispatchPrice) {
                        $areaIndex = $index;
                        break;
                    }
                }
            }

            // 获取最终使用配送区域规则
            $dispatchAreaRule = $this->settings['dispatch_area'][$areaIndex];

            //  根据不同配送方式获取不同价格
            if ($this->settings['delivery_area'] == 0) {
                $dispatchPrice += $dispatchAreaRule['dispatch_price'];
            } else if ($this->settings['delivery_area'] == 1) {
                // 获取实际距离
                $actualDistance = AmapClient::getActualDistance($shopLocation, $location);
                if (is_error($actualDistance)) {
                    $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_ACTUAL_DISTANCE_ERROR);
                    return;
                }

                // 获取实际距离运费
                if ($this->settings['delivery_area'] == 0) {
                    // 按照范围配送初始配送费用为配送费
                    $initialPrice = $dispatchAreaRule['dispatch_price'];
                } else {
                    // 按照距离配送初始配送费为初始距离配送费
                    $initialPrice = $this->settings['dispatch_rule']['initial_dispatch_price'];
                }
                $dispatchPrice += $this->getActualDistancePrice($actualDistance['results'][0]['distance'], $initialPrice);
            }

            // 计算超出部分的价格
            if (!empty($goodsAllWeight)) {
                // 有商品重量，且商品重量大于起送重量
                if ($goodsAllWeight > $initialWeight && $increaseWeight > 0) {
                    $beyondWeight = ceil((($goodsAllWeight - $initialWeight) / $increaseWeight));
                    $dispatchPrice += round2($beyondWeight * $increaseWeightPrice, 2);
                }
            }

        } else {
            /** 按行政区域 delivery_area == 2 */

            // 判断是否超出配送区域
            if (!in_array($this->address['area_code'], (array)$this->settings['dispatch_barrio'])) {
                $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_DISPATCH_INTRACITY_AREA_INVALID);
                return;
            }

            // 判断是否达到起送金额
            if ($goodsAllPrice < $this->settings['barrio_rule']['initial_price']) {
                $gap = round2($this->settings['barrio_rule']['initial_price'] - $goodsAllPrice);
                $this->mountError(OrderCreatorException::ORDER_CREATOR_KERNEL_INTRACITY_HANDLER_BARRO_INITIAL_PRICE_UNDER, '还差￥' . $gap . '起送');
                return;
            }


            /***** 开启阶梯价 *****/
            // 配送费
            $dispatchPrice = $this->settings['barrio_rule']['dispatch_price'];
        }

        // 挂在运费
        $this->mountOrderData($dispatchPrice);
        return;

    }

    /**
     * 获取按距离配送的运费
     * @param float $actualDistance 实际距离(米)
     * @param float $initialPrice 初始配送价格
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    protected function getActualDistancePrice(float $actualDistance, float $initialPrice): float
    {
        // 运费
        $dispatchPrice = 0;

        // 初始配送距离
        $initialDistance = $this->settings['dispatch_rule']['initial_distance'];
        // 每增加配送距离
        $increaseDistance = $this->settings['dispatch_rule']['increase_distance'];
        // 每增加距离增加的金额
        $increaseDistancePrice = $this->settings['dispatch_rule']['increase_distance_price'];
        // 超出固定距离
        $overDistance = $this->settings['dispatch_rule']['over_distance'];
        // 超出固定距离固定金额
        $overDistanceFixPrice = $this->settings['dispatch_rule']['over_distance_fix_price'];

        // 米转化为千米
        $actualDistance = round2($actualDistance / 1000, 2);

        // 计算距离所占的运费
        if ($actualDistance <= $initialDistance) {
            // 配送距离在基础配送距离内
            $dispatchPrice += $initialPrice;
        } elseif ($actualDistance > $overDistance) {
            $dispatchPrice += $overDistanceFixPrice;
        } else {
            // 计算超出距离部分的运费
            $outrange = ceil((($actualDistance - $initialDistance) / $increaseDistance));
            $dispatchPrice += $initialPrice + round2($outrange * $increaseDistancePrice, 2);
        }

        return (float)$dispatchPrice;
    }

    /**
     * 挂载到订单运费
     * @param float $price
     * @author 青岛开店星信息技术有限公司
     */
    private function mountOrderData(float $price)
    {
        // 挂载 配送价格、规则到订单数据
        $this->orderCreatorKernel->orderData['dispatch_price'] = $price;
    }

    /**
     * 挂载错误信息
     * @param $errorCode
     * @param string $errorMsg
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    private function mountError($errorCode, string $errorMsg = '')
    {
        // 确认订单返回弱类型错误
        if ($this->orderCreatorKernel->isConfirm) {
            $this->orderCreatorKernel->orderData['dispatch_price'] = 0;
            $this->orderCreatorKernel->orderData['dispatch_intracity_error'] = [
                'code' => $errorCode,
                'msg' => empty($errorMsg) ? OrderCreatorException::getMessages($errorCode) : $errorMsg
            ];
        } else {
            // 下单直接返回异常
            throw new OrderCreatorException($errorCode);
        }


    }

}
