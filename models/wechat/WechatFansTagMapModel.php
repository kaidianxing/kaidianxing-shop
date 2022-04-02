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
 * This is the model class for table "{{%wechat_fans_tag_map}}".
 *
 * @property int $id
 * @property int $wechat_tag_id 公众号标签id
 * @property int $fans_id 公众号粉丝标签
 * @property string $created_at 添加时间
 */
class WechatFansTagMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_fans_tag_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['wechat_tag_id', 'fans_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wechat_tag_id' => '公众号标签id',
            'fans_id' => '公众号粉丝标签',
            'created_at' => '添加时间',
        ];
    }
}