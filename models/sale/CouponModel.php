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

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\coupon\CouponTimeLimitConstant;
use shopstar\constants\log\sale\CouponLogConstant;
 
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;

/**
 * This is the model class for table "{{%coupon}}".
 *
 * @property int $id id
 * @property string $coupon_name 优惠券名称
 * @property int $coupon_type 优惠券类型 1购物优惠券
 * @property int $get_max 每人领取的张数
 * @property int $get_max_type 每人领取张数限制类型 0 不限制 1自定义限制
 * @property string $enough 使用金额门槛
 * @property string $discount_price 优惠价格 or 折扣额度，视type而定
 * @property int $coupon_sale_type 优惠券优惠类型 1立减 2打折
 * @property int $get_total 已领取数量
 * @property int $stock 发送数量
 * @property int $stock_type 发送数量限制 0无限制 1自定义
 * @property int $time_limit 时间限制类型  0为时间区间 1为有效天数
 * @property int $limit_day 有效天数 0为不限制
 * @property string $start_time 开始时间  只有在time_limit =0是有效
 * @property string $end_time 结束时间  只有在time_limit =0是有效
 * @property int $pick_type 领取类型 0领券中心 1快速领取链接
 * @property int $is_free 免费领取 1免费 0不免费
 * @property int $credit 消费积分领取
 * @property string $balance 消费余额领取
 * @property int $limit_member 是否限制领取会员等级 1限制 0不限制
 * @property int $coupon_sale_limit 优惠使用限制 0无限制  1不予会员折扣同享
 * @property int $goods_limit 商品使用限制 0无限制 1允许以下产品使用 2不允许一下产品使用 3允许以下分类使用
 * @property int $sort 排序
 * @property int $state 发放状态   0停止  1 发放
 * @property int $default_description 是否使用统一说明   1使用 2 自定义
 * @property string $description 说明
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CouponModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_type', 'get_max', 'get_max_type', 'coupon_sale_type', 'get_total', 'stock', 'stock_type', 'time_limit', 'limit_day', 'pick_type', 'is_free', 'credit', 'limit_member', 'coupon_sale_limit', 'goods_limit', 'sort', 'state', 'default_description'], 'integer'],
            [['enough', 'discount_price', 'balance'], 'number'],
            [['start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['description'], 'string'],
            [['coupon_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'coupon_name' => '优惠券名称',
            'coupon_type' => '优惠券类型 1购物优惠券',
            'get_max' => '每人领取的张数 ',
            'get_max_type' => '每人领取张数限制类型 0 不限制 1自定义限制',
            'enough' => '使用金额门槛',
            'discount_price' => '优惠价格 or 折扣额度，视type而定',
            'coupon_sale_type' => '优惠券优惠类型 1立减 2打折 ',
            'get_total' => '已领取数量',
            'stock' => '发送数量',
            'stock_type' => '发送数量限制 0无限制 1自定义',
            'time_limit' => '时间限制类型  0为时间区间 1为有效天数',
            'limit_day' => '有效天数 0为不限制',
            'start_time' => '开始时间  只有在time_limit =0是有效',
            'end_time' => '结束时间  只有在time_limit =0是有效',
            'pick_type' => '领取类型 0领券中心 1快速领取链接',
            'is_free' => '免费领取 1免费 0不免费',
            'credit' => '消费积分领取',
            'balance' => '消费余额领取',
            'limit_member' => '是否限制领取会员等级 1限制 0不限制',
            'coupon_sale_limit' => '优惠使用限制 0无限制  1不予会员折扣同享 ',
            'goods_limit' => '商品使用限制 0无限制 1允许以下产品使用 2不允许一下产品使用 3允许以下分类使用 ',
            'sort' => '排序',
            'state' => '发放状态   0停止  1 发放  ',
            'default_description' => '是否使用统一说明   1使用 2 自定义',
            'description' => '说明',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * LOG
     * @return string[]
     * @author 青岛开店星信息技术有限公司
     */
    public function logAttributeLabels()
    {
        return [
            'id' => 'id',
            'coupon_name' => '优惠券名称',
            'coupon_type' => '优惠券类型',
            'get_max' => '每人领取的张数 ',
            'get_max_type' => '每人领取张数限制类型',
            'enough' => '使用金额门槛',
            'discount_price' => '优惠',
            'coupon_sale_type' => '优惠券优惠类型',
            'get_total' => '已领取数量',
            'stock' => '发送数量',
            'stock_type' => '发送数量限制',
            'time_limit' => '时间限制类型',
            'limit_day' => '有效天数',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'limit_member_level' => '会员等级id',
            'limit_commission_level' => '分销等级id',
            'pick_type' => '领取类型',
            'is_free' => '免费领取',
            'credit' => '消费积分领取',
            'balance' => '消费余额领取',
            'limit_member' => '是否限制领取会员等级',
            'coupon_sale_limit' => '优惠使用限制',
            'goods_limit' => '商品使用限制',
            'sort' => '排序',
            'state' => '发放状态',
            'default_description' => '是否使用统一说明',
            'description' => '说明',
            'goods_ids' => '商品或分类id',
            'delete_type' => '删除类型',
            'content' => '优惠内容',
            'member_level_text' => '会员等级',
            'commission_level_text' => '分销等级',
            'goods_text' => '商品名称',
            'goods_cate_text' => '商品分类名称'
        ];
    }


    /**
     * 优惠券会员领取限制关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getRules()
    {
        return $this->hasMany(CouponRuleModel::class, ['coupon_id' => 'id']);
    }

    /**
     * 优惠券商品领取限制关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMap()
    {
        return $this->hasMany(CouponMapModel::class, ['coupon_id' => 'id']);
    }

    /**
     * 优惠券领取关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMemberCoupon()
    {
        return $this->hasMany(CouponMemberModel::class, ['coupon_id' => 'id']);
    }

    /**
     * 获取优惠券基本信息
     * @param array $couponIds
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCouponInfo(array $couponIds, array $options = [])
    {
        $query = CouponModel::find()
            ->select('id, coupon_name, coupon_sale_type, enough, discount_price, start_time, end_time, stock, stock_type, get_total')
            ->where(['id' => $couponIds]);
        if ($options['index'] == 1) {
            $query->indexBy('id');
        }

        $couponInfo = $query->get();
        if (is_array($couponInfo)) {
            foreach ($couponInfo as $key => $value) {
                if ($value['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                    $couponInfo[$key]['content'] = '满' . ValueHelper::delZero($value['enough']) . '减' . ValueHelper::delZero($value['discount_price']);
                } else {
                    // 打折类型
                    $couponInfo[$key]['content'] = '满' . ValueHelper::delZero($value['enough']) . '享' . ValueHelper::delZero($value['discount_price']) . '折';
                }

                // 剩余数量
                if ($value['stock_type'] == CouponConstant::COUPON_STOCK_TYPE_LIMIT) {
                    $couponInfo[$key]['surplus'] = $couponInfo[$key]['stock'] - $couponInfo[$key]['get_total'];
                }
            }
        }
        return $couponInfo;
    }

    /**
     * 活动发送优惠券
     * @param int $memberId 用户id
     * @param array $couponIds 优惠券id
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function activitySendCoupon(int $memberId, array $couponIds, $options = [])
    {
        $member = MemberModel::findOne(['id' => $memberId]);
        if (empty($member)) {
            return error('会员不存在');
        }
        $coupons = self::find()
            ->where([
                'pick_type' => CouponConstant::COUPON_PICK_TYPE_ACTIVITY,
            ])
            ->andWhere(['id' => $couponIds])
            ->all();
        if (empty($coupons)) {
            return error('优惠券不存在');
        }
        $insertId = [];
        $sendCouponId = [];
        foreach ($coupons as $coupon) {
            // 超库存
            if ($coupon->stock_type == 1 && ($coupon->stock - $coupon->get_total) < 1) {
                continue;
            }
            // 领取数量 + 1
            $coupon->get_total = $coupon->get_total + 1;
            if (!$coupon->save()) {
                continue;
            }
            // 时间限制 天数限制
            if ($coupon->time_limit == 1) {
                $startTime = DateTimeHelper::now();
                $endTime = DateTimeHelper::after(time(), $coupon->limit_day * 86400);
            } else {
                $startTime = $coupon->start_time;
                $endTime = $coupon->end_time;
            }
            // 插入数据
            $insertData = [
                'member_id' => $memberId,
                'coupon_id' => $coupon['id'],
                'coupon_sale_type' => $coupon['coupon_sale_type'],
                'title' => $coupon['coupon_name'],
                'discount_price' => $coupon['discount_price'],
                'enough' => $coupon['enough'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => DateTimeHelper::now(),
                'source' => '20',
                'coupon_sale_limit' => $coupon['coupon_sale_limit'],
                'goods_limit' => $coupon['goods_limit'],
            ];
            $insert = new CouponMemberModel();
            $insert->setAttributes($insertData);
            $insert->save();
            $insertId[] = $insert->id;
            $sendCouponId[] = $coupon['id'];
            //消息通知
            $messageData = [
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                'member_nickname' => $member->nickname,
                'coupon_send_status' => '成功',
                'coupon_send_time' => DateTimeHelper::now(),
                'coupon_type' => $coupon['coupon_sale_type'] == 1 ? '满减券' : '折扣券',
            ];

            $notice = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_COUPON_SEND, $messageData);
            if (!is_error($notice)) {
                $notice->sendMessage($memberId);
            }
        }
        if ($options['return_coupon_id']) {
            return $sendCouponId;
        }
        return $insertId;
    }
}
