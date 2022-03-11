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

use shopstar\helpers\DateTimeHelper;
use shopstar\models\goods\GoodsModel;

/**
 * This is the model class for table "{{%member_browse_footprint}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $member_id 用户id
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class MemberBrowseFootprintModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_browse_footprint}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'member_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['updated_at'], 'required'],
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
            'member_id' => '用户id',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 浏览足迹 商品 关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGoods()
    {
        return $this->hasOne(GoodsModel::class, ['id' => 'goods_id']);
    }

    /**
     * @param int $goodsId
     * @param int $memberId
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveFootprint(int $goodsId, int $memberId)
    {
        $model = self::findOne(['goods_id' => $goodsId, 'member_id' => $memberId]);
        if ($model === null) {
            $model = new self();
        }
        $model->setAttributes([
            'goods_id' => $goodsId,
            'member_id' => $memberId,
            'updated_at' => DateTimeHelper::now()
        ]);

        return $model->save();
    }


    public function getFavorite()
    {
        return $this->hasOne(MemberFavoriteModel::class, ['goods_id' => 'goods_id']);
    }
}