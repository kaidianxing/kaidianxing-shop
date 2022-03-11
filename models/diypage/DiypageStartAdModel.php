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

namespace shopstar\models\diypage;

use shopstar\bases\model\BaseActiveRecord;

/**
 * 应用-店铺装修-启动广告实体类
 * This is the model class for table "{{%app_diypage_start_ad}}".
 *
 * @property int $id
 * @property string $name 广告名称
 * @property string $content 广告内容
 * @property int $status 状态 0:不启用 1:启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class DiypageStartAdModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_diypage_start_ad}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'content'], 'required'],
            [['status'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '广告名称',
            'content' => '广告内容',
            'status' => '状态 0:不启用 1:启用',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}