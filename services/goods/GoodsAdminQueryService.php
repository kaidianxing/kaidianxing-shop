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

namespace shopstar\services\goods;

use shopstar\bases\service\BaseService;
use shopstar\constants\activity\ActivityTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\goods\GoodsBuyButtonConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityGoodsRuleModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityModel;
use shopstar\services\groups\GroupsGoodsService;
use yii\helpers\ArrayHelper as YiiArrayHelper;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsAdminQueryService extends BaseService
{
    /**
     * 需要查询的字段
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    protected $field = [
        'id',
        'created_at',
        'title',
        'sub_name',
        'short_name',
        'type',
        'thumb',
        'video',
        'video_thumb',
        'unit',
        'goods_sku',
        'bar_code',
        'sort_by',
        'stock',
        'real_sales',
        '`sales` + `real_sales` as sales',
        'price',
        'min_price',
        'max_price',
        'cost_price',
        'original_price',
        'pv_count',
        'has_option',
        'is_hot',
        'is_recommand',
        'is_new',
        'status',
        'reduction_type',
        'ext_field',
        'dispatch_type',
        'is_commission',
        'dispatch_express',
        'dispatch_intracity',
        'is_recommand',
        'is_hot',
        'is_new',
        'weight',
    ];

    /**
     * 获取单个商品
     * @param $goodsId
     * @param $flag
     * @return array
     * @throws GoodsException
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOne($goodsId, $flag)
    {
        $data = GoodsService::getGoods($goodsId, [
            'get_option' => 1,
            'where' => []
        ]);
        // 价格面议, 层级往前提, 兼容前端
        $data['buy_button_settings'] = $data['ext_field']['buy_button_settings'] ?? [];
        $data['buy_button_type'] = $data['ext_field']['buy_button_type'] ?? (string)GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_DEFAULT;

        //处理商品权限返回
        $data['perm_data'] = GoodsPermMapModel::getGoodsPerm($goodsId, true);

        // 接收复制入口的请求，重置实际销量

        $flag != 'copy' ?: $data['real_sales'] = 0;

        // 处理详情页的腾讯视频
        if (!empty($data['content'])) {
            $data['content'] = VideoHelper::parseRichTextTententVideo($data['content']);
        }

        return $data;
    }


    /**
     * 后台管理，得到产品列表
     * @param array $options
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @throws GoodsException
     * @throws \Exception
     * @author: Terry
     */
    public function getGoodsList(array $options = [])
    {
        $pager = YiiArrayHelper::getValue($options, 'pager', 0);
        $export = YiiArrayHelper::getValue($options, 'export', 0);
        $activityType = YiiArrayHelper::getValue($options, 'activityType', '');
        $activityId = YiiArrayHelper::getValue($options, 'activityId', 0);
        $showActivity = YiiArrayHelper::getValue($options, 0);
        $flag = YiiArrayHelper::getValue($options, 'flag', '');
        $isShoppingReward = YiiArrayHelper::getValue($options, 'isShoppingReward', '');

        //获取参数
        //$params = $this->getParams();
        if (empty($this->_params)) {
            throw new GoodsException(GoodsException::SERVICE_GET_MANAGER_LIST_MUST_INIT_PARAM);
        }
        $list = GoodsModel::getColl($this->_params, [
            'callable' => function (&$row) {
                $row['ext_field'] = $row['ext_field'] ? Json::decode($row['ext_field']) : [];
                $row['wap_url'] = ShopUrlHelper::wap('/kdxGoods/detail/index', [
                    'goods_id' => $row['id']
                ], true);
            },
            'pager' => $pager,
        ]);

        //处理商品分类返回
        $goodsCategory = GoodsCategoryMapModel::find()
            ->alias('category_map')
            ->leftJoin(GoodsCategoryModel::tableName() . ' category', 'category.id=category_map.category_id')
            ->where(['category_map.goods_id' => array_column($list['list'] ?: [], 'id')])
            ->select([
                'category_map.goods_id',
                'category_map.category_id',
                'category.name',
            ])
            ->asArray()
            ->all();

        foreach ((array)$goodsCategory as $goodsCategoryIndex => $goodsCategoryItem) {
            foreach ($list['list'] as $listIndex => &$listItem) {
                if ($listItem['id'] == $goodsCategoryItem['goods_id']) {
                    $listItem['category'][] = $goodsCategoryItem;
                }
            }
        }

        if ($isShoppingReward) {
            $consumeRewardActivity = ShoppingRewardActivityModel::where(['status' => 0, 'is_deleted' => 0])
                ->andWhere(['<', 'start_time', DateTimeHelper::now()])
                ->andWhere(['>', 'end_time', DateTimeHelper::now()])
                ->indexBy('id')
                ->get();

            if (!empty($consumeRewardActivity)) {
                $goodsId = ShoppingRewardActivityGoodsRuleModel::where([
                    'activity_id' => array_keys($consumeRewardActivity),
                ])->select([
                    'goods_or_cate_id',
                    'activity_id'
                ])->indexBy('goods_or_cate_id')->get();

                foreach ($list['list'] as $listIndex => &$listItem) {
                    if (isset($goodsId[$listItem['id']])) {
                        $activityInfo = $consumeRewardActivity[$goodsId[$listItem['id']]['activity_id']];
                        $listItem['is_activity_goods'] = 1;
                        $listItem['join_activity'][] = [
                            'id' => $activityInfo['id'],
                            'start_time' => $activityInfo['start_time'],
                            'end_time' => $activityInfo['end_time'],
                            'status' => 1,
                            'title' => $activityInfo['title'],
                            'type_text' => '购物奖励'
                        ];
                    }
                }
            }

        } else {
            // 参加活动的商品
            // 获取是否活动商品 (只要有结束的活动  就算)
            $goodsActivity = GoodsActivityModel::find()
                ->where(['goods_id' => array_column($list['list'] ?: [], 'id'), 'is_delete_activity' => 0])
                ->andWhere(['>', 'end_time', DateTimeHelper::now()])
                ->get();

            // 查找活动
            if ($showActivity) {
                $goodsActivityIds = [];
                // 区分预售和其他
                foreach ($goodsActivity as $item) {
                    $goodsActivityIds[] = $item['activity_id'];
                }
                if (!empty($goodsActivityIds)) {
                    $activityList = ShopMarketingModel::find()->select(['id', 'title', 'start_time', 'end_time', 'status', 'type'])->where(['id' => $goodsActivityIds])->indexBy('id')->get();
                }
            }

            foreach ($goodsActivity as $index => $item) {
                foreach ($list['list'] as $listIndex => &$listItem) {
                    if ($listItem['id'] == $item['goods_id']) {
                        $listItem['is_activity_goods'] = 1;
                        // 如果要显示正在参与的
                        if ($showActivity) {
                            $activity = [];
                            // 非预售
                            if (!empty($activityList[$item['activity_id']])) {
                                $activity = $activityList[$item['activity_id']];
                                // 正在参与
                                if ($activity['start_time'] < DateTimeHelper::now()) {
                                    $activity['status'] = 1;
                                }
                                $activity['type_text'] = ActivityTypeConstant::getText($activityList[$item['activity_id']]['type']);
                            }
                            $listItem['join_activity'][] = $activity;
                        }
                    }
                }
            }

            unset($listIndex, $listItem);

            //获取其他额外特殊活动
            $shopActivity = ShopMarketingModel::where([
                'type' => ActivityTypeConstant::ACTIVITY_TYPE_FULL_REDUCE,
                'status' => 0,
                'is_deleted' => 0
            ])
                ->andWhere(['>', 'end_time', DateTimeHelper::now()])
                ->get();


            foreach ($shopActivity as $shopActivityIndex => $shopActivityItem) {
                foreach ($list['list'] as $listIndex => &$listItem) {

                    $goodsIds = explode(',', $shopActivityItem['goods_ids']);

                    //全部商品参与
                    $condition1 = $shopActivityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_TYPE_ALL;

                    //部分商品参与
                    $condition2 = $shopActivityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_PART_GOODS_JOIN && in_array($listItem['id'], $goodsIds);

                    //部分商品不参与
                    $condition3 = $shopActivityItem['goods_join_type'] == FullReduceGoodsJoinTypeConstant::FULL_REDUCE_GOODS_JOIN_PART_GOODS_NOT_JOIN && !in_array($listItem['id'], $goodsIds);

                    if (!$condition1 && !$condition2 && !$condition3) {
                        continue;
                    }

                    $listItem['is_activity_goods'] = 1;
                    $listItem['is_full_reduce'] = 1;
                    $activity = [
                        'end_time' => $shopActivityItem['end_time'],
                        'id' => $shopActivityItem['id'],
                        'start_time' => $shopActivityItem['start_time'],
                        'title' => $shopActivityItem['title']
                    ];

                    if ($shopActivityItem['start_time'] < DateTimeHelper::now()) {
                        $activity['status'] = 1;
                    }

                    $activity['type_text'] = ActivityTypeConstant::getText($shopActivityItem['type']);

                    $listItem['join_activity'] = array_merge($listItem['join_activity'] ?? [], [$activity]);
                }
            }
        }

        if (!empty($activityType)) {
            switch ($activityType) {

                default: // 除了预售其他活动
                    $this->activity($list, $activityId, $activityType);
                    break;
            }
        }

        return $list;
    }

    protected $_params;

    /**
     * 获取参数
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function initParams($options)
    {
        $select = YiiArrayHelper::getValue($options, 'select', []);
        $labelField = YiiArrayHelper::getValue($options, 'labelField', '');
        $status = YiiArrayHelper::getValue($options, 'status', -1);
        $get = YiiArrayHelper::getValue($options, 'get', []);
        $flag = YiiArrayHelper::getValue($options, 'flag');
        $type = YiiArrayHelper::getValue($options, 'type', 'all');
        $clientType = YiiArrayHelper::getValue($options, 'clientType', '');

        $select = !empty($select) ? $select : $this->field;

        //追加连表前缀
        array_walk($select, function (&$result) {
            $result = 'goods.' . $result;
        });

        //拼装传参
        $params = [
            'alias' => 'goods',
            'where' => [],
            'select' => $select,
            'searchs' => [
                [['goods.title', 'goods.goods_sku', 'goods.bar_code'], 'like', 'keywords'],
                ['goods.created_at', 'between', 'created_at'],
                // ['goods.type', 'int', 'type'],
            ],
        ];

        // 判断是是单店铺助手，过滤预约跟虚拟卡密
        if ($clientType == ClientTypeConstant::MANAGE_SHOP_ASSISTANT) {
            $params['andWhere'][] = ['not in', 'goods.type', [GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT]];
        }

        // 全部搜索
        if ($type != 'all') {
            $params['searchs'][] = ['goods.type', 'int', 'type'];
        }

        //如果有排序
        if (!empty($get['sort']) && !empty($get['by'])) {
            $params['orderBy']['goods.' . $get['sort']] = $get['by'] == 'asc' ? SORT_ASC : SORT_DESC;
            $params['orderBy']['goods.sort_by'] = SORT_DESC;
        } else {
            //追加排序
            $params['orderBy']['goods.sort_by'] = SORT_DESC;
            $params['orderBy']['goods.created_at'] = SORT_DESC;
        }

        //商品id
        if ($get['ids']) {
            if (is_array($get['ids'])) {
                $goodsId = $get['ids'] ?: [];
            } else {
                $goodsId = ArrayHelper::explode(',', $get['ids']) ?: [];
            }

            $params['where']['goods.id'] = $goodsId;

            //重置排序
            $params['orderBy'] = [
                new \yii\db\Expression('FIELD (goods.id,' . implode(',', $goodsId) . ')'),
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC,
            ];
        }

        //按照分类id查找
        if (!empty($get['category_id'])) {
            $goodsId = GoodsCategoryMapModel::getGoodsIdByCategoryId((array)$get['category_id']) ?: [];
            empty($goodsId) ? $params['where']['goods.id'] = 0 : $params['where']['goods.id'] = $goodsId;
        }

        //如果有分组id则根据分组id查找
        if ($get['group_id']) {
            $goodsId = GoodsGroupMapModel::getGoodsIdByGroupId((array)$get['group_id']) ?: [];
            empty($goodsId) ? $params['where']['goods.id'] = 0 : $params['where']['goods.id'] = $goodsId;
        }

        //热卖标签字段 is_recommand:推荐 is_hot:热卖 is_new:新品
        if (!empty($labelField)) {
            $params['andWhere'][] = ['goods.' . $labelField => 1];
        }

        /**
         * 判断商品状态  by 青椒
         */
        switch ($status) {
            case 1://上架
                $params['where']['goods.status'] = [1, 2];
                $params['andWhere'][] = ['>', 'goods.stock', 0];
                $params['where']['goods.is_deleted'] = 0;

                break;
            case 2: //售罄
                $params['where']['goods.status'] = 1;
                $params['where']['goods.stock'] = 0;
                $params['where']['goods.is_deleted'] = 0;

                break;
            case 5: //上架和售罄
                $params['where']['goods.status'] = [1, 2];
                $params['where']['goods.is_deleted'] = 0;
                break;
            case 3: //下架
                $params['where']['goods.status'] = 0;
                $params['where']['goods.is_deleted'] = 0;

                break;
            case 4: //删除
                $params['where']['goods.is_deleted'] = 1;
                break;
            default: //上架下架和售罄
                $params['where']['goods.status'] = [0, 1, 2];
                $params['where']['goods.is_deleted'] = [0, 1];
                break;
        }

        // 判断是否预售活动进入,屏蔽掉虚拟卡密商品 和 预约到店
        if (isset($flag)) {
            if ($flag == 'seckill') {
                $params['andWhere'][] = ['not in', 'type', [3]];
            }
        }

        $this->_params = $params;
    }

    /**
     * 获取活动信息
     * @param $list
     * @param int $activityId
     * @param string $activityType
     * @author 青岛开店星信息技术有限公司
     */
    private function activity(&$list, int $activityId, string $activityType)
    {
        $activitys = ShopMarketingModel::getActivityInfoById($activityId, $activityType);
        if (is_error($activitys)) {
            return;
        }

        // 如果是拼团，需要查自己的商品
        if ($activityType == 'groups') {
            $groupsGoods = GroupsGoodsService::getAllGoodsOptionInfo($activityId);
            foreach ($activitys as $k => &$v) {
                $v['price_range'] = $groupsGoods[$k]['price_range'];
            }
            unset($v);
        }

        if (!is_error($activitys)) {
            foreach ($list['list'] as &$item) {
                foreach ($activitys as $goodsId => $activity) {
                    if ($item['id'] == $goodsId) {
                        $item['activitys'][$activity['type']] = $activity;
                    }
                }
            }
            unset($item);
        }
    }

    /**
     * 获取所有活动商品
     * @param $type
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author: Terry
     */
    public static function getActivityGoods($type)
    {
        $andWhere = [];
        $leftJoins = [];
        $select = [
            'goods.title',
            'goods.thumb',
            'goods.type',
            'goods.has_option',
            'goods.price',
            'goods.min_price',
            'goods.max_price',
            '(goods.sales + goods.real_sales) as sales',
            'goods.original_price',
            'goods.stock',
            'activity_goods.start_time',
            'activity_goods.end_time',
            'activity_goods.activity_id',
            'activity_goods.goods_id',
        ];

        $leftJoins[] = [GoodsModel::tableName() . ' goods', 'goods.id=activity_goods.goods_id'];

        $params = [
            'searchs' => [
                [['goods.title', 'goods.goods_sku', 'goods.bar_code'], 'like', 'keywords'],
            ],
            'alias' => 'activity_goods',
            'select' => $select,
            'where' => [
                'and',
                ['activity_goods.is_delete_activity' => 0],
                ['activity_goods.activity_type' => $type],
                ['>', 'activity_goods.end_time', DateTimeHelper::now()],
            ],
            'andWhere' => $andWhere,
            'leftJoins' => $leftJoins,
            'orderBy' => [
                'goods.sort_by' => SORT_DESC,
                'goods.created_at' => SORT_DESC,
            ],
        ];

        $list = GoodsActivityModel::getColl($params);

        // 组合
        foreach ($list['list'] as &$value) {
            $activity = ShopMarketingModel::getActivityInfo($value['goods_id'], 0, 'seckill', $value['has_option'], ['activity_id' => $value['activity_id'], 'not_check_time' => 1]);
            $value['activitys'][$type] = $activity;
            $value['id'] = $value['goods_id'];
        }
        unset($value);

        return $list;
    }

}