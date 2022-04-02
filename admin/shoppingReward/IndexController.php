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

namespace shopstar\admin\shoppingReward;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\shoppingReward\ShoppingRewardActivityConstant;
use shopstar\constants\shoppingReward\ShoppingRewardActivityLogConstant;
use shopstar\exceptions\shoppingReward\ShoppingRewardException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\jobs\shoppingReward\AutoStopShoppingRewardJob;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityGoodsRuleModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityMemberRuleModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityModel;
use shopstar\services\shoppingReward\ShoppingRewardActivityService;

/**
 * 活动管理
 * Class IndexController
 * @package shopstar\admin\shoppingReward
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends KdxAdminApiController
{

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
            ],
            'where' => [
                'is_deleted' => 0,
            ],
            'andWhere' => $andWhere,
            'orderBy' => [
                'status' => SORT_DESC,
                'id' => SORT_DESC
            ]
        ];

        $list = ShoppingRewardActivityModel::getColl($params, [
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
     * @throws ShoppingRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new ShoppingRewardException(ShoppingRewardException::DETAIL_PARAMS_ERROR);
        }
        $detail = ShoppingRewardActivityModel::find()
            ->where(['id' => $id])
            ->first();
        // 活动不存在
        if (empty($detail)) {
            throw new ShoppingRewardException(ShoppingRewardException::DETAIL_ACTIVITY_NOT_EXISTS);
        }
        // 优惠券
        if (!empty($detail['coupon_ids'])) {
            $couponIds = explode(',', $detail['coupon_ids']);
            $detail['coupon_info'] = CouponModel::getCouponInfo($couponIds);
        }
        // 商品限制
        if ($detail['goods_type'] != 0) {
            $goodsLimit = ShoppingRewardActivityGoodsRuleModel::find()->where(['activity_id' => $detail['id']])->get();
            $idGoodsOrCate = array_column($goodsLimit, 'goods_or_cate_id');
            // 商品限制
            if ($detail['goods_type'] == ShoppingRewardActivityConstant::GOODS_TYPE_ALLOW_GOODS || $detail['goods_type'] == ShoppingRewardActivityConstant::GOODS_TYPE_NOT_ALLOW_GOODS) {
                $detail['goods'] = GoodsModel::find()->select('id, title, thumb, price, stock, type')->where(['id' => $idGoodsOrCate])->get();
            } else {
                // 允许分类使用
                $detail['goods_cate'] = GoodsCategoryModel::find()->select('id, name')->where(['id' => $idGoodsOrCate])->get();
            }
        }
        // 会员限制
        if ($detail['member_type'] != 0) {
            $memberLimit = ShoppingRewardActivityMemberRuleModel::find()->where(['activity_id' => $detail['id']])->get();
            $idLevelOrGroup = array_column($memberLimit, 'level_or_group_id');
            // 会员等级限制
            if ($detail['member_type'] == ShoppingRewardActivityConstant::MEMBER_LEVEL_LIMIT) {
                $detail['member_level'] = MemberLevelModel::find()->select('id, level_name')->where(['id' => $idLevelOrGroup])->get();
            } else {
                // 会员分组限制
                $detail['member_group'] = MemberGroupModel::find()->select('id, group_name')->where(['id' => $idLevelOrGroup])->get();
            }
        }

        return $this->result(['data' => $detail]);
    }

    /**
     * 新增活动
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $data = RequestHelper::post();
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = ShoppingRewardActivityService::addActivity($data, $this->userId);
            if (is_error($res)) {
                throw new ShoppingRewardException(ShoppingRewardException::ADD_ACTIVITY_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
        return $this->success($data);
    }

    /**
     * 编辑
     * @throws ShoppingRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        $endTime = RequestHelper::post('end_time');

        // 查找任务
        $detail = ShoppingRewardActivityModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_REWARD_NOT_EXISTS);
        }
        // 已停止的任务不能修改
        if ($detail->end_time < DateTimeHelper::now() || $detail->status == -2) {
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_ACTIVITY_IS_STOP);
        }
        // 不能等于当前时间
        if ($endTime == $detail->end_time) {
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_ACTIVITY_NOT_CHANGE);
        }
        // 不能往前修改
        if ($endTime < DateTimeHelper::now()) {
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_ACTIVITY_TIME_ERROR);
        }
        // 查找其他任务
        $isExists = ShoppingRewardActivityModel::checkExistsByTime($detail->start_time, $endTime, $id);
        if ($isExists) {
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_ACTIVITY_TIME_IS_EXISTS);
        }
        // 可以修改
        $detail->end_time = $endTime;

        // 添加新任务
        $delay = strtotime($endTime) - time();
        $jobId = QueueHelper::push(new AutoStopShoppingRewardJob([
            'id' => $id
        ]), $delay);
        // 旧任务id
        $oldJobId = $detail->job_id;
        // 新任务id
        $detail->job_id = $jobId;
        if (!$detail->save()) {
            QueueHelper::remove($jobId);
            throw new ShoppingRewardException(ShoppingRewardException::EDIT_ACTIVITY_FAIL);
        }
        // 删除旧任务
        QueueHelper::remove($oldJobId);

        // 记录日志
        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientType = array_intersect_key(ShoppingRewardActivityModel::$clientType, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
        // 奖励文字
        $reward = array_flip(explode(',', $detail->reward));
        // 交集取文字
        $rewardText = implode('、', array_intersect_key(ShoppingRewardActivityModel::$rewardText, $reward));

        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '弹框样式' => ShoppingRewardActivityModel::$popupType[$detail->popup_type],
            '渠道' => $clientTypeText,
            '指定商品' => ShoppingRewardActivityModel::$goodsType[$detail->goods_type],
        ];
        if ($detail->goods_type != 0) {
            // 获取id
            $goodsTypeData = ShoppingRewardActivityGoodsRuleModel::find()->where(['activity_id' => $id])->get();
            $idGoodsOrCate = array_column($goodsTypeData, 'goods_or_cate_id');
            if ($detail->goods_type == 3) {
                $goodsCate = GoodsCategoryModel::find()->where(['id' => $idGoodsOrCate])->get();
                $text = array_column($goodsCate, 'name');
                $goodsTypeArray = [
                    '商品分类' => implode(',', $text)
                ];
            } else {
                $goods = GoodsModel::find()->where(['id' => $idGoodsOrCate])->get();
                $text = array_column($goods, 'title');
                $goodsTypeArray = [
                    '商品' => implode(',', $text)
                ];
            }
            $logPrimary = array_merge($logPrimary, $goodsTypeArray);
        }
        $logPrimary = array_merge($logPrimary, [
            '参与资格' => ShoppingRewardActivityModel::$memberType[$detail->member_type],
        ]);
        // 等级
        $idLevelOrGroup = ShoppingRewardActivityMemberRuleModel::find()->where(['activity_id' => $id])->get();
        $idLevelOrGroup = array_column($idLevelOrGroup, 'level_or_group_id');
        if ($detail->member_type == 1) {
            $level = MemberLevelModel::find()->where(['id' => $idLevelOrGroup])->get();
            $text = array_column($level, 'level_name');
            $memberTypeArray = [
                '会员等级' => implode(',', $text)
            ];
        } else if ($detail->member_type == 2) {
            // 标签
            $group = MemberGroupModel::find()->where(['id' => $idLevelOrGroup])->get();
            $text = array_column($group, 'group_name');
            $memberTypeArray = [
                '会员标签' => implode(',', $text)
            ];
        }
        if (!empty($memberTypeArray)) {
            $logPrimary = array_merge($logPrimary, $memberTypeArray);
        }
        $logPrimary = array_merge($logPrimary, [
            '发送时间结点' => $detail->send_type ? '订单完成' : '下单支付成功',
            '优惠奖励' => $rewardText,
        ]);

        if (!empty($detail->coupon_ids)) {
            $couponIds = explode(',', $detail->coupon_ids);
            $couponInfo = CouponModel::getCouponInfo($couponIds);
            $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
        }
        $reward = explode(',', $detail->reward);
        foreach ($reward as $item) {
            if ($item == ShoppingRewardActivityConstant::REWARD_COUPON) {
                $logPrimary = array_merge($logPrimary, [
                    '优惠券名称' => $couponTitle ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_CREDIT) {
                // 积分
                $logPrimary = array_merge($logPrimary, [
                    '积分' => $detail->credit ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_BALANCE) {
                // 余额
                $logPrimary = array_merge($logPrimary, [
                    '余额' => $detail->balance ?: '-',
                ]);
            }
        }
        // 领取次数
        if ($detail->pick_times_type == 0) {
            $pickTimesText = '不限制';
        } else if ($detail->pick_times_type == 1) {
            $pickTimesText = '每人活动期间最多领取' . $detail->pick_times_limit . '次';
        } else {
            $pickTimesText = '每人每天最多领取' . $detail->pick_times_limit . '次';
        }
        $logPrimary = array_merge($logPrimary, [
            '领取次数' => $pickTimesText
        ]);

        LogModel::write(
            $this->userId,
            ShoppingRewardActivityLogConstant::ACTIVITY_EDIT,
            ShoppingRewardActivityLogConstant::getText(ShoppingRewardActivityLogConstant::ACTIVITY_EDIT),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    ShoppingRewardActivityLogConstant::ACTIVITY_ADD,
                    ShoppingRewardActivityLogConstant::ACTIVITY_EDIT,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 手动停止
     * @throws ShoppingRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualStop()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new ShoppingRewardException(ShoppingRewardException::MANUAL_STOP_PARAMS_ERROR);
        }
        $detail = ShoppingRewardActivityModel::findOne(['id' => $id, 'is_deleted' => 0]);
        if (empty($detail)) {
            throw new ShoppingRewardException(ShoppingRewardException::MANUAL_STOP_ACTIVITY_NOT_EXISTS);
        }
        // 活动状态错误
        if ($detail->status != 0) {
            throw new ShoppingRewardException(ShoppingRewardException::MANUAL_STOP_ACTIVITY_STATUS_ERROR);
        }
        $detail->status = -2;
        $detail->stop_time = DateTimeHelper::now();

        if (!$detail->save()) {
            throw new ShoppingRewardException(ShoppingRewardException::MANUAL_STOP_ACTIVITY_FAIL);
        }

        // 删除任务
        QueueHelper::remove($detail->job_id);

        // 记录日志
        LogModel::write(
            $this->userId,
            ShoppingRewardActivityLogConstant::ACTIVITY_MANUAL_STOP,
            ShoppingRewardActivityLogConstant::getText(ShoppingRewardActivityLogConstant::ACTIVITY_MANUAL_STOP),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => [
                    'id' => $detail->id,
                    '活动名称' => $detail->title,
                    '停止时间' => $detail->stop_time,
                ],
                'dirty_identify_code' => [
                    ShoppingRewardActivityLogConstant::ACTIVITY_MANUAL_STOP,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 删除任务
     * @throws ShoppingRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new ShoppingRewardException(ShoppingRewardException::DELETE_PARAMS_ERROR);
        }
        $detail = ShoppingRewardActivityModel::findOne(['id' => $id, 'is_deleted' => 0]);
        if (empty($detail)) {
            throw new ShoppingRewardException(ShoppingRewardException::DELETE_REWARD_NOT_EXISTS);
        }
        $detail->is_deleted = 1;
        if (!$detail->save()) {
            throw new ShoppingRewardException(ShoppingRewardException::DELETE_FAIL);
        }

        QueueHelper::remove($detail->job_id);

        // 记录日志
        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($detail->client_type));
        $clientType = array_intersect_key(ShoppingRewardActivityModel::$clientType, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
        // 奖励文字
        $reward = array_flip(explode(',', $detail->reward));
        // 交集取文字
        $rewardText = implode('、', array_intersect_key(ShoppingRewardActivityModel::$rewardText, $reward));

        $logPrimary = [
            'id' => $detail->id,
            '活动名称' => $detail->title,
            '活动时间' => $detail->start_time . '~' . $detail->end_time,
            '渠道' => $clientTypeText,
            '指定商品' => ShoppingRewardActivityModel::$goodsType[$detail->goods_type],
        ];
        if ($detail->goods_type != 0) {
            // 获取id
            $goodsTypeData = ShoppingRewardActivityGoodsRuleModel::find()->where(['activity_id' => $id])->get();
            $idGoodsOrCate = array_column($goodsTypeData, 'goods_or_cate_id');
            if ($detail->goods_type == 3) {
                $goodsCate = GoodsCategoryModel::find()->where(['id' => $idGoodsOrCate])->get();
                $text = array_column($goodsCate, 'name');
                $goodsTypeArray = [
                    '商品分类' => implode(',', $text)
                ];
            } else {
                $goods = GoodsModel::find()->where(['id' => $idGoodsOrCate])->get();
                $text = array_column($goods, 'title');
                $goodsTypeArray = [
                    '商品' => implode(',', $text)
                ];
            }
            $logPrimary = array_merge($logPrimary, $goodsTypeArray);
        }
        $logPrimary = array_merge($logPrimary, [
            '参与资格' => ShoppingRewardActivityModel::$memberType[$detail->member_type],
        ]);

        // 等级
        $idLevelOrGroup = ShoppingRewardActivityMemberRuleModel::find()->where(['activity_id' => $id])->get();
        $idLevelOrGroup = array_column($idLevelOrGroup, 'level_or_group_id');
        if ($detail->member_type == 1) {
            $level = MemberLevelModel::find()->where(['id' => $idLevelOrGroup])->get();
            $text = array_column($level, 'level_name');
            $memberTypeArray = [
                '会员等级' => implode(',', $text)
            ];
        } else if ($detail->member_type == 2) {
            // 标签
            $group = MemberGroupModel::find()->where(['id' => $idLevelOrGroup])->get();
            $text = array_column($group, 'group_name');
            $memberTypeArray = [
                '会员标签' => implode(',', $text)
            ];
        }
        if (!empty($memberTypeArray)) {
            $logPrimary = array_merge($logPrimary, $memberTypeArray);
        }
        $logPrimary = array_merge($logPrimary, [
            '发送时间结点' => $detail->send_type ? '订单完成' : '下单支付成功',
            '优惠奖励' => $rewardText,
        ]);

        if (!empty($detail->coupon_ids)) {
            $couponIds = explode(',', $detail->coupon_ids);
            $couponInfo = CouponModel::getCouponInfo($couponIds);
            $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
        }
        $reward = explode(',', $detail->reward);

        foreach ($reward as $item) {
            if ($item == ShoppingRewardActivityConstant::REWARD_COUPON) {
                $logPrimary = array_merge($logPrimary, [
                    '优惠券名称' => $couponTitle ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_CREDIT) {
                // 积分
                $logPrimary = array_merge($logPrimary, [
                    '积分' => $detail->credit ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_BALANCE) {
                // 余额
                $logPrimary = array_merge($logPrimary, [
                    '余额' => $detail->balance ?: '-',
                ]);
            }
        }

        // 领取次数
        if ($detail->pick_times_type == 0) {
            $pickTimesText = '不限制';
        } else if ($detail->pick_times_type == 1) {
            $pickTimesText = '每人活动期间最多领取' . $detail->pick_times_limit . '次';
        } else {
            $pickTimesText = '每人每天最多领取' . $detail->pick_times_limit . '次';
        }
        $logPrimary = array_merge($logPrimary, [
            '领取次数' => $pickTimesText
        ]);

        LogModel::write(
            $this->userId,
            ShoppingRewardActivityLogConstant::ACTIVITY_DELETE,
            ShoppingRewardActivityLogConstant::getText(ShoppingRewardActivityLogConstant::ACTIVITY_DELETE),
            $detail->id,
            [
                'log_data' => $detail->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    ShoppingRewardActivityLogConstant::ACTIVITY_DELETE,
                ]
            ]
        );

        return $this->success();
    }

}