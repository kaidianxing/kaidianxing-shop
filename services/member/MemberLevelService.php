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

namespace shopstar\services\member;

use shopstar\bases\service\BaseService;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class MemberLevelService extends BaseService
{

    /**
     * 会员自动升级
     * @param int $memberId
     * @param float $payPrice
     * @param int $orderId
     * @return bool|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function autoUpLevel(int $memberId, float $payPrice, int $orderId)
    {
        //会员当前的等级
        $member = MemberModel::findOne(['id' => $memberId]);
        if (empty($member)) return false;

        $memberLevel = MemberLevelModel::find()->where(['id' => $member->level_id])->asArray()->one();
        if (empty($memberLevel)) return;

        //所有大于当前会员等级的会员等级
        $level = MemberLevelModel::find()->where([
            'and',
            ['state' => 1],
            ['update_condition' => [1, 2, 3]],
            ['>', 'level', $memberLevel['level']]
        ])->orderBy(['level' => SORT_DESC])->asArray()->all();

        if (empty($level)) return;

        $orderWhere = [
            'and',
            ['member_id' => $memberId],
            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
            ['is_count' => 1],
        ];

        //所有订单数
        $orderNum = OrderModel::find()->where($orderWhere)->count();

        //购买的订单金额
        $orderPrice = OrderModel::find()->where($orderWhere)->select('sum(pay_price) - sum(refund_price) as pay_price')->asArray()->one()['pay_price'] ?: 0.00;

        //购买的商品id
        $goodsId = OrderGoodsModel::find()->where(['order_id' => $orderId])->asArray()->select(['goods_id'])->column();

        //从大到小遍历商品等级，第一个符合条件的就是最高的等级
        foreach ($level as $levelIndex => $levelItem) {
            //满足订单数
            if ($levelItem['update_condition'] == 1 && !empty($orderNum) && $levelItem['order_count'] <= $orderNum) {
                $member->level_id = $levelItem['id'];
                break;
            }

            //满足订单金额
            if ($levelItem['update_condition'] == 2 && !empty($orderPrice) && $levelItem['order_money'] <= $orderPrice) {
                $member->level_id = $levelItem['id'];
                break;

            }

            //满足订单商品
            if ($levelItem['update_condition'] == 3 && !empty($goodsId) && !empty($levelItem['goods_ids'])) {
                $levelItem['goods_ids'] = Json::decode($levelItem['goods_ids']);
                if (array_intersect($levelItem['goods_ids'], $goodsId)) {
                    $member->level_id = $levelItem['id'];
                    break;
                }
            }
        }

        return $member->save();
    }

}