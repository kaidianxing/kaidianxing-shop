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
 * Class CreditActivity
 * @package shopstar\models\order\create\activityProcessor
 */
class CreditActivity implements OrderCreatorActivityProcessorInterface
{

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
        if ($set['credit_state'] == 0) {
            return false;
        }
        // 用户积分为0
        if ($orderCreatorKernel->member['credit'] == 0) {
            return false;
        }
        // 订单商品
        $goods = $orderCreatorKernel->goods;
        // 累计可抵扣的金额
        $sumDeductMoney = 0;
        // 累计抵扣积分
        $sumDeductCredit = 0;
        // 抵扣商品数
        $countDeductGoods = 0;
        
        foreach ($goods as &$item) {
            // 如果商品设置关闭 跳过
            if ($item['deduction_credit_type'] == 0) {
                continue;
            }
    
            // 商品总价
            $totalPrice = $assistant->getPayPrice($item['id'], $item['option_id'] ?? 0);
            // 商品数量
            $total = $assistant->getGoodsTotal($item['id'], $item['option_id'] ?? 0);
            
            // 价格小于0  直接返回
            if ($totalPrice <= 0) {
                continue;
            }
            
            // 不限制
            if ($item['deduction_credit_type'] == 1) {
                // 合计金额
                $sumDeductMoney = bcadd($sumDeductMoney, $totalPrice, 2);
                // 每个商品最多可抵扣的金额
                $item['can_deduct_money'] = $totalPrice;
            } else {
                // 限制数量
                // 不允许重复抵扣
                if ($item['deduction_credit_repeat'] == 0) {
                    // 如果商品金额小于最多抵扣金额 则用商品金额
                    if (bccomp($item['deduction_credit'], $totalPrice, 2) > 0) {
                        // 合计金额
                        $sumDeductMoney = bcadd($sumDeductMoney, $totalPrice, 2);
                        // 每个商品最多可抵扣的金额
                        $item['can_deduct_money'] = $totalPrice;
                    } else {
                        // 否则用限制金额
                        $sumDeductMoney = bcadd($sumDeductMoney, $item['deduction_credit'], 2);
                        // 每个商品最多可抵扣的金额
                        $item['can_deduct_money'] = $item['deduction_credit'];
                    }
                } else {
                    // 允许重复抵扣
                    $totalDeductMoney = bcmul($item['deduction_credit'], $total, 2);
                    // 如果商品金额小于最多抵扣金额 则用商品金额
                    if (bccomp($totalDeductMoney, $totalPrice, 2) > 0) {
                        // 合计金额
                        $sumDeductMoney = bcadd($sumDeductMoney, $totalPrice, 2);
                        // 每个商品最多可抵扣的金额
                        $item['can_deduct_money'] = $totalPrice;
                    } else {
                        // 否则用限制金额
                        $sumDeductMoney = bcadd($sumDeductMoney, $totalDeductMoney, 2);
                        // 每个商品最多可抵扣的金额
                        $item['can_deduct_money'] = $totalDeductMoney;
                    }
                }
            }
            // 商品数 +1
            $countDeductGoods ++;
            $assistant->setGoodsCanDeduct($item['id'], (float)$item['can_deduct_money'], 'can_deduct_money', $item['option_id'] ?? 0);
        }
        unset($item);
        
        // 可抵扣金额为0 则返回
        if ($sumDeductMoney == 0) {
            return $assistant;
        }
        // 积分-价格 比例
        $scale = bcdiv($set['basic_credit_num'], $set['credit_num'], 2);
        // 总抵扣积分数
        $sumDeductCredit = ceil(bcmul($sumDeductMoney, $scale, 2));
    
        // 跟用户剩余积分比较  如果用户积分不够，则使用用户剩余积分，重新计算 平均到每个商品
        if (bccomp($orderCreatorKernel->member['credit'], $sumDeductCredit, 2) < 0) {
            // 最多抵扣数量
            $orderCreatorKernel->confirmData['max_deduction_credit'] = $orderCreatorKernel->member['credit'];
            $sumDeductMoney = bcdiv($orderCreatorKernel->member['credit'], $scale, 2);
            $count = 0; // 累计抵扣次数
            $deductSum = 0; // 累计抵扣金额
            // 减免比例  在原来的抵扣积分乘该比例
            $subScale = bcdiv($orderCreatorKernel->member['credit'], $sumDeductCredit, 10);
            $sumDeductCredit = $orderCreatorKernel->member['credit'];
            foreach ($goods as &$item) {
                // 商品无抵扣金额 则跳出
                if ($item['can_deduct_money'] == 0) {
                    continue;
                }
                $count ++; // 抵扣次数加一
                if ($count == $countDeductGoods && $deductSum != $sumDeductMoney) {
                    // 算出还有多少可抵扣低分  分到最后一个商品上
                    $deductNum = bcsub($sumDeductMoney, $deductSum, 2);
                } else {
                    // 抵扣金额
                    $deductNum = bcmul($item['can_deduct_money'], $subScale, 2);
                    $deductSum = bcadd($deductSum, $deductNum, 2);
                }
                // 可抵扣金额
                $item['can_deduct_money'] = $deductNum;
                $assistant->setGoodsCanDeduct((int)$item['id'], $deductNum, 'can_deduct_money', $item['option_id'] ?? 0);
            }
            unset($item);

        } else {
            // 用户积分够 设置可抵扣金额
            $orderCreatorKernel->confirmData['max_deduction_credit'] = $sumDeductCredit;
        }
        // 积分对应抵扣金额
        $orderCreatorKernel->confirmData['max_deduction_credit_money'] = $sumDeductMoney;
        
        // 如果选择积分抵扣了
        if ($orderCreatorKernel->inputData['deduct_credit'] == 1) {
            $count = 0; // 累计抵扣次数
            $deductSum = 0; // 累计抵扣积分数
            // 抵扣规则
            foreach ($goods as $item) {
                // 商品无抵扣金额 则跳出
                if (empty($item['can_deduct_money'])) {
                    continue;
                }
                $count ++;
                // 判断最后一个商品 并且 累计抵扣不等于统计的累计抵扣
                if ($count == $countDeductGoods && $deductSum != $sumDeductCredit) {
                    // 算出还有多少可抵扣低分  分到最后一个商品上
                    $deductNum = $sumDeductCredit - $deductSum;
                } else {
                    // 抵扣积分数量
                    $deductNum = ceil(bcmul($item['can_deduct_money'], $scale, 2));
                    $deductSum += $deductNum;
                }
                // 抵扣积分数量
                $rule['credit'] = [
                    'credit' =>  $deductNum,
                    'scale' => $scale,
                    'price' => bcdiv($deductNum, $scale, 2),
                    'goods_id' => $item['id'],
                    'option_id' => $item['option_id'],
                ];
                // 扣除
                $assistant->setCutPrice($item['id'], $item['option_id'] ?? 0, $item['can_deduct_money'], 'credit', $rule);
            }
            
            // 下单
            if (!$orderCreatorKernel->isConfirm) {
                // 扣除积分
                $result = MemberModel::updateCredit($orderCreatorKernel->memberId, $orderCreatorKernel->confirmData['max_deduction_credit'], 0, 'credit', '2', '积分抵扣', MemberCreditRecordStatusConstant::CREDIT_STATUS_DEDUCTION,[
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
        MemberCreditRecordModel::updateAll(['order_id' => $orderCreatorKernel->orderData['id']],[
            'id' => self::$recordId
        ]);
    }
}