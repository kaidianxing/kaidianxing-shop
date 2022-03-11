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
 * This is the model class for table "{{%app_notice_wxapp_template}}".
 *
 * @property int $id
 * @property string $title 模板名称
 * @property string $scene_code 模板对应消息接口
 * @property string $template_id 模板消息tid
 * @property string $pri_tmpl_id 可发送模板消息id
 * @property string $created_at 创建时间
 * @property string $content 内容
 * @property string $kid_list 字段id顺序
 * @property string $scene_desc 服务场景描述
 */
class NoticeWxappTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_notice_wxapp_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'scene_desc'], 'required'],
            [['created_at'], 'safe'],
            [['content'], 'string'],
            [['title', 'template_id', 'pri_tmpl_id', 'kid_list', 'scene_desc'], 'string', 'max' => 191],
            [['scene_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '模板名称',
            'scene_code' => '模板对应消息接口',
            'template_id' => '模板消息tid',
            'pri_tmpl_id' => '可发送模板消息id',
            'created_at' => '创建时间',
            'content' => '内容',
            'kid_list' => '字段id顺序',
            'scene_desc' => '服务场景描述',
        ];
    }
}
