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
 * This is the model class for table "{{%poster_log}}".
 *
 * @property int $id auto increment id
 * @property int $poster_id 海报ID
 * @property string $openid 推荐者
 * @property string $from_openid 关注者
 * @property int $sub_credit 关注者获得积分
 * @property string $sub_cash 关注者获得现金
 * @property int $sub_coupon 关注者获得优惠券
 * @property int $rec_credit 推荐者获得积分
 * @property string $rec_cash 推荐者获得现金
 * @property int $rec_coupon 推荐者获得优惠券
 * @property string $created_at 创建时间
 */
class PosterLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%poster_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poster_id', 'sub_credit', 'sub_coupon', 'rec_credit', 'rec_coupon'], 'integer'],
            [['sub_cash', 'rec_cash'], 'number'],
            [['created_at'], 'safe'],
            [['openid', 'from_openid'], 'string', 'max' => 64],
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
            'openid' => '推荐者',
            'from_openid' => '关注者',
            'sub_credit' => '关注者获得积分',
            'sub_cash' => '关注者获得现金',
            'sub_coupon' => '关注者获得优惠券',
            'rec_credit' => '推荐者获得积分',
            'rec_cash' => '推荐者获得现金',
            'rec_coupon' => '推荐者获得优惠券',
            'created_at' => '创建时间',
        ];
    }
}