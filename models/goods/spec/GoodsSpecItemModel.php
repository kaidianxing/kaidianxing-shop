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
 * This is the model class for table "{{%goods_spec_item}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $spec_id 规格ID
 * @property string $title 标题
 * @property int $sort_by 顺序
 */
class GoodsSpecItemModel extends BaseActiveRecord
{
    use SpecTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_spec_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'spec_id', 'sort_by'], 'integer'],
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
            'goods_id' => '商品id',
            'spec_id' => '规格ID',
            'title' => '标题',
            'sort_by' => '顺序',
        ];
    }

}