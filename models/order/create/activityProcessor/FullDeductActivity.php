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

use shopstar\models\order\create\interfaces\OrderCreatorActivityProcessorInterface;
use shopstar\models\order\create\OrderCreatorActivityAssistant;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

/**
 * 满额立减
 * Class CreditActivity
 * @package shopstar\models\order\create\activityProcessor
 */
class FullDeductActivity implements OrderCreatorActivityProcessorInterface
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
        // 获取满额立减设置
        $set = ShopSettings::get('sale.basic.enough');
        // 如果系统设置关闭 返回false
        if ($set['state'] <= 0) {
            return false;
        }
        // 满减设置
        $deductSet = $set['set'];
        // 订单支付价格
        $orderPrice = $assistant->getTotalPayPrice();
        // 根据满减价格倒序排序
        usort($deductSet, [$this, 'sortSale']);
        // 匹配优惠
        foreach ($deductSet as $value) {
            // 满足使用条件
            if (bccomp($orderPrice, $value['enough'], 2) >= 0) {
                $assistant->setTotalCutPrice($value['deduct'], 'full_deduct', ['full_deduct' => $value]);
                break;
            }
        }

        return $assistant;
    }

    /**
     * 根据满减额度排序 倒序
     * @param array $a
     * @param array $b
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private function sortSale(array $a, array $b)
    {
        if (bccomp($a['enough'], $b['enough'], 2) > 0) {
            return -1;
        } else if (bccomp($a['enough'], $b['enough'], 2) == 0) {
            return 0;
        } else {
            return 1;
        }
    }
}