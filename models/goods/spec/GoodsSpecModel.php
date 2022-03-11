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

namespace shopstar\models\goods\spec;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\models\goods\SpecTrait;

/**
 * This is the model class for table "{{%goods_spec}}".
 *
 * @property int $id
 * @property int $goods_id 商品ID
 * @property int $sort_by 排序
 * @property string $title 标题
 * @property int $image_checked 是否有图片
 */
class GoodsSpecModel extends BaseActiveRecord
{
    use SpecTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_spec}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'sort_by', 'image_checked'], 'integer'],
            [['title'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品ID',
            'sort_by' => '排序',
            'title' => '标题',
            'image_checked' => '是否有图片',
        ];
    }

    /**
     * @param int $id
     * @return array|bool|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSpaceById(int $id)
    {
        if (empty($id)) {
            return false;
        }

        //获取spec 然后获取specitem
        $spec = self::find()
            ->where(['goods_id' => $id])
            ->select('goods_id,title,id,image_checked')
            ->orderBy(['sort_by' => SORT_DESC])
            ->with(['items' => function ($query) {
                $query->select('title,id,spec_id');
                $query->orderBy(['id' => SORT_ASC]);
            }])
            ->asArray()
            ->all();
        return $spec;
    }

    public function getitems()
    {
        return $this->hasMany(GoodsSpecItemModel::class, ['spec_id' => 'id']);
    }
}