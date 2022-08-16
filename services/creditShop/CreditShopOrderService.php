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

namespace shopstar\services\creditShop;

use shopstar\models\creditShop\CreditShopOrderModel;

/**
 * 积分商城订单服务类
 * Class CreditShopOrderService.
 * @package shopstar\services\creditShop
 */
class CreditShopOrderService
{
    /**
     * 获取渠道金额
     * @param $clientType
     * @param string $type
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getClientPrice($clientType = 0, string $type = 'pay_price')
    {
        $query = CreditShopOrderModel::find()->where(['status' => 1]);

        if ($clientType != 0) {
            $query->andWhere(['client_type' => $clientType]);
        }

        return $query->sum($type) ?? 0;
    }

    /**
     * 获取已购买数量
     * 除去维权
     * @param int $goodsId
     * @param int $memberId
     * @param int $limitDay
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getBuyTotal(int $goodsId, int $memberId, int $limitDay = 0)
    {
        $query = CreditShopOrderModel::find()->where(['goods_id' => $goodsId, 'member_id' => $memberId])->andWhere(['<>', 'status', -1]);

        if ($limitDay != 0) {
            $startTime = date('Y-m-d H:i:s', strtotime('- '.$limitDay.' days'));
            $query->andWhere(['>', 'created_at', $startTime]);
        }

        return $query->sum('total') ?? 0;
    }
}
