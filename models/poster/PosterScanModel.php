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
 * This is the model class for table "{{%poster_scan}}".
 *
 * @property int $id auto increment id
 * @property int $poster_id 海报ID
 * @property string $openid 推荐者openid
 * @property string $from_openid 关注者openid
 * @property string $scan_time 扫码时间
 */
class PosterScanModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%poster_scan}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poster_id'], 'integer'],
            [['scan_time'], 'safe'],
            [['openid', 'from_openid'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment id',
            'poster_id' => '海报ID',
            'openid' => '推荐者openid',
            'from_openid' => '关注者openid',
            'scan_time' => '扫码时间',
        ];
    }
}