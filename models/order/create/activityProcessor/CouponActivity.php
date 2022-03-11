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

use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\order\create\interfaces\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\sale\CouponMemberModel;

/**
 * 优惠券处理器
 * Class CouponActivity
 * @package shopstar\models\order\create\activityProcessor
 */
class CouponActivity implements OrderCreatorActivityProcessorInterface
{
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

        // 返回可用优惠券列表  先查出所有 然后过滤
        $couponList = CouponMemberModel::getMemberCoupon($orderCreatorKernel->memberId);

        // 无优惠券直接跳出
        if (empty($couponList)) {
            return false;
        }
        // 订单商品
        $goods = $assistant->getGoodsInfo();

        // 优惠券遍历
        foreach ($couponList as $key => $coupon) {

            // 优惠券是否可用 默认不可用 满足条件设为可用
            $isCanUse = false;
            // 订单可用商品原价累计
            $totalPrice = 0;
            // 遍历商品判断

            foreach ($goods as $item) {
                // 商品限制  不可用则跳出

                if (!$this->checkGoodsLimit($item, $coupon)) {
                    continue;
                }
                // 商品价格 （折扣完的）
                $goodsPrice = $assistant->getPayPrice($item['goods_id'], $item['option_id'] ?? 0);

                // 该商品可用 优惠券可用置为true
                $isCanUse = true;
                // 累加价格
                $totalPrice = bcadd($totalPrice, $goodsPrice, 2);
            }
            // 没有达到最低使用额度 或者所有商品均不可用 就删除
            if (bccomp($totalPrice, $coupon['enough'], 2) < 0 || !$isCanUse) {
                unset($couponList[$key]);
                continue;
            }

            // 该优惠券可用 计算该优惠券的优惠额度
            // 满减
            if ($coupon['coupon_sale_type'] == 1) {
                if (bccomp($coupon['discount_price'], $totalPrice, 2) > 0) {
                    $couponList[$key]['can_deduct_price'] = $totalPrice;
                } else {
                    $couponList[$key]['can_deduct_price'] = $coupon['discount_price'];
                }
            } else {
                // 打折
                $scale = bcsub(10, $coupon['discount_price'], 2);
                $couponList[$key]['can_deduct_price'] = bcmul($totalPrice, bcdiv($scale, 10, 2), 2);
            }
            // 保存该优惠券可在订单上折扣的总金额
            $couponList[$key]['can_use_order_price'] = $totalPrice;
            $couponList[$key]['show_end_time'] = mb_substr($coupon['end_time'], 0, 10);
        }
        // 过滤完为空 跳出
        if (empty($couponList)) {
            return false;
        }

        // 默认选中最优优惠券  根据 can_deduct_price 排序
        usort($couponList, [$this, 'sortSale']);
        $couponList[0]['is_default'] = 1;

        // 放到订单信息中
        $assistant->setActivityReturnData('coupon', $couponList);

        // 选中的优惠券
        $selectCouponId = $orderCreatorKernel->inputData['select_coupon_id'];
        // 默认最优优惠券
        if ($selectCouponId == '') {
            $selectCouponId = $couponList[0]['id'];
        }

        // 返回前端 选择的优惠券id
        $orderCreatorKernel->confirmData['select_coupon_id'] = $selectCouponId;

        // 如果选了优惠券  -1 表示不使用优惠券
        if ($selectCouponId != -1) {
            foreach ($couponList as $coupon) {
                if ($coupon['id'] == $selectCouponId) {
                    // 计算可打折比例
                    // $scale = bcdiv($coupon['can_deduct_price'], $coupon['can_use_order_price'], 10);
                    // 折扣总金额 用来处理精度缺失  如果最终 不等于 $coupon['can_deduct_price']  把最后一个商品加上该差价
                    $sumDeduct = 0;
                    // 商品id 进行过折扣的
                    $goodsInfo = [];
                    // 给商品打折
                    foreach ($goods as $item) {
                        // 不可用则跳出
                        if (!$this->checkGoodsLimit($item, $coupon)) {
                            continue;
                        }
                        // 商品价格
                        $goodsPrice = $assistant->getPayPrice($item['goods_id'], $item['option_id'] ?? 0);
                        $scale = bcdiv($goodsPrice, $coupon['can_use_order_price'], 10);
                        // 折扣金额 也可作为最后一件折扣商品的折扣金额 如过产生误差时 使用此金额
                        $deduct = bcmul($coupon['can_deduct_price'], $scale, 2);
                        $sumDeduct = bcadd($sumDeduct, $deduct, 2);
                        // 设置商品金额
                        $assistant->setCutPrice($item['goods_id'], $item['option_id'] ?? 0, $deduct, 'coupon', ['coupon' => $coupon]);
                        $goodsInfo[] = ['id' => $item['goods_id'], 'option_id' => $item['option_id']];
                    }

                    // 误差 如有误差 把差价加到最后一次折扣商品中
                    if (bccomp($coupon['can_deduct_price'], $sumDeduct, 2) != 0) {
                        // 误差
                        $sub = bcsub($coupon['can_deduct_price'], $sumDeduct, 2);
                        // 最后一次折扣商品
                        $last = end($goodsInfo);
                        // 先加上之前减的  $deduct 为上边最后一次循环的值 保留
                        $assistant->setCutPrice($last['id'], $last['option_id'] ?? 0, -$deduct, 'coupon', ['coupon' => $coupon]);
                        // 应该的折扣 差价加上一次抵扣的
                        $deduct = bcadd($deduct, $sub, 2);
                        // 重新设置折扣
                        $assistant->setCutPrice($last['id'], $last['option_id'] ?? 0, $deduct, 'coupon', ['coupon' => $coupon]);
                    }

                    // 跳出循环
                    break;
                }
            }
        }

        return $assistant;
    }

    /**
     * 根据优惠力度排序 倒序
     * @param array $a
     * @param array $b
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private function sortSale(array $a, array $b)
    {
        if (bccomp($a['can_deduct_price'], $b['can_deduct_price'], 2) > 0) {
            return -1;
        } else if (bccomp($a['can_deduct_price'], $b['can_deduct_price'], 2) == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * 检查商品是否可以优惠券
     * @param array $goods
     * @param array $coupon
     * @return bool 返回 true 可用  false 不可用
     * @author 青岛开店星信息技术有限公司
     */
    private function checkGoodsLimit(array $goods, array $coupon)
    {
        // 会员折扣限制
        if ($coupon['coupon_sale_limit'] == 1 && !empty($goods['activity_package']['member_price'])) {
            return false;
        }
        // 商品使用限制
        if ($coupon['goods_limit'] != 0) {
            $limit_goods_ids = array_column($coupon['map'], 'goods_cate_id');

            // 商品过滤 根据id  不在允许的商品id里
            if ($coupon['goods_limit'] == 1 && !in_array($goods['goods_id'], $limit_goods_ids)) {
                return false;
            } else if ($coupon['goods_limit'] == 2 && in_array($goods['goods_id'], $limit_goods_ids)) {
                // 在不允许使用的商品里
                return false;
            } else if ($coupon['goods_limit'] == 3) {
                // 获取商品分类
                $goodsCate = GoodsCategoryMapModel::find()->where(['goods_id' => $goods['goods_id']])->indexBy('category_id')->get();
                $goodsCateIds = array_keys($goodsCate);
                // 取交集
                $intersect = array_intersect($limit_goods_ids, $goodsCateIds);
                if (empty($intersect)) {
                    // 不在允许使用的分类里
                    return false;
                }
            }
        }

        return true;
    }
}