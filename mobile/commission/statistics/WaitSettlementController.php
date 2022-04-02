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

namespace shopstar\mobile\commission\statistics;

use shopstar\helpers\DateTimeHelper;
use shopstar\mobile\commission\CommissionClientApiController;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;

/**
 * 待入账佣金
 * Class WaitSettlementController
 * @package shopstar\mobile\commission\statistics;
 * @author 青岛开店星信息技术有限公司
 */
class WaitSettlementController extends CommissionClientApiController
{

    /**
     * 获取待入账佣金总数
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetTotal()
    {
        return $this->result([
            'total' => CommissionAgentTotalModel::getWaitSettlementPrice($this->memberId),
        ]);
    }

    /**
     * 待入账佣金列表
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetList()
    {
        $where = [
            ['order_goods.agent_id' => $this->memberId],
            ['order_goods.is_count_refund' => 1],
            [
                'or',
                ['>', 'order.account_time', DateTimeHelper::now()],
                ['order.account_time' => 0]
            ],
            ['order_goods.status' => 0],
            ['order_goods.agent_id' => $this->memberId]
        ];

        $params = [
            'alias' => 'order_goods',
            'leftJoins' => [
                [CommissionOrderModel::tableName() . ' as order', 'order.order_id = order_goods.order_id'],
                [OrderGoodsModel::tableName() . ' as shop_order_goods', 'shop_order_goods.id = order_goods.order_goods_id and shop_order_goods.shop_goods_id=0'],
                [OrderModel::tableName() . ' as shop_order', 'shop_order.id = order.order_id'],
            ],
            'andWhere' => $where,
            'select' => [
                'order_goods.order_goods_id',
                'order_goods.can_withdraw_commission as commission',
                'order_goods.ladder_commission',
                'shop_order_goods.title',
                'shop_order_goods.goods_id',
                'shop_order_goods.option_title',
                'shop_order_goods.total',
                'shop_order_goods.price',
                'shop_order_goods.created_at',
                'shop_order_goods.thumb',
                'shop_order.activity_type',
                'shop_order.id',
            ],
            'orderBy' => [
                'order_goods.id' => SORT_DESC,
            ],
        ];
        $result = CommissionOrderGoodsModel::getColl($params, [
            'callable' => function (&$row) {
            }
        ]);

        return $this->result($result);
    }

}