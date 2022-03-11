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

namespace shopstar\models\order\create\activityProcessor;

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\create\interfaces\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

/**
 * 积分抵扣处理器
 * Class BalanceActivity
 * @package shopstar\models\order\create\activityProcessor
 */
class BalanceActivity implements OrderCreatorActivityProcessorInterface
{

    /**
     *  记录id
     * @var
     * @author 青岛开店星信息技术有限公司.
     */
    public static $recordId;

    /**
     * 接收优惠活动分发器指派过来的活动处理任务
     * ---------------------------------------------------
     * 优惠处理器processor方法使用注意 ：
     * 期望能返回\shopstar\models\order\OrderAssistant对象，
     * 以便订单能够自动处理订单结果。
     * 如果你返回其他类型的值，
     * 那么你需要自己修改订单数据，因为这将不能自动合并优惠结果！
     * ---------------------------------------------------
     * @param OrderCreatorActivityAssistant $assistant 传递过来的订单助手实例，里面包含了当前活动所支持的商品片段
     * @param array $activityInfo 当前活动信息都会原样传递回来
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实例，里面包含了关于当前订单的一切
     *
     * @return OrderCreatorActivityAssistant|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function processor(OrderCreatorActivityAssistant $assistant, array $activityInfo, OrderCreatorKernel &$orderCreatorKernel)
    {
        // 获取积分余额抵扣设置
        $set = ShopSettings::get('sale.basic.deduct');
        // 如果系统设置关闭 返回false
        if ($set['balance_state'] == 0) {
            return false;
        }
        // 用户余额为0 跳出
        if ($orderCreatorKernel->member['balance'] == 0) {
            return false;
        }
        $goods = $orderCreatorKernel->goods;
        // 合计折扣余额
        $sumDeductBalance = 0;

        // 抵扣商品数
        $countDeductGoods = 0;
        foreach ($goods as &$item) {
            // 如果商品设置关闭 返回false
            if ($item['deduction_balance_type'] == 0) {
                continue;
            }
            // 价格小于0  直接返回
            if ($item['price'] <= 0) {
                continue;
            }
            // 订单上的商品信息
            $orderGoodsInfo = [];

            $goodsInfo = $assistant->getGoodsInfo();
            // 匹配商品信息
            foreach ($goodsInfo as $value) {
                if ($value['goods_id'] == $item['id'] && $value['option_id'] == $item['option_id']) {
                    $orderGoodsInfo = $value;
                }
            }
            // 支付金额
            $totalPrice = $assistant->getPayPrice($item['id'], $item['option_id'] ?? 0);
            if ($totalPrice == 0) {
                continue;
            }

            // 不限制
            if ($item['deduction_balance_type'] == 1) {
                // 可抵扣余额
                $item['can_deduct_balance'] = $totalPrice;
                // 可抵扣余额合计
                $sumDeductBalance = bcadd($sumDeductBalance, $totalPrice, 2);
            } else {

                // 不允许重复抵扣
                if ($item['deduction_balance_repeat'] == 0) {
                    // 如果可抵扣大于商品价格 使用商品价格 否则使用抵扣金额
                    if (bccomp($item['deduction_balance'], $totalPrice, 2) > 0) {
                        $item['can_deduct_balance'] = $totalPrice;
                        $sumDeductBalance = bcadd($sumDeductBalance, $totalPrice, 2);
                    } else {
                        $item['can_deduct_balance'] = $item['deduction_balance'];
                        $sumDeductBalance = bcadd($sumDeductBalance, $item['deduction_balance'], 2);
                    }
                } else {
                    // 允许重复抵扣
                    // 可抵扣总额合计
                    $totalDeductMoney = bcmul($item['deduction_balance'], $orderGoodsInfo['total'], 2);
                    // 如果可抵扣大于商品价格 使用商品价格 否则使用抵扣金额
                    if (bccomp($totalDeductMoney, $totalPrice, 2) > 0) {
                        // 可抵扣余额
                        $item['can_deduct_balance'] = $totalPrice;
                        $sumDeductBalance = bcadd($sumDeductBalance, $totalPrice, 2);
                    } else {
                        // 可抵扣余额
                        $item['can_deduct_balance'] = $totalDeductMoney;
                        $sumDeductBalance = bcadd($sumDeductBalance, $totalDeductMoney, 2);
                    }
                }
            }

            // 商品数 +1
            $countDeductGoods++;
            // 保存起来 该商品可抵扣的余额
            $assistant->setGoodsCanDeduct((int)$item['id'], (float)$item['can_deduct_balance'], 'can_deduct_balance', $item['option_id'] ?? 0);
        }
        unset($item);

        // 抵扣余额为0 跳出
        if ($sumDeductBalance == 0) {
            return $assistant;
        }

        // 跟用户剩余余额比较  如果用户余额不够，则使用用户剩余余额，重新计算 平均到每个商品
        if (bccomp($orderCreatorKernel->member['balance'], $sumDeductBalance, 2) < 0) {
            // 设置可抵扣金额
            $orderCreatorKernel->confirmData['max_deduction_balance'] = $orderCreatorKernel->member['balance'];
            // 减少比例
            $subScale = bcdiv($orderCreatorKernel->member['balance'], $sumDeductBalance, 10);
            $count = 0; // 累计抵扣次数
            $deductSum = 0; // 累计抵扣金额
            foreach ($goods as &$item) {
                // 商品无抵扣金额 则跳出
                if ($item['can_deduct_balance'] == 0) {
                    continue;
                }
                $count++; // 抵扣次数加一
                if ($count == $countDeductGoods && $deductSum != $orderCreatorKernel->confirmData['max_deduction_balance']) {
                    // 算出还有多少可抵扣余额  分到最后一个商品上
                    $deductNum = bcsub($orderCreatorKernel->confirmData['max_deduction_balance'], $deductSum, 2);
                } else {
                    // 抵扣金额
                    $deductNum = bcmul($item['can_deduct_balance'], $subScale, 2);
                    $deductSum = bcadd($deductSum, $deductNum, 2);
                }
                $item['can_deduct_balance'] = $deductNum;
                // 保存起来 该商品可抵扣的余额
                $assistant->setGoodsCanDeduct((int)$item['id'], (float)$deductNum, 'can_deduct_balance', $item['option_id'] ?? 0);
            }
        } else {
            // 设置可抵扣金额
            $orderCreatorKernel->confirmData['max_deduction_balance'] = $sumDeductBalance;
        }

        // 选了余额抵扣
        if ($orderCreatorKernel->inputData['deduct_balance'] == 1) {
            $goods = $assistant->getGoodsInfo();
            foreach ($goods as $item) {
                // 不支持抵扣则跳出
                if (empty($item['can_deduct_balance'])) {
                    continue;
                }
                $rule['balance'] = [
                    'goods_id' => $item['goods_id'],
                    'option_id' => $item['option_id'],
                    'price' => $item['can_deduct_balance']
                ];
                $assistant->setCutPrice($item['goods_id'], $item['option_id'] ?? 0, $item['can_deduct_balance'], 'balance', $rule);
            }

            // 下单
            if (!$orderCreatorKernel->isConfirm) {
                // 扣除用户余额
                $result = MemberModel::updateCredit($orderCreatorKernel->memberId, $orderCreatorKernel->confirmData['max_deduction_balance'], 0, 'balance', '2', '余额抵扣', MemberCreditRecordStatusConstant::BALANCE_STATUS_DEDUCTION, [
                    'get_record' => true
                ]);

                self::$recordId = $result['record']['id'];
            }
        }

        return $assistant;
    }

    /**
     * @param OrderCreatorKernel $orderCreatorKernel
     * @author 青岛开店星信息技术有限公司.
     */
    public static function afterCreator(OrderCreatorKernel $orderCreatorKernel)
    {
        //添加记录和订单映射关系
        MemberCreditRecordModel::updateAll(['order_id' => $orderCreatorKernel->orderData['id']], [
            'id' => self::$recordId
        ]);
    }
}