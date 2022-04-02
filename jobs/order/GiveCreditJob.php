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

namespace shopstar\jobs\order;

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * 消费送积分
 * Class GiveCreditJob
 * @package shopstar\jobs\order
 * @author 青岛开店星信息技术有限公司
 */
class GiveCreditJob extends BaseObject implements JobInterface
{

    /**
     * @var int 订单id
     */
    public $orderId = 0;

    /**
     * @var int 会员id
     */
    public $memberId = 0;

    /**
     * @param Queue $queue
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        // 查找订单
        $order = OrderModel::findOne(['id' => $this->orderId]);
        if (empty($order)) {
            echo "订单不存在";
            return;
        }
        // 判断订单状态
        if ($order->status < OrderStatusConstant::ORDER_STATUS_SUCCESS) {
            echo "订单状态错误";
            return;
        }
        // 查找设置
        $tradeSet = ShopSettings::get('sysset.credit');
        $num = 0; // 发送积分
        if ($tradeSet['give_credit_status'] == 1) {
            // 按订单结算
            if ($tradeSet['give_credit_type'] == 0) {
                // 计算积分数量
                $num = bcmul(bcdiv($tradeSet['give_credit_scale'], 100, 4), $order->pay_price);
            } else {
                // 按商品结算
                // 查找商品
                $goods = GoodsModel::find()
                    ->select(['give_credit_status', 'give_credit_num'])
                    ->alias('goods')
                    ->leftJoin(OrderGoodsModel::tableName() . ' order_goods', 'order_goods.goods_id=goods.id')
                    ->where(['order_goods.order_id' => $this->orderId])
                    ->get();
                foreach ($goods as $item) {
                    if ($item['give_credit_status'] == 1) {
                        $num += $item['give_credit_num'];
                    }
                }
            }

        }
        echo $num;
        if ($num > 0) {
            // 发放积分
            $res = MemberModel::updateCredit($this->memberId, $num, 0, 'credit', 1, '消费得积分', MemberCreditRecordStatusConstant::ORDER_GIVE_CREDIT);
            if (is_error($res)) {
                echo $res['message'] . "\n";
            }
        }

    }
}