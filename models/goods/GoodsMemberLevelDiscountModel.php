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

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%goods_member_level_discount}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $level_id 会员等级id
 * @property int $type 类型 1 折扣 2 价格
 * @property int $option_id 规格id  当type=3时用
 * @property string $discount 折扣
 */
class GoodsMemberLevelDiscountModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_member_level_discount}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'level_id', 'type', 'option_id'], 'integer'],
            [['discount'], 'number'],
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
            'level_id' => '会员等级id',
            'type' => '折扣类型  2 指定会员等级   3 多规格折扣',
            'option_id' => '规格id  当type=3时用',
            'discount' => '折扣',
        ];
    }


    /**
     * 获取会员等级折扣信息
     * @param int $goodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDiscount(int $goodsId)
    {
        return self::find()
            ->select('id, level_id, option_id, type, discount')
            ->where(['goods_id' => $goodsId])
            ->all();
    }
}