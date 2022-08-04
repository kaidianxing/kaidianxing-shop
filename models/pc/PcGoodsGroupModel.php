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

namespace shopstar\models\pc;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%pc_goods_group}}".
 *
 * @property int $id
 * @property int $status 0关闭，1开启
 * @property string $name
 * @property string $main_img
 * @property string $main_img_url
 * @property int $goods_type 1代表手动选择，2代表选择分类，3代表手动分组
 * @property string $goods_info
 * @property int $sort_order
 * @property string $top_advertise_img
 * @property string $top_advertise_img_url
 * @property string $bottom_advertise_img
 * @property string $bottom_advertise_img_url
 * @property string $created_at
 * @property string $updated_at
 */
class PcGoodsGroupModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%pc_goods_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'goods_type', 'sort_order'], 'integer'],
            [['goods_type', 'goods_info'], 'required'],
            [['goods_info'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['main_img', 'main_img_url', 'top_advertise_img', 'top_advertise_img_url', 'bottom_advertise_img', 'bottom_advertise_img_url'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'status' => '0关闭，1开启',
            'name' => 'Name',
            'main_img' => 'Main Img',
            'main_img_url' => 'Main Img Url',
            'goods_type' => '1代表手动选择，2代表选择分类，3代表手动分组',
            'goods_info' => 'Goods Info',
            'sort_order' => 'Sort Order',
            'top_advertise_img' => 'Top Advertise Img',
            'top_advertise_img_url' => 'Top Advertise Img Url',
            'bottom_advertise_img' => 'Bottom Advertise Img',
            'bottom_advertise_img_url' => 'Bottom Advertise Img Url',
            'created_at' => 'Create Time',
            'updated_at' => 'Update Time',
        ];
    }
}
