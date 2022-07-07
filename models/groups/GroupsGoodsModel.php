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

namespace shopstar\models\groups;

use shopstar\bases\model\BaseActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%shopstar_groups_goods}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $option_id 规格id
 * @property int $activity_id 活动id
 * @property string $price 金额
 * @property string $ladder_price 阶梯金额
 * @property string $leader_price 团长价
 * @property string $is_ladder 是否是阶梯团
 */
class GroupsGoodsModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%groups_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['goods_id', 'option_id', 'activity_id', 'is_ladder'], 'integer'],
            [['price', 'leader_price'], 'number'],
            [['ladder_price', 'leader_price'], 'required'],
            [['ladder_price'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
            'option_id' => '规格id',
            'activity_id' => '活动id',
            'price' => '金额',
            'ladder_price' => '阶梯金额',
            'leader_price' => '团长价',
            'is_ladder' => '是否是阶梯团',
        ];
    }

    /**
     * 获取一个
     * @param int $activityId
     * @param int $goodsId
     * @param int $optionId
     * @return array|null
     * @author likexin
     */
    public static function getOne(int $activityId, int $goodsId, int $optionId): ?array
    {
        return self::find()
            ->where([
                'activity_id' => $activityId,
                'goods_id' => $goodsId,
                'option_id' => $optionId,
            ])
            ->select([
                'ladder_price',
                'price',
                'leader_price'
            ])
            ->first();
    }

    /**
     * 根据活动id获取所有的商品价格
     * @param int $activityId
     * @return array
     * @author likexin
     */
    public static function getGroupsGoodsPriceInfoByActivityId(int $activityId): array
    {
        $info = self::find()->where([
            'activity_id' => $activityId,
        ])->get();

        //为空
        if (empty($info)) {
            return [];
        }

        $data = [];
        foreach ($info as $item) {

            if ($item['ladder_price']) {
                $item['ladder_price'] = Json::decode($item['ladder_price']);
            }

            //修改key
            $data[$item['goods_id'] . '_' . $item['option_id']] = $item;
        }

        return $data;
    }

    /**
     * 保存团
     * @param int $activityId
     * @param int $innerType
     * @param array $goodsInfo
     * @return bool
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function saveData(int $activityId, int $innerType, array $goodsInfo): bool
    {
        // 删除之前数据
        self::deleteAll([
            'activity_id' => $activityId,
        ]);

        $data = [];

        //遍历团等级，统一商品多次插入
        foreach ($goodsInfo as $goodsInfoItem) {

            //单规格
            if ($goodsInfoItem['has_option'] == 0) {

                //单规格参数构成
                $data[] = [
                    'goods_id' => $goodsInfoItem['goods_id'],
                    'option_id' => 0,
                    'activity_id' => $activityId,
                    'price' => $goodsInfoItem['activity_price'],
                    'ladder_price' => '',
                    'leader_price' => $goodsInfoItem['leader_price'],
                    'is_ladder' => $innerType
                ];

                continue;
            }

            //多规格
            $goodsRules = $goodsInfoItem['rules'];

            foreach ((array)$goodsRules as $goodsRulesIndex => $goodsRulesItem) {

                // 规格不参与  跳过
                if ($goodsRulesItem['is_join'] == 0) {
                    continue;
                }

                //多规格参数构成
                $data[] = [
                    'goods_id' => $goodsInfoItem['goods_id'],
                    'option_Id' => $goodsRulesItem['option_id'],
                    'activity_id' => $activityId,
                    'price' => $goodsRulesItem['activity_price'],
                    'ladder_price' => '',
                    'leader_price' => $goodsRulesItem['leader_price'],
                    'is_ladder' => $innerType
                ];
            }
        }

        // 如果为空则返回
        if (empty($data)) {
            return false;
        }

        // 批量入库
        return self::batchInsert(array_keys(current($data)), $data);
    }

    /**
     * 计算阶梯团中最低价格
     * @param int $activityId
     * @param int $goodsId
     * @return mixed
     * @author Jason
     */
    public static function calculateLadderPrice(int $activityId, int $goodsId)
    {
        $goodsInfo = self::find()
            ->where([
                'activity_id' => $activityId,
                'goods_id' => $goodsId,
            ])
            ->select([
                'price',
                'ladder_price',
                'option_id',
                'is_ladder',
            ])
            ->indexBy('option_id')
            ->get();

        if (empty($goodsInfo)) {
            return error('未查询到商品活动信息');
        }

        //如果商品有规格，就是多规格商品，需要查最低价格
        $hasOption = !empty((float)reset($goodsInfo)['option_id']);

        //如果是阶梯团，同样要查阶梯价格
        $hasLadder = reset($goodsInfo)['is_ladder'] == 1;

        $priceInfo = [];
        foreach ($goodsInfo as $info) {
            if ($hasLadder) {
                $priceInfo = array_merge($priceInfo, Json::decode($info['ladder_price']));
            } elseif ($hasOption) {
                $priceInfo[] = $info['price'];
            } else {
                $priceInfo[] = $info['price'];
            }
        }

        $priceRange['has_range'] = true;
        //如果没规格没阶梯，直接取
        if (empty($hasLadder) && empty($hasOption)) {
            $priceRange['has_range'] = false;
            $priceRange['activity_price'] = min($priceInfo);
        } else {
            $priceRange['min_price'] = min($priceInfo);
            $priceRange['max_price'] = max($priceInfo);
        }

        return $priceRange;
    }

}