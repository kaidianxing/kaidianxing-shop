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

namespace shopstar\models\material;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "shopstar_material".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property string|null $goods 支持商品json
 * @property int $description_type 0 系统默认 1 自定义
 * @property string $description 介绍
 * @property int $material_type 0 图片 1 视频
 * @property string|null $thumb_all 所有商品封面图
 * @property string $video 首图视频
 * @property string $video_thumb 视频首图
 * @property int $is_deleted 是否删除 1删除
 * @property string $create_time 创建时间
 */
class MaterialModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%material}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['goods_id', 'description_type', 'material_type', 'is_deleted'], 'integer'],
            [['goods', 'thumb_all'], 'string'],
            [['create_time'], 'safe'],
            [['description'], 'string', 'max' => 1000],
            [['video', 'video_thumb'], 'string', 'max' => 191],
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
            'goods' => '支持商品json',
            'description_type' => '0 系统默认 1 自定义',
            'description' => '介绍',
            'material_type' => '0 图片 1 视频',
            'thumb_all' => '所有商品封面图',
            'video' => '首图视频',
            'video_thumb' => '视频首图',
            'is_deleted' => '是否删除 1删除',
            'create_time' => '创建时间',
        ];
    }
}
