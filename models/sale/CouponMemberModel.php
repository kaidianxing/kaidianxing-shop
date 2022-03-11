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

namespace shopstar\models\sale;

use shopstar\constants\coupon\CouponConstant;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%coupon_member}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $coupon_id 优惠券id
 * @property int $coupon_sale_type 优惠券类型 1满减券/2折扣券
 * @property string $title 优惠券名称
 * @property string $discount_price 优惠券优惠额度
 * @property string $enough 优惠券使用门槛
 * @property string $start_time 优惠券有效时间左边界
 * @property string $end_time 优惠券有效时间右边界
 * @property string $used_time 已使用则记录使用时间，但优惠券是否已经使用，应该根据order_id的值去判断
 * @property string $notice_time 优惠券通知用户时间
 * @property int $order_id 优惠券使用的订单的ID，订单发生取消时，可能需要退还，退换请务必把此字段置位为0，因为此字段是判断优惠券是否使用的依据
 * @property string $order_no 订单号
 * @property int $source 来来源标识  0 后台发放   2新人发券 3满额发券 4购物发券   11免费 12付费  13链接  20 活动领取 21积分商城兑换
 * @property int $coupon_sale_limit 优惠使用限制 0无限制 1不予会员折扣同享
 * @property int $goods_limit 商品使用限制 0无限制 1允许以下产品使用 2不允许一下产品使用 3允许以下分类使用
 * @property string $created_at 优惠券领取时间
 * @property string $updated_at 更新时间
 * @property int $status 使用状态
 */
class CouponMemberModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_member}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'coupon_id', 'coupon_sale_type', 'order_id', 'source', 'coupon_sale_limit', 'goods_limit', 'status'], 'integer'],
            [['discount_price', 'enough'], 'number'],
            [['start_time', 'end_time', 'used_time', 'notice_time', 'created_at', 'updated_at'], 'safe'],
            [['title', 'order_no'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'coupon_id' => '优惠券id',
            'coupon_sale_type' => '优惠券类型 1满减券/2折扣券',
            'title' => '优惠券名称',
            'discount_price' => '优惠券优惠额度',
            'enough' => '优惠券使用门槛',
            'start_time' => '优惠券有效时间左边界',
            'end_time' => '优惠券有效时间右边界',
            'used_time' => '已使用则记录使用时间，但优惠券是否已经使用，应该根据order_id的值去判断',
            'notice_time' => '优惠券通知用户时间',
            'order_id' => '优惠券使用的订单的ID，订单发生取消时，可能需要退还，退换请务必把此字段置位为0，因为此字段是判断优惠券是否使用的依据',
            'order_no' => '订单号',
            'source' => '来源标识  0 后台发放   2新人发券 3满额发券 4购物发券   11免费 12付费  13链接  20 活动领取 21积分商城兑换',
            'coupon_sale_limit' => '优惠使用限制 0无限制 1不予会员折扣同享',
            'goods_limit' => '商品使用限制 0无限制 1允许以下产品使用 2不允许一下产品使用 3允许以下分类使用 ',
            'created_at' => '优惠券领取时间',
            'updated_at' => '更新时间',
            'status' => '使用状态',
        ];
    }

    /**
     * 优惠券map关系
     * @return \yii\db\ActiveQuery
     */
    public function getMap()
    {
        return $this->hasMany(CouponMapModel::class, ['coupon_id' => 'coupon_id']);
    }

    /**
     * 会员优惠券关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getCoupon()
    {
        return $this->hasOne(CouponModel::class, ['id' => 'coupon_id']);
    }

    /**
     * 获取用户所有可用优惠券
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberCoupon(int $memberId)
    {
        return self::find()
            ->select('id, coupon_id, coupon_sale_type, title, enough, discount_price, end_time, goods_limit, coupon_sale_limit')
            ->where(['member_id' => $memberId, 'order_id' => 0, 'status' => 0])
            ->andWhere(['>=', 'end_time', DateTimeHelper::now()])
            ->andWhere(['<=', 'start_time', DateTimeHelper::now()])
            ->with('map')
            ->get();
    }

    /**
     * 获取优惠券数量
     * @param $memberId
     * @param $state
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTotal($memberId, $state)
    {
        $andWhere = self::getCouponListFilterWhere($memberId, $state);
        return self::find()
            ->where($andWhere)
            ->count();
    }

    /**
     * 获取优惠券列表筛选条件
     * @param $memberId
     * @param $state
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function getCouponListFilterWhere($memberId, $state)
    {
        $andWhere = [
            'and',
            ['member_id' => $memberId],
        ];
        if ($state == CouponConstant::COUPON_LIST_TYPE_NORMAL) {
            $andWhere[] = [
                'and',
                ['order_id' => 0],
                ['status' => 0],
            ];
            $andWhere[] = [
                'or',
                [
                    'and',
                    ['>', 'end_time', DateTimeHelper::now()],
                    ['<', 'start_time', DateTimeHelper::now()],
                ],
                ['end_time' => null]
            ];
        }
        if ($state == CouponConstant::COUPON_LIST_TYPE_USED) {
            $andWhere[] = [
                'or',
                ['>', 'order_id', 0],
                ['status' => 1],
            ];
        }
        if ($state == CouponConstant::COUPON_LIST_TYPE_EXPIRE) {
            $andWhere[] = [
                'and',
                ['<', 'end_time', DateTimeHelper::now()],
                [
                    'or',
                    ['order_id' => 0],
                    ['status' => 0],
                ],
            ];
        }

        return $andWhere;
    }

    /**
     * 获取优惠券基本信息
     * @param int $memberId
     * @param array $couponIds
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCouponInfo(int $memberId, array $couponIds)
    {
        $couponInfo = self::find()
            ->select('coupon_id, title, coupon_sale_type, enough, discount_price, start_time, end_time, notice_time')
            ->where([
                'member_id' => $memberId,
                'coupon_id' => $couponIds
            ])
            ->asArray()
            ->all();
        if (is_array($couponInfo)) {
            foreach ($couponInfo as $key => $value) {
                if ($value['coupon_sale_type'] == 1) {
                    $couponInfo[$key]['content'] = '满' . $value['enough'] . '满' . $value['discount_price'];
                } else {
                    // 打折类型
                    $couponInfo[$key]['content'] = '满' . $value['enough'] . '享' . $value['discount_price'] . '折';
                }
            }
        }
        return $couponInfo;
    }


}
