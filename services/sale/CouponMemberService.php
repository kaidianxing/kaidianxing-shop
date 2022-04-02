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

namespace shopstar\services\sale;

use shopstar\bases\service\BaseService;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\sale\CouponUseOrderMapModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CouponMemberService extends BaseService
{
    /**
     * 发送优惠券
     * @param int $memberId
     * @param array $coupon
     * @param int $source
     * @param array $options
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendCoupon(int $memberId, array $coupon, int $source = 0, array $options = [])
    {
        $options = array_merge([
            'get_id' => false,
            'not_add_get_num' => false
        ], $options);

        $couponMemberId = 0;

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $model = new CouponMemberModel();
            $model->member_id = $memberId;
            $model->coupon_id = $coupon['id'];
            $model->coupon_sale_type = $coupon['coupon_sale_type'];
            $model->title = $coupon['coupon_name'];
            $model->discount_price = $coupon['discount_price'];
            $model->enough = $coupon['enough'];
            if ($coupon['time_limit'] == 0) {
                $model->end_time = $coupon['end_time'];
                $model->start_time = $coupon['start_time'];
            } else {
                $model->start_time = DateTimeHelper::now();
                if ($coupon['limit_day'] != 0) {
                    $model->end_time = date('Y-m-d H:i:s', strtotime('+' . $coupon['limit_day'] . 'days'));
                }
            }
            $model->created_at = DateTimeHelper::now();
            $model->source = $source;
            $model->coupon_sale_limit = $coupon['coupon_sale_limit'];
            $model->goods_limit = $coupon['goods_limit'];
            $model->save();

            $couponMemberId = $model->id;

            //每次领取 领取数+1
            !$options['not_add_get_num'] && CouponModel::updateAllCounters(['get_total' => 1], ['id' => $coupon['id']]);

            $transaction->commit();

            $member = MemberModel::findOne($memberId);

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
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return error($exception->getMessage(), $exception->getCode());
        }

        return $options['get_id'] ? $couponMemberId : true;
    }


    /**
     * 下单使用优惠券
     * @param array $orderData
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function useCoupon(array $orderData)
    {
        if (StringHelper::isJson($orderData['extra_discount_rules_package'])) {
            $orderData['extra_discount_rules_package'] = Json::decode($orderData['extra_discount_rules_package']);
        }
        // 使用  一对多  平台优惠券可能插入多条

        // 先更新使用状态
        foreach ((array)$orderData['extra_discount_rules_package'] as $item) {
            // 优惠券
            $memberCouponIds = [];
            // 使用map
            $insertUseData = [];
            // 非平台使用优惠券
            if (!empty($item['coupon'])) {
                $memberCouponIds[] = $item['coupon']['id'];
                // 插入map
                $insertUseData[] = [
                    $orderData['id'],
                    $orderData['order_no'],
                    $item['coupon']['id'],
                ];
            }
            // 使用平台优惠券
            if (!empty($item['platform_coupon'])) {
                $memberCouponIds[] = $item['platform_coupon']['id'];
                // 插入map
                $insertUseData[] = [
                    $orderData['id'],
                    $orderData['order_no'],
                    $item['platform_coupon']['id'],
                ];
            }
            // 更新使用状态
            CouponMemberModel::updateAll([
                'used_time' => DateTimeHelper::now(),
                'status' => 1,
            ], ['id' => $memberCouponIds]);

            // 批量插入map
            $fields = ['order_id', 'order_no', 'coupon_member_id'];
            CouponUseOrderMapModel::batchInsert($fields, $insertUseData);
        }
    }


    /**
     * 返还优惠券
     * @param int $couponId
     * @param int $memberId
     * @param int $orderId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function returnCoupon(int $couponId, int $memberId, int $orderId)
    {
        // 如果 查找订单那id对应的优惠券  有id  先查找coupon_member 表  如果没值  先删除当前订单的 统计count条数  如果大于0 则不返还 =0 就返还
        // 查找优惠券
        $couponMember = CouponMemberModel::findOne(['id' => $couponId, 'member_id' => $memberId]);
        if (empty($couponMember)) {
            return error('优惠券不存在');
        }
        // 如果order_id 有值 直接退
        if ($couponMember->order_id != 0) {
            $couponMember->order_id = 0;
            $couponMember->save();
            return true;
        }
        // 删除map 表
        CouponUseOrderMapModel::deleteAll(['order_id' => $orderId, 'coupon_member_id' => $couponId]);

        // 获取还有没有订单用这个优惠券
        $isExists = CouponUseOrderMapModel::find()->where(['order_id' => $orderId, 'coupon_member_id' => $couponId])->exists();
        if ($isExists) {
            return true;
        }
        // 返还优惠券
        $couponMember->status = 0;
        $couponMember->save();

        return true;
    }


}