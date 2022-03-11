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


namespace shopstar\services\commission;

use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionRelationLogModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\bases\service\BaseService;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;

class CommissionAgentService extends BaseService
{
    /**
     * 降级
     * @param array $degradeAgent $degradeAgent['member_id'] 分销商会员id  $degradeAgent['level_id'] 当前等级
     * @return array|bool 实际执行的降级分销商
     * @author nizengchao
     */
    public static function degrade(array $degradeAgent = [])
    {
        // 获取全部的等级
        $levels = CommissionLevelModel::getSimpleList([], 'id,level,status', ['level' => SORT_ASC]);
        if (!$levels) {
            return error('获取分销等级失败');
        }

        // 低一级等级与当前等级的映射表
        $degradeLevelMaps = [];

        // 循环判断没一个分销商应该降级到的等级
        foreach ($degradeAgent as $k => $agent) {
            // 0级是最低等级, 不需要降级
            if ($agent['level'] == 0) {
                unset($degradeAgent[$k]);
                continue;
            }

            // 获取当前等级的数组下标, 从而获取上一个开启的等级下标
            foreach ($levels as $index => $level) {
                // 未定位到数据, 则跳过
                if ($level['level'] != $agent['level'] || $level <= 0) {
                    continue;
                }

                $degradeLevel = 0;//初始值
                if (isset($degradeLevelMaps[$agent['level_id']])) {
                    $degradeLevel = $degradeLevelMaps[$agent['level_id']];
                } else {
                    // 获取低一级的已启用的等级
                    for ($i = $index - 1; $i >= 0; $i--) {
                        if ($levels[$i]['status'] != 1) {
                            continue;
                        }
                        $degradeLevel = $levels[$i]['id'];
                        if (!isset($degradeLevelMaps[$agent['level_id']])) {
                            // 加入映射表, 减少循环次数
                            $degradeLevelMaps[$agent['level_id']] = $degradeLevel;
                        }
                        break;
                    }
                }

                // 没有可用的低一等级的分销等级, 则不需要降级
                if (!$degradeLevel) {
                    unset($degradeAgent[$k]);
                    continue;
                }
                $degradeAgent[$k]['degrade_level'] = $degradeLevel;
            }
        }

        // 有数据, 进行降级
        if (!empty($degradeAgent)) {
            foreach ($degradeAgent as $agent) {
                try {
                    CommissionAgentModel::updateAll(['level_id' => $agent['degrade_level']], ['member_id' => $agent['member_id']]);

                } catch (\Throwable $exception) {
                    return error('降级更改数据失败:' . $exception->getMessage());
                }
            }
        }

        return array_values($degradeAgent);
    }


    /**
     * 获取会员分销信息
     * @param int $memberId
     * @param int $inviter
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCommissionInfo(int $memberId, int $inviter = 0)
    {
        $info = [];
        // 获取上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        if (!empty($parentId)) {
            $info['parent_info'] = MemberModel::find()
                ->select('id, nickname, avatar')
                ->where(['id' => $parentId])
                ->asArray()
                ->one();
        }
        // 会员分销信息
        $info['agent_info'] = CommissionAgentModel::find()
            ->select('agent.*, level.name level_name')
            ->alias('agent')
            ->leftJoin(CommissionLevelModel::tableName() . ' level', 'level.id=agent.level_id')
            ->where(['agent.member_id' => $memberId, 'agent.status' => 1])
            ->first();
        // 非分销商
        if (is_null($info['agent_info'])) {
            $info['agent_info'] = ['is_agent' => 0];
        } else {
            // 上级是总店
            if (empty($parentId)) {
                $info['parent_info'] = ['nickname' => '总店'];
            }
            $setLevel = CommissionSettings::get('set.commission_level');
            // 获取下级
            $info['chile_count'] = CommissionAgentModel::getChildCountInfo($memberId, $setLevel);

            $info['agent_info']['wait_commission'] = CommissionAgentTotalModel::getWaitSettlementPrice($memberId); // 待入账佣金
            $info['agent_info']['commission_order'] = CommissionOrderDataModel::getOrderCount($memberId, 0, 0); // 分销订单数量 有效
        }

        // 获取邀请人信息
        if (!empty($inviter)) {
            $info['inviter_info'] = MemberModel::find()
                ->select('id, nickname, avatar')
                ->where(['id' => $inviter])
                ->first();
        }

        return $info;
    }


    /**
     * 注册分销商
     * 成为分销商
     * @param int $memberId
     * @param int $parentId
     * @param bool $isApply
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function register(int $memberId, int $parentId = 0, bool $isApply = false)
    {
        if (empty($memberId)) {
            return error('参数错误');
        }

        // 分销设置
        $set = CommissionSettings::get('set');
        if (empty($set['commission_level'])) {
            return error('未开启分销');
        }

        // 获取分销商信息（包含拒绝）
        $agent = CommissionAgentModel::find()->where(['member_id' => $memberId])->one();
        if (!empty($agent) && $agent->status >= CommissionAgentConstant::AGENT_STATUS_WAIT) {
            return error('已经是分销商或正在申请中');
        }

        // 手动申请
        if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_APPLY && !$isApply) {
            return error('需手动申请');
        } else if ($set['become_condition'] != CommissionAgentConstant::AGENT_BECOME_CONDITION_NO_CONDITION) {
            // 成为分销商条件  无条件的无需处理
            $checkResult = []; // 是否符合
            // 购买商品
            if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_BUY_GOODS) {
                // 检查购买商品是否符合条件
                $checkResult = CommissionAgentModel::checkBuyGoods($memberId, $set);
            } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_MONEY_COUNT) {
                // 消费金额
                $checkResult = CommissionAgentModel::checkMoneyCount($memberId, $set);
            } else if ($set['become_condition'] == CommissionAgentConstant::AGENT_BECOME_CONDITION_PAY_ORDER_COUNT) {
                // 支付订单数量
                $checkResult = CommissionAgentModel::checkPayOrderCount($memberId, $set);
            }
            // 不符合 跳出
            if (is_error($checkResult)) {
                return $checkResult;
            }
        }
        // 取分销默认等级
        $defaultLevel = CommissionLevelModel::getDefaultId();
        if (is_error($defaultLevel)) {
            return $defaultLevel;
        }
        // 获取上级
        if (empty($parentId)) {
            $parentId = CommissionRelationModel::getParentId($memberId);
        }
        // 如果上级不是分销商
        if (!empty($parentId) && !CommissionAgentModel::isAgent($parentId)) {
            $parentId = 0;
        }

        $data = [
            'agent_id' => $parentId,
            'level_id' => $defaultLevel,
            'status' => $set['is_audit'] ? 0 : 1,
            'is_black' => 0,
            'apply_time' => DateTimeHelper::now(),
            'child_time' => DateTimeHelper::now(),
            'is_auto_upgrade' => 1,
        ];
        // 如果不需要审核
        if ($set['is_audit'] != 1) {
            $data['become_time'] = DateTimeHelper::now();
        }
        // 如果是已拒绝的 重置状态
        if (!empty($agent)) {
            $agent->setAttributes($data);
        } else {
            // 新成为
            $agent = new CommissionAgentModel();
            $data['member_id'] = $memberId;
            $agent->setAttributes($data);
        }
        if ($agent->save() === false) {
            return error('成为分销商错误');
        }

        // 成为分销商

        $member = MemberModel::findOne(['id' => $memberId]);
        if (!$set['is_audit']) {
            // 写入缓存
            $key = 'show_success_' .  '_' . $memberId;
            \Yii::$app->redis->set($key, DateTimeHelper::now());

            // 发送通知  买家成为分销商
            $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME, [
                'member_nickname' => $member->nickname,
                'change_time' => DateTimeHelper::now(),
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
            ], 'commission');
            if (!is_error($result)) {
                $result->sendMessage($memberId);
            }
            // 更新上级的下级分销商数量
            if (!empty($parentId)) {
                CommissionAgentTotalModel::updateAgentChildCount($memberId);
                // 新增下级通知 TODO 青岛开店星信息技术有限公司  各级通知
                $parent = MemberModel::findOne(['id' => $parentId]);
                $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD, [
                    'member_nickname' => $parent->nickname,
                    'down_line_nickname' => $member->nickname,
                ]);
                if (!is_error($result)) {
                    $result->sendMessage($parentId);
                }
            }

        } else {
            // 需要审核  发送卖家通知
            $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_SELLER_APPLY, [
                'member_nickname' => $member->nickname,
                'change_time' => DateTimeHelper::now(),
            ]);
            if (!is_error($result)) {
                $result->sendMessage($parentId);
            }
        }

        // 处理上下线关系
        // 处理上级分销商升级
        if (!empty($parentId) && CommissionAgentModel::isAutoUpgrade($parentId)) {
            CommissionLevelService::upgrade($parentId);
        }

        return true;
    }



    /**
     * 手动设置分销商
     * @param int $memberId
     * @param int $uid
     * @return MemberModel|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function manualAgent(int $memberId,  int $uid)
    {
        $member = MemberModel::findOne(['id' => $memberId]);
        if (empty($member)) {
            return error('会员不存在');
        }
        $agent = CommissionAgentModel::findOne(['member_id' => $memberId]);
        // 是分销商则跳出
        if ($agent->status == 1) {
            return error('该会员已经是分销商');
        }
        // 获取上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        // 获取默认分销等级
        $defaultLevel = CommissionLevelModel::find()->select('id')->where(['is_default' => 1])->first();

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            if (empty($agent)) {
                $agent = new CommissionAgentModel();
                $agent->member_id = $memberId;
            }
            $agent->agent_id = $parentId;
            $agent->status = 1;
            $agent->level_id = $defaultLevel['id'];
            $agent->become_time = DateTimeHelper::now();
            $agent->is_auto_upgrade = 1;
            if (!$agent->save()) {
                return error('设置失败，' . $agent->getErrorMessage());
            }

            // 处理分销关系
            CommissionRelationModel::modify($memberId, $parentId);

            // 更新上级的下级分销商数量
            CommissionAgentTotalModel::updateAgentChildCount($memberId);


            // 成为分销商设置缓存
            $key = 'show_success_' .  '_' . $memberId;
            \Yii::$app->redis->set($key, DateTimeHelper::now());

            // 日志
            LogModel::write(
                $uid,
                CommissionLogConstant::AGENT_MANUAL_AGENT,
                CommissionLogConstant::getText(CommissionLogConstant::AGENT_MANUAL_AGENT),
                $memberId,
                [
                    'log_data' => [
                        'member_id' => $memberId,
                    ],
                    'log_primary' => [
                        '会员id' => $memberId,
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::AGENT_MANUAL_AGENT,
                    ]
                ]
            );

            // 发送通知  买家成为分销商
            $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME, [
                'member_nickname' => $member->nickname,
                'change_time' => DateTimeHelper::now(),
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
            ], 'commission');
            if (!is_error($result)) {
                $result->sendMessage($memberId);
            }

            // 新增下级通知
            $parent = MemberModel::findOne(['id' => $parentId]);
            $parentIds = CommissionRelationModel::getAllParentId($member->id);
            if (!empty($parentIds)) {
                foreach ($parentIds as $key => $value) {
                    $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD, [
                        'member_nickname' => $parent->nickname,
                        'change_time' => DateTimeHelper::now(),
                        'down_line_nickname' => $member->nickname,
                    ], 'commission');
                    if (!is_error($result)) {
                        $result->sendMessage([], ['commission_level' => $key, 'member_id' => $value]);
                    }
                }
            }


            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

        }
        return $member;
    }

    /**
     * 通过审核
     * @param array $memberIds
     * @param int $uid
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeStatusSuccess(array $memberIds, int $uid)
    {
        // 获取默认等级id
        $defaultLevel = CommissionLevelModel::getDefaultId();
        if (is_error($defaultLevel)) {
            return $defaultLevel;
        }
        // 修改状态
        CommissionAgentModel::updateAll(
            ['status' => CommissionAgentConstant::AGENT_STATUS_SUCCESS, 'become_time' => DateTimeHelper::now(), 'level_id' => $defaultLevel],
            ['in', 'member_id', $memberIds]
        );
        // 遍历会员处理
        foreach ($memberIds as $memberId) {
            // 通过后写入缓存
            $key = 'show_success_' .  '_' . $memberId;
            \Yii::$app->redis->set($key, DateTimeHelper::now());
            // 更新上级的下级分销商数量
            CommissionAgentTotalModel::updateAgentChildCount($memberId);
            // 发送通知  买家成为分销商
            $member = MemberModel::findOne(['id' => $memberId]);
            $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME, [
                'member_nickname' => $member->nickname,
                'change_time' => DateTimeHelper::now(),
                'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
            ], 'commission');
            if (!is_error($result)) {
                $result->sendMessage($memberId);
            }
            $parentId = CommissionRelationModel::getParentId($memberId);
            // 新增下级通知
            $parent = MemberModel::findOne(['id' => $parentId]);
            $parentIds = CommissionRelationModel::getAllParentId($member->id);
            if (!empty($parentIds)) {
                foreach ($parentIds as $key => $value) {
                    $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD, [
                        'member_nickname' => $parent->nickname,
                        'change_time' => DateTimeHelper::now(),
                        'down_line_nickname' => $member->nickname,
                    ], 'commission');
                    if (!is_error($result)) {
                        $result->sendMessage([], ['commission_level' => $key, 'member_id' => $value]);
                    }
                }
            }

            // 日志
            LogModel::write(
                $uid,
                CommissionLogConstant::AGENT_AUDIT,
                CommissionLogConstant::getText(CommissionLogConstant::AGENT_AUDIT),
                $memberId,
                [
                    'log_data' => ['member_id' => $memberId, 'status' => 1],
                    'log_primary' => [
                        '会员ID' => $memberId,
                        '分销商状态' => '通过',
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::AGENT_AUDIT,
                    ]
                ]
            );
        }


        return true;
    }



    /**
     * 取消分销商资格
     * @param int $memberId
     * @param int $uid
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeStatusCancel(int $memberId,  int $uid)
    {
        $agentInfo = CommissionAgentModel::findOne(['member_id' => $memberId]);
        // 取消
        $agentInfo->status = CommissionAgentConstant::AGENT_STATUS_CANCEL;
        $agentInfo->save();
        // 获取一级下级
        $child = CommissionRelationModel::find()->where(['parent_id' => $memberId, 'level' => 1])->get();
        if (!empty($child)) {
            // 下线变为上级变为总店
            CommissionAgentModel::updateAll(['agent_id' => 0], ['agent_id' => $memberId]);
            $logData = [];

            // 删除关系
            // 处理关系
            foreach ($child as $item) {
                CommissionRelationModel::modify($item['member_id'], 0);
                // 当前关系日志
                $log = [
                    'member_id' => $item['member_id'],
                    'parent_id' => 0,
                    'old_parent_id' => $item['parent_id'],
                    'is_agent' => CommissionAgentModel::isAgent($item['member_id']),
                    'type' => CommissionRelationLogConstant::TYPE_CANCEL_COMMISSION_UNBIND,
                ];
                $logData[] = $log;
            }

            // 保存关系更改日志
            if (!empty($logData)) {
                CommissionRelationLogModel::saveLog($logData);
            }
        }
        // 更新上级的下级分销商数量
        CommissionAgentTotalModel::updateAgentChildCount($memberId);

        // 日志
        LogModel::write(
            $uid,
            CommissionLogConstant::AGENT_CANCEL_AGENT,
            CommissionLogConstant::getText(CommissionLogConstant::AGENT_CANCEL_AGENT),
            $memberId,
            [
                'log_data' => [
                    'member_id' => $memberId,
                    'level_name' => $agentInfo->level->name ?? '-',
                    'commission_total' => $agentInfo->commission_total,
                    'commission_pay' => $agentInfo->commission_pay,
                    'become_time' => $agentInfo->become_time,
                    'status' => CommissionAgentModel::$statusText[$agentInfo->status],
                    'child_id' => implode(',', array_column($child, 'member_id')) ?: '-',
                ],
                'log_primary' => [
                    '会员id' => $memberId,
                    '分销等级' => $agentInfo->level->name ?? '-',
                    '累计佣金' => $agentInfo->commission_total,
                    '已提现佣金' => $agentInfo->commission_pay,
                    '成为分销商时间' => $agentInfo->become_time,
                    '分销状态' => CommissionAgentModel::$statusText[$agentInfo->status],
                    '下级id' => implode(',', array_column($child, 'member_id')) ?: '-',
                ],
                'dirty_identity_code' => [
                    CommissionLogConstant::AGENT_CANCEL_AGENT,
                ]
            ]
        );
    }



    /**
     * 删除分销商
     * @param int $memberId
     * @return array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteAgent(int $memberId)
    {
        $returnData = [];
        // 获取会员分销商记录
        $agent = CommissionAgentModel::findOne(['member_id' => $memberId]);
        // 如果不为空 就可能有下级
        if (!empty($agent)) {
            $returnData = [
                'level_name' => $agent->level->name ?? '-',
                'commission_total' => $agent->commission_total,
                'commission_pay' => $agent->commission_pay,
                'become_time' => $agent->become_time,
                'status' => CommissionAgentModel::$statusText[$agent->status],
                'agent_name' => '总店',
                'agent_id' => 0,
                'child_id' => '-',
            ];
            // 删除该记录
            $agent->is_deleted = 1;
            $agent->save();

            // 关系更改日志
            $logData = [];

            // 获取该用户的一级下级
            $child = CommissionRelationModel::find()->where(['parent_id' => $memberId, 'level' => 1])->get();
            // 如果有一级下级
            if (!empty($child)) {
                $returnData['child_id'] = implode(',', array_column($child, 'member_id'));
                // 更新该用户的下级用户上级为总店
                CommissionAgentModel::updateAll(['agent_id' => 0], ['agent_id' => $memberId]);

                // 处理关系
                foreach ($child as $item) {
                    CommissionRelationModel::modify($item['member_id'], 0);

                    // 当前关系日志
                    $log = [
                        'member_id' => $item['member_id'],
                        'parent_id' => 0,
                        'old_parent_id' => $item['parent_id'],
                        'is_agent' => CommissionAgentModel::isAgent($item['member_id']),
                        'type' => CommissionRelationLogConstant::TYPE_DELETE_MEMBER_UNBIND,
                    ];
                    $logData[] = $log;
                }
            }
        }

        // 获取上级
        $parentId = CommissionRelationModel::getParentId($memberId);
        // 如果有上级
        if (!empty($parentId)) {
            $parent = MemberModel::find()->select('nickname')->where(['id' => $parentId])->first();
            $returnData['agent_name'] = $parent['nickname'];
            $returnData['agent_id'] = $parentId;
            // 先更新该会员上级的下级分销商数量
            CommissionAgentTotalModel::updateAgentChildCount($memberId);
            // 删除该会员的上级关系
            CommissionRelationModel::deleteAll(['member_id' => $memberId]);

            // 当前关系日志
            $log = [
                'member_id' => $memberId,
                'parent_id' => 0,
                'old_parent_id' => $parentId,
                'is_agent' => CommissionAgentModel::isAgent($memberId),
                'type' => CommissionRelationLogConstant::TYPE_DELETE_MEMBER_UNBIND,
            ];
            $logData[] = $log;
            // 上级下线数 -1

        }

        // 保存关系更改日志
        if (!empty($logData)) {
            CommissionRelationLogModel::saveLog($logData);
        }

        return $returnData;
    }
}