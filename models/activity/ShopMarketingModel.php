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

namespace shopstar\models\activity;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\activity\ActivityConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderGoodsModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%marketing}}".
 *
 * @property string $id
 * @property string $type 活动类型
 * @property int $inner_type 活动的活动类型: 拼团：0普通拼团
 * @property string $title 活动标题
 * @property int $status  活动状态 0 未开始或进行中  -1停止 -2手动停止
 * @property string $start_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property string $goods_ids 商品id
 * @property string $option_ids 规格id
 * @property string $client_type 客户端类型
 * @property string $rules 规则
 * @property int $is_deleted 是否删除
 * @property int $is_preheat 是否预热
 * @property string $preheat_time 预热时间
 * @property string $stop_time 停止时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $goods_join_type 商品参与类型
 */
class ShopMarketingModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%marketing}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['is_deleted', 'status', 'is_preheat', 'inner_type', 'goods_join_type'], 'integer'],
            [['start_time', 'end_time', 'stop_time', 'created_at', 'updated_at', 'preheat_time'], 'safe'],
            [['goods_ids', 'option_ids', 'rules'], 'string'],
            [['title', 'client_type', 'type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'type' => '活动类型',
            'inner_type' => '活动的活动类型: 拼团：0普通拼团',
            'title' => '活动标题',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'goods_ids' => '商品id',
            'option_ids' => '规格id',
            'client_type' => '客户端类型',
            'rules' => '规则',
            'is_deleted' => '是否删除',
            'stop_time' => '停止时间',
            'is_preheat' => '是否预热',
            'preheat_time' => '预热时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '活动状态 0 未开始或进行中  -1停止 -2手动停止',
            'goods_join_type' => '商品参与类型'
        ];
    }

    /**
     * 获取活动列表
     * @param array $data 条件
     * @param string $type 活动
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityList(array $data, string $type)
    {
        $andWhere = [];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        if (!empty($data['keyword'])) {
            $andWhere[] = ['like', 'title', $data['keyword']];
        }

        $select = [
            'activity.id',
            'activity.title',
            'activity.inner_type',
            'activity.start_time',
            'activity.end_time',
            'activity.stop_time',
            'activity.status',
            'activity.goods_ids',
            'activity.is_preheat',
            'if(activity.stop_time=0, 1, 2) as level',
        ];

        $leftJoins = [];
        // 商品名称搜索
        $goodsTitle = $data['goods_title'];
        if (!empty($goodsTitle)) {
            $andWhere[] = ['like', 'goods.title', $goodsTitle];
            $leftJoins = [
                [ShopMarketingGoodsMapModel::tableName() . ' activity_goods_map', 'activity_goods_map.activity_id=activity.id'],
                [GoodsModel::tableName() . ' goods', 'goods.id=activity_goods_map.goods_id'],
            ];
        }


        // 日期筛选
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = [
                'or',
                [
                    'and',
                    ['>=', 'activity.start_time', $startTime],
                    ['<=', 'activity.start_time', $endTime],
                    ['>=', 'activity.end_time', $startTime],
                    ['>=', 'activity.end_time', $endTime],

                ],
                [
                    'and',
                    ['>=', 'activity.start_time', $startTime],
                    ['<=', 'activity.start_time', $endTime],
                    ['>=', 'activity.end_time', $startTime],
                    ['<=', 'activity.end_time', $endTime],
                ],
                [
                    'and',
                    ['<=', 'activity.start_time', $startTime],
                    ['<=', 'activity.start_time', $endTime],
                    ['>=', 'activity.end_time', $startTime],
                    ['>=', 'activity.end_time', $endTime],
                ],
                [
                    'and',
                    ['<=', 'activity.start_time', $startTime],
                    ['<=', 'activity.start_time', $endTime],
                    ['>=', 'activity.end_time', $startTime],
                    ['<=', 'activity.end_time', $endTime],
                ]
            ];
        }

        // 活动状态
        switch ($data['status']) {
            case '1': // 活动中
                $andWhere[] = [
                    'and',
                    ['activity.status' => 0],
                    ['<', 'activity.start_time', DateTimeHelper::now()],
                    ['>', 'activity.end_time', DateTimeHelper::now()],
                ];
                break;
            case '0': // 未开始
                $andWhere[] = [
                    'and',
                    ['activity.status' => 0],
                    ['>', 'activity.start_time', DateTimeHelper::now()],
                ];
                break;
            case '-1': // 已停止
                $andWhere[] = [
                    'or',
                    ['activity.status' => -1],
                    ['<', 'activity.end_time', DateTimeHelper::now()],
                ];
                break;
            case '-2': // 手动停止
                $andWhere[] = ['activity.status' => -2];
                break;
            default: // 全部
                break;
        }

        // 活动选择器
        if (!empty($data['activity_select'])) {
            $andWhere[] = [
                'and',
                ['activity.status' => 0],
                [
                    'or',
                    [ // 进行中
                        'and',
                        ['<', 'activity.start_time', DateTimeHelper::now()],
                        ['>', 'activity.end_time', DateTimeHelper::now()],
                    ],
                    [ // 预热中
                        'and',
                        ['activity.is_preheat' => 1],
                        ['<', 'activity.preheat_time', DateTimeHelper::now()]
                    ]
                ]
            ];
        }

        if (!empty($data['goods_title'])) {
            $andWhere[] = [];
        }

        $params = [
            'select' => $select,
            'alias' => 'activity',
            'searchs' => [
                ['activity.inner_type', 'int']
            ],
            'leftJoins' => $leftJoins,
            'where' => [
                'activity.is_deleted' => 0,
                'activity.type' => $type,
            ],
            'andWhere' => $andWhere,
            'orderBy' => [
                'level' => SORT_ASC,
                'activity.stop_time' => SORT_DESC,
                'activity.status' => SORT_DESC,
                'activity.id' => SORT_DESC,
            ]
        ];

        return self::getColl($params, [
            'pager' => !($data['is_all'] == 1),
//            'onlyList' => $data['is_all'] == 1,
            'callable' => function (&$row) {
                if ($row['status'] == 0 && $row['start_time'] < DateTimeHelper::now() && $row['end_time'] > DateTimeHelper::now()) {
                    $row['status'] = '1'; // 进行中
                } else if ($row['status'] == 0 && $row['end_time'] < DateTimeHelper::now()) {
                    $row['status'] = '-1'; // 停止
                }
                if ($row['stop_time'] == 0) {
                    $row['stop_time'] = '-';
                }
                $row['goods_ids'] = explode(',', $row['goods_ids']);
                $row['goods_count'] = count($row['goods_ids']);
            }
        ]);
    }

    /**
     * 获取活动详情
     * @param int $activityId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityDetail(int $activityId)
    {
        if (empty($activityId)) {
            return error('参数错误');
        }
        $detail = self::find()->where(['id' => $activityId])->first();
        // 字节跳动渠道处理
        $clientType = explode(',', $detail['client_type']);
        if (in_array('30', $clientType)) {
            $clientType = ArrayHelper::deleteByValue($clientType, '31');
            $clientType = ArrayHelper::deleteByValue($clientType, '32');
            $detail['client_type'] = implode(',', $clientType);
        }
        $detail['rules'] = Json::decode($detail['rules']);
        // 获取商品信息
        $goodsInfo = ShopMarketingGoodsMapModel::find()
            ->alias('rule')
            ->select([
                'rule.goods_id',
                'goods.title',
                'rule.option_id',
                'rule.activity_stock', // 活动库存
                'rule.activity_price', // 价格
                'rule.is_join', // 是否参与
                'goods.stock', // 商品库存
                'goods.type', // 商品类型
                'goods.min_price', // 商品最小价格
                'goods.max_price', // 商品最大价格
                'goods.thumb', // 商品缩略图
                'goods.sales', // 商品销量
                'goods.price', // 商品价格
                'option.stock option_stock', // 规格库存
                'option.price option_price', // 规格价格
                'option.title option_title', // 规格标题
                'option.weight option_weight', // 规格重量
            ])
            ->leftJoin(GoodsModel::tableName() . ' goods', 'goods.id = rule.goods_id')
            ->leftJoin(GoodsOptionModel::tableName() . ' option', 'option.id = rule.option_id and option.goods_id = rule.goods_id')
            ->where(['rule.activity_id' => $activityId])
            ->get();
        // 重组商品信息
        $goodsTemp = [];
        foreach ($goodsInfo as $item) {
            // 单规格
            if ($item['option_id'] == 0) {
                $goodsTemp[$item['goods_id']] = $item;
                $goodsTemp[$item['goods_id']]['id'] = $item['goods_id'];
                unset($goodsTemp[$item['goods_id']]['goods_id']);
            } else {
                if (empty($goodsTemp[$item['goods_id']])) {
                    $goodsTemp[$item['goods_id']] = [
                        'id' => $item['goods_id'],
                        'stock' => $item['stock'],
                        'title' => $item['title'],
                        'min_price' => $item['min_price'],
                        'max_price' => $item['max_price'],
                        'has_option' => 1,
                        'thumb' => $item['thumb'],
                        'type' => $item['type'],
                        'sales' => $item['sales'],
                    ];
                }
                $goodsTemp[$item['goods_id']]['rules'][] = [
                    'id' => $item['option_id'],
                    'activity_stock' => $item['activity_stock'],
                    'activity_price' => $item['activity_price'],
                    'is_join' => $item['is_join'],
                    'stock' => $item['option_stock'],
                    'price' => $item['option_price'],
                    'title' => $item['option_title'],
                    'weight' => $item['option_weight'],
                ];
            }
        }
        $detail['goods_info'] = array_values($goodsTemp);

        return $detail;
    }

    /**
     * 手动停止活动
     * @param int $activityId
     * @param string $type
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function manualStop(int $activityId, string $type)
    {
        if (empty($activityId)) {
            return error('参数错误');
        }
        $activity = self::findOne(['id' => $activityId, 'is_deleted' => 0, 'type' => $type]);
        if (empty($activity)) {
            return error('活动不存在');
        }

        $activity->status = ActivityConstant::ACTIVITY_STATUS_MANUAL_STOP;
        $activity->stop_time = DateTimeHelper::now();
        if (!$activity->save()) {
            return error($activity->getErrorMessage());
        }

        return $activity->toArray();
    }

    /**
     * 删除活动
     * @param int $activityId
     * @param string $type
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteActivity(int $activityId, string $type)
    {
        if (empty($activityId)) {
            return error('参数错误');
        }
        $activity = self::findOne(['id' => $activityId, 'is_deleted' => 0, 'type' => $type]);
        if (empty($activity)) {
            return error('活动不存在');
        }
        // 如果活动进行中 且 停止时间为空 不允许删
        if ($activity->start_time < DateTimeHelper::now() && $activity->end_time > DateTimeHelper::now() && $activity->stop_time == 0) {
            // 如果活动未结束 请先结束
            return error('活动状态错误');
        }
        $activity->is_deleted = 1;
        if (!$activity->save()) {
            return error($activity->getErrorMessage());
        }

        return $activity->toArray();
    }

    /**
     * 保存前校验
     * @param bool $insert
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function beforeSave($insert)
    {
        // 插入时校验
        if ($insert) {
            // 校验数据
            if (empty($this->title)) {
                $this->addError('title', '活动名称不能为空');
                return false;
            }
            if (mb_strlen($this->title) > 25) {
                $this->addError('title', '活动名称超长');
                return false;
            }

            //  活动名称重复
            $isExistsTitle = ShopMarketingModel::checkActivityTitle($this->title, $this->type);
            if ($isExistsTitle) {
                $this->addError('title', '活动名称不能重复');
                return false;
            }

            if ($this->end_time < DateTimeHelper::now()) {
                $this->addError('end_time', '结束时间不能小于当前时间');
                return false;
            }
            if ($this->end_time < $this->start_time) {
                $this->addError('end_time', '结束时间不能小于开始时间');
                return false;
            }
            // 渠道不能为空
            if (empty($this->client_type)) {
                $this->addError('client_type', '渠道不能为空');
                return false;
            }
            // 字节跳动小程序渠道 特殊处理
            $clientType = explode(',', $this->client_type);
            if (in_array('30', $clientType)) {
                $clientType[] = '31';
                $clientType[] = '32';
                $this->client_type = implode(',', $clientType);
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * 检测活动名称是否存在
     * @param string $title
     * @param string $type
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkActivityTitle(string $title, string $type)
    {
        return self::find()->where(['title' => $title, 'type' => $type, 'is_deleted' => 0])->exists();
    }

    /**
     * 根据活动id获取活动信息
     * @param int $activityId
     * @param string $activityType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityInfoById(int $activityId, string $activityType)
    {
        $activity = self::find()->where(['id' => $activityId, 'type' => $activityType])->first();
        if (empty($activity)) {
            return error('无活动');
        }
        $activity['rules'] = Json::decode($activity['rules']);
        // 获取商品规则
        $goodsInfo = ShopMarketingGoodsMapModel::find()
            ->where(['activity_id' => $activity['id']])
            ->indexBy('goods_id')
            ->get();
        // 返回数据
        $returnData = [];
        // 重组商品信息
        foreach ($goodsInfo as $goodsId => $item) {
            $returnData[$goodsId] = $activity;
            $returnData[$goodsId]['goods_info'][] = $item;
        }
        // 重组返回数据
        foreach ($returnData as $goodsId => &$item) {
            // 单规格
            if ($item['goods_info'][0]['option_id'] == 0) {
                $item['activity_stock'] = $item['goods_info'][0]['activity_stock'];
                $item['activity_price'] = $item['goods_info'][0]['activity_price'];
            } else {
                // 多规格
                $priceRange = [
                    'min_price' => $item['goods_info'][0]['activity_price'],
                ];
                $activityStock = 0;
                foreach ($item['goods_info'] as $goods) {
                    $priceRange['min_price'] = min($priceRange['min_price'], $goods['activity_price']);
                    $priceRange['max_price'] = max($priceRange['max_price'], $goods['activity_price']);
                    $activityStock += $goods['activity_stock'];
                }
                $item['price_range'] = $priceRange;
                $item['activity_stock'] = $activityStock;
            }
        }
        unset($item);

        return $returnData;
    }

    /**
     * 获取单个商品活动信息
     * @param int $goodsId 商品id
     * @param int $clientType 客户端类型
     * @param string $type 活动类型
     * @param int $hasOption
     * @param array $options 扩展参数
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getActivityInfo(int $goodsId, int $clientType, string $type, int $hasOption, array $options = [])
    {

        $options = array_merge([
            'not_check_time' => false,//无须检查活动日期
        ], $options);

        // 查询条件
        $query = self::find()
            ->where(['type' => $type, 'is_deleted' => 0]);

        //不需要判断有效期
        if (!$options['not_check_time']) {
            $query->andWhere(['<=', 'start_time', DateTimeHelper::now()])->andWhere(['>=', 'end_time', DateTimeHelper::now()])->andWhere('status = 0 ');
        }

        $query->andWhere('find_in_set(' . $goodsId . ',goods_ids)');


        // 传入活动ID
        if (!empty($options['activity_id'])) {
            $query->andWhere(['id' => $options['activity_id']]);
        }

        // 客户端类型
        if (!empty($clientType)) {
            $query->andWhere('find_in_set(' . $clientType . ',client_type)');
        }
        $query->orderBy(['start_time' => SORT_ASC]);

        // 查询活动
        $activity = $query->first();

        if (empty($activity)) {
            return error('无活动');
        }

        //规则设置
        $activity['rules'] = Json::decode($activity['rules']);

        // 获取商品规则
        $activity['goods_info'] = ShopMarketingGoodsMapModel::find()
            ->where(['activity_id' => $activity['id'], 'goods_id' => $goodsId])
            ->indexBy('option_id')
            ->get();

        if ($hasOption) {
            // 查询规格
            $goodsOptions = GoodsOptionModel::find()->select('id, stock')->where(['goods_id' => $goodsId])->indexBy('id')->get();
            $priceRange = [
                'min_price' => reset($activity['goods_info'])['activity_price'],
            ];
            $activityStock = 0;
            foreach ($activity['goods_info'] as $optionId => &$item) {
                if ($item['is_join']) {
                    $priceRange['min_price'] = min($priceRange['min_price'], $item['activity_price']);
                    $priceRange['max_price'] = max($priceRange['max_price'], $item['activity_price']);
                    // 哪个库存小用哪个
                    if ($goodsOptions[$optionId]['stock'] < $item['activity_stock']) {
                        $item['activity_stock'] = $goodsOptions[$optionId]['stock'];
                    }
                    $activityStock += $item['activity_stock'];
                }
            }
            unset($item);
            $activity['price_range'] = $priceRange;
            $activity['activity_stock'] = $activityStock;
        } else {
            // 商品库存小 用商品库存
            if (!empty($options['stock']) && $activity['goods_info'][0]['activity_stock'] > $options['stock']) {
                $activity['goods_info'][0]['activity_stock'] = $options['goods_stock'];
            }
            $activity['activity_stock'] = $activity['goods_info'][0]['activity_stock'];
            $activity['activity_price'] = reset($activity['goods_info'])['activity_price'];
        }

        if (!empty($options['member_id'])) {


            if ($activity['rules']['limit_type'] == ActivityConstant::ACTIVITY_LIMIT_TYPE_MORE_BUY) {
                // 查找购买数量
                $activity['buy_count'] = OrderGoodsModel::find()
                    ->alias('order_goods')
                    ->innerJoin(OrderActivityModel::tableName() . ' order_activity', 'order_activity.order_id=order_goods.order_id')
                    ->where(['order_activity.activity_id' => $activity['id'], 'order_goods.member_id' => $options['member_id'], 'order_goods.goods_id' => $goodsId, 'order_goods.shop_goods_id' => 0])
                    ->andWhere(['>=', 'order_goods.status', 0])
                    ->sum('total');

            } else if ($activity['rules']['limit_type'] == ActivityConstant::ACTIVITY_LIMIT_TYPE_DAY_MORE_BUY) {
                // 活动期内每人每天最多购买
                $date = DateTimeHelper::now(false);
                // 查找购买数量
                $activity['buy_count'] = OrderGoodsModel::find()
                    ->alias('order_goods')
                    ->innerJoin(OrderActivityModel::tableName() . ' order_activity', 'order_activity.order_id=order_goods.order_id')
                    ->where(['order_activity.activity_id' => $activity['id'], 'order_goods.member_id' => $options['member_id'], 'order_goods.goods_id' => $goodsId, 'order_goods.shop_goods_id' => 0])
                    ->andWhere(['between', 'order_goods.created_at', $date, $date . ' 23:59:59'])
                    ->andWhere(['>=', 'order_goods.status', 0])
                    ->sum('total');
            }
        }

        return $activity;
    }

    /**
     * 商品是否参与分销
     * @param $goodsId
     * @param int $clientType
     * @return bool true 支持  false 不支持
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkGoodsCommission($goodsId, int $clientType = 0): bool
    {
        $query = self::find()
            ->select('rules, type')
            ->where(['is_deleted' => 0, 'status' => 0])
            ->andWhere(['<', 'start_time', DateTimeHelper::now()])
            ->andWhere('find_in_set(' . $goodsId . ',goods_ids)');
        if (!empty($clientType)) {
            $query->andWhere('find_in_set(' . $clientType . ',client_type)');
        }
        $activity = $query->first();
        if (empty($activity)) {
            return true;
        }

        $rules = Json::decode($activity['rules']);
        // 活动不支持
        if ($rules['is_commission'] == 0) {
            return false;
        }

        return true;
    }

}
