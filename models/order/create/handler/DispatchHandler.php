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

use shopstar\constants\goods\GoodsDispatchTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\order\DispatchModel;
use shopstar\models\shop\ShopSettings;

class DispatchHandler implements HandlerInterface
{
    private $orderCreatorKernel;

    /**
     * HandlerInterface constructor.
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return mixed|void
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 普通配送才进行计算
        if ($this->orderCreatorKernel->orderData['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS) {
            return;
        }

        // 普通配送开启状态
        $expressEnable = ShopSettings::get('dispatch.express.enable');
        if (empty($expressEnable)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_EXPRESS_ENABLE_ERROR);
        }

        // 包邮商品总数
        $freeDispatchGoodsTotal = 0;

        // 读取所有的运费模板ID
        $dispatchTemplateIds = [];
        foreach ($this->orderCreatorKernel->orderGoodsData as $item) {
            if ($item['dispatch_info']['type'] == 0) { //商品默认包邮
                $freeDispatchGoodsTotal++;
                continue;
            }

            //如果是统一运费
            if ($item['dispatch_info']['type'] == 2 && $item['dispatch_info']['exec_price'] <= 0) {
                $freeDispatchGoodsTotal++;
                continue;
            }

            //如果是运费模板
            if ($item['dispatch_info']['type'] == 1 && !empty($item['dispatch_info']['template_id'])) {
                $dispatchTemplateIds[] = $item['dispatch_info']['template_id'];
            }
        }

        // 全部商品包邮，直接返回
        if ($freeDispatchGoodsTotal == count($this->orderCreatorKernel->orderGoodsData)) {
            $this->mountOrderData(0.00, null);
        }

        //如果是快递配送
        if ($this->orderCreatorKernel->orderData['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS && !empty($this->orderCreatorKernel->address)) {
            $result = $this->dispatchExpress($dispatchTemplateIds);
        } else {
            // 免运费(自提、无需发货)
            $result = [
                'price' => 0,
                'rules' => null,
            ];
        }

        // 如果提交订单时有错误则跑出异常
        if (!$this->orderCreatorKernel->isConfirm && is_error($result)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_DELIVERY_PRICE_ERROR);
        }

        $this->mountOrderData($result['price'], $result['rules']);

        return;
    }

    /**
     * 挂载到订单运费
     * @param float $price
     * @param $ruels
     * @author 青岛开店星信息技术有限公司
     */
    private function mountOrderData(float $price, $ruels)
    {
        // 挂载 配送价格、规则到订单数据
        $this->orderCreatorKernel->orderData['original_dispatch_price'] = $this->orderCreatorKernel->orderData['dispatch_price'] = $price;
        $this->orderCreatorKernel->orderData['dispatch_info'] = $ruels ?? null;
    }

    /**
     * 计算运费
     * @param $dispatchTemplateIds
     * @return array
     * @throws OrderCreatorException
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function dispatchExpress($dispatchTemplateIds)
    {
        // 查询快递模板
        $dispatchTemplates = [];
        if (!empty($dispatchTemplateIds)) {
            $dispatchTemplates = DispatchModel::find()->where(['id' => $dispatchTemplateIds, 'state' => 1])->indexBy('id')->asArray()->all();
            // 判断查询出的模板与商品的运费模板数量不符则报错(说明有的商品运费模板不存在)
            if (count($dispatchTemplates) != count(array_unique((array)$dispatchTemplateIds))) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_TEMPLATE_NOT_FOUND_ERROR);
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
                    throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_DISPATCH_HANDLER_NOT_IN_DELIVERY_AREA_ERROR);
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
