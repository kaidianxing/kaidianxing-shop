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

namespace shopstar\models\goods\label;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%goods_label_group}}".
 *
 * @property int $id
 * @property string $name 标签组名称
 * @property string $status 状态 1开启0关闭
 * @property int $sort_by 权重
 * @property int $created_at 创建时间
 * @property int $is_default 是否默认标签组
 */
class GoodsLabelGroupModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_label_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['sort_by', 'status', 'is_default'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 191],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => '商品标签组id',
            'sort_by' => '权重',
            'name' => '商品标签组名称',
            'status' => '商品标签组状态',
            'label_name' => '标签名称',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '标签组名称',
            'status' => '状态 1开启0关闭',
            'sort_by' => '权重',
            'created_at' => '创建时间',
            'is_default' => '是否默认标签组',
        ];
    }

    public function getLabel()
    {
        return $this->hasMany(GoodsLabelModel::class, ['group_id' => 'id']);
    }

    /**
     * 内置标签
     */
    const RECOMMEND = [
        [
            'id' => -1,
            'name' => '正品保证',
            'desc' => '系统内置',
        ],
        [
            'id' => -2,
            'name' => '假一赔十',
            'desc' => '系统内置',
        ],
        [
            'id' => -3,
            'name' => '7天无理由退货',
            'desc' => '系统内置',
        ],
    ];

    /**
     * 系统默认标签组
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function defaultLabelGroup()
    {
        $result = [
            'name' => '默认标签组',
            'status' => '1',
            'sort_by' => '9999',
            'is_default' => '1',
        ];
        return $result;
    }
}