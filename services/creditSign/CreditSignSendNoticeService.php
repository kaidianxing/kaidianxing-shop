<?php

namespace shopstar\services\creditSign;

use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\creditSign\AutoCreditSignSendNoticeJob;
use shopstar\models\creditSign\CreditSignMemberTotalModel;
use shopstar\models\creditSign\CreditSignRecordModel;
use shopstar\models\member\MemberModel;

/**
 * 发送模板消息服务
 * Class CreditSignSendNoticeService
 * @package shopstar\services\creditSign
 * @author yuning
 */
class CreditSignSendNoticeService
{
    /**
     * 执行每日提醒
     * @param int $memberId
     * @param int $activityId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendNotice(int $memberId, int $activityId)
    {
        // 查询正在进行中的活动
        $activityInfo = CreditSignActivityService::getActivityOne();

        if (is_error($activityInfo)) {
            return error($activityInfo['message']);
        }

        // 判断活动id 防止活动删除后 在当天重新创建活动发送多条
        if ($activityInfo['id'] != $activityId) {
            return error('活动ID与正在进行中的不匹配');
        }

        $extField = $activityInfo['ext_field'];

        // 查询用户
        $memberInfo = CreditSignMemberTotalModel::find()->alias('total')
            ->leftJoin(MemberModel::tableName() . ' as member', 'member.id = total.member_id')
            ->where([
                'total.member_id' => $memberId,
            ])
            ->select([
                'total.member_id',
                'member.nickname',
                'total.is_remind',
                'total.continuity_day',
            ])
            ->first();

        if (empty($memberInfo)) {
            return error('未查询到需要发送的会员');
        }

        // 明天20:00继续推送
        self::sendNoticeQueue($memberId, $activityInfo['id']);

        // 判断会员今天是否签到
        $where = [
            'member_id' => $memberId,
            'activity_id' => $activityInfo['id'],
            'sign_time' => DateTimeHelper::now(false),
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ];
        $isExists = CreditSignRecordModel::find()->where($where)->exists();
        if ($isExists) {
            return error('今天已签到,无需提醒');
        }

        $messageData = [
            'activity_name' => '签到得好礼',
            'now_sign_day' => '等你来拿积分',
            'nickname' => $memberInfo['nickname'],
            'activity_time' => $activityInfo['start_time'] . '-' . $activityInfo['end_time'],
            'remark' => '快来签到',
            'day_num' => $memberInfo['sign_day'] . '天',
            'sign_reward' => $extField['day_reward'] . '积分',
            'sign_tips' => '连续签到有机会获得积分、优惠券',
            'wxapp_activity_name' => '签到得好礼',
            'wxapp_sign_day' => $memberInfo['continuity_day'],
        ];

        // 执行发送
        $result = NoticeComponent::getInstance(NoticeTypeConstant::CREDIT_SIGN_NOTICE, $messageData, 'creditSign');
        if (is_error($result)) {
            return error("会员ID：" . $memberInfo['member_id'] . "发送失败");
        }
        $result->sendMessage($memberInfo['member_id'], []);

        return true;
    }

    /**
     * 积分签到提醒入队列
     * @param int $memberId
     * @param int $activityId
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendNoticeQueue(int $memberId, int $activityId)
    {
        $delay = strtotime(date('Y-m-d 20:00:00', strtotime('+1 day'))) - time();
        QueueHelper::push(new AutoCreditSignSendNoticeJob([
            'memberId' => $memberId,
            'activityId' => $activityId,
        ]), $delay);
    }
}
