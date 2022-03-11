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

namespace shopstar\models\goods;

use shopstar\helpers\DateTimeHelper;


/**
 * This is the model class for table "{{%goods_activity}}".
 *
 * @property string $id
 * @property int $goods_id 商品
 * @property int $activity_id 活动id
 * @property string $activity_type 活动标识
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property int $is_delete_activity 删除活动
 * @property string $client_type 客户端类型
 * @property int $is_preheat 是否预热
 * @property string $preheat_time 预热时间
 */
class GoodsActivityModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'activity_id', 'is_delete_activity', 'is_preheat'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'preheat_time'], 'safe'],
            [['activity_type'], 'string', 'max' => 50],
            [['client_type'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品',
            'activity_id' => '活动id',
            'activity_type' => '活动标识',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'is_delete_activity' => '删除活动',
            'client_type' => '客户端类型',
            'is_preheat' => '是否预热',
            'preheat_time' => '预热时间',
        ];
    }

    /**
     * 插入活动商品
     * @param array $data
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function insertData(array $data)
    {
        $goodsActivityFields = ['goods_id', 'activity_id', 'activity_type', 'start_time', 'end_time', 'client_type', 'is_preheat', 'preheat_time'];
        self::batchInsert($goodsActivityFields, $data);
    }

    /**
     * 删除活动
     * @param int $activityId
     * @param string $activity
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteActivity(int $activityId, string $activity)
    {
        self::updateAll(['is_delete_activity' => 1], ['activity_id' => $activityId, 'activity_type' => $activity]);
    }

    /**
     * 修改结束时间
     * @param int $activityId
     * @param string $activity
     * @param string $endTime
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeEndTime(int $activityId, string $activity, string $endTime)
    {
        self::updateAll(['end_time' => $endTime], ['activity_id' => $activityId, 'activity_type' => $activity]);
    }

    /**
     * 修改预热
     * @param int $activityId
     * @param string $activity
     * @param int $isPreHeat
     * @param string $preheatTime
     * @author 青岛开店星信息技术有限公司
     */
    public static function changePreheat(int $activityId, string $activity, int $isPreHeat, string $preheatTime)
    {
        $data = [
            'is_preheat' => $isPreHeat,
        ];
        if ($isPreHeat) {
            $data['preheat_time'] = $preheatTime;
        }
        self::updateAll($data, ['activity_id' => $activityId, 'activity_type' => $activity]);
    }

    /**
     * 是否存在预热商品
     * @param array $goodsIds
     * @param int $clientType
     * @return array
     * @author nizengchao
     */
    public static function getPreheatActivityExist(array $goodsIds = [], int $clientType = 0): array
    {
        $nowDate = DateTimeHelper::now();
        $goodsIdStr = implode(',', $goodsIds);
        return self::find()
            ->select('id,goods_id,activity_id,activity_type')
            ->where(['is_delete_activity' => 0])
            ->andWhere([
                'and',
                ['is_preheat' => 1],
                ['<', 'preheat_time', $nowDate],
                ['>', 'end_time', $nowDate],
            ])
            ->andWhere('find_in_set(' . $clientType . ',client_type)')
            ->andWhere(['in', 'goods_id', $goodsIdStr])
            ->asArray()
            ->all();
    }

    /**
     * 处理价格面议过滤
     * 无活动及无预热活动, 开启了价格面议, 直接失效该商品
     * @param array $list
     * @return array
     * @author nizengchao
     */
    public static function doBuyButtonFilter(array $list = []): array
    {
        $loseGoodsIds = [];
        foreach ($list as $k => $goods) {
            if (!empty($goods['activity_type']) || !empty($goods['preheat_activity_type'])) {
                continue;
            }
            if ($goods['buy_button_status'] == 1) {
                $loseGoodsIds[] = $goods['goods_id'];
                unset($list[$k]);
            }
        }

        // 失效的
        if (!empty($loseGoodsIds)) {
            GoodsCartModel::updateAll(['is_lose_efficacy' => 1], ['goods_id' => array_unique($loseGoodsIds)]);
        }

        return array_values($list);
    }

}