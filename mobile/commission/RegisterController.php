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

namespace shopstar\mobile\commission;

use shopstar\components\notice\NoticeComponent;
use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\commission\CommissionAgentException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;

/**
 * 分销商注册
 * Class RegisterController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class RegisterController extends CommissionClientApiController
{
    public $allowAgentActions = [
        'index',
        'submit'
    ];

    /**
     * 申请分销商信息
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = [];

        // 获取分销设置
        $set = CommissionSettings::get('set');
        $data['is_audit'] = $set['is_audit'];
        // 获取会员信息
        $member = MemberModel::findOne(['id' => $this->memberId]);
        // 获取分销商信息
        $agent = CommissionAgentModel::find()->where(['member_id' => $this->memberId])->asArray()->first();
        // 如果分销商信息不为空
        if (!empty($agent)) {
            $data['status'] = $agent['status'];
            // 已经是分销商了 直接返回
            if ($agent['status'] == 1) {
                return $this->success($data);
            } else if ($agent['status'] == -1) {
                // 如果拒绝  判断 没有展示过拒绝页面需要展示
                $key = 'show_reject_' . '_' . $this->memberId;
                if (!empty(\Yii::$app->redis->get($key))) {
                    \Yii::$app->redis->del($key);
                    $data['show_reject'] = 1;
                }
            }
        }
        // 能走到这 说明是等待审核或拒绝状态，不管成为条件是否满足
        $data['become_condition'] = $set['become_condition'];
        $data['banner'] = $set['banner']; // 首图
        $data['show_agreement'] = $set['show_agreement']; // 是否显示协议
        if ($data['show_agreement'] == 1) {
            // 获取协议
            $otherSet = CommissionSettings::get('other');
            $data['agreement_title'] = $otherSet['agreement_title'];
            $data['agreement_content'] = $otherSet['agreement_content'];
        }
        // 获取时间结点
        $data['become_order_status'] = $set['become_order_status'];
        // 订单状态 付款完成后 订单完成后
        if ($data['become_order_status'] == 1) {
            $status = OrderStatusConstant::ORDER_STATUS_WAIT_SEND;
        } else {
            $status = OrderStatusConstant::ORDER_STATUS_SUCCESS;
        }

        // 判断成为分销商类型
        if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_NO_CONDITION) {
            // 无条件 无需判断
        } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_BUY_GOODS) {
            // 购买商品
            $data['goods_info']['goods_ids'] = explode(',', $set['become_goods_ids']);
            $data['goods_info']['member_goods_ids'] = OrderGoodsModel::getMemberOrderGoodsIds($this->memberId, $status, $data['goods_info']['goods_ids']);

        } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_MONEY_COUNT) {
            // 满足消费金额  实际支付价格 + 余额抵扣 - 维权金额
            // 商城设置
            $data['become_order_money'] = $set['become_order_money'];
            // 查找当前数据
            $data['member_order_money'] = OrderModel::getOrderPrice($this->memberId, $status);
        } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_PAY_ORDER_COUNT) {
            // 支付订单数量  支付数量 - 维权数量 （只要有维权就不算
            // 商城设置
            $data['become_order_count'] = $set['become_order_count'];
            // 当前数据
            $data['member_order_count'] = OrderModel::getOrderCount($this->memberId, $status);
        } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_APPLY) {
            // 手动申请
            // 邀请人先取上级
            $relation = CommissionRelationModel::find()
                ->alias('relation')
                ->select([
                    'relation.member_id',
                    'relation.parent_id',
                    'member.nickname'
                ])
                ->leftJoin(MemberModel::tableName() . ' member', 'member.id = relation.parent_id')
                ->where(['relation.member_id' => $this->memberId, 'relation.level' => 1])
                ->first();
            if ($relation) {
                $data['inviter_name'] = $relation['nickname'];
            } elseif (!empty($member->inviter)) {
                // 如果有邀请人
                $inviterInfo = MemberModel::findOne(['id' => $member->inviter]);
                $data['inviter_name'] = $inviterInfo['nickname'];
            }

        }

        return $this->result($data);
    }

    /**
     * 申请
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit()
    {
        $post = RequestHelper::post();
        if (empty($post['name'])) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_EMPTY_NAME);
        }
        if (empty($post['mobile'])) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_EMPTY_MOBILE);
        }
        if (!ValueHelper::isMobile($post['mobile'])) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_MOBILE_ERROR);
        }
        // 申请信息
        $applyData = [
            'name' => $post['name'],
            'mobile' => $post['mobile']
        ];
        // 系统设置
        $set = CommissionSettings::get('set');
        // 申请后的状态
        $status = $set['is_audit'] ? CommissionAgentConstant::AGENT_STATUS_WAIT : CommissionAgentConstant::AGENT_STATUS_SUCCESS;
        // 分销商信息
        $agent = CommissionAgentModel::findOne(['member_id' => $this->memberId]);
        // 用户信息
        $member = MemberModel::findOne(['id' => $this->memberId]);

        if (!empty($agent)) {
            // 已经是分销商
            if ($agent->status == 1) {
                throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_MEMBER_IS_AGENT);
            } else if ($agent->status == 0) {
                // 待审核
                throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_WAIT_AUDIT);
            }
        } else {
            // 无分销商信息 （第一次申请
            $defaultLevelId = CommissionLevelModel::getDefaultId();
            $agent = new CommissionAgentModel();
            $agent->member_id = $this->memberId;
            $agent->level_id = $defaultLevelId;
        }
        $agent->status = $status;
        $status ? $agent->become_time = DateTimeHelper::now() : false;
        $agent->apply_time = DateTimeHelper::now();
        $agent->apply_data = Json::encode($applyData);
        $agent->is_auto_upgrade = 1;

        $parentId = CommissionRelationModel::getParentId($this->memberId);
        if (!empty($parentId)) {
            $agent->agent_id = $parentId;
        }
        // 保存
        if ($agent->save() === false) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_REGISTER_SAVE_FAIL);
        }


        // 需要审核 发送卖家通知
        if ($status == 0) {
            // 发送通知
            $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_SELLER_APPLY, [
                'member_nickname' => $member->nickname ?: $member->mobile,
                'change_time' => DateTimeHelper::now(),
                'name' => $member->nickname,
            ], 'commission');

            if (!is_error($result)) {
                $result->sendMessage();
            }
        } else {

            // 自动成为分销商 更新上级数量
            // 更新上级的下级分销商数量
            CommissionAgentTotalModel::updateAgentChildCount($this->memberId);
            $parentId = CommissionRelationModel::getParentId($this->memberId);
            // 新增下级通知
            $agent = MemberModel::findOne(['id' => $parentId]);
            $parentIds = CommissionRelationModel::getAllParentId($member->id);
            if (!empty($parentIds)) {
                foreach ($parentIds as $key => $value) {
                    $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD, [
                        'member_nickname' => $agent->nickname,
                        'change_time' => DateTimeHelper::now(),
                        'down_line_nickname' => $member->nickname,
                    ], 'commission');
                    if (!is_error($result)) {
                        $result->sendMessage([], ['commission_level' => $key, 'member_id' => $value]);
                    }
                }
            }

        }

        return $this->success(['status' => $status]);
    }
}
