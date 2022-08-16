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

namespace shopstar\services\creditShop\handler;

use shopstar\constants\goods\GoodsDispatchTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\exceptions\creditShop\CreditShopOrderException;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\order\DispatchModel;
use shopstar\models\shop\ShopSettings;

class DispatchHandler implements HandlerInterface
{
    /**
     * 订单实体类
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public OrderCreatorKernel $orderCreatorKernel;

    /**
     * GoodsHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 处理运费挂载
     * @return void
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 普通快递的运费计算

        // 普通配送才进行计算
        if ($this->orderCreatorKernel->orderData['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS) {
            return;
        }

        // 配送方式状态
        $expressEnable = ShopSettings::get('dispatch.express.enable');
        if (empty($expressEnable)) {
            throw new CreditShopOrderException(CreditShopOrderException::DISPATCH_HANDLER_CREDIT_SHOP_DISPATCH_EXPRESS_ERROR);
        }

        $goods = $this->orderCreatorKernel->goods[0];
        // 看设置是否包邮
        if ($goods['dispatch_type'] == 1 || $goods['shop_goods_dispatch_type'] == 0) {
            // 包邮
            $this->mountOrderData(0.00, null);
            return ;
        }

        // 计算运费
        //如果是快递配送
        if (!empty($this->orderCreatorKernel->address)) {
            $result = $this->dispatchExpress($goods['dispatch_id'] ?? '');
        } else {
            // 免运费(自提、无需发货)
            $result = [
                'price' => 0,
                'rules' => null,
            ];
        }

        // 如果提交订单时有错误则跑出异常
        if (!$this->orderCreatorKernel->isConfirm && is_error($result)) {
            throw new CreditShopOrderException(CreditShopOrderException::DISPATCH_HANDLER_CREDIT_SHOP_DELIVERY_PRICE_ERROR);
        }

        $this->mountOrderData($result['price'], $result['rules']);
    }

    /**
     * 挂载到订单运费
     * @param float $price
     * @param $rules
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private function mountOrderData(float $price, $rules)
    {
        // 挂载 配送价格、规则到订单数据
        $this->orderCreatorKernel->orderData['original_dispatch_price'] = $this->orderCreatorKernel->orderData['dispatch_price'] = $price;
        $this->orderCreatorKernel->orderData['dispatch_info'] = $rules ?? null;
    }

    /**
     * 计算运费
     * @param $dispatchTemplateIds
     * @return array
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    private function dispatchExpress($dispatchTemplateIds): array
    {
        // 查询快递模板
        $dispatchTemplates = [];
        if (!empty($dispatchTemplateIds)) {
            $dispatchTemplates = DispatchModel::find()->where(['id' => $dispatchTemplateIds, 'state' => 1])->indexBy('id')->asArray()->all();

            // 判断查询出的模板与商品的运费模板数量不符则报错(说明有的商品运费模板不存在)
            if (count($dispatchTemplates) != count(array_unique((array)$dispatchTemplateIds))) {
                throw new CreditShopOrderException(CreditShopOrderException::DISPATCH_HANDLER_CREDIT_SHOP_TEMPLATE_NOT_FOUND_ERROR);
            }
        }

        $totalDispatchPrice = 0;
        $totalDispatchRules = [];

        // 运费模版分组
        $dispatchGroup = [];

        // 固定运费组
        $fixedDispatch = [];

        // 检测商品的不配送区域
        foreach ($this->orderCreatorKernel->orderGoodsData as $item) {
            $item['dispatch_info']['goods_id'] = $item['goods_id'];
            //运费模板
            if ($item['dispatch_info']['type'] == GoodsDispatchTypeConstant::GOODS_DISPATCH_TYPE_TEMPLATE) {
                // 检测商品配送区域

                $result = DispatchModel::checkDispatchArea($item['dispatch_info']['template_id'], $dispatchTemplates, $this->orderCreatorKernel->address['area_code']);
                if (!$result && !$this->orderCreatorKernel->isConfirm) {
                    throw new CreditShopOrderException(CreditShopOrderException::DISPATCH_HANDLER_CREDIT_SHOP_NOT_IN_DELIVERY_AREA_ERROR);
                }

                // 如果组中未塞入时塞入
                $dispatchId = (int)$item['dispatch_info']['template_id'];
                $dispatchGroup[$dispatchId][] = $item;
            } else {
                $fixedDispatch[] = $item;
            }
        }

        // 处理运费分组
        if (!empty($dispatchGroup)) {
            // 首重最高运费
            $dispatchResult = DispatchModel::getMaxDispatch($dispatchTemplates, $this->orderCreatorKernel->address);
            // 计算
            foreach ($dispatchGroup as $dispatchId => $goods) {
                $dispatchRule = $dispatchResult['dispatchList'][$dispatchId];
                if (empty($dispatchRule)) {
                    continue;
                }


                //计算数量 计件 || 计重
                $param = 0;
                foreach ($goods as $goodsItem) {
                    if ($dispatchRule['type'] == 1) {
                        // 件
                        $param += $goodsItem['total'];
                    } elseif ($dispatchRule['type'] == 0) {
                        // 重
                        $param += $goodsItem['weight'] * $goodsItem['total'];
                    }
                }

                // 计算首重 || 首件
                if ($dispatchResult['maxDispatchId'] == $dispatchId) {
                    $totalDispatchPrice += $dispatchResult['maxDispatch']['first_price'];

                    if ($dispatchResult['maxDispatch']['first_num'] >= $param) {
                        $param = 0;
                    } else {
                        $param = $param - $dispatchResult['maxDispatch']['first_num'];
                    }
                }

                // 计算续重 || 首件
                if ($param > 0 && $dispatchRule['second_num'] > 0) {
                    $totalDispatchPrice += ceil($param / $dispatchRule['second_num']) * $dispatchRule['second_price'];
                }

                $totalDispatchRules[] = $dispatchRule;
            }
        }

        // 处理固定运费(取最大固定运费)
        if (!empty($fixedDispatch)) {
            $dispatchPrice = 0;
            $rules = null;
            foreach ($fixedDispatch as $item) {
                //包邮不计算
                if ($item['dispatch_info']['type'] != GoodsDispatchTypeConstant::GOODS_DISPATCH_TYPE_FREE && $item['dispatch_info']['exec_price'] > $dispatchPrice) {
                    $dispatchPrice = $item['dispatch_info']['exec_price'];
                    $rules = $item['dispatch_info'];
                }
            }

            // 运费
            $totalDispatchPrice += $dispatchPrice;
            // 规则
            $totalDispatchRules[] = $rules;
        }

        return [
            'price' => $totalDispatchPrice,
            'rules' => $totalDispatchRules,
        ];
    }
}
