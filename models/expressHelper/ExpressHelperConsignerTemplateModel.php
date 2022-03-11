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

namespace shopstar\models\expressHelper;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_express_helper_consigner_template}}".
 *
 * @property string $name 模板名称
 * @property string $consigner_company 发件人公司
 * @property string $consigner_name 发件人名称
 * @property string $consigner_mobile 发件人手机号
 * @property string $consigner_province 发件省
 * @property string $consigner_city 发件市
 * @property string $consigner_area 发件区
 * @property string $consigner_address 发件详细地址
 * @property int $postcode 邮编
 * @property string $created_at 创建时间
 * @property int $is_default 是否默认1是0否
 */

class ExpressHelperConsignerTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_express_helper_consigner_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_default'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'consigner_company', 'consigner_name', 'consigner_address'], 'string', 'max' => 120],
            [['consigner_mobile'], 'string', 'max' => 11],
            [['consigner_province', 'consigner_city', 'consigner_area'], 'string', 'max' => 20],
            [['postcode'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => '模板名称',
            'consigner_company' => '发件人公司',
            'consigner_name' => '发件人名称',
            'consigner_mobile' => '发件人手机号',
            'consigner_province' => '发件省',
            'consigner_city' => '发件市',
            'consigner_area' => '发件区',
            'consigner_address' => '发件详细地址',
            'postcode' => '邮编',
            'created_at' => '创建时间',
            'is_default' => '是否默认1是0否',
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'name' => '模板名称',
            'consigner_company' => '发件人公司',
            'consigner_name' => '发件人名称',
            'consigner_mobile' => '发件人手机号',
            'consigner_province' => '发件省',
            'consigner_city' => '发件市',
            'consigner_area' => '发件区',
            'consigner_address' => '发件详细地址',
            'postcode' => '邮编',
            'is_default' => '是否默认',
        ];
    }
}