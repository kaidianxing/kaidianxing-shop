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

namespace shopstar\models\poster;


use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_poster_template}}".
 *
 * @property int $id 模板ID
 * @property int $system_id 系统模板ID 0:非系统模板 >0:系统模板
 * @property int $type 模板类型 0:自定义 10:商品海报 20:分销海报 30:关注海报
 * @property string $name 模板类型
 * @property int $status 状态 0:未启用 1:启用
 * @property string $thumb 封面图
 * @property string $content 模板内容
 * @property string $created_at 添加时间
 */
class PosterTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_poster_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_id', 'type', 'status'], 'integer'],
            [['status'], 'required'],
            [['content'], 'string'],
            [['created_at'], 'safe'],
            [['name', 'thumb'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '模板ID',
            'system_id' => '系统模板ID 0:非系统模板 >0:系统模板',
            'type' => '模板类型 0:自定义 10:商品海报 20:分销海报 30:关注海报',
            'name' => '模板类型',
            'status' => '状态 0:未启用 1:启用',
            'thumb' => '封面图',
            'content' => '模板内容',
            'created_at' => '添加时间',
        ];
    }
}