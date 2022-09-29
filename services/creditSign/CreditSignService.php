<?php

namespace shopstar\services\creditSign;

use shopstar\constants\creditSign\CreditSignMemberTotalConstant;
use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\constants\creditSign\CreditSignRewardRecordConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\creditSign\CreditSignException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\creditSign\CreditSignMemberTotalModel;
use shopstar\models\creditSign\CreditSignRecordModel;
use shopstar\models\creditSign\CreditSignRewardRecordModel;
use shopstar\models\creditSign\CreditSignTotalModel;
use shopstar\models\member\MemberModel;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * 积分签到服务
 * Class CreditSignService
 * @package shopstar\services\creditSign
 * @author yuning
 */
class CreditSignService
{


    /**
     * @var int 会员ID
     */
    private int $memberId;

    /**
     * @var int 签到记录ID
     */
    private int $signRecordId = 0;

    /**
     * @var int|mixed 活动ID
     */
    private $activityId = 0;

    /**
     * @var array|mixed 当前活动的扩展字段
     */
    private $extField = [];

    /**
     * @var array 活动信息
     */
    private array $activityInfo = [];

    /**
     * @var int 奖励积分
     */
    private int $integral = 0;

    /**
     * @var false|string 签到时间
     */
    private $signTime;

    /**
     * @var int 累计签到天数
     */
    private int $signDays = 1;

    /**
     * @var int 连续签到天数
     */
    private int $continuityDays = 1;

    /**
     * @var int 递增签到天数
     */
    private int $increasingDays = 0;

    /**
     * @var int 最长累计签到天数
     */
    private int $longestDays = 1;

    /**
     * @var string 当前签到日期
     */
    private string $currentDate;

    /**
     * @var string 上一次签到日期
     */
    private string $lastDate;

    /**
     * @var array 用户签到统计数据（所有活动）
     */
    private array $memberTotal;

    /**
     * @var array 签到统计数据
     */
    private array $memberSignTotal = [];

    /**
     * @var array 会员签到记录
     */
    private array $memberSignRecord = [];

    /**
     * @var array 已获得的连续签到奖励数据(用于判断是否发送了奖励)
     */
    private array $memberContinuityInfo = [];

    /**
     * @var array 奖励记录数据
     */
    private array $rewardRecordData = [];

    /**
     * @var bool|mixed|string 是否补签
     */
    private $isSupplementarySign;

    public function __construct(int $memberId, array $options = [])
    {
        $date = DateTimeHelper::now(false);

        $options = array_merge([
            'isSupplementarySign' => false, // 是否补签
            'supplementarySignTime' => '', // 补签时间
        ], $options);

        // 处理初始数据
        $this->memberId = $memberId;
        $this->isSupplementarySign = $options['isSupplementarySign'];
//        $this->signTime = $this->isSupplementarySign ? $options['supplementarySignTime'] : $date;
        $this->signTime = !empty($options['supplementarySignTime']) ? $options['supplementarySignTime'] : $date;

        // 初始化数据
        $this->init();
    }

    /**
     * 初始化数据
     * @return void
     * @author yuning
     */
    private function init(): void
    {
        // 获取当前正在进行中的活动
        $activityInfo = CreditSignActivityService::getActivityOne();
        if (is_error($activityInfo)) {
            error($activityInfo['message']);
            return;
        }

        $this->activityId = $activityInfo['id'];
        $this->extField = $activityInfo['ext_field'];
        $this->activityInfo = $activityInfo;

        $this->memberSignTotal = CreditSignTotalModel::getActivityMemberTotal( $this->memberId, $this->activityId);
        $this->memberTotal = CreditSignMemberTotalModel::getMemberTotalInfo( $this->memberId);
        $this->memberContinuityInfo = CreditSignRewardRecordModel::find()->where([
            'member_id' => $this->memberId,
            'activity_id' => $this->activityId,
            'type' => CreditSignRewardRecordConstant::REWARD_RECORD_TYPE_CONTINUITY,
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
        ])->select([
            'id',
            'continuity_day',
            'status',
        ])->indexBy('continuity_day')->get();
    }

    /**
     * 获取签到记录(因补签计算连签天数原因 在记录添加后获取)
     * @author yuning
     */
    private function memberSignRecord()
    {
        $this->memberSignRecord = CreditSignRecordModel::find()->where([
                'member_id' => $this->memberId,
                'activity_id' => $this->activityId,
                'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
            ])->orderBy('sign_time desc')->get() ?? [];
    }

    /**
     * 签到方法
     * @return array|bool
     * @author yuning
     */
    public function sign()
    {
        // 检测重复签到
        $isExists = $this->checkExistsSignByTime();
        if ($isExists) {
            return error('请勿重复签到');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 新增签到记录
            $this->addSignRecord();

            $this->memberSignRecord();

            // 检测签到时间是否在活动时间内
            $this->checkDate();

            // 检测是否可补签 扣除积分
            if ($this->isSupplementarySign) {
                $this->checkSupplementarySign();

                $this->creditDeduction();
            }

            // 处理签到统计数据
            $this->calculateSignTotal();

            // 发送奖励
            $this->dayReward();
            // 补签不执行递增奖励
            if (!$this->isSupplementarySign) $this->increasingReward();
            // 连续签到发送奖励
            $this->continuityReward();

            // 保存奖励记录
            $this->addSignRewardRecord();

            // 保存统计记录
            $this->saveSignTotal();

            // 计算保存会员统计
            $this->calculateSaveMemberTotal();

            // 签到提醒入队列 (不是补签且是活动第一次签到入队列 活动结束队列自动结束)
            if (!$this->isSupplementarySign && empty($this->memberSignTotal)) {
                CreditSignSendNoticeService::sendNoticeQueue( $this->memberId, $this->activityId);
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return error($exception->getMessage(), $exception->getCode());
        }

        return true;
    }

    /**
     * 检测是否重复签到
     * @return bool
     * @author yuning
     */
    private function checkExistsSignByTime(): bool
    {
        // 签到时间是否签到
        $where = [
            'member_id' => $this->memberId,
            'activity_id' => $this->activityId,
            'sign_time' => $this->signTime,
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ];

        return CreditSignRecordModel::find()->where($where)->exists();
    }

    /**
     * 检测签到时间
     * @throws CreditSignException
     * @author yuning
     */
    private function checkDate()
    {
        if ($this->signTime < $this->activityInfo['start_time'] || $this->signTime > $this->activityInfo['end_time']) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SIGN_TIME_NOT_ACTIVITY_ERROR);
        }
    }

    /**
     * 检测补签次数
     * @throws CreditSignException
     * @author yuning
     */
    private function checkSupplementarySign()
    {
        // 判断补签是否开启 设置的补签次数是否小于0
        if ($this->extField['supplementary']['status'] == 0 || $this->extField['supplementary']['num'] <= 0) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SUPPLEMENTARY_STATUS_NOT_OPEN_ERROR);
        }

        // 计算会员补签次数 如果没有补签过直接往下走即可
        if (empty($this->memberSignRecord)) {
            return;
        }

        $supplementarySignRecord = [];
        foreach ($this->memberSignRecord as $item) {
            if ($item['status'] == CreditSignRecordConstant::RECORD_STATUS_SUPPLEMENTARY && $this->signTime != $item['sign_time']) {
                $supplementarySignRecord[] = $item;
            }
        }

        // 判断是否可以补签
        if ($this->extField['supplementary']['num'] <= count($supplementarySignRecord)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SUPPLEMENTARY_NUM_INSUFFICIENT_ERROR);
        }
    }

    /**
     * 积分扣除
     * @throws CreditSignException
     * @author yuning
     */
    private function creditDeduction()
    {
        if ($this->extField['supplementary']['consume'] <= 0) {
            return;
        }
        $member = MemberModel::updateCredit($this->memberId, $this->extField['supplementary']['consume'], 0, 'credit', 2,
            '积分签到', MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_CREDIT_CONSUME);
        if (is_error($member)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SUPPLEMENTARY_INTEGRAL_INSUFFICIENT_ERROR, $member['message']);
        }
    }

    /**
     * 添加签到记录
     * @throws CreditSignException
     * @author yuning
     */
    private function addSignRecord()
    {
        $model = new CreditSignRecordModel();

        $logData = [
            'activity_id' => $this->activityId,
            'member_id' => $this->memberId,
            'sign_time' => $this->signTime,
            'status' => $this->isSupplementarySign ? CreditSignRecordConstant::RECORD_STATUS_SUPPLEMENTARY : CreditSignRecordConstant::RECORD_STATUS_DAY,
            'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
        ];

        $model->setAttributes($logData);

        if (!$model->save()) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_ADD_RECORD_ERROR);
        }

        $this->signRecordId = $model->id;
    }

    /**
     * 处理签到数据
     * @author yuning
     */
    private function calculateSignTotal()
    {
        $this->currentDate = $this->signTime;

        // 签到统计数据为空 走默认数据
        if (empty($this->memberSignTotal)) {
            $this->lastDate = $this->signTime;
            return;
        }

        $this->longestDays = $this->memberSignTotal['longest_days'];
        // 累计签到不管是日签还是补签全部加一天
        $this->signDays = $this->memberSignTotal['sign_days'] + 1;

        // 补签计算方式
        if ($this->isSupplementarySign) {
            // 补签时不更新递增天数
            $this->increasingDays = $this->memberSignTotal['increasing_days'];

            $continuityDays = 1;

            for ($i = 0; $i < count($this->memberSignRecord); $i++) {
                if (DateTimeHelper::days($this->memberSignRecord[$i]['sign_time'], $this->memberSignRecord[$i + 1]['sign_time']) <= 1) {
                    $continuityDays++;
                } else {
                    break;
                }
            }

            $this->continuityDays = $continuityDays;
            $this->currentDate = $this->memberSignTotal['current_date'];
        }

        // 昨天是否签到 如果签到那么连续签到加一天
        if (DateTimeHelper::days($this->signTime, $this->memberSignTotal['current_date']) <= 1 && !$this->isSupplementarySign) {
            $this->continuityDays = $this->memberSignTotal['continuity_days'] + 1;

            // 维护递增天数
            if ($this->extField['increasing']['status'] == 1) {
                $this->increasingDays = $this->extField['increasing']['day'] < $this->memberSignTotal['increasing_days'] + 1 ? $this->extField['increasing']['day'] : $this->memberSignTotal['increasing_days'] + 1;
            }
        }

        // 判断更新最长签到时间
        if ($this->continuityDays >= $this->longestDays) {
            $this->longestDays = $this->continuityDays;
        }

        // 更新上次签到时间
        $this->lastDate = $this->memberSignTotal['current_date'];
    }

    /**
     * 日常签到奖励
     * @throws CreditSignException
     * @author yuning
     */
    private function dayReward()
    {
        $this->integral = $this->extField['day_reward'] ?? 0;
        $remark = '签到日签奖励';

        // 添加奖励记录
        $this->rewardRecordData[] = [
            'member_id' => $this->memberId,
            'activity_id' => $this->activityId,
            'sign_id' => $this->signRecordId,
            'type' => CreditSignRewardRecordConstant::REWARD_RECORD_TYPE_DAY,
            'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_YES,
            'continuity_day' => $this->continuityDays,
            'credit_num' => $this->integral,
            'coupon_num' => 0,
            'content' => Json::encode([
                'credit' => $this->integral,
            ]),
            'created_at' => DateTimeHelper::now(),
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
        ];

        // 执行发送
        $this->sendCredit($remark, MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_DAY);
    }

    /**
     * 发送积分奖励
     * @param string $remark
     * @param int $type
     * @throws CreditSignException
     * @author yuning
     */
    private function sendCredit(string $remark, int $type)
    {
        // 如果积分为0 跳出
        if ($this->integral <= 0) {
            return;
        }

        $sendCredit = CreditSignRewardService::sendCredit($this->memberId, $this->integral, $remark, $type);

        if (is_error($sendCredit)) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SEND_INTEGRAL_ERROR, $sendCredit['message']);
        }
    }

    /**
     * 递增签到奖励
     * @throws CreditSignException
     * @author miabowen
     */
    private function increasingReward()
    {
        // 判断递增签到是否开启 且昨天是否签到
        if ($this->extField['increasing']['status'] == 0) {
            return;
        }

        // 判断递增天数
        if ($this->increasingDays > $this->extField['increasing']['day']) {
            $this->increasingDays = $this->extField['increasing']['day'];
        }

        // 计算递增需要发送的积分
        $this->integral = bcmul($this->extField['increasing']['integral'], $this->increasingDays);
        $remark = '签到递增奖励';

        // 添加奖励记录
        $this->rewardRecordData[] = [
            'member_id' => $this->memberId,
            'activity_id' => $this->activityId,
            'sign_id' => $this->signRecordId,
            'type' => CreditSignRewardRecordConstant::REWARD_RECORD_TYPE_INCREASING,
            'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_YES,
            'continuity_day' => $this->continuityDays,
            'credit_num' => $this->integral,
            'coupon_num' => 0,
            'content' => Json::encode([
                'credit' => $this->integral,
            ]),
            'created_at' => DateTimeHelper::now(),
            'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
        ];

        // 执行发送
        $this->sendCredit($remark, MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_INCREASING);
    }

    /**
     * 连续签到奖励
     * @author yuning
     */
    private function continuityReward()
    {
        // 判断连续签到奖励是否开启
        if ($this->extField['continuity']['status'] == 0 || empty($this->extField['continuity']['info'])) {
            return;
        }

        // 判断是否可以获得奖励 加入奖励记录中
        foreach ($this->extField['continuity']['info'] as $item) {
            if ((($item['day'] - $this->continuityDays) > 0) || isset($this->memberContinuityInfo[$item['day']])) {
                continue;
            }

            $integral = 0;
            $couponNum = 0;
            $select = ArrayHelper::explode(',', $item['award']['select']) ?: [];
            if (in_array('credit', $select)) {
                $integral = $item['award']['credit'];
            }
            if (in_array('coupon', $select)) {
                $couponNum = count(ArrayHelper::explode(',', $item['award']['coupon']));
            }

            $this->rewardRecordData[] = [
                'member_id' => $this->memberId,
                'activity_id' => $this->activityId,
                'sign_id' => $this->signRecordId,
                'type' => CreditSignRewardRecordConstant::REWARD_RECORD_TYPE_CONTINUITY,
                'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_NO,
                'continuity_day' => $item['day'],
                'credit_num' => $integral,
                'coupon_num' => $couponNum,
                'content' => Json::encode([
                    'credit' => $item['award']['credit'],
                    'coupon' => $item['award']['coupon'],
                ]),
                'created_at' => DateTimeHelper::now(),
                'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
            ];
        }
    }

    /**
     * 保存奖励记录
     * @throws CreditSignException
     * @throws Exception
     * @author yuning
     */
    private function addSignRewardRecord()
    {
        $model = CreditSignRewardRecordModel::batchInsert(array_keys(current($this->rewardRecordData)), $this->rewardRecordData);
        if (!$model) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_ADD_REWARD_RECORD_ERROR);
        }
    }

    /**
     * 保存统计数据
     * @throws CreditSignException
     * @author yuning
     */
    private function saveSignTotal()
    {
        $totalData = [
            'activity_id' => $this->activityId,
            'sign_days' => $this->signDays,
            'continuity_days' => $this->continuityDays,
            'increasing_days' => $this->increasingDays,
            'longest_days' => $this->longestDays,
            'current_date' => $this->currentDate,
            'last_date' => $this->lastDate,
        ];

        // 处理更新统计数据
        $result = CreditSignTotalModel::saveTotal($this->memberId, $this->activityId, empty($this->memberSignTotal) ? 0 : $this->memberSignTotal['id'], $totalData);

        if (!$result) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SAVE_TOTAL_ERROR);
        }
    }

    /**
     * 保存用户统计数据
     * @throws CreditSignException
     * @author yuning
     */
    private function calculateSaveMemberTotal()
    {
        $data = [
            'first_date' => $this->signTime,
            'last_date' => $this->signTime,
            'sign_day' => 1,
            'continuity_day' => 1,
            'longest_day' => 1,
            'is_remind' => CreditSignMemberTotalConstant::IS_REMIND_NO,
        ];

        // 如果统计数据不为空 那么处理数据
        if (!empty($this->memberTotal)) {
            $data['first_date'] = $this->memberTotal['first_date'];
            $data['sign_day'] = $this->memberTotal['sign_day'] + 1;
            $data['last_date'] = $this->signTime;

            // 补签计算所有签到记录
            if ($this->isSupplementarySign) {
                $memberSignRecord = CreditSignRecordModel::find()->where([
                        'member_id' => $this->memberId,
                        'is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_NO,
                    ])->orderBy('sign_time desc')->get() ?? [];

                $continuityDay = 1;

                for ($i = 0; $i < count($memberSignRecord); $i++) {
                    if (DateTimeHelper::days($memberSignRecord[$i]['sign_time'], $memberSignRecord[$i + 1]['sign_time']) <= 1) {
                        $continuityDay++;
                    } else {
                        break;
                    }
                }

                $data['continuity_day'] = $continuityDay;
                $data['last_date'] = $this->memberTotal['last_date'];
            }

            // 昨天是否签到 如果签到那么连续签到加一天
            if (DateTimeHelper::days($this->signTime, $this->memberTotal['last_date']) <= 1 && !$this->isSupplementarySign) {
                $data['continuity_day'] = $this->memberTotal['continuity_day'] + 1;
            }

            if ($this->memberTotal['longest_day'] < $data['continuity_day']) {
                $data['longest_day'] = $data['continuity_day'];
            }

            $data['created_at'] = $this->memberTotal['created_at'];
            $data['is_remind'] = $this->memberTotal['is_remind'];
        }

        $result = CreditSignMemberTotalModel::saveMemberTotal($this->memberId, empty($this->memberTotal) ? 0 : $this->memberTotal['id'], $data);

        if (!$result) {
            throw new CreditSignException(CreditSignException::CREDIT_SIGN_SAVE_MEMBER_TOTAL_ERROR);
        }
    }
}