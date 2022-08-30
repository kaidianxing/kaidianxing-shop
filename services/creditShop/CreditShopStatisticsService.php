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

use shopstar\constants\ClientTypeConstant;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\creditShop\CreditShopStatisticsModel;
use shopstar\models\creditShop\CreditShopViewLogModel;

class CreditShopStatisticsService
{
    /**
     * 每日统计
     * 定时任务调用
     * @return bool|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function createDayStatistic()
    {
        // 查找是否统计过
        $date = date('Y-m-d', strtotime('-1 day'));
        $isCalculate = CreditShopStatisticsModel::find()->where(['date' => $date])->exists();
        if ($isCalculate) {
            return $date . ' 该日期已统计';
        }

        // 统计
        self::calculate($date);

        return true;
    }

    /**
     * 统计
     * @param bool $date 统计日期
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private static function calculate(bool $date): void
    {
        $startTime = $date . ' 00:00:00';
        $endTime = $date . ' 23:59:59';

        $data = [
            'date' => $date,
        ];

        $order = CreditShopOrderModel::find()
            ->select([
                'id',
                'member_id',
                'pay_credit',
                'pay_price',
                'client_type'
            ])
            ->where(['status' => 1])
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->get();

        foreach ($order as $item) {
            $data['order_count']++;
            $data['order_credit_sum'] += $item['pay_credit'];
            $data['order_price_sum'] += $item['pay_price'];
            switch ($item['client_type']) {
                case ClientTypeConstant::CLIENT_H5:
                    $data['h5_order_price_sum'] += $item['pay_credit'];
                    break;
                case ClientTypeConstant::CLIENT_WECHAT:
                    $data['wechat_order_price_sum'] += $item['pay_credit'];
                    break;
                case ClientTypeConstant::CLIENT_WXAPP:
                    $data['wxapp_order_price_sum'] += $item['pay_credit'];
                    break;
                case ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO:
                    $data['byte_dance_order_price_sum'] += $item['pay_credit'];
                    break;
            }
        }

        // 商品数
        $data['goods_num'] = CreditShopGoodsModel::find()->where(['is_delete' => 0])->count();
        // 访客量
        $data['member_count'] = CreditShopViewLogModel::find()
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->count('distinct(member_id)');
        // 浏览量
        $data['view_count'] = CreditShopViewLogModel::find()
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->count();

        $statistics = new CreditShopStatisticsModel();
        $statistics->setAttributes($data);
        $statistics->save();
    }
}
