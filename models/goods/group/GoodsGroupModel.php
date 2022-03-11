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

use shopstar\exceptions\goods\GoodsException;

/**
 * This is the model class for table "{{%goods_group}}".
 *
 * @property int $id
 * @property int $status 状态0禁用 1启用
 * @property string $name 商品组名称
 * @property string $desc 商品简介
 * @property int $sort_type 排序方式,0=创建时间倒序,1=创建时间正序,2=商品浏览量倒序,3=商品销量倒序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class GoodsGroupModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'sort_type'], 'integer'],
            [['desc'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['updated_at'], 'required'],
            [['name'], 'string', 'max' => 191],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => '商品组id',
            'name' => '商品组名称',
            'status' => '商品组状态',
            'goods_title' => '商品名称',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => '状态0禁用 1启用',
            'name' => '商品组名称',
            'desc' => '商品简介',
            'sort_type' => '排序方式,0=创建时间倒序,1=创建时间正序,2=商品浏览量倒序,3=商品销量倒序',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取单个分组
     * @param $id
     * @return GoodsGroupModel|null
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOne($id)
    {
        $model = self::findOne(['id' => $id]);

        if (empty($model)) {
            throw new GoodsException(GoodsException::GROUP_GET_ONE_NOT_FOUND_ERROR);
        }

        return $model;
    }

}