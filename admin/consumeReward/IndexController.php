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

namespace shopstar\admin\consumeReward;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\consumeReward\ConsumeRewardConstant;
use shopstar\constants\consumeReward\ConsumeRewardLogConstant;
use shopstar\exceptions\consumeReward\ConsumeRewardException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\jobs\consumeReward\AutoStopConsumeRewardJob;
use shopstar\models\consumeReward\ConsumeRewardActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * 消费奖励
 * Class IndexController
 * @package shopstar\admin\consumeReward
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 活动列表
     * @return array|int[]|\yii\web\Response
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
                $andWhere[] = [
                    'or',
                    ['status' => -1],
                    ['<', 'end_time', DateTimeHelper::now()],
                ];
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
                'if(stop_time=0, 1, 2) as level'
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

        $list = ConsumeRewardActivityModel::getColl($params, [
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
     * 详情
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new ConsumeRewardException(ConsumeRewardException::DETAIL_PARAMS_ERROR);
        }

        $detail = ConsumeRewardActivityModel::find()
            ->where(['id' => $id])
            ->first();
        if (empty($detail)) {
            throw new ConsumeRewardException(ConsumeRewardException::DETAIL_REWARD_NOT_EXISTS);
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

        // 商品信息
        if (!empty($detail['goods_limit'])) {
            $goodsIds = explode(',', $detail['goods_limit']);
            $detail['goods_info'] = GoodsModel::find()
                ->select('id, thumb, title, price')
                ->where(['id' => $goodsIds])
                ->get();
        }

        return $this->result(['data' => $detail]);
    }

    /**
     * 新增活动
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $res = ConsumeRewardActivityModel::easyAdd([
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
                $isExists = ConsumeRewardActivityModel::checkExistsByTime($data->start_time, $data->end_time);
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

                foreach ((array)$data['rules']['award'] as $item) {

                    if (empty($item['reward'])) {
                        return error('请选择至少一种奖励');
                    }

                    foreach ((array)$item['reward'] as $rule) {

                        if ($rule == ConsumeRewardConstant::ACTIVITY_SEND_COUPON) {
                            $couponIds = explode(',', $item['coupon_ids']);
                            if (empty($couponIds)) {
                                return error('请选择优惠券');
                            }
                            if (count($couponIds) > 3) {
                                return error('最多选择三张优惠券');
                            }
                        } else if ($rule == ConsumeRewardConstant::ACTIVITY_SEND_CREDIT) {
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
                        } else if ($rule == ConsumeRewardConstant::ACTIVITY_SEND_BALANCE) {
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
                        } elseif ($rule == ConsumeRewardConstant::ACTIVITY_SEND_RED_PACKAGE) {
                            //
                            if (empty($item['red_package']['money'])) {
                                return error('金额不能为空');
                            }
                            if (empty($item['red_package']['expiry'])) {
                                return error('过期天数不能为空');
                            }

                        }
                    }
                }

                $data['rules'] = Json::encode($data['rules']);
            },
            'afterSave' => function ($data) {
                $delay = strtotime($data->end_time) - time();
                $jobId = QueueHelper::push(new AutoStopConsumeRewardJob([
                    'id' => $data->id,
                ]), $delay);

                // 保存任务id
                $data->job_id = $jobId;
                $data->save();
                // 记录日志

                // 拼装渠道
                $clientTypeArray = array_flip(StringHelper::explode($data->client_type));
                $clientType = array_intersect_key(ConsumeRewardActivityModel::$clientType, $clientTypeArray);
                $clientTypeText = implode('、', $clientType);
                // 拼装提现方式
                $payType = array_flip(StringHelper::explode($data->pay_type));
                $payType = array_intersect_key(ConsumeRewardActivityModel::$payType, $payType);
                $payTypeText = implode('、', $payType);
                // 活动限制
                $activityLimit = array_flip(explode(',', $data->activity_limit));
                $activityLimit = array_intersect_key(ConsumeRewardActivityModel::$activityLimit, $activityLimit);
                $activityLimitText = implode('、', $activityLimit);
                // 不参与活动的商品
                if (!empty($data->goods_limit)) {
                    $goods = GoodsModel::find()->select('title')->where(['id' => explode(',', $data->goods_limit)])->get();
                    $goodsLimitText = implode(',', array_column($goods, 'title'));
                }

                $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];

                $rules = Json::decode($data->rules);

                $logPrimary = [
                    'id' => $data->id,
                    '活动名称' => $data->title,
                    '活动时间' => $data->start_time . '~' . $data->end_time,
                    '弹框样式' => ConsumeRewardActivityModel::$popupType[$data->popup_type],
                    '渠道' => $clientTypeText,
                    '适用人群' => $permTypeDefault[$rules['permission']],
                    '支付方式' => $payTypeText,
                    '消费类型' => $data->type ? '单次消费' : '累计消费',
                    '重复领取' => $data->is_repeat ? '可重复领取' : '不可重复领取',
                    '赠送时间' => $data->send_type ? '订单付款后' : '订单完成后',
                    '活动限制' => $activityLimitText,
                    '不参与活动商品' => $goodsLimitText ?? '-',
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
                    $rewardText = implode('、', array_intersect_key(ConsumeRewardActivityModel::$rewardText, $reward));

                    $logRules['消费金额'] = $awardItem['money'];
                    $logRules['奖励内容'] = $rewardText;

                    foreach ($awardItem['reward'] as $rewardItem) {
                        if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_COUPON) {

                            if (!empty($awardItem['coupon_ids'])) {
                                $couponIds = explode(',', $awardItem['coupon_ids']);
                                $couponInfo = CouponModel::getCouponInfo($couponIds);
                                $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                            }

                            $logRules['优惠券名称'] = $couponTitle ?: '-';
                        } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_CREDIT) {

                            // 积分
                            $logRules['积分'] = $awardItem['credit'] ?: '-';
                        } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_BALANCE) {

                            // 余额
                            $logRules['余额'] = $awardItem['balance'] ?: '-';
                        } elseif ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_RED_PACKAGE) {

                            $redPackage = [
                                '金额' => $awardItem['red_package']['money'] ?? '-',
                                '有效期' => $awardItem['red_package']['expiry'] ?? '-',
                                '祝福语' => $awardItem['red_package']['blessing'] ?? '-',
                            ];

                            $logRules['红包'] = $redPackage;
                        }
                    }

                    $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
                }


                LogModel::write(
                    $this->userId,
                    ConsumeRewardLogConstant::CONSUME_REWARD_ADD,
                    ConsumeRewardLogConstant::getText(ConsumeRewardLogConstant::CONSUME_REWARD_ADD),
                    $data->id,
                    [
                        'log_data' => $data->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            ConsumeRewardLogConstant::CONSUME_REWARD_ADD,
                            ConsumeRewardLogConstant::CONSUME_REWARD_EDIT,
                        ]
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new ConsumeRewardException(ConsumeRewardException::ADD_REWARD_FAIL, $res['message']);
        }
        return $this->success();
    }

    /**
     * 修改
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        $endTime = RequestHelper::post('end_time');

        // 查找任务
        $detail = ConsumeRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_NOT_EXISTS);
        }
        // 已停止的任务不能修改
        if ($detail->end_time < DateTimeHelper::now() || $detail->status == -2) {
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_IS_STOP);
        }
        // 不能等于当前时间
        if ($endTime == $detail->end_time) {
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_NOT_CHANGE);
        }
        // 不能往前修改

        if ($endTime < DateTimeHelper::now()) {
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_TIME_ERROR);
        }
        // 查找其他任务
        $isExists = ConsumeRewardActivityModel::checkExistsByTime($detail->start_time, $endTime, $id);
        if ($isExists) {
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_TIME_IS_EXISTS);
        }

        // 可以修改
        $detail->end_time = $endTime;
        // 添加新任务
        $delay = strtotime($endTime) - time();
        $jobId = QueueHelper::push(new AutoStopConsumeRewardJob([
            'id' => $id
        ]), $delay);
        // 旧任务id
        $oldJobId = $detail->job_id;
        // 新任务id
        $detail->job_id = $jobId;
        if (!$detail->save()) {
            QueueHelper::remove($jobId);
            throw new ConsumeRewardException(ConsumeRewardException::EDIT_ACTIVITY_FAIL);
        }
        // 删除旧任务
        QueueHelper::remove($oldJobId);

        // 记录日志
        $reward = array_flip(explode(',', $detail->reward));
        // 交集取文字
        $rewardText = implode('、', array_intersect_key(ConsumeRewardActivityModel::$rewardText, $reward));
        if (!empty($detail->coupon_ids)) {
            $couponIds = explode(',', $detail->coupon_ids);
            $couponInfo = CouponModel::getCouponInfo($couponIds);
            $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
        }
        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientType = array_intersect_key(ConsumeRewardActivityModel::$clientType, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
        // 拼装提现方式
        $payType = array_flip(StringHelper::explode($detail->pay_type));
        $payType = array_intersect_key(ConsumeRewardActivityModel::$payType, $payType);
        $payTypeText = implode('、', $payType);
        // 活动限制
        $activityLimit = array_flip(explode(',', $detail->activity_limit));
        $activityLimit = array_intersect_key(ConsumeRewardActivityModel::$activityLimit, $activityLimit);
        $activityLimitText = implode('、', $activityLimit);
        // 不参与活动的商品
        if (!empty($detail->goods_limit)) {
            $goods = GoodsModel::find()->select('title')->where(['id' => explode(',', $detail->goods_limit)])->get();
            $goodsLimitText = implode(',', array_column($goods, 'title'));
        }

        $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];

        $rules = Json::decode($detail->rules);

        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '弹框样式' => ConsumeRewardActivityModel::$popupType[$detail->popup_type],
            '渠道' => $clientTypeText,
            '适用人群' => $permTypeDefault[$rules['permission']],
            '支付方式' => $payTypeText,
            '消费类型' => $detail->type ? '单次消费' : '累计消费',
            '重复领取' => $detail->is_repeat ? '可重复领取' : '不可重复领取',
            '赠送时间' => $detail->send_type ? '订单付款后' : '订单完成后',
            '活动限制' => $activityLimitText,
            '不参与活动商品' => $goodsLimitText ?? '-',
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
            $rewardText = implode('、', array_intersect_key(ConsumeRewardActivityModel::$rewardText, $reward));

            $logRules['充值金额'] = $awardItem['money'];
            $logRules['奖励内容'] = $rewardText;

            foreach ($awardItem['reward'] as $rewardItem) {
                if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_COUPON) {

                    if (!empty($awardItem['coupon_ids'])) {
                        $couponIds = explode(',', $awardItem['coupon_ids']);
                        $couponInfo = CouponModel::getCouponInfo($couponIds);
                        $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                    }

                    $logRules['优惠券名称'] = $couponTitle ?: '-';
                } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_CREDIT) {

                    // 积分
                    $logRules['积分'] = $awardItem['credit'] ?: '-';
                } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_BALANCE) {

                    // 余额
                    $logRules['余额'] = $awardItem['balance'] ?: '-';
                } elseif ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_RED_PACKAGE) {

                    $logRules['红包'] = $awardItem['red_package'] ?: '-';
                }
            }

            $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
        }

        LogModel::write(
            $this->userId,
            ConsumeRewardLogConstant::CONSUME_REWARD_EDIT,
            ConsumeRewardLogConstant::getText(ConsumeRewardLogConstant::CONSUME_REWARD_EDIT),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    ConsumeRewardLogConstant::CONSUME_REWARD_ADD,
                    ConsumeRewardLogConstant::CONSUME_REWARD_EDIT,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 手动停止活动
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualStop()
    {
        $id = RequestHelper::get('id');
        $detail = ConsumeRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new ConsumeRewardException(ConsumeRewardException::MANUAL_STOP_ACTIVITY_NOT_EXISTS);
        }
        // 活动状态错误
        if ($detail->status != 0) {
            throw new ConsumeRewardException(ConsumeRewardException::MANUAL_STOP_ACTIVITY_STATUS_ERROR);
        }
        $detail->status = -2;
        $detail->stop_time = DateTimeHelper::now();

        if (!$detail->save()) {
            throw new ConsumeRewardException(ConsumeRewardException::MANUAL_STOP_ACTIVITY_FAIL);
        }
        // 删除任务
        QueueHelper::remove($detail->job_id);

        // 记录日志
        LogModel::write(
            $this->userId,
            ConsumeRewardLogConstant::CONSUME_REWARD_MANUAL_STOP,
            ConsumeRewardLogConstant::getText(ConsumeRewardLogConstant::CONSUME_REWARD_MANUAL_STOP),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => [
                    'id' => $detail->id,
                    '活动名称' => $detail->title,
                    '停止时间' => $detail->stop_time
                ],
                'dirty_identify_code' => [
                    ConsumeRewardLogConstant::CONSUME_REWARD_MANUAL_STOP,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 删除
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new ConsumeRewardException(ConsumeRewardException::DELETE_PARAMS_ERROR);
        }
        $detail = ConsumeRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new ConsumeRewardException(ConsumeRewardException::DELETE_REWARD_NOT_EXISTS);
        }
        $detail->is_deleted = 1;

        $detail->stop_time = DateTimeHelper::now();
        if (!$detail->save()) {
            throw new ConsumeRewardException(ConsumeRewardException::DELETE_FAIL);
        }
        // 删除活动
        QueueHelper::remove($detail->job_id);

        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientType = array_intersect_key(ConsumeRewardActivityModel::$clientType, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
        // 拼装提现方式
        $payType = array_flip(StringHelper::explode($detail->pay_type));
        $payType = array_intersect_key(ConsumeRewardActivityModel::$payType, $payType);
        $payTypeText = implode('、', $payType);
        // 活动限制
        $activityLimit = array_flip(explode(',', $detail->activity_limit));
        $activityLimit = array_intersect_key(ConsumeRewardActivityModel::$activityLimit, $activityLimit);
        $activityLimitText = implode('、', $activityLimit);
        // 不参与活动的商品
        if (!empty($detail->goods_limit)) {
            $goods = GoodsModel::find()->select('title')->where(['id' => explode(',', $detail->goods_limit)])->get();
            $goodsLimitText = implode(',', array_column($goods, 'title'));
        }

        $permTypeDefault = ['0' => '全部会员', '1' => '会员等级', '2' => '会员标签'];

        $rules = Json::decode($detail->rules);

        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '渠道' => $clientTypeText,
            '适用人群' => $permTypeDefault[$rules['permission']],
            '支付方式' => $payTypeText,
            '消费类型' => $detail->type ? '单次消费' : '累计消费',
            '重复领取' => $detail->is_repeat ? '可重复领取' : '不可重复领取',
            '赠送时间' => $detail->send_type ? '订单付款后' : '订单完成后',
            '活动限制' => $activityLimitText,
            '不参与活动商品' => $goodsLimitText ?? '-',
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
            $rewardText = implode('、', array_intersect_key(ConsumeRewardActivityModel::$rewardText, $reward));

            $logRules['奖励内容'] = $rewardText;
            $logRules['充值金额'] = $awardItem['money'];

            foreach ($awardItem['reward'] as $rewardItem) {
                if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_COUPON) {

                    if (!empty($awardItem['coupon_ids'])) {
                        $couponIds = explode(',', $awardItem['coupon_ids']);
                        $couponInfo = CouponModel::getCouponInfo($couponIds);
                        $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                    }

                    $logRules['优惠券名称'] = $couponTitle ?: '-';
                } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_CREDIT) {

                    // 积分
                    $logRules['积分'] = $awardItem['credit'] ?: '-';
                } else if ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_BALANCE) {

                    // 余额
                    $logRules['余额'] = $awardItem['balance'] ?: '-';
                } elseif ($rewardItem == ConsumeRewardConstant::ACTIVITY_SEND_RED_PACKAGE) {

                    $logRules['红包'] = $awardItem['red_package'] ?: '-';
                }
            }

            $logPrimary[($index + 1) . '级奖励规则'] = $logRules;
        }

        LogModel::write(
            $this->userId,
            ConsumeRewardLogConstant::CONSUME_REWARD_DELETE,
            ConsumeRewardLogConstant::getText(ConsumeRewardLogConstant::CONSUME_REWARD_DELETE),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    ConsumeRewardLogConstant::CONSUME_REWARD_DELETE,
                ]
            ]
        );

        return $this->success();
    }

}