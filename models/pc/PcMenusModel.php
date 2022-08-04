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
 * This is the model class for table "{{%pc_menus}}".
 *
 * @property int $id
 * @property int $status 0关闭，1开启
 * @property string $name
 * @property string $url
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 * @property int $type 1为顶部菜单，2为底部菜单
 * @property string $img 图
 */
class PcMenusModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%pc_menus}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status', 'sort_order', 'type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['url', 'img'], 'string', 'max' => 150],
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
            'url' => 'Url',
            'sort_order' => 'Sort Order',
            'created_at' => 'Create Time',
            'updated_at' => 'Update Time',
            'type' => '1为顶部菜单，2为底部菜单',
            'img' => '图',
        ];
    }
}
