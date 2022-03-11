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

namespace shopstar\models\core;
use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%core_express_cache}}".
 *
 * @property int $id
 * @property string $express_sn
 * @property string $express_code
 * @property int $last_time
 * @property string $express_data
 */
class CoreExpressCacheModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_express_cache}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['last_time'], 'required'],
            [['last_time'], 'safe'],
            [['express_data'], 'string'],
            [['express_sn', 'express_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_sn' => 'Express Sn',
            'express_code' => 'Express Code',
            'last_time' => 'last_time',
            'express_data' => 'Express Data',
        ];
    }
}