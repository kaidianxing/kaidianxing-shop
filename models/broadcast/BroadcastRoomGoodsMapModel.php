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

namespace shopstar\models\broadcast;

use shopstar\bases\model\BaseActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%broadcast_goods_map}}".
 *
 * @property int $id
 * @property int $broadcast_goods_id 小程序商品库商品id
 * @property int $goods_id 商城商品id
 * @property int $room_id 直播间id
 * @property int $pv_count 浏览量
 * @property int $sales 销量
 */
class BroadcastRoomGoodsMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%broadcast_goods_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['broadcast_goods_id', 'goods_id', 'room_id', 'pv_count', 'sales'], 'integer'],
            [['goods_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'broadcast_goods_id' => '小程序商品库商品id',
            'goods_id' => '商城商品id',
            'room_id' => '直播间id',
            'pv_count' => '浏览量',
            'sales' => '销量',
        ];
    }

    /**
     * 保存映射关系
     * @param array $goods
     * @param int $roomId
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveMap(array $goods, int $roomId)
    {

        $data = [];
        foreach ($goods as $item) {
            $data[] = [
                'broadcast_goods_id' => $item['broadcast_goods_id'],
                'goods_id' => $item['goods_id'],
                'room_id' => $roomId
            ];
        }

        return self::batchInsert(array_keys($data[0]), $data);
    }

    /**
     * 获取映射
     * @param int $roomId
     * @param int $goodsId
     * @return BroadcastRoomGoodsMapModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMapModel(int $roomId, int $goodsId)
    {
        return self::findOne([
            'room_id' => $roomId,
            'goods_id' => $goodsId
        ]);
    }

    /**
     * 添加销量
     * @param int $roomId
     * @param array $goods
     * @param bool $add
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function addSales(int $roomId, array $goods, $add = true)
    {
        foreach ((array)$goods as $item) {
            BroadcastRoomGoodsMapModel::updateAll(['sales' => new Expression('sales ' . ($add ? '+ ' : '- ') . $item['total'])], [
                'room_id' => $roomId,
                'goods_id' => $item['goods_id']
            ]);
        }

        return true;
    }
}
