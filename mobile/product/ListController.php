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

namespace shopstar\mobile\product;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\services\goods\GoodsMobileQueryService;

/**
 * @author 青岛开店星信息技术有限公司
 */
class ListController extends BaseMobileApiController
{
    /**
     * 允许不登录
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $configActions = [
        'allowNotLoginActions' => [
            'get-list',
            'get-activity',
            'get-activity-goods',
        ]
    ];

    /**
     * 获取商品列表
     * @return \yii\web\Response
     * @throws GoodsException
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList(): \yii\web\Response
    {
        $get = RequestHelper::get();

        $goodsService = new GoodsMobileQueryService();
        $goodsService->initParams($get, [
            'memberId' => $this->memberId,
            'member' => $this->member,
            'clientType' => $this->clientType,
        ]);
        $list = $goodsService->getGoodsList([
            'activity_id' => RequestHelper::get('activity_id', 0),
            'get_activity' => RequestHelper::get('get_activity', 0),
        ]);

        return $this->success($list);
    }

    /**
     * 获取活动信息
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetActivity()
    {
        $activityId = RequestHelper::get('activity_id');
        if (empty($activityId)) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_LIST_ACTIVITY_PARAMS_ERROR);
        }
        $activity = ShopMarketingModel::find()->where(['id' => $activityId])->first();
        if (empty($activity)) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_LIST_ACTIVITY_NOT_EXISTS);
        }

        return $this->result($activity);
    }


    /**
     * 获取活动商品列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetActivityGoods()
    {
        $id = RequestHelper::getArray('id');
        $activityType = RequestHelper::get('activity_type');

        $leftJoins = [];
        $select = [
            'goods.title',
            'goods.id',
            'goods.thumb',
            'goods.type',
            'goods.has_option',
            'goods.price',
            'goods.min_price',
            'goods.max_price',
            'goods.stock',
            '(goods.sales + goods.real_sales) as sales',
            'goods.original_price',
            'goods.is_commission',
            'goods.is_hot',
            'goods.sub_name',
            'goods.short_name',
            'goods.status',
            'goods.is_recommand',
            'goods.is_new',
            'goods.cost_price',
            'goods.video_thumb',
            'goods.unit',
            'activity_goods.start_time',
            'activity_goods.end_time',
            'activity_goods.activity_id',
        ];

        $leftJoins[] = [GoodsModel::tableName() . ' goods', 'goods.id=activity_goods.goods_id'];

        $params = [
            'alias' => 'activity_goods',
            'select' => $select,
            'where' => [
                'and',
                ['activity_goods.goods_id' => $id],
                ['activity_goods.activity_type' => $activityType],
                ['>', 'activity_goods.end_time', DateTimeHelper::now()],
                ['activity_goods.is_delete_activity' => 0],
                [
                    'or',
                    [
                        'and',
                        ['activity_goods.is_preheat' => 0],
                        ['<', 'activity_goods.start_time', DateTimeHelper::now()],
                    ],
                    [
                        'and',
                        ['activity_goods.is_preheat' => 1],
                        ['<', 'activity_goods.preheat_time', DateTimeHelper::now()],
                    ]
                ]
            ],
            'andWhere' => ['find_in_set(' . $this->clientType . ',client_type)'],
            'leftJoins' => $leftJoins,
            'orderBy' => [
                'activity_goods.start_time' => SORT_ASC,
                'goods.sort_by' => SORT_DESC,
                'goods.created_at' => SORT_DESC,
            ],
            'groupBy' => [
                'goods.id'
            ]
        ];

        $list = GoodsActivityModel::getColl($params);

        // 组合
        foreach ($list['list'] as $index => $value) {
            $activity = ShopMarketingModel::getActivityInfo($value['id'], $this->clientType, 'seckill', $value['has_option'], ['activity_id' => $value['activity_id'], 'not_check_time' => 1]);
            if (!is_error($activity)) {
                $list['list'][$index]['activities'][$activityType] = $activity;
            } else {
                unset($list['list'][$index]);
            }
        }
        $list['list'] = array_values($list['list']);

        return $this->result($list);
    }

}
