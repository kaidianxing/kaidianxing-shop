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
 * This is the model class for table "{{%pc_home_advertise}}".
 *
 * @property int $id
 * @property string $name
 * @property string $img 图
 * @property string $url
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class PcHomeAdvertiseModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%pc_home_advertise}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['sort_order'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['img', 'url'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'img' => '图',
            'url' => 'Url',
            'sort_order' => 'Sort Order',
            'created_at' => 'Create Time',
            'updated_at' => 'Update Time',
        ];
    }
}
