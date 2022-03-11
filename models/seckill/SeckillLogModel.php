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

namespace shopstar\models\seckill;

/**
 * This is the model class for table "{{%app_seckill_log}}".
 *
 * @property string $id
 * @property int $member_id 会员id
 * @property int $activity_id 活动id
 * @property int $goods_id 商品id
 * @property int $total 数量
 * @property string $created_at 创建时间
 */
class SeckillLogModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_seckill_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'activity_id', 'goods_id', 'total'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => ' ',
            'member_id' => '会员id',
            'activity_id' => '活动id',
            'goods_id' => '商品id',
            'total' => '数量',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 秒杀记录
     * @param int $memberId
     * @param int $goodsId
     * @param int $total
     * @param int $activityId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function createLog(int $memberId, int $goodsId, int $total, int $activityId)
    {
        $log = new self();
        $log->setAttributes([
            'member_id' => $memberId,
            'activity_id' => $activityId,
            'goods_id' => $goodsId,
            'total' => $total,
        ]);
        if (!$log->save()) {
            return error('秒杀记录保存失败 ' . $log->getErrorMessage());
        }

        return true;
    }
}