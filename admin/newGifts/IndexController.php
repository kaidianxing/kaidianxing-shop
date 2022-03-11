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

namespace shopstar\admin\newGifts;

use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\sale\CouponModel;
use shopstar\constants\newGifts\ActivityConstant;
use shopstar\constants\newGifts\NewGiftsLogConstant;
use shopstar\exceptions\newGifts\NewGiftsException;
use shopstar\jobs\newGifts\AutoStopActivityJob;
use shopstar\models\newGifts\NewGiftsActivityModel;
use shopstar\bases\KdxAdminApiController;
use yii\db\StaleObjectException;
use yii\helpers\StringHelper;

class IndexController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
        ]
    ];
    /**
     * 活动列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $status = RequestHelper::get('status');
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $andWhere = [];
        // 活动时间
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
            ],
            'select' => [
                'id',
                'title',
                'start_time',
                'end_time',
                'stop_time',
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
                'id' => SORT_DESC
            ]
        ];
        $list = NewGiftsActivityModel::getColl($params, [
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
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new NewGiftsException(NewGiftsException::DETAIL_PARAMS_ERROR);
        }
        $detail = NewGiftsActivityModel::find()
            ->select('id, title, start_time, end_time, pick_type, gifts, coupon_ids, credit, balance, client_type, popup_type')
            ->where(['id' => $id])
            ->first();
        // 活动不存在
        if (empty($detail)) {
            throw new NewGiftsException(NewGiftsException::DETAIL_ACTIVITY_NOT_EXISTS);
        }
        // 字节跳动渠道处理
        $clientType = explode(',', $detail['client_type']);
        if (in_array('30', $clientType)) {
            $clientType = ArrayHelper::deleteByValue($clientType, '31');
            $clientType = ArrayHelper::deleteByValue($clientType, '32');
            $detail['client_type'] = implode(',', $clientType);
        }
        // 优惠券
        if (!empty($detail['coupon_ids'])) {
            $couponIds = explode(',', $detail['coupon_ids']);
            $detail['coupon_info'] = CouponModel::getCouponInfo($couponIds);
        }
        
        return $this->result(['data' => $detail]);
    }
    
    /**
     * 新增活动
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $endTime = RequestHelper::post('end_time');
        $delay = strtotime($endTime) - time();
        $res = NewGiftsActivityModel::easyAdd([
            'attributes' => [],
            'beforeSave' => function ($data) {
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
                $isExists = NewGiftsActivityModel::checkExistsByTime($data->start_time, $data->end_time);
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
                $gifts = explode(',', $data['gifts']);
                if (empty($gifts)) {
                    return error('至少选择一项优惠奖励');
                }
                foreach ($gifts as $item) {
                    // 优惠券
                    if ($item == ActivityConstant::ACTIVITY_SEND_COUPON) {
                        $couponIds = explode(',', $data['coupon_ids']);
                        if (empty($couponIds)) {
                            return error('请选择优惠券');
                        }
                        if (count($couponIds) > 3) {
                            return error('最多选择三张优惠券');
                        }
                    } else if ($item == ActivityConstant::ACTIVITY_SEND_CREDIT) {
                        // 积分
                        if (empty($data['credit'])) {
                            return error('积分不能为空');
                        }
                        if ($data['credit'] < 0) {
                            return error('积分不能为负数');
                        }
                        if (bccomp($data['credit'], 99999999) > 0) {
                            return error('积分超过限额');
                        }
                    } else if ($item == ActivityConstant::ACTIVITY_SEND_BALANCE) {
                        // 余额
                        if (empty($data['balance'])) {
                            return error('余额不能为空');
                        }
                        if ($data['balance'] < 0) {
                            return error('余额不能为负数');
                        }
                        if (bccomp($data['balance'], 99999999.99, 2) > 0) {
                            return error('余额超过限额');
                        }
                    }
                }
            },
            'afterSave' => function ($data) use ($delay) {
                // 添加队列 插入队列id
                $jobId = QueueHelper::push(new AutoStopActivityJob([
                    'id' => $data->id,
                ]), $delay);
                
                // 保存任务id
                $data->job_id = $jobId;
                $data->save();
                // 记录日志
                $gifts = array_flip(explode(',', $data->gifts));
                // 交集取文字
                $giftsText = implode('、', array_intersect_key(NewGiftsActivityModel::$giftsText, $gifts));
                if (!empty($data->coupon_ids)) {
                    $couponIds = explode(',', $data->coupon_ids);
                    $couponInfo = CouponModel::getCouponInfo($couponIds);
                    $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
                }
                // 拼装渠道
                $clientTypeArray = array_flip(StringHelper::explode($data->client_type));
                $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
                $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
                $clientTypeText = implode('、', $clientType);
                
                $logPrimary = [
                    'id' => $data->id,
                    '活动名称' => $data->title,
                    '活动时间' => $data->start_time . '~' . $data->end_time,
                    '弹框样式' => NewGiftsActivityModel::$popupType[$data->popup_type],
                    '渠道' => $clientTypeText,
                    '领取条件' => $data->pick_type ? '新注册会员' : '无消费记录会员',
                    '优惠奖励' => $giftsText,
                ];
                $gifts = explode(',', $data->gifts);
                foreach ($gifts as $item) {
                    if ($item == ActivityConstant::ACTIVITY_SEND_COUPON) {
                        $logPrimary = array_merge($logPrimary, [
                            '优惠券名称' => $couponTitle ?: '-',
                        ]);
                    } else if ($item == ActivityConstant::ACTIVITY_SEND_CREDIT) {
                        // 积分
                        $logPrimary = array_merge($logPrimary, [
                            '积分' => $data->credit ?: '-',
                        ]);
                    } else if ($item == ActivityConstant::ACTIVITY_SEND_BALANCE) {
                        // 余额
                        $logPrimary = array_merge($logPrimary, [
                            '余额' => $data->balance ?: '-',
                        ]);
                    }
                }
                
                LogModel::write(
                    $this->userId,
                    NewGiftsLogConstant::NEW_GIFTS_ADD,
                    NewGiftsLogConstant::getText(NewGiftsLogConstant::NEW_GIFTS_ADD),
                    $data->id,
                    [
                        'log_data' => $data->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            NewGiftsLogConstant::NEW_GIFTS_ADD,
                            NewGiftsLogConstant::NEW_GIFTS_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new NewGiftsException(NewGiftsException::ADD_FAIL, $res['message']);
        }
        return $this->success();
    }
    
    /**
     * 修改任务
     * @return array|int[]|\yii\web\Response
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        $endTime = RequestHelper::post('end_time');
        // 任务结束时间
        $delay = strtotime($endTime) - time();
        // 查找任务
        $detail = NewGiftsActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_NOT_EXISTS);
        }
        
        // 已停止的任务不能修改
        if ($detail->end_time < DateTimeHelper::now() || $detail->status == -2) {
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_IS_STOP);
        }
        // 不能等于当前时间
        if ($endTime == $detail->end_time) {
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_NOT_CHANGE);
        }
        // 不能往前修改
        if ($endTime < DateTimeHelper::now()) {
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_TIME_ERROR);
        }
        // 查找其他任务
        $isExists = NewGiftsActivityModel::checkExistsByTime($detail->start_time, $endTime, $id);
        if ($isExists) {
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_TIME_IS_EXISTS);
        }
        
        // 可以修改
        $detail->end_time = $endTime;
        // 添加新任务
        $jobId = QueueHelper::push(new AutoStopActivityJob([
            'id' => $id
        ]), $delay);
        // 旧任务id
        $oldJobId = $detail->job_id;
        // 新任务id
        $detail->job_id = $jobId;
        if (!$detail->save()) {
            QueueHelper::remove($jobId);
            throw new NewGiftsException(NewGiftsException::EDIT_ACTIVITY_FAIL);
        }
        // 删除旧任务
        QueueHelper::remove($oldJobId);
        
        // 记录日志
        $gifts = array_flip(explode(',', $detail->gifts));
        // 交集取文字
        $giftsText = implode('、', array_intersect_key(NewGiftsActivityModel::$giftsText, $gifts));
        if (!empty($detail->coupon_ids)) {
            $couponIds = explode(',', $detail->coupon_ids);
            $couponInfo = CouponModel::getCouponInfo($couponIds);
            $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
        }
        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
        $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
    
        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '弹框样式' => NewGiftsActivityModel::$popupType[$detail->popup_type],
            '渠道' => $clientTypeText,
            '领取条件' => $detail->pick_type ? '新注册会员' : '无消费记录会员',
            '优惠奖励' => $giftsText,
        ];
        $gifts = explode(',', $detail->gifts);
        foreach ($gifts as $item) {
            if ($item == ActivityConstant::ACTIVITY_SEND_COUPON) {
                $logPrimary = array_merge($logPrimary, [
                    '优惠券名称' => $couponTitle ?: '-',
                ]);
            } else if ($item == ActivityConstant::ACTIVITY_SEND_CREDIT) {
                // 积分
                $logPrimary = array_merge($logPrimary, [
                    '积分' => $detail->credit ?: '-',
                ]);
            } else if ($item == ActivityConstant::ACTIVITY_SEND_BALANCE) {
                // 余额
                $logPrimary = array_merge($logPrimary, [
                    '余额' => $detail->balance ?: '-',
                ]);
            }
        }
        LogModel::write(
            $this->userId,
            NewGiftsLogConstant::NEW_GIFTS_EDIT,
            NewGiftsLogConstant::getText(NewGiftsLogConstant::NEW_GIFTS_EDIT),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    NewGiftsLogConstant::NEW_GIFTS_ADD,
                    NewGiftsLogConstant::NEW_GIFTS_EDIT,
                ]
            ]
        );
        
        return $this->success();
    }
    
    /**
     * 手动停止活动
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualStop()
    {
        $id = RequestHelper::get('id');
        $detail = NewGiftsActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new NewGiftsException(NewGiftsException::MANUAL_STOP_ACTIVITY_NOT_EXISTS);
        }
        // 活动状态错误
        if ($detail->status != 0) {
            throw new NewGiftsException(NewGiftsException::MANUAL_STOP_ACTIVITY_STATUS_ERROR);
        }
        $detail->status = -2;
        $detail->stop_time = DateTimeHelper::now();
        
        if (!$detail->save()) {
            throw new NewGiftsException(NewGiftsException::MANUAL_STOP_ACTIVITY_FAIL);
        }
        // 删除任务
        QueueHelper::remove($detail->job_id);
    
        // 记录日志
        LogModel::write(
            $this->userId,
            NewGiftsLogConstant::NEW_GIFTS_MANUAL_STOP,
            NewGiftsLogConstant::getText(NewGiftsLogConstant::NEW_GIFTS_MANUAL_STOP),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => [
                    'id' => $detail->id,
                    '活动名称' => $detail->title,
                    '停止时间' => $detail->stop_time,
                ],
                'dirty_identify_code' => [
                    NewGiftsLogConstant::NEW_GIFTS_MANUAL_STOP,
                ]
            ]
        );
        
        return $this->success();
    }
    
    /**
     * 删除活动
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new NewGiftsException(NewGiftsException::DELETE_ACTIVITY_PARAMS_ERROR);
        }
        // 获取详情
        $detail = NewGiftsActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new NewGiftsException(NewGiftsException::DELETE_ACTIVITY_NOT_EXISTS);
        }
        try {
            $detail->is_deleted = 1;
            $detail->save();
            // 删除活动
            QueueHelper::remove($detail->job_id);
    
            // 记录日志
            $gifts = array_flip(explode(',', $detail->gifts));
            // 交集取文字
            $giftsText = implode('、', array_intersect_key(NewGiftsActivityModel::$giftsText, $gifts));
            if (!empty($detail->coupon_ids)) {
                $couponIds = explode(',', $detail->coupon_ids);
                $couponInfo = CouponModel::getCouponInfo($couponIds);
                $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
            }
            // 拼装渠道
            $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
            $clientTypeDefault = ['10' => 'H5', '20' => '微信公众号', '21' => '微信小程序', '30' => '头条/抖音小程序'];
            $clientType = array_intersect_key($clientTypeDefault, $clientTypeArray);
            $clientTypeText = implode('、', $clientType);
            $logPrimary = [
                'id' => $detail->id,
                '活动名称' => $detail->title,
                '活动时间' => $detail->start_time . '~' . $detail->end_time,
                '渠道' => $clientTypeText,
                '领取条件' => $detail->pick_type ? '新注册会员' : '无消费记录会员',
                '优惠奖励' => $giftsText,
            ];
            $gifts = explode(',', $detail->gifts);
            foreach ($gifts as $item) {
                if ($item == ActivityConstant::ACTIVITY_SEND_COUPON) {
                    $logPrimary = array_merge($logPrimary, [
                        '优惠券名称' => $couponTitle ?: '-',
                    ]);
                } else if ($item == ActivityConstant::ACTIVITY_SEND_CREDIT) {
                    // 积分
                    $logPrimary = array_merge($logPrimary, [
                        '积分' => $detail->credit ?: '-',
                    ]);
                } else if ($item == ActivityConstant::ACTIVITY_SEND_BALANCE) {
                    // 余额
                    $logPrimary = array_merge($logPrimary, [
                        '余额' => $detail->balance ?: '-',
                    ]);
                }
            }
            LogModel::write(
                $this->userId,
                NewGiftsLogConstant::NEW_GIFTS_DELETE,
                NewGiftsLogConstant::getText(NewGiftsLogConstant::NEW_GIFTS_DELETE),
                $detail->id,
                [
                    'log_data' => $detail->attributes,
                    'log_primary' => $logPrimary,
                    'dirty_identify_code' => [
                        NewGiftsLogConstant::NEW_GIFTS_DELETE,
                    ]
                ]
            );
        } catch (\Throwable $exception) {
            throw new NewGiftsException(NewGiftsException::DELETE_ACTIVITY_FAIL);
        }
        
        return $this->success();
    }
    
}