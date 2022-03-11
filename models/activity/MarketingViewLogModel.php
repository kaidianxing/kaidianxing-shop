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

namespace shopstar\models\activity;


/**
 * This is the model class for table "{{%activity_view_log}}".
 *
 * @property int $id
 * @property int $activity_id 活动id
 * @property string $activity_type 活动类型
 * @property int $member_id 会员id
 * @property int $goods_id 商品id
 * @property string $created_at 创建时间
 */
class MarketingViewLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%marketing_view_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'member_id', 'goods_id'], 'integer'],
            [['created_at'], 'safe'],
            [['activity_type'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动id',
            'activity_type' => '活动类型',
            'member_id' => '会员id',
            'goods_id' => '商品id',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 添加查看记录
     * @param int $activityId
     * @param int $memberId
     * @param int $goodsId
     * @param string $activityType
     * @author 青岛开店星信息技术有限公司
     */
    public static function insertViewLog(int $activityId, int $memberId, int $goodsId, string $activityType)
    {
        $log = new self();
        $log->setAttributes([
            'activity_id' => $activityId,
            'member_id' => $memberId,
            'goods_id' => $goodsId,
            'activity_type' => $activityType,
        ]);
        $log->save();
    }
}