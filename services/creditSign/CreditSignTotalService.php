<?php

namespace shopstar\services\creditSign;

use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\constants\creditSign\CreditSignRewardRecordConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\creditSign\CreditSignRecordModel;
use shopstar\models\creditSign\CreditSignRewardRecordModel;
use shopstar\models\creditSign\CreditSignTotalModel;
use shopstar\models\sale\CouponModel;
use shopstar\services\sale\CouponService;

/**
 * 积分签到统计数据服务
 * Class CreditSignTotalService
 * @package shopstar\services\creditSign
 * @author yuning
 */
class CreditSignTotalService
{
    /**
     * 计算可得积分(今天签到了就计算明天可得 今天没有签到就计算今天可得) 和 今天签到状态
     * @param int $memberId
     * @param int $activityId
     * @return int[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculateTodayAndTomorrowIntegral(int $memberId, int $activityId): array
    {
        // 获取活动详情
        $activityInfo = CreditSignActivityService::getActivityDetail( $activityId);
        if (is_error($activityInfo)) {
            return error($activityInfo['message']);
        }
        $extField = $activityInfo['ext_field'];
        $date = DateTimeHelper::now(false);

        $integral = $extField['day_reward'] ?: 0; // 今日可得积分(含递增)
        $continuityStatus = false; // 是否是连续签到奖励
        // 当天是否签到
        $isSign = CreditSignRecordModel::find()->where([
            'member_id' => $memberId,
            'activity_id' => $activityId,
            'sign_time' => $date,
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ])->exists();

        // 获取用户在活动中的统计数据
        $totalInfo = CreditSignTotalModel::getActivityMemberTotal( $memberId, $activityId, 'id, increasing_days, continuity_days');

        // 增加递增奖励积分
        if ($extField['increasing']['status'] == 1) {
            // 判断递增天数
            $increasingDays = $isSign ? ($totalInfo['increasing_days'] + 1 > $extField['increasing']['day'] ? $extField['increasing']['day'] : $totalInfo['increasing_days'] + 1) : $totalInfo['increasing_days'];

            // 递增可得积分
            $increasingIntegral = bcmul($extField['increasing']['integral'], $increasingDays);

            // 将递增积分与每日积分相加
            $integral = bcadd($integral, $increasingIntegral);
        }

        // 判断今天或明天 是否可以获得连续签到奖励
        if ($extField['continuity']['status'] == 1) {
            $continuityDays = $totalInfo['continuity_days'];
            // 判断奖励是否为空
            if (!empty($extField['continuity']['info'])) {
                foreach ($extField['continuity']['info'] as $item) {
                    if ((($item['day'] - $continuityDays) == 1 && $isSign) || (($item['day'] - $continuityDays) == 0 && !$isSign)) {
                        $continuityStatus = true;
                    }
                }
            }
        }

        return [$integral, $isSign, $continuityStatus];
    }

    /**
     * 连续签到奖励数据
     * @param int $memberId
     * @param int $activityId
     * @return array|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getContinuityInfo(int $memberId, int $activityId)
    {
        // 获取活动详情
        $activityInfo = CreditSignActivityService::getActivityDetail( $activityId);
        if (is_error($activityInfo)) {
            return error($activityInfo['message']);
        }
        $extField = $activityInfo['ext_field'];

        // 获取连续签到奖励数据
        $continuityRecord = CreditSignRewardRecordModel::find()->where([
            'member_id' => $memberId,
            'activity_id' => $activityId,
            'type' => CreditSignRewardRecordConstant::REWARD_RECORD_TYPE_CONTINUITY,
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
        ])->select([
            'id',
            'continuity_day',
            'status',
        ])->indexBy('continuity_day')->get();

        // 获取用户在活动中的统计数据
        $totalInfo = CreditSignTotalModel::getActivityMemberTotal( $memberId, $activityId, 'id, continuity_days');

        // 连续签到奖励数据
        $continuityInfo = [];
        if ($extField['continuity']['status'] == 1) {
            $continuityInfo = $extField['continuity']['info'];

            if (!empty($continuityInfo)) {
                foreach ($continuityInfo as &$item) {

                    // 处理奖励数据 前端展示用
                    $select = ArrayHelper::explode(',', $item['award']['select']);
                    if (in_array('credit', $select)) {
                        $item['reward_list'][] = [
                            'type' => 'credit',
                            'info' => $item['award']['credit'],
                            'status' => true
                        ];
                    }
                    if (in_array('coupon', $select)) {
                        foreach ($item['coupon_info'] as $value) {
                            $checkCoupon = CouponService::checkReceive( $memberId, $value['id']);
                            $status = true;
                            if(is_error($checkCoupon)){
                                $status = false;
                            }
                            $item['reward_list'][] = [
                                'type' => 'coupon',
                                'info' => $value,
                                'status' => $status
                            ];
                        }
                    }
                    unset($item['coupon_info']);

                    // 判断统计中是否有奖励
                    if (!isset($continuityRecord[$item['day']])) {
                        $item['is_receive_award'] = false;
                        $item['id'] = 0;
                        $item['status'] = CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_NO;
                        $item['several_days'] = (int)bcsub($item['day'], $totalInfo['continuity_days']); // 在签多少天可得
                        continue;
                    }

                    $item['is_receive_award'] = true;
                    $item['status'] = $continuityRecord[$item['day']]['status'];
                    $item['id'] = $continuityRecord[$item['day']]['id'];
                    $item['several_days'] = 0;
                }
                unset($item);
            }
        }

        return $continuityInfo;
    }
}
