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

namespace shopstar\admin\rechargeReward;

use  shopstar\jobs\rechargeReward\AutoStopRechargeRewardJob;
use shopstar\bases\KdxAdminApiController;
use shopstar\constants\rechargeReward\RechargeRewardConstant;
use shopstar\constants\rechargeReward\RechargeRewardLogConstant;
use shopstar\exceptions\rechargeReward\RechargeRewardException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\rechargeReward\RechargeRewardActivityModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * 充值奖励
 * Class IndexController
 * @package apps\rechargeReward\manage
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $status = RequestHelper::get('status');
        $andWhere = [];
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = [
                'or',
                [
                    'and',
                    ['>', 'start_time', $startTime],
                    ['<', 'start_time', $endTime],
                    ['>', 'end_time', $startTime],
                    ['>', 'end_time', $endTime],

                ],
                [
                    'and',
                    ['>', 'start_time', $startTime],
                    ['<', 'start_time', $endTime],
                    ['>', 'end_time', $startTime],
                    ['<', 'end_time', $endTime],
                ],
                [
                    'and',
                    ['<', 'start_time', $startTime],
                    ['<', 'start_time', $endTime],
                    ['>', 'end_time', $startTime],
                    ['>', 'end_time', $endTime],
                ],
                [
                    'and',
                    ['<', 'start_time', $startTime],
                    ['<', 'start_time', $endTime],
                    ['>', 'end_time', $startTime],
                    ['<', 'end_time', $endTime],
                ]
            ];
        }

        // 活动状态
        switch ($status) {
            case '1': // 活动中
                $andWhere[] = [
                    'and',
                    ['status' => 0],
                    ['<', 'start_time', DateTimeHelper::now()],
                    ['>', 'end_time', DateTimeHelper::now()],
                ];
                break;
            case '0': // 未开始
                $andWhere[] = [
                    'and',
                    ['status' => 0],
                    ['>', 'start_time', DateTimeHelper::now()],
                ];
                break;
            case '-1': // 已停止
                $andWhere[] = ['status' => -1];
                break;
            case '-2': // 手动停止
                $andWhere[] = ['status' => -2];
                break;
            default: // 全部
                break;
        }

        $params = [
            'searchs' => [
                ['title', 'like', 'keyword'],
                ['type', 'int', 'type']
            ],
            'select' => [
                'id',
                'title',
                'start_time',
                'end_time',
                'stop_time',
                'type',
                'send_count',
                'status',
                'if(stop_time=0, 1, 2) as level '
            ],
            'where' => [
                'is_deleted' => 0,
            ],
            'andWhere' => $andWhere,
            'orderBy' => [
                'level' => SORT_ASC,
                'stop_time' => SORT_DESC,
                'status' => SORT_DESC,
                'id' => SORT_DESC,
            ]
        ];

        $list = RechargeRewardActivityModel::getColl($params, [
            'callable' => function (&$row) {
                if ($row['status'] == 0 && $row['start_time'] < DateTimeHelper::now() && $row['end_time'] > DateTimeHelper::now()) {
                    $row['status'] = '1';
                } else if ($row['status'] == 0 && $row['end_time'] < DateTimeHelper::now()) {
                    $row['status'] = '-1';
                }
                if ($row['stop_time'] == 0) {
                    $row['stop_time'] = '-';
                }
            }
        ]);
        return $this->result($list);
    }

    /**
     * 活动详情
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new RechargeRewardException(RechargeRewardException::DETAIL_PARAMS_ERROR);
        }
        $detail = RechargeRewardActivityModel::find()
            ->where(['id' => $id, 'is_deleted' => 0])
            ->first();
        if (empty($detail)) {
            throw new RechargeRewardException(RechargeRewardException::DETAIL_REWARD_NOT_EXISTS);
        }
        // 字节跳动渠道处理
        $clientType = explode(',', $detail['client_type']);
        if (in_array('30', $clientType)) {
            $clientType = ArrayHelper::deleteByValue($clientType, '31');
            $clientType = ArrayHelper::deleteByValue($clientType, '32');
            $detail['client_type'] = implode(',', $clientType);
        }

        if ($detail['rules']) {
            $detail['rules'] = Json::decode($detail['rules']);
            foreach ($detail['rules']['award'] as &$rule) {
                if (empty($rule['coupon_ids'])) {
                    continue;
                }

                $rule['coupon_info'] = CouponModel::getCouponInfo(explode(',', $rule['coupon_ids']));
            }

            if ($detail['rules']['permission'] == 1) {
                $detail['member_level'] = MemberLevelModel::where([
                    'id' => $detail['rules']['permission_value'],
                ])->get();
            } elseif ($detail['rules']['permission'] == 2) {
                $detail['member_group'] = MemberGroupModel::where([
                    'id' => $detail['rules']['permission_value'],
                ])->get();
            }

        } else {
            // 优惠券
            if (!empty($detail['coupon_ids'])) {
                $couponIds = explode(',', $detail['coupon_ids']);
                $detail['coupon_info'] = CouponModel::getCouponInfo($couponIds);
            }
        }


        return $this->result(['data' => $detail]);
    }

    /**
     * 添加活动
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $delay = strtotime(RequestHelper::post('end_time')) - time();
        $res = RechargeRewardActivityModel::easyAdd([
            'attributes' => [],
            'beforeSave' => function (&$data) {
                // 校验数据
                if (empty($data['title'])) {
                    return error('活动名称不能为空');
                }
                if ($data['end_time'] < DateTimeHelper::now()) {
                    return error('结束时间不能小于当前时间');
                }
                if ($data['end_time'] < $data['start_time']) {
                    return error('结束时间不能大于开始时间');
                }
                // 活动时间不能冲突 查找时间段内的活动
                $isExists = RechargeRewardActivityModel::checkExistsByTime($data->start_time, $data->end_time);
                // 如果该时间段内有活动
                if ($isExists) {
                    return error('该时间段已存在活动');
                }
                // 渠道不能为空
                if (empty($data->client_type)) {
                    return error('渠道不能为空');
                }
                // 字节跳动小程序渠道 特殊处理
                $clientType = explode(',', $data->client_type);
                if (in_array('30', $clientType)) {
                    $clientType[] = '31';
                    $clientType[] = '32';
                    $data->client_type = implode(',', $clientType);
                }
                // 奖励
//                $reward = explode(',', $data->reward);
//                if (empty($reward)) {
//                    return error('至少选择一项优惠奖励');
//                }
                foreach ($data['rules']['award'] as $item) {

                    if (empty($item['reward'])) {
                        return error('请选择至少一种奖励');
                    }

                    foreach ($item['reward'] as $rule) {
                        if ($rule == RechargeRewardConstant::ACTIVITY_SEND_COUPON) {
                            $couponIds = explode(',', $item['coupon_ids']);
                            if (empty($couponIds)) {
                                return error('请选择优惠券');
                            }
                            if (count($couponIds) > 3) {
                                return error('最多选择三张优惠券');
                            }
                        } else if ($rule == RechargeRewardConstant::ACTIVITY_SEND_CREDIT) {
                            // 积分
                            if (empty($item['credit'])) {
                                return error('积分不能为空');
                            }
                            if ($item['credit'] < 0) {
                                return error('积分不能为负数');
                            }
                            if (bccomp($item['credit'], 99999999) > 0) {
                                return error('积分超过限额');
                            }
                        } else if ($rule == RechargeRewardConstant::ACTIVITY_SEND_BALANCE) {
                            // 余额
                            if (empty($item['balance'])) {
                                return error('余额不能为空');
                            }
                            if ($item['balance'] < 0) {
                                return error('余额不能为负数');
                            }
                            if (bccomp($item['balance'], 99999999.99, 2) > 0) {
                                return error('余额超过限额');
                            }
                        }
                    }
                }

                $data['rules'] = Json::encode($data['rules']);
            },
            'afterSave' => function ($data) use ($delay) {
                // 添加队列 插入队列id

                $jobId = QueueHelper::push(new AutoStopRechargeRewardJob([
                    'id' => $data->id,
                ]), $delay);

                // 保存任务id
                $data->job_id = $jobId;
                $data->save();

                // 拼装渠道
                $clientTypeArray = array_flip(StringHelper::explode($data->client_type));
                $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
                $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
                $clientTypeText = implode('、', $clientType);

                $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];


                $rules = Json::decode($data->rules);

                // 记录日志
                $logPrimary = [
                    'id' => $data->id,
                    '活动名称' => $data->title,
                    '活动时间' => $data->start_time . '~' . $data->end_time,
                    '渠道' => $clientTypeText,
                    '充值类型' => $data->type ? '单次充值' : '累计充值',
                    '适用人群' => $permTypeDefault[$rules['permission']],
                ];

                if ($rules['permission'] != 0) {

                    if ($rules['permission'] == 1) {
                        $name = MemberLevelModel::where([
                            'id' => $rules['permission_value'],
                        ])->select(['level_name'])->column();
                    } else {
                        $name = MemberGroupModel::where([
                            'id' => $rules['permission_value'],
                        ])->select(['group_name'])->column();
                    }

                    $logPrimary[$permTypeDefault[$rules['permission']] . '名称'] = implode('、', $name);
                }

                foreach ($rules['award'] as $index => $awardItem) {

                    $logRules = [];
                    $reward = array_flip($awardItem['reward']);

                    // 交集取文字
                    $rewardText = implode('、', array_intersect_key(RechargeRewardActivityModel::$rewardText, $reward));

                    $logRules['奖励内容'] = $rewardText;
                    $logRules['充值金额'] = $awardItem['money'];

                    foreach ($awardItem['reward'] as $rewardItem) {
                        if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_COUPON) {

                            if (!empty($awardItem['coupon_ids'])) {
                                $couponIds = explode(',', $awardItem['coupon_ids']);
                                $couponInfo = CouponModel::getCouponInfo($couponIds);
                                $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                            }

                            $logRules['优惠券名称'] = $couponTitle ?: '-';
                        } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_CREDIT) {

                            // 积分
                            $logRules['积分'] = $awardItem['credit'] ?: '-';
                        } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_BALANCE) {

                            // 余额
                            $logRules['余额'] = $awardItem['balance'] ?: '-';
                        }
                    }

                    $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
                }


                LogModel::write(
                    $this->userId,
                    RechargeRewardLogConstant::RECHARGE_REWARD_ADD,
                    RechargeRewardLogConstant::getText(RechargeRewardLogConstant::RECHARGE_REWARD_ADD),
                    $data->id,
                    [
                        'log_data' => $data->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            RechargeRewardLogConstant::RECHARGE_REWARD_ADD,
                            RechargeRewardLogConstant::RECHARGE_REWARD_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new RechargeRewardException(RechargeRewardException::ADD_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 编辑活动
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        $endTime = RequestHelper::post('end_time');

        // 查找任务
        $detail = RechargeRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new RechargeRewardException(RechargeRewardException::EDIT_REWARD_NOT_EXISTS);
        }
        // 已停止的任务不能修改
        if ($detail->end_time < DateTimeHelper::now() || $detail->status == -2) {
            throw new RechargeRewardException(RechargeRewardException::EDIT_ACTIVITY_IS_STOP);
        }
        // 不能等于当前时间
        if ($endTime == $detail->end_time) {
            throw new RechargeRewardException(RechargeRewardException::EDIT_ACTIVITY_NOT_CHANGE);
        }
        // 不能往前修改
        if ($endTime < DateTimeHelper::now()) {
            throw new RechargeRewardException(RechargeRewardException::EDIT_ACTIVITY_TIME_ERROR);
        }
        // 查找其他任务
        $isExists = RechargeRewardActivityModel::checkExistsByTime($detail->start_time, $endTime, $id);
        if ($isExists) {
            throw new RechargeRewardException(RechargeRewardException::EDIT_ACTIVITY_TIME_IS_EXISTS);
        }
        // 可以修改
        $detail->end_time = $endTime;
        // 添加新任务
        $delay = strtotime($endTime) - time();
        $jobId = QueueHelper::push(new AutoStopRechargeRewardJob([
            'id' => $id
        ]), $delay);
        // 旧任务id
        $oldJobId = $detail->job_id;
        // 新任务id
        $detail->job_id = $jobId;
        if (!$detail->save()) {
            QueueHelper::remove($jobId);
            throw new RechargeRewardException(RechargeRewardException::EDIT_ACTIVITY_FAIL);
        }
        // 删除旧任务
        QueueHelper::remove($oldJobId);

        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
        $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);


        $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];


        $rules = Json::decode($detail->rules);

        // 记录日志
        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '渠道' => $clientTypeText,
            '充值类型' => $detail->type ? '单次充值' : '累计充值',
            '适用人群' => $permTypeDefault[$rules['permission']],
        ];

        if ($rules['permission'] != 0) {

            if ($rules['permission'] == 1) {
                $name = MemberLevelModel::where([
                    'id' => $rules['permission_value'],
                ])->select(['level_name'])->column();
            } else {
                $name = MemberGroupModel::where([
                    'id' => $rules['permission_value'],
                ])->select(['group_name'])->column();
            }

            $logPrimary[$permTypeDefault[$rules['permission']] . '名称'] = implode('、', $name);
        }

        foreach ($rules['award'] as $index => $awardItem) {

            $logRules = [];
            $reward = array_flip($awardItem['reward']);

            // 交集取文字
            $rewardText = implode('、', array_intersect_key(RechargeRewardActivityModel::$rewardText, $reward));

            $logRules['奖励内容'] = $rewardText;
            $logRules['充值金额'] = $awardItem['money'];

            foreach ($awardItem['reward'] as $rewardItem) {
                if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_COUPON) {

                    if (!empty($awardItem['coupon_ids'])) {
                        $couponIds = explode(',', $awardItem['coupon_ids']);
                        $couponInfo = CouponModel::getCouponInfo($couponIds);
                        $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                    }

                    $logRules['优惠券名称'] = $couponTitle ?: '-';
                } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_CREDIT) {

                    // 积分
                    $logRules['积分'] = $awardItem['credit'] ?: '-';
                } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_BALANCE) {

                    // 余额
                    $logRules['余额'] = $awardItem['balance'] ?: '-';
                }
            }

            $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
        }

        LogModel::write(
            $this->userId,
            RechargeRewardLogConstant::RECHARGE_REWARD_EDIT,
            RechargeRewardLogConstant::getText(RechargeRewardLogConstant::RECHARGE_REWARD_EDIT),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    RechargeRewardLogConstant::RECHARGE_REWARD_ADD,
                    RechargeRewardLogConstant::RECHARGE_REWARD_EDIT,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 手动停止活动
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualStop()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new RechargeRewardException(RechargeRewardException::MANUAL_STOP_PARAMS_ERROR);
        }
        $detail = RechargeRewardActivityModel::findOne(['id' => $id, 'is_deleted' => 0]);
        if (empty($detail)) {
            throw new RechargeRewardException(RechargeRewardException::MANUAL_STOP_ACTIVITY_NOT_EXISTS);
        }
        // 活动状态错误
        if ($detail->status != 0) {
            throw new RechargeRewardException(RechargeRewardException::MANUAL_STOP_ACTIVITY_STATUS_ERROR);
        }
        $detail->status = -2;
        $detail->stop_time = DateTimeHelper::now();

        if (!$detail->save()) {
            throw new RechargeRewardException(RechargeRewardException::MANUAL_STOP_ACTIVITY_FAIL);
        }
        // 删除任务
        QueueHelper::remove($detail->job_id);
        // 记录日志
        LogModel::write(
            $this->userId,
            RechargeRewardLogConstant::RECHARGE_REWARD_MANUAL_STOP,
            RechargeRewardLogConstant::getText(RechargeRewardLogConstant::RECHARGE_REWARD_MANUAL_STOP),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => [
                    'id' => $detail->id,
                    '活动名称' => $detail->title,
                    '停止时间' => $detail->stop_time,
                ],
                'dirty_identify_code' => [
                    RechargeRewardLogConstant::RECHARGE_REWARD_MANUAL_STOP,
                ]
            ]
        );
        return $this->success();
    }

    /**
     * 删除活动
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new RechargeRewardException(RechargeRewardException::DELETE_PARAMS_ERROR);
        }
        $detail = RechargeRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new RechargeRewardException(RechargeRewardException::DELETE_REWARD_NOT_EXISTS);
        }
        $detail->is_deleted = 1;
        if (!$detail->save()) {
            throw new RechargeRewardException(RechargeRewardException::DELETE_FAIL);
        }
        QueueHelper::remove($detail->job_id);
        // 记录日志

        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
        $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);


        $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];


        $rules = Json::decode($detail->rules);

        // 记录日志
        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '渠道' => $clientTypeText,
            '充值类型' => $detail->type ? '单次充值' : '累计充值',
            '适用人群' => $permTypeDefault[$rules['permission']],
        ];

        if ($rules['permission'] != 0) {

            if ($rules['permission'] == 1) {
                $name = MemberLevelModel::where([
                    'id' => $rules['permission_value'],
                ])->select(['level_name'])->column();
            } else {
                $name = MemberGroupModel::where([
                    'id' => $rules['permission_value'],
                ])->select(['group_name'])->column();
            }

            $logPrimary[$permTypeDefault[$rules['permission']] . '名称'] = implode('、', $name);
        }

        foreach ($rules['award'] as $index => $awardItem) {

            $logRules = [];
            $reward = array_flip($awardItem['reward']);

            // 交集取文字
            $rewardText = implode('、', array_intersect_key(RechargeRewardActivityModel::$rewardText, $reward));

            $logRules['奖励内容'] = $rewardText;
            $logRules['充值金额'] = $awardItem['money'];

            foreach ($awardItem['reward'] as $rewardItem) {
                if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_COUPON) {

                    if (!empty($awardItem['coupon_ids'])) {
                        $couponIds = explode(',', $awardItem['coupon_ids']);
                        $couponInfo = CouponModel::getCouponInfo($couponIds);
                        $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                    }

                    $logRules['优惠券名称'] = $couponTitle ?: '-';
                } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_CREDIT) {

                    // 积分
                    $logRules['积分'] = $awardItem['credit'] ?: '-';
                } else if ($rewardItem == RechargeRewardConstant::ACTIVITY_SEND_BALANCE) {

                    // 余额
                    $logRules['余额'] = $awardItem['balance'] ?: '-';
                }
            }

            $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
        }

        LogModel::write(
            $this->userId,
            RechargeRewardLogConstant::RECHARGE_REWARD_DELETE,
            RechargeRewardLogConstant::getText(RechargeRewardLogConstant::RECHARGE_REWARD_DELETE),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    RechargeRewardLogConstant::RECHARGE_REWARD_DELETE,
                ]
            ]
        );


        return $this->success();
    }
}