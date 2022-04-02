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

namespace shopstar\admin\statistics;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\statistics\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\member\MemberBrowseFootprintModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\statistics\StatisticsModel;
use shopstar\services\statistics\StatisticsService;

/**
 * 商品统计
 * Class GoodsController
 * @package shopstar\admin\statistics
 * @author 青岛开店星信息技术有限公司
 */
class GoodsController extends KdxAdminApiController
{

    /**
     * 商品信息概览
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $goodsDate = RequestHelper::get('goods_date', date('Y-m-d', strtotime('-1 days')));

        // 商品信息
        if ($goodsDate == DateTimeHelper::now(false)) {
            $data = StatisticsService::calculate($goodsDate, ['goods_pv_count', 'cart_goods_count', 'pay_goods_count']);
        } else {
            $data = StatisticsModel::find()
                ->select('goods_pv_count, cart_goods_count, pay_goods_count')
                ->where(['statistic_date' => $goodsDate])
                ->asArray()
                ->one();
        }

        return $this->result($data);
    }

    /**
     * 商品排行
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRank()
    {
        $startTime = RequestHelper::get('start_time', date('Y-m-d', strtotime('-7 days'))) . " 00:00:00";
        $endTime = RequestHelper::get('end_time', date('Y-m-d', strtotime('-1 days'))) . " 23:59:59";
        $field = RequestHelper::get('field');
        $sort = RequestHelper::get('sort');

        $orderBy = [];

        // 排序常量
        if ($sort == 'desc') {
            $sort = SORT_DESC;
        } else if ($sort == 'asc') {
            $sort = SORT_ASC;
        }
        // 排序字段
        if (!empty($field)) {
            $orderBy[$field] = $sort;
        }
        // 排序
        $orderBy['goods.sort_by'] = SORT_DESC;
        // 创建时间
        $orderBy['goods.created_at'] = SORT_DESC;

        $pvSelect = '(select count(foot.id) from ' . MemberBrowseFootprintModel::tableName() . ' foot WHERE foot.goods_id=goods.id and foot.created_at between "' . $startTime . '" and "' . $endTime . '") as pv';

        $params = [
            'select' => [
                'goods.id',
                'goods.title',
                'goods.thumb',
                'has_option',
                'goods.status',
                'goods.stock',
                'goods.is_deleted',
                // 'COALESCE(sum(order_goods.total), 0) as total',
                'goods.real_sales as total',
                'ifnull(sum(order_goods.price),0) as price',
                $pvSelect
            ],
            'andWhere' => [
                ['between', 'order_goods.pay_time', $startTime, $endTime]
            ],
            'leftJoin' => [
                OrderGoodsModel::tableName() . ' order_goods',
                'order_goods.goods_id = goods.id and order_goods.is_count = 1 and order_goods.shop_goods_id=0'
            ],
            'groupBy' => 'goods.id',
            'alias' => 'goods',
            'orderBy' => $orderBy
        ];
        $list = GoodsModel::getColl($params, [
            'callable' => function (&$row) {
                if ($row['status'] == 1 && $row['stock'] > 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 1; // 上架
                } else if ($row['status'] == 1 && $row['stock'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 2; // 售罄
                } else if ($row['status'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 3; // 下架
                } else if ($row['is_deleted'] == 1) {
                    $row['status'] = 4; // 回收站
                }
            }
        ]);

        return $this->result($list);
    }

    /**
     * 详情
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $goodsId = RequestHelper::get('id');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::GOODS_DETAIL_PARAMS_ERROR);
        }
        // 获取商品
        $goods = GoodsModel::findOne(['id' => $goodsId]);

        if (empty($goods) || $goods->has_option != 1) {
            throw new GoodsException(GoodsException::GOODS_DETAIL_NOT_EXISTS);
        }
        // 获取所有规格
        $options = GoodsOptionModel::findAll(['goods_id' => $goodsId]);
        $data = [];
        // 分别获取销售量
        foreach ($options as $item) {
            $detail = OrderGoodsModel::find()
                ->select(['sum(price) price', 'sum(total) total'])
                ->where([
                    'and',
                    ['goods_id' => $goodsId],
                    [
                        'or',
                        ['refund_type' => 0],
                        [
                            'and',
                            ['<>', 'refund_type', 3],
                            ['>', 'refund_status', 9]
                        ]
                    ],
                    ['option_id' => $item->id],
                    ['shop_goods_id' => 0]
                ])->one();
            $data[] = [
                'id' => $item->id,
                'title' => $item->title,
                'price' => $detail->price,
                'total' => $detail->total
            ];
        }
        return $this->result(['data' => $data]);
    }

}