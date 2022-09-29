<?php

namespace shopstar\admin\creditSign;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\creditSign\CreditSignActivityConstant;
use shopstar\constants\creditSign\CreditSignLogConstant;
use shopstar\constants\creditSign\CreditSignRecordConstant;
use shopstar\constants\creditSign\CreditSignRewardRecordConstant;
use shopstar\exceptions\creditSign\CreditSignActivityException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\creditSign\AutoCreditSignStartActivityJob;
use shopstar\jobs\creditSign\AutoCreditSignStopActivityJob;
use shopstar\models\creditSign\CreditSignMemberTotalModel;
use shopstar\models\creditSign\CreditSignModel;
use shopstar\models\creditSign\CreditSignRecordModel;
use shopstar\models\creditSign\CreditSignRewardRecordModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\services\creditSign\CreditSignActivityService;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 积分签到控制器
 * Class ListController
 * @package shopstar\admin\creditSign
 * @author yuning
 */
class ListController extends KdxAdminApiController
{
    /**
     * 签到活动列表
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionList()
    {
        $result = CreditSignActivityService::getList();

        return $this->result($result);
    }

    /**
     * 获取领取记录
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionTotalList()
    {
        $params = [
            'alias' => 'total',
            'searchs' => [
                ['member.nickname', 'like', 'nickname'],
            ],
            'leftJoins' => [
                [MemberModel::tableName() . ' as member', 'member.id=total.member_id'],
            ],
            'where' => [
            ],
            'select' => [
                'total.id',
                'total.member_id',
                'total.first_date',
                'total.last_date',
                'total.sign_day',
                'total.longest_day',
                'member.avatar',
                'member.nickname',
            ],
            'orderBy' => [
                'total.created_at' => SORT_DESC,
            ],
        ];

        $result = CreditSignMemberTotalModel::getColl($params, [
            'callable' => function (&$row) {
                $rewardRecord = CreditSignRewardRecordModel::find()->where([
                    'member_id' => $row['member_id'],
                    'is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_NO,
                    'status' => CreditSignRewardRecordConstant::REWARD_RECORD_STATUS_RECEIVE_YES,
                ])->select([
                    'credit_num',
                    'coupon_num',
                ])->get();

                $row['credit_num'] = 0;
                $row['coupon_num'] = 0;
                // 处理积分/优惠券发放记录
                if (!empty($rewardRecord)) {
                    $row['credit_num'] = array_sum(array_column($rewardRecord, 'credit_num'));
                    $row['coupon_num'] = array_sum(array_column($rewardRecord, 'coupon_num'));
                }
            },
        ]);

        return $this->result($result);
    }

    /**
     * 获取活动详情
     * @return array|int[]|Response
     * @throws CreditSignActivityException
     * @author yuning
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');

        if (empty($id)) {
            throw new CreditSignActivityException(CreditSignActivityException::PARAMETER_NOT_FOUND);
        }

        $info = CreditSignActivityService::getActivityDetail($id);
        if (is_error($info)) {
            return $this->error($info['message']);
        }

        return $this->result($info);
    }

    /**
     * 新增签到活动
     * @return array|int[]|Response
     * @throws CreditSignActivityException
     * @author yuning
     */
    public function actionAdd()
    {
        $result = CreditSignActivityService::addResult($this->userId);

        if (is_error($result)) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_ADD_ACTIVITY_FAIL, $result['message']);
        }

        return $this->success();
    }

    /**
     * 修改签到任务
     * @return array|int[]|Response
     * @throws CreditSignActivityException
     * @author yuning
     */
    public function actionEdit()
    {
        $id = RequestHelper::postInt('id', 0);
        $startTime = RequestHelper::post('start_time');
        $endTime = RequestHelper::post('end_time');
        $data = RequestHelper::post();

        // 查找任务
        $detail = CreditSignModel::findOne(['id' => $id]);

        // 判断是否为空
        if (empty($detail)) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_NOT_EXISTS);
        }

        // 已停止的任务不能修改
        if ($detail->end_time < DateTimeHelper::now() || $detail->status == CreditSignActivityConstant::STATUS_MANUAL_END || $detail->status == CreditSignActivityConstant::STATUS_MANUAL_STOP) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_IS_STOP);
        }

        // 不能往前修改
        if ($endTime < DateTimeHelper::now()) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_TIME_ERROR);
        }

        // 查找其他任务
        $isExists = CreditSignActivityService::checkExistsByTime($detail->start_time, $endTime, $id);
        if ($isExists) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_TIME_IS_EXISTS);
        }

        // 活动参数校验
        $checkParams = CreditSignActivityService::saveCheckParams($data);
        if (is_error($checkParams)) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_PARAMS_ERROR, $checkParams['message']);
        }

        // 可以修改
        if ($detail->status == CreditSignActivityConstant::STATUS_WAIT) {
            $detail->start_time = $startTime;
            // 如果活动开始时间未到 添加队列
            if (strtotime($startTime) > time()) {
                // 添加定时开启任务
                QueueHelper::push(new AutoCreditSignStartActivityJob([
                    'id' => $detail->id,
                ]), strtotime($startTime) - time());
            } else {
                // 开始时间已经等于或小于当前时间就变为进行中
                $detail->status = CreditSignActivityConstant::STATUS_UNDERWAY;
            }
        }
        $detail->end_time = $endTime;
        $detail->ext_field = $data['ext_field'];

        // 添加新任务
        $delay = strtotime($endTime) - time();
        $jobId = QueueHelper::push(new AutoCreditSignStopActivityJob([
            'id' => $id
        ]), $delay);

        // 旧任务id
        $oldJobId = $detail->job_id;
        // 新任务id
        $detail->job_id = $jobId;

        if (!$detail->save()) {
            QueueHelper::remove($jobId);
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_EDIT_ACTIVITY_FAIL);
        }
        // 删除旧任务
        QueueHelper::remove($oldJobId);

        // 日志
        $clientType = explode(',', $detail->client_type);
        $client_type = in_array('20', $clientType) ? '微信公众号,' : '';
        $client_type .= in_array('21', $clientType) ? '微信小程序,' : '';
        $client_type .= in_array('10', $clientType) ? '手机浏览器H5,' : '';
        $client_type .= in_array('30', $clientType) ? '头条/抖音小程序,' : '';

        $extField = Json::decode($detail->ext_field);

        $logPrimary = [
            '活动名称' => $detail->activity_name,
            '活动期限' => '起:' . $detail->start_time . ',止:' . $detail->end_time,
            '活动渠道' => trim($client_type),
            '日签奖励' => $extField['day_reward'],
        ];

        if ($extField['increasing']['status'] == 1) {
            $logPrimary['递增奖励'] = '开启,第二天起递增奖励' . $extField['increasing']['integral'] . '积分且' . $extField['increasing']['day'] . '天不再递增';
        } else {
            $logPrimary['递增奖励'] = '关闭';
        }

        if ($extField['continuity']['status'] == 1) {
            $continuityLog = '';
            foreach ($extField['continuity']['info'] as $item) {
                $continuityLog .= '连签' . $item['day'] . '天赠送:';
                $select = ArrayHelper::explode(',', $item['award']['select']) ?: [];
                if (in_array('credit', $select)) {
                    $continuityLog .= "积分:" . $item['award']['credit'] . "个,";
                }
                if (in_array('coupon', $select)) {
                    if (empty($item['award']['coupon'])) {
                        $item['coupon_info'] = [];
                        continue;
                    }

                    $couponArray = explode(',', $item['award']['coupon']);
                    $couponInfo = CouponModel::getCouponInfo($couponArray);
                    $couponLog = '';
                    foreach ($couponInfo as $value) {
                        $couponLog .= $value['coupon_name'] . ',';
                    }
                    $continuityLog .= '优惠券' . count($couponInfo) . '张:(' . $couponLog . ')';
                }
            }

            $logPrimary['连签奖励'] = '开启,' . trim($continuityLog);
        } else {
            $logPrimary['连签奖励'] = '关闭';
        }

        if ($extField['supplementary']['status'] == 1) {
            $logPrimary['补签设置'] = '开启,补签次数' . $extField['supplementary']['num'] . '次,每次消耗' . $extField['supplementary']['consume'] . '积分';
        } else {
            $logPrimary['补签设置'] = '关闭';
        }
        $logPrimary['规则说明'] = $extField['rule_description']['rule'];
        $logPrimary['页面名称'] = $extField['page_setup']['link_name'];
        if ($extField['page_setup']['activity_link'] == 1) {
            $logPrimary['活动链接'] = '开启,活动标题:' . $extField['page_setup']['activity_title'] . ';入口文案:' . $extField['page_setup']['content'] . ';跳转链接:' . $extField['page_setup']['link_url'];
        } else {
            $logPrimary['活动链接'] = '关闭';
        }

        LogModel::write(
            $this->userId,
            CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_EDIT_LOG,
            CreditSignLogConstant::getText(CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_EDIT_LOG),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_ADD_LOG,
                    CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_EDIT_LOG,
                ],
            ]
        );

        return $this->success();
    }

    /**
     * 删除签到活动
     * @return array|int[]|Response
     * @throws CreditSignActivityException
     * @author yuning
     */
    public function actionDelete()
    {
        $id = RequestHelper::postInt('id');

        if (empty($id)) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_DELETE_ACTIVITY_PARAMS_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            $detail = CreditSignModel::findOne(['id' => $id]);

            // 查找任务

            // 判断是否为空
            if (empty($detail)) {
                throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_DELETE_ACTIVITY_REWARD_NOT_EXISTS);
            }

            $detail->is_deleted = CreditSignActivityConstant::IS_DELETE_YES;

            if (!$detail->save()) {
                throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_DELETE_ACTIVITY_FAIL);
            }
            // 删除队列任务
            QueueHelper::remove($detail->job_id);
            // 清空记录
            CreditSignRewardRecordModel::updateAll(['is_deleted' => CreditSignRewardRecordConstant::REWARD_RECORD_IS_DELETE_YES], [ 'activity_id' => $detail->id]);
            CreditSignRecordModel::updateAll(['is_deleted' => CreditSignRecordConstant::RECORD_IS_DELETE_YES], [ 'activity_id' => $detail->id]);

            // 日志
            LogModel::write(
                $this->userId,
                CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_DELETED_LOG,
                CreditSignLogConstant::getText(CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_DELETED_LOG),
                $detail->id,
                [
                    'log_data' => $detail->attributes,
                    'log_primary' => [
                        '活动名称' => $detail->activity_name,
                    ],
                ]
            );

            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->success();
    }

    /**
     * 手动停止活动
     * @return array|int[]|Response
     * @throws CreditSignActivityException
     * @author yuning
     */
    public function actionStop()
    {
        $id = RequestHelper::postInt('id');

        $detail = CreditSignModel::findOne([ 'id' => $id]);
        if (empty($detail)) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_MANUAL_STOP_ACTIVITY_NOT_EXISTS);
        }

        // 活动状态错误
        if (!in_array($detail->status, [CreditSignActivityConstant::STATUS_UNDERWAY, CreditSignActivityConstant::STATUS_WAIT])) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_MANUAL_STOP_ACTIVITY_STATUS_ERROR);
        }
        $detail->status = -2;
        $detail->stop_time = DateTimeHelper::now();

        if (!$detail->save()) {
            throw new CreditSignActivityException(CreditSignActivityException::CREDIT_SIGN_MANUAL_STOP_ACTIVITY_FAIL);
        }
        // 删除队列任务
        QueueHelper::remove($detail->job_id);

        // 日志
        LogModel::write(
            $this->userId,
            CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_STOP_LOG,
            CreditSignLogConstant::getText(CreditSignLogConstant::CREDIT_SIGN_ACTIVITY_STOP_LOG),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => [
                    '活动名称' => $detail->activity_name,
                ],
            ]
        );

        return $this->success();
    }
}