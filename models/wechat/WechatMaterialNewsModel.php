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

namespace shopstar\models\wechat;


use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_wechat_material_news}}".
 *
 * @property int $id
 * @property int $material_id 素材库主id
 * @property int $index 排序
 * @property string $media_id media_id
 * @property string $title 文章标题
 * @property string $author 作者
 * @property string $description 简介
 * @property string|null $content 文章内容
 * @property string $content_source_url 内容引用地址
 * @property string $thumb_media_id 封面图media id
 * @property int $show_cover_pic 是否显示封面图
 * @property string $url 图文url
 * @property string $thumb_url 缩略图url
 * @property int $need_open_comment 打开评论
 * @property int $only_fans_can_comment 是否只有粉丝能评论
 */
class WechatMaterialNewsModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_wechat_material_news}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['material_id', 'index', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment'], 'integer'],
            [['content'], 'string'],
            [['media_id', 'title', 'author', 'description', 'content_source_url', 'thumb_media_id'], 'string', 'max' => 191],
            [['url', 'thumb_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'material_id' => '素材库主id',
            'index' => '排序',
            'media_id' => 'media_id',
            'title' => '文章标题',
            'author' => '作者',
            'description' => '简介',
            'content' => '文章内容',
            'content_source_url' => '内容引用地址',
            'thumb_media_id' => '封面图media id',
            'show_cover_pic' => '是否显示封面图',
            'url' => '图文url',
            'thumb_url' => '缩略图url',
            'need_open_comment' => '打开评论',
            'only_fans_can_comment' => '是否只有粉丝能评论',
        ];
    }
}