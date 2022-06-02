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
 * This is the model class for table "{{%notice_wechat_subscribe_default_template}}".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $scene_code 类型编码
 * @property string $template_id 模板编码
 * @property string $template_name 模板名称
 * @property string $kid_list 关键词顺序
 * @property string $scene_desc 服务场景描述
 * @property string $content 内容
 */
class NoticeWechatSubscribeDefaultTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%notice_wechat_subscribe_default_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['content'], 'required'],
            [['content'], 'string'],
            [['name', 'scene_code', 'template_id', 'template_name', 'kid_list', 'scene_desc'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'scene_code' => '类型编码',
            'template_id' => '模板编码',
            'template_name' => '模板名称',
            'kid_list' => '关键词顺序',
            'scene_desc' => '服务场景描述',
            'content' => '内容',
        ];
    }
}