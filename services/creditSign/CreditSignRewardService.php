<?php

namespace shopstar\services\creditSign;

use shopstar\constants\creditSign\CreditSignRewardRecordConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\creditSign\CreditSignException;
use shopstar\helpers\ArrayHelper;
use shopstar\models\creditSign\CreditSignRewardRecordModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\services\sale\CouponMemberService;
use shopstar\services\sale\CouponService;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * 积分签到奖励服务
 * Class CreditSignRewardService
 * @package shopstar\services\creditSign
 * @author yuning
 */
class CreditSignRewardService
{
    /**
     * 领取连续签到奖励
     * @param int $memberId
     * @param int $rewardId
     * @return array|bool
     * @author yuning
     */
    public static function receiveContinuityReward(int $memberId, int $rewardId)
    {
        // 查询奖励记录
        $rewardInfo = CreditSignRewardRecordModel::findOne([
            'member_id' => $memberId,
            'id' => $rewardId,
            'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_NO,
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
        ]);

        if (empty($rewardInfo)) {
            return error('奖励记录不存在');
        }

        $rewardArray = Json::decode($rewardInfo->content);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!empty($rewardArray['credit'])) {
                $sendCredit = self::sendCredit($memberId, $rewardArray['credit'], '签到连签奖励', MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_CONTINUITY);
                if (is_error($sendCredit)) {
                    throw new CreditSignException(CreditSignException::CREDIT_SIGN_SEND_INTEGRAL_ERROR, $sendCredit['message']);
                }
            }

            if (!empty($rewardArray['coupon'])) {
                $sendCoupon = self::sendRewardCoupon( $memberId, ArrayHelper::explode(',', $rewardArray['coupon']));

                if (is_error($sendCoupon)) {
                    throw new CreditSignException(CreditSignException::CREDIT_SIGN_SEND_COUPON_ERROR, $sendCoupon['message']);
                }

                // 更改优惠券数量
                $rewardInfo->coupon_num = $sendCoupon;
            }

            $rewardInfo->status = CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_YES;

            if (!$rewardInfo->save()) {
                throw new Exception('领取失败');
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return error($exception->getMessage());
        }

        return true;
    }

    /**
     * 发送积分奖励
     * @param int $memberId
     * @param $integral
     * @param string $remark
     * @param int $type
     * @return array|bool
     * @author yuning
     */
    public static function sendCredit(int $memberId, $integral, string $remark = '积分签到', int $type)
    {
        // 如果积分为0 跳出
        if ($integral <= 0) {
            return error('发送积分不可为空');
        }

        $member = MemberModel::updateCredit($memberId, $integral, 0, 'credit', 1, $remark, $type);

        if (is_error($member)) {
            return error($member['message']);
        }

        return true;
    }

    /**
     * 发送优惠券奖励
     * @param int $memberId
     * @param array $couponArray
     * @return array|int
     * @author yuning
     */
    public static function sendRewardCoupon(int $memberId, array $couponArray)
    {
        $couponNum = 0;

        foreach ($couponArray as $value) {
            // 检查优惠券 优惠券失效跳过领取下一张
            $checkReceive = CouponService::checkReceive( $memberId, $value);
            if (is_error($checkReceive)) {
                continue;
            }

            // 发送优惠券
            $rewardCoupon = CouponMemberService::sendCoupon($memberId, $checkReceive, 20,[]);
            if (is_error($rewardCoupon)) {
                return error($rewardCoupon['message']);
            }
            $couponNum++;
        }

        return $couponNum;
    }
}