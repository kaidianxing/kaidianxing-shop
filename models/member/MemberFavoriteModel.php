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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\models\goods\GoodsModel;

/**
 * This is the model class for table "{{%member_favorite}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $member_id 会员id
 * @property string $created_at 创建时间
 */
class MemberFavoriteModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_favorite}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'member_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
            'member_id' => '会员id',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 收藏商品关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGoods()
    {
        return $this->hasOne(GoodsModel::class, ['id' => 'goods_id']);
    }

    /**
     * 获取是否已收藏
     * @param int $goodsId
     * @param int $memberId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getIsFavorite(int $goodsId, int $memberId): bool
    {
        return !empty(self::findOne(['goods_id' => $goodsId, 'member_id' => $memberId]));
    }

    /**
     * 修改收藏
     * @param bool $isAdd
     * @param $goodsId array|integer
     * @param int $memberId
     * @return bool|int
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeFavorite(bool $isAdd, $goodsId, int $memberId)
    {
        $data = [
            'goods_id' => $goodsId,
            'member_id' => $memberId,
        ];

        self::deleteAll($data);

        if (!$isAdd) {
            return true;
        }

        $value = [];
        foreach ((array)$goodsId as $goodsIdIndex => $goodsIdItem) {
            $value[] = [$goodsIdItem, $memberId];
        }

        return self::batchInsert(['goods_id', 'member_id'], $value);
    }

    /**
     * 获取列表
     * @func getResult
     * @param $member_id
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getResult($member_id): array
    {
        $result = self::getColl(
            [
                'where' => [
                    'member_id' => $member_id
                ],
                'select' => 'goods_id',
                'orderby' => 'id desc',
                'with' => ['goods' => function ($query) {
                    $query->select('id,title,thumb');
                }]
            ],
            []
        );
        return $result;
    }
}
