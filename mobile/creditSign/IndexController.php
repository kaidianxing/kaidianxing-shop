<?php

namespace shopstar\mobile\creditSign;

use shopstar\bases\controller\BaseCreditSignMobileApiController;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\exceptions\creditSign\CreditSignException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\creditSign\CreditSignMemberTotalModel;
use shopstar\models\creditSign\CreditSignRecordModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\creditSign\CreditSignActivityService;
use shopstar\services\creditSign\CreditSignRewardService;
use shopstar\services\creditSign\CreditSignService;
use shopstar\services\creditSign\CreditSignTotalService;
use yii\web\Response;

/**
 * 积分签到移动端控制器
 * Class IndexController
 * @package shopstar\mobile\creditSign
 * @author yuning
 */
class IndexController extends BaseCreditSignMobileApiController
{

    /**
     * 获取用户签到详情与活动详情
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionMemberSignDetail()
    {
        // 获取正在进行中的数据
        $activityInfo = CreditSignActivityService::getActivityOne();
        if (is_error($activityInfo)) {
            return $this->error($activityInfo['message']);
        }

        // 计算可得积分(含递增积分) 与今天签到状态
        [$integral, $isSign, $continuityStatus] = CreditSignTotalService::calculateTodayAndTomorrowIntegral($this->memberId, $activityInfo['id']);

        $memberTotalInfo = CreditSignMemberTotalModel::find()->where([
            'member_id' => $this->memberId,
        ])->select('is_remind')->first();

        // 判断是否开启消息通知,前端判断用
        $noticeSettings = ShopSettings::get('plugin_notice.send.' . NoticeTypeConstant::CREDIT_SIGN_NOTICE);
        $noticeStatus = false;
        if ($noticeSettings['sms']['status'] == 1 || $noticeSettings['wechat']['status'] == 1) {
            $noticeStatus = true;
        }

        // 返回数据
        $result = [
            'activity' => $activityInfo, // 获得详情
            'integral' => $integral, // 今日或明日可得积分
            'is_sign' => $isSign, // 今日是否签到
            'continuity_status' => $continuityStatus, // 是否展示大礼包
            'is_remind' => $memberTotalInfo['is_remind'], // 是否开启提醒
            'notice_status' => $noticeStatus,
        ];

        return $this->result(['data' => $result]);
    }

    /**
     * 获取用户签到记录
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionList()
    {
        [$startDate, $endDate] = DateTimeHelper::getMonthDate(false);
        $startTime = RequestHelper::get('start_time', $startDate);
        $endTime = RequestHelper::get('end_time', $endDate);

        $params = [
            'where' => [
                'member_id' => $this->memberId,
                'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
            ],
            'andWhere' => [
                ['>=', 'sign_time', $startTime],
                ['<=', 'sign_time', $endTime],
            ],
            'select' => [
                'sign_time',
            ],
            'orderBy' => [
                'sign_time' => SORT_DESC,
            ],
        ];

        $list = CreditSignRecordModel::getColl($params, [
            'pager' => false,
            'onlyList' => true,
        ]);

        return $this->result(['list' => array_column($list, 'sign_time')]);
    }

    /**
     * 获取活动连续签到奖励已用户是否可领取状态
     * @return array|int[]|Response
     * @throws CreditSignException
     * @author yuning
     */
    public function actionCalculateRewards()
    {
        $activityId = RequestHelper::getInt('id', 0);

        if (empty($activityId)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SIGN_ACTIVITY_ID_NOT_EMPTY_ERROR);
        }

        // 连续签到奖励数据
        $continuityInfo = CreditSignTotalService::getContinuityInfo($this->memberId, $activityId);

        return $this->result([
            'data' => $continuityInfo,
        ]);
    }

    /**
     * 签到
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionSign()
    {
        $signService = new CreditSignService($this->memberId);

        $result = $signService->sign();

        if (is_error($result)) {
            return $this->error($result['message'], empty($result['error']) ? -1 : $result['error']);
        }

        return $this->success();
    }

    /**
     * 获取用户是否可补签以及补签次数
     * @return array|int[]|Response
     * @throws CreditSignException
     * @author yuning
     */
    public function actionGetSupplementCount()
    {
        $activityId = RequestHelper::getInt('id', 0);

        if (empty($activityId)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SIGN_ACTIVITY_ID_NOT_EMPTY_ERROR);
        }

        // 获取用户补签次数
        $memberSupplementaryCount = CreditSignRecordModel::find()->where([
            'member_id' => $this->memberId,
            'activity_id' => $activityId,
            'status' => CreditSignRecordConstant::RECORD_STATUS_SUPPLEMENTARY,
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ])->count();

        // 获取活动详情
        $activityInfo = CreditSignActivityService::getActivityDetail($activityId);
        if (is_error($activityInfo)) {
            return error($activityInfo['message']);
        }
        $extField = $activityInfo['ext_field'];

        // 判断是否可补签
        if ($extField['supplementary']['num'] <= $memberSupplementaryCount) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SUPPLEMENTARY_NUM_INSUFFICIENT_ERROR);
        }

        // 用户补签数据
        $supplementary = [
            'num' => $extField['supplementary']['num'] - $memberSupplementaryCount,
            'consume' => $extField['supplementary']['consume'],
        ];

        return $this->result([
            'data' => $supplementary
        ]);
    }

    /**
     * 补签
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionSupplementSign()
    {
        $time = RequestHelper::post('time');

        $signService = new CreditSignService($this->memberId, [
            'isSupplementarySign' => true,
            'supplementarySignTime' => $time,
        ]);

        $result = $signService->sign();

        if (is_error($result)) {
            return $this->error($result['message'], empty($result['error']) ? -1 : $result['error']);
        }

        return $this->success();
    }

    /**
     * 领取奖励
     * @return array|int[]|Response
     * @throws CreditSignException
     * @author yuning
     */
    public function actionReceiveReward()
    {
        $rewardId = RequestHelper::postInt('id', 0);

        if (empty($rewardId)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SIGN_REWARD_ID_NOT_EMPTY_ERROR);
        }

        // 领取奖励
        $result = CreditSignRewardService::receiveContinuityReward($this->memberId, $rewardId);
        if (is_error($result)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_RECEIVE_REWARD_ERROR, $result['message']);
        }

        return $this->success();
    }

    /**
     * 开启/关闭 用户每日消息提醒
     * @return array|int[]|Response
     * @throws CreditSignException
     * @author yuning
     */
    public function actionSetDailyReminder()
    {
        $remindStatus = RequestHelper::postInt('remind_status', 0);

        $memberTotalInfo = CreditSignMemberTotalModel::findOne([
            'member_id' => $this->memberId,
        ]);

        if (empty($memberTotalInfo)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_ERROR);
        }

        $memberTotalInfo->is_remind = $remindStatus;

        if (!$memberTotalInfo->save()) {
            return $this->error($memberTotalInfo->getErrorMessage());
        }

        return $this->success();
    }
}