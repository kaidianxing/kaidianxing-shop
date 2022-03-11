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

namespace shopstar\models\order\create;

use ArrayAccess;

/**
 * 订单活动助手类
 * Class OrderCreatorActivityAssistant
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\models\order\create
 */
class OrderCreatorActivityAssistant implements ArrayAccess
{

    /**
     * 本助手的商品片段
     * @var array
     */
    public $goods = [];

    /**
     * 附加参数
     * @var array
     */
    public $extra = [];

    /**
     * 活动信息
     * @var array
     */
    public $activity = [];

    /**
     * 确认订单返回的活动数据
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $confirmReturnActivityData = [];

    public function __construct(array $goods, $goodsIds = null, $optionIds = null)
    {
        $this->segmentGoods($goods, $goodsIds, $optionIds);
    }

    /**
     * @param string $actSign
     * @param string|array $data
     * @author 青岛开店星信息技术有限公司
     */
    public function setActivityReturnData(string $actSign, $data)
    {
        $this->confirmReturnActivityData[$actSign] = $data;
    }

    /**
     * 获取需要返回的活动规则
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getActivityReturnData()
    {
        return $this->confirmReturnActivityData;
    }

    /**
     * 截取商品片段，支持到规格级别，有四种截取方式
     * @param array $goods 订单中的商品信息
     * @param array $goodsIds 希望截取的商品ID
     * @param array $optionIds 希望截取的规格ID
     * @author 青岛开店星信息技术有限公司
     */
    protected function segmentGoods(array $goods, $goodsIds = null, $optionIds = null)
    {
        if (is_null($optionIds)) {
            if (is_null($goodsIds)) {
                $this->goods = $goods;
            } else {//加载指定的商品
                foreach ($goods as $goodsItem) {
                    in_array($goodsItem['goods_id'], $goodsIds) && $this->goods[] = $goodsItem;
                }
            }
        } else {
            if (!empty($goodsIds)) {
                foreach ($goods as $goodsItem) {
                    //加载指定的无规格商品
                    if ($goodsItem['option_id'] === '0' && in_array($goodsItem['goods_id'], $goodsIds)) {
                        $this->goods[] = $goodsItem;
                    } //加载指定规格的商品
                    elseif (in_array($goodsItem['goods_id'], $goodsIds) && in_array($goodsItem['option_id'], $optionIds)) {
                        $this->goods[] = $goodsItem;
                    }
                }
            }
        }
    }

    /**
     * 重置覆盖商品
     * @param $goods
     * @author 青岛开店星信息技术有限公司.
     */
    public function resetGoods($goods)
    {
        $this->goods = $goods;
    }

    /**
     * 这些变量不随数量的变化而变化，而是一个总量
     * @var array
     */
    protected $overallField = ['total', 'price', 'price_original', 'credit', 'credit_original', 'price_discount'];

    /**
     * 通用读取方法
     * @param string $field
     * @param int $goodsId
     * @param int $optionId
     * @return float
     */
    protected function commonSum(string $field, int $goodsId, int $optionId): float
    {
        $sum = 0;
        foreach ($this->goods as $goods) {
            if ((empty($goodsId) || $goods['goods_id'] == $goodsId) &&
                (empty($optionId) || $goods['option_id'] == $optionId)) {
                $sum += in_array($field, $this->overallField) ?
                    $goods[$field] : $goods[$field] * max(1, $goods['total']);
            }
        }
        return round2($sum, 2);
    }

    /**
     * 通用设置方法
     * @param string $field
     * @param $value
     * @param int $goodsId
     * @param int $optionId
     * @return string
     */
    public function commonSet(string $field, $value, int $goodsId, int $optionId)
    {
        foreach ($this->goods as &$goods) {
            if ((empty($goodsId) || $goods['goods_id'] == $goodsId) &&
                (empty($optionId) || $goods['option_id'] == $optionId)) {
                $goods[$field] = is_numeric($value) && !in_array($field, $this->overallField) ?
                    round2($value / $goods['total'], 2) : $value;
            }
        }
        unset($goods);
    }

    /****** 关于商品的信息 ******/
    /**
     * 获取商品个数
     * @param int $goodsId
     * @param int $optionId
     * @return int
     */
    public function getGoodsTotal($goodsId = 0, $optionId = 0)
    {
        return (int)$this->commonSum('total', $goodsId, $optionId);
    }

    public function setGoodsExtra($goodsId, $optionId, $key, $value)
    {
        $this->commonSet('extra', [$key => $value], $goodsId, $optionId);
    }

    /**
     * 获取商品原价
     * @param int $goodsId
     * @param int $optionId
     * @return float
     */
    public function getGoodsPrice($goodsId = 0, $optionId = 0)
    {
        return $this->commonSum('price_unit', $goodsId, $optionId);
    }

    /**
     * 获取商品信息
     * @return array
     */
    public function getGoodsInfo()
    {
        return $this->goods;
    }

    /**
     * 更新商品，你也可以直接操作$goods成员
     * @param $orderGoodsData
     */
    public function updateGoods($orderGoodsData)
    {
        $this->goods = $orderGoodsData;
    }

    /****** 关于价格的信息 ******/
    public function getPayPrice(int $goodsId, int $optionId)
    {
        return $this->commonSum('price', $goodsId, $optionId);
    }

    /**
     * 获取支付积分
     * @param $goodsId
     * @param $optionId
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    public function getPayCredit($goodsId, $optionId)
    {
        return $this->commonSum('credit', $goodsId, $optionId);
    }

    /**
     * 获取支付总价
     * @return float
     */
    public function getTotalPayPrice()
    {
        return $this->getPayPrice(0, 0);
    }

    /**
     * 获取支付总积分
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    public function getTotalPayCredit()
    {
        return $this->getPayCredit(0, 0);
    }

    /**
     * 获取指定商品折扣总价
     * @param int $goodsId
     * @param int $optionId
     * @return float
     */
    public function getDiscountPrice($goodsId = 0, $optionId = 0)
    {
        return $this->commonSum('price_discount', $goodsId, $optionId);
    }

    /**
     * 获取折扣总价
     * @return float
     */
    public function getTotalDiscountPrice()
    {
        return $this->getDiscountPrice(0, 0);
    }

    /**
     * 给指定商品执行优惠
     * @param int $goodsId
     * @param int $optionId
     * @param $cutPrice
     * @param string $actSign
     * @param array $actRule
     * @return bool
     */
    public function setCutPrice($goodsId, $optionId, $cutPrice, string $actSign, array $actRule)
    {
        $discountPrice = $this->getDiscountPrice($goodsId, $optionId);
        $payPrice = $this->getPayPrice($goodsId, $optionId);
        $this->commonSet('price_discount', round2($discountPrice + $cutPrice, 2), $goodsId, $optionId);
        $this->commonSet('price', round2($payPrice - $cutPrice, 2), $goodsId, $optionId);
        $this->commonSet('price_original', round2($payPrice - $cutPrice, 2), $goodsId, $optionId);
        $this->setAppliedRules($actSign, $actRule, $cutPrice, $goodsId, $optionId);
        return true;
    }

    /**
     * 给本组商品执行优惠
     * @param float $cutPrice 减价金额（减多少？）
     * @param string $actSign 优惠活动标识（为什么减价？）
     * @param array $actRule 优惠活动规则信息（减价规则，怎么减价？）
     * @param string $reference 减价参考标准（参考当前价格还是参考商品原价？）
     * @param array $resetGoods 需要部分处理的商品(在全部订单里处理相同活动的两个或多个商品处理均摊)  必须是在当前订单上截取的商品片段
     * @return bool             处理结果是否成功
     * @author 青岛开店星信息技术有限公司
     */
    public function setTotalCutPrice(float $cutPrice, string $actSign, array $actRule, $reference = 'price', array $resetGoods = [])
    {
        if ($cutPrice <= 0) {
            return false;
        }

        switch ($reference) {
            case 'price':

                if (!empty($resetGoods)) {
                    $totalPrice = array_sum(array_column($resetGoods, 'price'));
                    break;
                }

                $totalPrice = $this->getTotalPayPrice();

                break;
            case 'original':

                if (!empty($resetGoods)) {
                    $totalPrice = array_sum(array_column($resetGoods, 'price_unit'));
                    break;
                }

                $totalPrice = $this->getGoodsPrice();
                break;
            default:
                return false;
        }

        // 过度优惠时，直接置零
        if ($cutPrice > $totalPrice) {
            $cutPrice = $totalPrice;
        }

        $goodsGroup = $resetGoods ?: $this->goods;

        $count = count($goodsGroup) - 1;
        $sum = 0;

        foreach ($goodsGroup as $key => &$goods) {
            // 1.当前商品价格占比
            $proportion = $goods[$reference] == 0 ? 0 : $goods[$reference] / $totalPrice;

            // 2.按比例均摊优惠价格
            if ($count === $key) { // 最后一个用减法算
                $cut = round2($cutPrice - $sum, 2);
            } else {
                $cut = round2($proportion * $cutPrice, 2);
                $sum = round2($sum + $cut, 2);
            }

            // 3.减价
            $this->setCutPrice($goods['goods_id'], $goods['option_id'], $cut, $actSign, $actRule);
        }
        unset($goods);
        return true;
    }

    /**
     * 为指定的商品设置运费
     * @param $goodsId
     * @param $optionId
     * @param float $price
     * @param array $rules
     * @return bool
     */
    public function setDispatchPrice($goodsId, $optionId, float $price, array $rules)
    {
        // 直接设置指定商品执行运费
        foreach ($this->goods as &$goods) {
            if ((empty($goodsId) || $goods['goods_id'] == $goodsId) &&
                (empty($optionId) || $goods['option_id'] == $optionId)) {

                $goods['dispatch_info']['exec_price'] = $price;
                $goods['dispatch_info']['type'] = '2';//设为固定运费
                $goods['dispatch_info']['rules'] = $rules;//设运费规则
                $goods['dispatch_info']['template_id'] = '0';//运费模板ID清空
            }
        }
        unset($goods);
        return true;
    }

    /**
     * 为商品片段设置总运费
     * @param float $price
     * @param array $rules
     * @return bool
     */
    public function setTotalDispatchPrice(float $price, array $rules)
    {
        return $this->setDispatchPrice(0, 0, $price, $rules);
    }

    private $originalDispatchPrice;

    /**
     * 获取商品总原价
     * @param int $dispatchType
     * @param array $address
     * @param string $payType
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function getDispatchOriginalPrice(int $dispatchType = 1, array $address, $payType = 'online')
    {
        //读取初始运费
        if (is_null($this->originalDispatchPrice)) {
            $this->originalDispatchPrice = $this->getDispatch($dispatchType, $address, $payType);
        }
        return $this->originalDispatchPrice;
    }

    /**
     * 获取应用规则
     * @param $goodsId
     * @param $optionId
     * @param string|null $actSign
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getAppliedRules($goodsId, $optionId, string $actSign = null): array
    {
        foreach ($this->goods as $goods) {
            if ((empty($goodsId) || $goods['goods_id'] == $goodsId) &&
                (empty($optionId) || $goods['option_id'] == $optionId)) {
                $activityPackage = (array)$goods['activity_package'];
                return isset($actSign) ? (array)$activityPackage[$actSign] : $activityPackage;
            }
        }
        return [];
    }

    /**
     * 商品可抵扣的积分/余额
     * @param int $goodsId
     * @param float $price
     * @param string $field
     * @param int $optionId
     * @author 青岛开店星信息技术有限公司
     */
    public function setGoodsCanDeduct(int $goodsId, float $price, string $field, int $optionId = 0)
    {
        foreach ($this->goods as &$goods) {
            if ((empty($goodsId) || $goods['goods_id'] == $goodsId) &&
                (empty($optionId) || $goods['option_id'] == $optionId)) {

                $goods[$field] = $price;
            }
        }
        unset($goods);
    }

    /**
     * 设置规则
     * @param string $actSign
     * @param array $rule
     * @param float $price
     * @param $goodsId
     * @param $optionId
     * @author 青岛开店星信息技术有限公司
     */
    private function setAppliedRules(string $actSign, array $rule, float $price, $goodsId, $optionId)
    {
        $rules = $this->getAppliedRules($goodsId, $optionId);
        $rules[$actSign] = ['rule' => $rule, 'price' => round2($price, 2)];
        $this->commonSet('activity_package', $rules, $goodsId, $optionId);
    }

    public function offsetExists($offset)
    {
        return isset($this->goods[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->goods[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->goods[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->goods[$offset]);
    }

    /**
     * 设置活动信息
     * @param $data
     * @author 青岛开店星信息技术有限公司
     */
    public function setActivity($data)
    {
        $this->activity = $data;
    }

    /**
     * 获取活动信息
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
