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
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\goods\GoodsCheckedConstant;

use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\services\goods\GoodsListActivityHandler;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\goods\group\GoodsGroupModel;
use shopstar\models\member\group\MemberGroupMapModel;
use yii\helpers\ArrayHelper as YiiArrayHelper;
use yii\helpers\Json;

class GoodsMobileQueryService extends BaseService
{
    /**
     * 需要判断权益的插件名称
     * @var string[]
     */
    public $goodsTypeMap = [
        2 => 'virtualAccount',
    ];

    protected $shopType;
    protected $memberId;
    protected $member;
    protected $clientType;

    protected $_params;

    /**
     * 前端api接口调用，获取产品列表
     * @param $options
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @throws GoodsException
     * @author: Terry
     */
    public function getGoodsList($options)
    {
        if (empty($this->_params)) {
            throw new GoodsException(GoodsException::SERVICE_GET_MOBILE_LIST_MUST_INIT_PARAM);
        }
        $activityId = YiiArrayHelper::getValue($options, 'activity_id', 0);
        $isGetActivity = YiiArrayHelper::getValue($options, 'get_activity', 0);

        $activityGoodsId = $this->getGoodsIdByActivityId();
        if (!empty($activityGoodsId)) {
            $this->_params['andWhere'][] = ['goods.id' => $activityGoodsId];
        }
        $subShopList = [];
        $list = GoodsModel::getColl($this->_params, [
            'callable' => function (&$result) use (&$subShopList) {
                $result['ext_field'] = Json::decode($result['ext_field']) ?? [];
                // 自定义购买按钮status, 影响加购按钮及价格文字显示
                $result['buy_button_status'] = GoodsService::getBuyButtonStatus($result['ext_field']['buy_button_type'], $result['ext_field']['buy_button_settings']);

                //如果是需要判断的权益插件，先判断插件权限
                if (isset($this->goodsTypeMap[$result['type']])) {

                    // 如果是单店铺，直接返回有权限
                    $subShopList = [
                        $this->goodsTypeMap[$result['type']] => true,
                    ];
                    //给商品赋值权限
                    $result['plugin_account'][$this->goodsTypeMap[$result['type']]] = $subShopList[$this->goodsTypeMap[$result['type']]];
                }
            }
        ]);
        //如果需要获取商品活动
        if ($isGetActivity == 1) {
            $this->getActivity($list['list'], $activityId);
        }
        $list['list'] = array_values($list['list']);

        return $list;
    }

    /**
     * @param array $get
     * @author: Terry
     */
    public function initParams($get, $options)
    {
        $this->shopType = YiiArrayHelper::getValue($options, 'shopType', '');
        $this->memberId = YiiArrayHelper::getValue($options, 'memberId', '');
        $this->member = YiiArrayHelper::getValue($options, 'member', '');
        $this->clientType = YiiArrayHelper::getValue($options, 'clientType', '');

        $orderBy = [];
        $andWhere = [];

        if (!empty($get['sort']) && !empty($get['by'])) {
            $orderBy[$get['sort']] = $get['by'] == 'asc' ? SORT_ASC : SORT_DESC;
        }

        //追加排序条件
        $orderBy['sort_by'] = SORT_DESC;
        $orderBy['created_at'] = SORT_DESC;

        //如果是获取推荐的话需要随机商品列表
        if ($get['is_recommand'] == 1) {
            $orderBy = 'RAND()';
            $andWhere[] = ['>', 'goods.stock', 0];
        }

        if ($get['id']) {
            $goodsId = ArrayHelper::explode(',', $get['id']) ?: [];
            $andWhere[] = ['goods.id' => $goodsId];
            //重置排序
            $orderBy = [
                new \yii\db\Expression('FIELD (id,' . $get['id'] . ')'),
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC,
            ];
        }

        //拼装前缀
        if ($get['select']) {
            array_walk($get['select'], function (&$result) {
                $result = 'goods.' . $result;
            });
        }

        $params = [
            'alias' => 'goods',
            'where' => [
                'goods.status' => [1, 2],
                'goods.is_deleted' => 0,
                'goods.is_checked' => GoodsCheckedConstant::GOODS_CHECKED_PASS,
            ],
            'andWhere' => $andWhere,
            'searchs' => [
                ['goods.title', 'like', 'title'],
                ['goods.is_recommand', 'int', 'is_recommand'],
                ['goods.is_hot', 'int', 'is_hot'],
                ['goods.is_new', 'int', 'is_new'],
            ],
            'select' => $get['select'] ?: [
                'goods.id',
                'goods.sort_by',
                'goods.title',
                'goods.status',
                'goods.type',
                'goods.sub_name',
                'goods.short_name',
                'goods.thumb',
                'goods.video',
                'goods.video_thumb',
                'goods.unit',
                'goods.goods_sku',
                'goods.stock',
                '(goods.sales + goods.real_sales) as sales',
                'goods.price',
                'goods.min_price',
                'goods.max_price',
                'goods.cost_price',
                'goods.original_price',
                'goods.has_option',
                'goods.is_recommand',
                'goods.is_hot',
                'goods.is_new',
                'goods.has_option',
                'goods.member_level_discount_type',
                'goods.ext_field',
                'goods.is_commission',
            ],
            'orderBy' => $orderBy,
            'indexBy' => 'id'
        ];

        // 如果是单店铺，卸载过滤商品审核
        unset($params['where']['goods.is_checked']);

        //如果有分类id则根据分类id查找
        if ($get['category_id']) {
            $goodsId = GoodsCategoryMapModel::getGoodsIdByCategoryId((array)$get['category_id'], 1);
            empty($goodsId) ? $params['where']['id'] = 0 : $params['where']['id'] = $goodsId;
        }

        //如果有分组id则根据分组id查找
        if ($get['group_id']) {
            $groupId = GoodsGroupModel::find()->where(['id' => $get['group_id'], 'status' => 1])->column();
            $goodsId = GoodsGroupMapModel::getGoodsIdByGroupId((array)$groupId);
            empty($goodsId) ? $params['where']['id'] = 0 : $params['where']['id'] = $goodsId;
        }

        //组装权限
        $this->perm($params);

        $this->_params = $params;
    }

    /**
     * 组装权限
     * @param $params
     * @author 青岛开店星信息技术有限公司
     */
    private function perm(&$params)
    {
        //获取会员标签id
        $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($this->memberId);
        $params['leftJoins'][] = [GoodsPermMapModel::tableName() . 'goods_perm', ' goods_perm.goods_id=goods.id and goods_perm.perm_type =' . GoodsPermMapModel::PERM_VIEW];
        $params['andWhere'][] = [
            'or',
            ['goods_perm.goods_id' => null],
            ['goods_perm.member_type' => 0],
            ['goods_perm.member_type' => 1, 'goods_perm.type_id' => $this->member['level_id']],
            ['goods_perm.member_type' => 2, 'goods_perm.type_id' => $memberGroupId],
        ];
    }

    /**
     * 根据活动取商品id
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    private function getGoodsIdByActivityId()
    {
        $activityId = RequestHelper::get('activity_id');
        $activityType = RequestHelper::get('activity_type');
        $goodsId = ArrayHelper::explode(',', RequestHelper::get('id')) ?: [];
        if (!empty($activityId) && !empty($activityType)) {

            $nowDate = DateTimeHelper::now();
            $goodsActivity = GoodsActivityModel::find()
                ->where(['activity_id' => $activityId, 'activity_type' => $activityType, 'is_delete_activity' => 0])
                ->andWhere([
                    'or',
                    [
                        'and',
                        ['is_preheat' => 1],
                        ['<', 'preheat_time', $nowDate],
                        ['>', 'end_time', $nowDate],
                    ],
                    [
                        'and',
                        ['<', 'start_time', $nowDate],
                        ['>', 'end_time', $nowDate]
                    ]
                ])
                ->andWhere('find_in_set(' . $this->clientType . ',client_type)')
                ->indexBy('goods_id')
                ->get();
            $activityGoodsId = array_keys($goodsActivity);
            if (empty($activityGoodsId)) {
                throw new GoodsException(GoodsException::CLIENT_GOODS_LIST_IS_EMPTY);
            }
            if (!empty($goodsId)) {
                return array_intersect($activityGoodsId, $goodsId);
            }
            return $activityGoodsId;

        }
        return 0;
    }

    /**
     * 获取活动
     * @param $list
     * @param int $activityId
     * @author 青岛开店星信息技术有限公司
     */
    private function getActivity(&$list, int $activityId = 0)
    {
        //初始化商品列表营销活动加载器
        $goodsListActivityHandler = GoodsListActivityHandler::init($list, $this->memberId ?: 0, $this->clientType, $activityId);

        //执行
        $goodsListActivityHandler->automation();

        //获取活动
        $activities = $goodsListActivityHandler->getActivity('all');

        //挂载商品活动
        foreach ($activities as $goodsId => $activity) {
            if (array_filter($activity, function ($result) {
                if (!empty($result) || $result === 0) {
                    return false;
                }
                return true;
            })) continue;

            $list[$goodsId]['activities'] = $activity;
        }

        return;
    }
}