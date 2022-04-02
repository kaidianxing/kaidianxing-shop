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
 * This is the model class for table "{{%poster_attention_profile}}".
 *
 * @property int $id auto increment id
 * @property int $poster_id 海报ID
 * @property int $type 推送方式 1图文推送 2文字推送
 * @property string $title 推送标题
 * @property string $thumb 推送封面
 * @property string $description 推送描述
 * @property string $url 推送链接
 * @property string $url_name 推送链接名称
 * @property int $status 关注奖励是否开启 0关闭1开启
 * @property int $rec_credit_enable 推荐人积分 0否1开启
 * @property int $rec_cash_enable 推荐人现金 0否1开启
 * @property int $rec_coupon_enable 推荐人优惠券 0否1开启
 * @property int $rec_credit 推荐人获得积分
 * @property int $rec_credit_limit 推荐人积分每月积分奖励上限
 * @property string $rec_cash 推荐人获得现金
 * @property string $rec_cash_limit 推荐人现金每月奖励上限
 * @property int $rec_cash_type 推荐人获得现金类型 1余额2红包
 * @property int $rec_coupon 推荐人获得优惠券
 * @property int $rec_coupon_limit 推荐人优惠券每月最多发放数量
 * @property int $sub_credit_enable 关注者积分 0否1开启
 * @property int $sub_cash_enable 关注者现金 0否1开启
 * @property int $sub_coupon_enable 关注者优惠券 0否1开启
 * @property int $sub_credit 关注者获得积分
 * @property string $sub_cash 关注者获得现金
 * @property int $sub_cash_type 关注者获得现金类型 1余额2红包
 * @property int $sub_coupon 关注者获得优惠券
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PosterAttentionModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%poster_attention}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poster_id', 'type', 'rec_credit_enable', 'rec_cash_enable', 'rec_coupon_enable', 'rec_credit', 'rec_credit_limit', 'rec_cash_type', 'rec_coupon', 'rec_coupon_limit', 'sub_credit_enable', 'sub_cash_enable', 'sub_coupon_enable', 'sub_credit', 'sub_cash_type', 'sub_coupon', 'status'], 'integer'],
            [['rec_cash', 'rec_cash_limit', 'sub_cash'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'thumb'], 'string', 'max' => 128],
            [['description', 'url', 'url_name'], 'string', 'max' => 255],
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
            'type' => '推送方式 1图文推送 2文字推送',
            'title' => '推送标题',
            'thumb' => '推送封面',
            'description' => '推送描述',
            'url' => '推送链接',
            'url_name' => '推送链接名称',
            'status' => '关注奖励是否开启 0关闭1开启',
            'rec_credit_enable' => '推荐人积分 0否1开启',
            'rec_cash_enable' => '推荐人现金 0否1开启',
            'rec_coupon_enable' => '推荐人优惠券 0否1开启',
            'rec_credit' => '推荐人获得积分',
            'rec_credit_limit' => '推荐人积分每月积分奖励上限',
            'rec_cash' => '推荐人获得现金',
            'rec_cash_limit' => '推荐人现金每月奖励上限',
            'rec_cash_type' => '推荐人获得现金类型 1余额2红包',
            'rec_coupon' => '推荐人获得优惠券',
            'rec_coupon_limit' => '推荐人优惠券每月最多发放数量',
            'sub_credit_enable' => '关注者积分 0否1开启',
            'sub_cash_enable' => '关注者现金 0否1开启',
            'sub_coupon_enable' => '关注者优惠券 0否1开启',
            'sub_credit' => '关注者获得积分',
            'sub_cash' => '关注者获得现金',
            'sub_cash_type' => '关注者获得现金类型 1余额2红包',
            'sub_coupon' => '关注者获得优惠券',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

}