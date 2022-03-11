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


namespace shopstar\services\shoppingReward;

use shopstar\jobs\shoppingReward\AutoStopShoppingRewardJob;
use shopstar\bases\service\BaseService;
use shopstar\constants\shoppingReward\ShoppingRewardActivityConstant;
use shopstar\constants\shoppingReward\ShoppingRewardActivityLogConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shoppingReward\ShoppingRewardActivityModel;
use yii\helpers\Json;

class ShoppingRewardActivityService extends BaseService
{
    /**
     * 添加活动
     * @param array $data
     * @param int $uid
     * @return array|bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function addActivity(array $data, int $uid)
    {

        $delay = strtotime($data['end_time']) - time();
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
        $isExists = ShoppingRewardActivityModel::checkExistsByTime($data['start_time'], $data['end_time'], 0, $data['goods_id']);
        // 如果该时间段内有活动
        if ($isExists) {
            return error('存在已参加购物奖励活动的商品');
        }
        // 渠道不能为空
        if (empty($data['client_type'])) {
            return error('渠道不能为空');
        }
        // 商品限制
        if ($data['goods_type'] == '') {
            return error('商品限制不能为空');
        }
        // 商品
        if (($data['goods_type'] == 1 || $data['goods_type'] == 2) && empty($data['goods_id'])) {
            return error('商品不能为空');
        } else if ($data['goods_type'] == 3 && empty($data['goods_cate_id'])) {
            // 商品分类
            return error('商品分类不能为空');
        }
        // 参与资格
        if ($data['member_type'] == '') {
            return error('参与资格不能为空');
        }
        // 等级限制
        if ($data['member_type'] == 1 && empty($data['member_level_id'])) {
            return error('会员等级不能为空');
        } else if ($data['member_type'] == 2 && empty($data['member_group_id'])) {
            return error('会员标签不能为空');
        }
        $reward = explode(',', $data['reward']);
        if (empty($reward)) {
            return error('至少选择一种奖励');
        }
        // 校验奖励
        foreach ($reward as $item) {
            if ($item == ShoppingRewardActivityConstant::REWARD_COUPON) {
                // 优惠券
                $couponIds = explode(',', $data['coupon_ids']);
                if (empty($couponIds)) {
                    return error('请选择优惠券');
                }
                if (count($couponIds) > 3) {
                    return error('最多选择三张优惠券');
                }
            } else if ($item == ShoppingRewardActivityConstant::REWARD_CREDIT) {
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
            } else if ($item == ShoppingRewardActivityConstant::REWARD_BALANCE) {
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
            } elseif ($item == ShoppingRewardActivityConstant::REWARD_RED_PACKAGE) {
                //
                if (empty($data['red_package']['money'])) {
                    return error('金额不能为空');
                }
                if (empty($data['red_package']['expiry'])) {
                    return error('过期天数不能为空');
                }
//                if (empty($data['red_package']['blessing'])) {
//                    return error('祝福语不能为空');
//                }

                //json串
                $data['red_package'] = Json::encode($data['red_package']);
            }
        }


        // 保存
        // 商品限制
        if ($data['goods_type'] == ShoppingRewardActivityConstant::GOODS_TYPE_ALLOW_GOODS || $data['goods_type'] == ShoppingRewardActivityConstant::GOODS_TYPE_NOT_ALLOW_GOODS) {
            $idGoodsOrCate = $data['goods_id'];
            unset($data['goods_id']);
        } else if ($data['goods_type'] == ShoppingRewardActivityConstant::GOODS_TYPE_ALLOW_CATE) {
            // 商品分类限制
            $idGoodsOrCate = $data['goods_cate_id'];
            unset($data['goods_cate_id']);
        }
        // 会员限制
        if ($data['member_type'] == ShoppingRewardActivityConstant::MEMBER_LEVEL_LIMIT) {
            $idLevelOrGroup = $data['member_level_id'];
            unset($data['member_level_id']);
        } else if ($data['member_type'] == ShoppingRewardActivityConstant::MEMBER_GROUP_LIMIT) {
            $idLevelOrGroup = $data['member_group_id'];
            unset($data['member_group_id']);
        }

        $activity = new ShoppingRewardActivityModel();
        $activity->setAttributes($data);
        if (!$activity->save()) {
            return error('保存失败');
        }
        // 保存商品限制
        if (!empty($idGoodsOrCate)) {
            $res = ShoppingRewardActivityModel::saveGoodsRule($activity->id, $idGoodsOrCate);
            if (is_error($res)) {
                return $res;
            }
        }
        // 保存会员限制
        if (!empty($idLevelOrGroup)) {
            $res = ShoppingRewardActivityModel::saveMemberRule($activity->id, $idLevelOrGroup);
            if (is_error($res)) {
                return $res;
            }
        }

        // 添加任务
        $jobId = QueueHelper::push(new AutoStopShoppingRewardJob([
            'id' => $activity->id,
        ]), $delay);
        $activity->job_id = $jobId;
        $activity->save();

        // 记录日志
        // 拼装渠道
        $clientTypeArray = array_flip(StringHelper::explode($activity->client_type));
        $clientType = array_intersect_key(ShoppingRewardActivityModel::$clientType, $clientTypeArray);
        $clientTypeText = implode('、', $clientType);
        // 奖励文字
        $reward = array_flip(explode(',', $activity->reward));
        // 交集取文字
        $rewardText = implode('、', array_intersect_key(ShoppingRewardActivityModel::$rewardText, $reward));

        $logPrimary = [
            'id' => $activity->id,
            '活动名称' => $activity->title,
            '活动时间' => $activity->start_time . '~' . $activity->end_time,
            '弹框样式' => ShoppingRewardActivityModel::$popupType[$activity->popup_type],
            '渠道' => $clientTypeText,
            '指定商品' => ShoppingRewardActivityModel::$goodsType[$activity->goods_type],
        ];
        if ($activity->goods_type != 0) {
            if ($activity->goods_type == 3) {
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
            '参与资格' => ShoppingRewardActivityModel::$memberType[$activity->member_type],
        ]);
        // 等级
        if ($activity->member_type == 1) {
            $level = MemberLevelModel::find()->where(['id' => $idLevelOrGroup])->get();
            $text = array_column($level, 'level_name');
            $memberTypeArray = [
                '会员等级' => implode(',', $text)
            ];
        } else if ($activity->member_type == 2) {
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
            '发送时间结点' => $activity->send_type ? '订单完成' : '下单支付成功',
            '优惠奖励' => $rewardText,
        ]);

        if (!empty($activity->coupon_ids)) {
            $couponIds = explode(',', $activity->coupon_ids);
            $couponInfo = CouponModel::getCouponInfo($couponIds);
            $couponTitle = implode(',', array_column($couponInfo, 'coupon_name'));
        }
        $reward = explode(',', $activity->reward);
        foreach ($reward as $item) {
            if ($item == ShoppingRewardActivityConstant::REWARD_COUPON) {
                $logPrimary = array_merge($logPrimary, [
                    '优惠券名称' => $couponTitle ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_CREDIT) {
                // 积分
                $logPrimary = array_merge($logPrimary, [
                    '积分' => $activity->credit ?: '-',
                ]);
            } else if ($item == ShoppingRewardActivityConstant::REWARD_BALANCE) {
                // 余额
                $logPrimary = array_merge($logPrimary, [
                    '余额' => $activity->balance ?: '-',
                ]);
            }
        }
        // 领取次数
        if ($activity->pick_times_type == 0) {
            $pickTimesText = '不限制';
        } else if ($activity->pick_times_type == 1) {
            $pickTimesText = '每人活动期间最多领取' . $activity->pick_times_limit . '次';
        } else {
            $pickTimesText = '每人每天最多领取' . $activity->pick_times_limit . '次';
        }
        $logPrimary = array_merge($logPrimary, [
            '领取次数' => $pickTimesText
        ]);
        LogModel::write(
            $uid,
            ShoppingRewardActivityLogConstant::ACTIVITY_ADD,
            ShoppingRewardActivityLogConstant::getText(ShoppingRewardActivityLogConstant::ACTIVITY_ADD),
            $activity->id,
            [
                'log_data' => $activity->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    ShoppingRewardActivityLogConstant::ACTIVITY_ADD,
                    ShoppingRewardActivityLogConstant::ACTIVITY_EDIT,
                ]
            ]
        );

        return true;
    }

}