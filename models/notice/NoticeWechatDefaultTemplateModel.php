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

namespace shopstar\models\notice;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_notice_default_template}}".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $type_code 类型编码
 * @property string $template_code 模板编码
 * @property string $template_name 模板名称
 * @property string $content 内容
 * @property int $group_id 分组
 */
class NoticeWechatDefaultTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_notice_wechat_default_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id'], 'required'],
            [['group_id'], 'integer'],
            [['name', 'type_code', 'template_code', 'template_name'], 'string', 'max' => 191],
            [['content'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'type_code' => '类型编码',
            'template_code' => '模板编码',
            'template_name' => '模板名称',
            'content' => '内容',
            'group_id' => '分组',
        ];
    }

}
