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

namespace shopstar\models\goods\group;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%goods_group_map}}".
 *
 * @property int $goods_id 商品id
 * @property int $group_id 分组id
 */
class GoodsGroupMapModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_group_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'group_id',], 'required'],
            [['goods_id', 'group_id',], 'integer'],
            [['goods_id', 'group_id'], 'unique', 'targetAttribute' => ['goods_id', 'group_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => '商品id',
            'group_id' => '分组id',
        ];
    }

    /**
     * 更新字段信息
     * @var array updateField
     */
    protected static $updateField = ['goods_id', 'group_id'];

    /**
     * 根据分组id获取商品id
     * @param $groupId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsIdByGroupId($groupId)
    {
        return self::find()
            ->where([
                'group_id' => $groupId,
            ])
            ->select(['goods_id'])->asArray()->column();
    }
}