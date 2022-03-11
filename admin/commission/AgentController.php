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

namespace shopstar\admin\commission;

use shopstar\services\commission\CommissionAgentService;
use shopstar\components\notice\NoticeComponent;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\exceptions\commission\CommissionAgentException;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionRelationLogModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\bases\KdxAdminApiController;

/**
 * 分销商管理
 * Class AgentController
 * @package apps\commission\manage
 */
class AgentController extends KdxAdminApiController
{
    public $configActions = [
        'allowHeaderActions' => [
            'index',
            'child-list',
        ],
        'postActions' => [
            'change-status',
            'change-level',
            'change-upgrade',
            'change-agent',
        ]
    ];



    /**
     * 分销商列表
     * @return mixed
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $export = RequestHelper::get('export', '0');
        $list = $this->getList(CommissionAgentConstant::ALL_AGENT);
        if ($export == 1) {
            try {
                ExcelHelper::export($list, CommissionAgentModel::$exportField, '分销商数据导出');
            } catch (\Throwable $exception) {
                throw new CommissionAgentException(CommissionAgentException::AGENT_EXPORT_FAIL);
            }
            die;
        }
        return $this->result($list);
    }

    /**
     * 获取列表
     * @param int $status 1已审核  0待审核列表
     * @return array|CommissionAgentModel[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getList(int $status)
    {
        $get = RequestHelper::get();
        $where = [];
        $select = [
            'agent.apply_time', // 申请时间
            'agent.become_time', // 成为分销商时间
            'agent.commission_total', // 累计佣金
            'agent.commission_pay', // 已提现佣金
            'agent.status', // 审核状态
            'level.name as commission_level_name', // 分销等级名称
            'member.nickname', // 昵称
            'member.mobile', // 手机号
            'member.realname', // 真实姓名
            'member.avatar', // 头像
            'member.created_at', // 注册时间
            'agent.agent_id', // 上级ID
            'member.source', // 用户来源
            'member.balance', // 用户余额
            'agent.member_id', // 用户id
            'member.level_id', // 会员等级id
        ];
        // 成为时间范围查询
        if (!empty($get['become_start_time']) && !empty($get['become_end_time'])) {
            $where[] = ['between', "agent.become_time", $get['become_start_time'], $get['become_end_time']];
        }
        // 申请时间范围查询
        if (!empty($get['apply_start_time']) && !empty($get['apply_end_time'])) {
            $where[] = ['between', "agent.apply_time", $get['apply_start_time'], $get['apply_end_time']];
        }
        // 注册时间范围查询
        if (!empty($get['create_start_time']) && !empty($get['create_end_time'])) {
            $where[] = ['between', "member.created_at", $get['create_start_time'], $get['create_end_time']];
        }
        // 分销商等级
        if (!empty($get['commission_level'])) {
            $where[] = ['level.id' => $get['commission_level']];
        }
        // 会员等级等级
        if (!empty($get['member_level'])) {
            $where[] = ['member.level_id' => $get['member_level']];
        }
        // 审核状态 只有待审核有这个条件
        if ($get['audit_status'] != '') {
            $where[] = ['agent.status' => $get['audit_status']];
        } else {
            // 否则 默认
            if (empty($status)) {
                $where[] = ['<>', 'agent.status', CommissionAgentConstant::AGENT_STATUS_SUCCESS];
            } else {
                $where[] = ['agent.status' => CommissionAgentConstant::AGENT_STATUS_SUCCESS];
            }
        }

        // 关键字
        if (!empty($get['keyword'])) {
            $where[] = [
                'or',
                ['like', 'member.nickname', $get['keyword']],
                ['like', 'member.realname', $get['keyword']],
                ['like', 'member.mobile', $get['keyword']],
                ['like', 'member.id', $get['keyword']]
            ];
        }

        $where[] = ['agent.is_deleted' => 0];
        // 排序
        $orderBy = [];
        if (!empty($get['sort'])) {
            $by = SORT_DESC;
            if ($get['by'] == 'asc') {
                $by = SORT_ASC;
            }
            $orderBy[$get['sort']] = $by;
        }

        if ($status == 1) {
            $orderBy['agent.become_time'] = SORT_DESC;
        } else {
            $orderBy['agent.apply_time'] = SORT_DESC;
        }

        // 连表
        $leftJoins = [
            [MemberModel::tableName() . ' member', 'member.id = agent.member_id'],
            [CommissionLevelModel::tableName() . ' level', 'agent.level_id = level.id'],
        ];
        // 待审核需要订单数  关联订单表
        if ($status == CommissionAgentConstant::WAIT_AGENT) {
            $leftJoins[] = [OrderModel::tableName() . ' order', 'member.id = order.member_id and order.is_count = 1 and order.status >= 30'];
            $select[] = 'count(order.id) as order_count';
        }
        $params = [
            'andWhere' => $where,
            'alias' => 'agent',
            'with' => [
                'agentMember' => function ($query) {
                    $query->select('id, nickname, avatar, mobile');
                }
            ],
            'leftJoins' => $leftJoins,
            'orderBy' => $orderBy,
            'select' => $select,
            'groupBy' => 'member.id',
        ];
        // 转换等级
        $levelList = MemberLevelModel::find()
            ->select('id, level_name')
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->indexBy('id')
            ->get();
        // 获取默认会员等级
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();

        // 获取分销层级设置
        $setLevel = CommissionSettings::get('set.commission_level');

        return CommissionAgentModel::getColl($params, [
            'disableSort' => true,
            'pager' => $get['export'] ? false : true,
            'onlyList' => $get['export'] ? true : false,
            'callable' => function (&$row) use ($setLevel, $status, $defaultLevelId, $levelList) {
                // 会员等级名称
                $row['level_name'] = $levelList[$row['level_id']]['level_name'];
                $row['status_text'] = CommissionAgentModel::$statusText[$row['status']];
                $row['agent_name'] = $row['agentMember']['nickname'];
                $row['commission_child'] = CommissionAgentModel::getChildTotal($row['member_id'], 0, null, $setLevel);
                if ($defaultLevelId == $row['level_id']) {
                    $row['is_default_level'] = 1;
                }
            }
        ]);
    }

    /**
     * 取消/通过/拒绝 分销商资格
     * -2取消 1通过 -1拒绝
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $post = RequestHelper::post();
        if (empty($post['member_id']) || $post['status'] == '') {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CANCEL_PARAMS_ERROR);
        }
        // 兼容其他不传数组接口
        if (!is_array($post['member_id'])) {
            $post['member_id'] = [$post['member_id']];
        }
        try {
            switch ($post['status']) {
                case CommissionAgentConstant::AGENT_STATUS_SUCCESS: // 通过 处理升级
                    CommissionAgentService::changeStatusSuccess($post['member_id'], $this->userId);
                    break;
                case CommissionAgentConstant::AGENT_STATUS_REJECT: // 拒绝 无需处理
                    CommissionAgentModel::updateAll(
                        ['status' => CommissionAgentConstant::AGENT_STATUS_REJECT],
                        ['in', 'member_id', $post['member_id']]
                    );
                    foreach ($post['member_id'] as $id) {
                        // 拒绝分销商设置缓存
                        $key = 'show_reject_' .  '_' . $id;
                        \Yii::$app->redis->set($key, DateTimeHelper::now());
                        // 日志
                        LogModel::write(
                            $this->userId,
                            CommissionLogConstant::AGENT_AUDIT,
                            CommissionLogConstant::getText(CommissionLogConstant::AGENT_AUDIT),
                            $id,
                            [
                                'log_data' => ['member_id' => $id, 'status' => -1],
                                'log_primary' => [
                                    '会员ID' => $id,
                                    '分销商状态' => '拒绝',
                                ],
                                'dirty_identity_code' => [
                                    CommissionLogConstant::AGENT_AUDIT,
                                ]
                            ]
                        );
                    }
                    break;
                case CommissionAgentConstant::AGENT_STATUS_CANCEL: // 取消
                    $memberId = $post['member_id'][0];
                    CommissionAgentService::changeStatusCancel($memberId, $this->userId);
                    break;
            }
        } catch (\Throwable $exception) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_STATUS_FAIL, $exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 分销商详情
     * @return array|\yii\web\Response
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_DETAIL_PARAMS_ERROR);
        }

        // 分销商信息
        $commissionMemberInfo = CommissionAgentModel::find()
            ->where([
                'and',
                ['member_id' => $id],
                ['>', 'status', 0]
            ])->first();
        // 不是分销商
        if (empty($commissionMemberInfo)) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_IS_NOT_COMMISSION);
        }

        // 会员信息
        $member = MemberModel::find()
            ->select('id, nickname, avatar')
            ->where(['id' => $id])
            ->asArray()
            ->one();
        // 会员不存在
        if (empty($member)) {
            throw new CommissionAgentException(CommissionAgentException::ANENT_MEMBER_NOT_EXISTS);
        }

        // 获取上级
        $agentInfo = MemberModel::find()
            ->select('id, nickname, avatar')
            ->where(['id' => $commissionMemberInfo['agent_id']])
            ->asArray()
            ->one();

        if (empty($agentInfo)) {
            $agentInfo = [
                'nickname' => '总店'
            ];
        }

        // 获取开启的分销层级
        $setLevel = CommissionSettings::get('set.commission_level');

        // 下级数量统计
        $childCount = CommissionAgentModel::getChildCountInfo($id, $setLevel);

        return $this->result(['child' => $childCount, 'member_info' => $member, 'agent_info' => $agentInfo]);
    }

    /**
     * 下线列表
     * @return array|\yii\web\Response
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChildList()
    {
        $get = RequestHelper::get();
        if (empty($get['id'])) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHILD_LIST_PARAMS_ERROR);
        }
        $select = [
            'member.nickname', // 用户名
            'member.mobile', // 手机号
            'member.realname', // 真实姓名
            'member.avatar', // 头像
            'agent.agent_id', // 上级id
            'agent.level_id', // 分销商等级
            'agent.become_time', // 成为分销商时间
            'member.id', // 用户id
            'member.source', // 用户来源
            'relation.level', // 分销层级
            'level.name level_name', // 等级名称
            'agent.commission_total', // 累计佣金
            'agent.commission_pay', // 已提现佣金
        ];

        $leftJoins = [
            [MemberModel::tableName() . ' member', 'member.id = relation.member_id'],
            [CommissionAgentModel::tableName() . ' agent', 'agent.member_id = member.id'],
            [CommissionLevelModel::tableName() . ' level', 'level.id = agent.level_id']
        ];
        // 获取分销设置
        $setLevel = CommissionSettings::get('set.commission_level');
        $where = [
            ['relation.parent_id' => $get['id']],
            ['<=', 'relation.level', $setLevel], // 最多查找设置级数
        ];
        // 关键字
        if (!empty($get['keyword'])) {
            $where[] = [
                'or',
                ['like', 'member.nickname', $get['keyword']],
                ['like', 'member.realname', $get['keyword']],
                ['like', 'member.mobile', $get['keyword']],
                ['like', 'member.id', $get['keyword']]
            ];
        }
        // 分销层级
        if (!empty($get['level'])) {
            $where[] = ['relation.level' => $get['level']];
        }
        // 分销商等级
        if (!empty($get['agent_level'])) {
            $where[] = ['agent.level_id' => $get['agent_level']];
        }
        // 是否为分销商
        if ($get['is_agent'] != '') {
            if ($get['is_agent'] == 1) {
                $where[] = ['>', 'agent.status', 0];
            } else {
                $where[] = [
                    'or',
                    ['agent.status' => null],
                    ['<=', 'agent.status', 0],
                ];
            }

        }
        // 成为分销商时间
        if (!empty($get['become_start_time']) && !empty($get['become_end_time'])) {
            $where[] = ['between', "agent.become_time", $get['become_start_time'], $get['become_end_time']];
        }

        $params = [
            'andWhere' => $where,
            'alias' => 'relation',
            'leftJoins' => $leftJoins,
            'orderBy' => ['relation.child_time' => SORT_DESC],
            'select' => $select,
        ];

        $setLevel = CommissionSettings::get('set.commission_level');
        $list = CommissionRelationModel::getColl($params, [
            'pager' => (bool)($get['export'] == 0 ? 1 : 0),
            'callable' => function (&$row) use ($setLevel) {
                $row['commission_child'] = CommissionAgentModel::getChildTotal($row['id'], 0, null, $setLevel);
            }
        ]);

        if ($get['export']) {
            ExcelHelper::export((array)$list['list'], [
                [
                    'field' => 'id',
                    'title' => '会员id',
                    'width' => 18,
                ],
                [
                    'field' => 'nickname',
                    'title' => '会员昵称',
                    'width' => 18,
                ],
                [
                    'field' => 'level_name',
                    'title' => '分销商等级',
                    'width' => 18,
                ],
                [
                    'field' => 'become_time',
                    'title' => '成为分销商时间',
                    'width' => 18,
                ],
                [
                    'field' => 'commission_total',
                    'title' => '累计佣金',
                    'width' => 18,
                ],
                [
                    'field' => 'commission_pay',
                    'title' => '已提现佣金',
                    'width' => 18,
                ],
                [
                    'field' => 'commission_child',
                    'title' => '下线总数',
                    'width' => 18,
                ]
            ], '分销关系导出');
        }

        return $this->result($list);
    }

    /**
     * 修改分销等级
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeLevel()
    {
        $levelId = RequestHelper::post('level_id');
        $memberId = RequestHelper::post('member_id');
        if (empty($memberId) || $levelId == '') {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_LEVEL_PARAMS_ERROR);
        }
        try {
            CommissionAgentModel::updateAll(['level_id' => $levelId], ['member_id' => $memberId]);

        } catch (\Throwable $exception) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_LEVEL_FAIL, $exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 修改自动升级
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeUpgrade()
    {
        $res = CommissionAgentModel::easySwitch('is_auto_upgrade', [
            'afterAction' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    CommissionLogConstant::AGENT_CHANGE_AUTO_UPGRADE,
                    CommissionLogConstant::getText(CommissionLogConstant::AGENT_CHANGE_AUTO_UPGRADE),
                    $model->member_id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => [
                            '会员id' => $model->member_id,
                            '是否自动升级' => $model->is_auto_upgrade ? '是' : '否',
                        ],
                        'dirty_identity_code' => [
                            CommissionLogConstant::AGENT_CHANGE_AUTO_UPGRADE
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AUTO_UPGRADE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 修改上级分销商
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeAgent()
    {
        $agentId = RequestHelper::post('agent_id');
        $memberId = RequestHelper::post('member_id');
        if (empty($memberId) || $agentId == '') {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AGENT_PARAMS_ERROR);
        }
        // 查找上下线关系 修改的上线 是 本来的下线 则不能修改
        $isHaveRelation = CommissionRelationModel::find()->where(['member_id' => $agentId, 'parent_id' => $memberId])->exists();
        if ($isHaveRelation) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AGENT_HAVE_RELATION);
        }

        // 查找当前的1级关系
        $relation = CommissionRelationModel::find()->where(['member_id' => $memberId, 'level' => 1])->first();

        $member = MemberModel::findOne(['id' => $memberId]);
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 设置邀请人
            $member->inviter = $agentId;
            $member->invite_time = DateTimeHelper::now();
            if ($member->save() === false) {
                return error('设置失败，' . $member->getErrorMessage());
            }
            // 获取之前的上级
            $oldAgent = CommissionAgentTotalModel::getOldAgentChildCount($memberId);
            // 更新所有上级关系
            CommissionRelationModel::modify($memberId, $agentId);

            // 如果该用户是分销商
            if (CommissionAgentModel::isAgent($memberId)) {
                // 更新上级
                CommissionAgentModel::updateAll(['agent_id' => $agentId], ['member_id' => $memberId]);
                // 更新所有上级的下级分销商数量
                CommissionAgentTotalModel::updateAgentChildCount($memberId, $oldAgent);

                // 如果是分销商

            }

            // 新增下线通知
            $agent = MemberModel::findOne(['id' => $agentId]);
            // 如果是分销商 发送下级通知
            if ($isAgent = CommissionAgentModel::isAgent($memberId)) {
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

            // 记录分销关系log
            $logData = [];
            // 如果之前有绑定关系, 则生成2条换绑(解绑,绑定)信息
            if ($relation) {
                // 只有parent_id 与 old_parent_id 不一致时才记录更改lgo
                if ($agentId != $relation['parent_id']) {
                    $data = [
                        'member_id' => $memberId,
                        'parent_id' => $agentId,
                        'old_parent_id' => $relation['parent_id'],
                        'is_agent' => $isAgent,
                    ];
                    $logData[] = array_merge($data, [
                        'is_find' => 0,// 换绑-解绑 按member_id查询不需要获取
                        'type' => CommissionRelationLogConstant::TYPE_MANUAL_CHANGE_UNBIND,// 手动换绑-解绑
                    ]);

                    $logData[] = array_merge($data, [
                        'is_find' => 1,// // 换绑-绑定 按member_id查询需要获取
                        'type' => CommissionRelationLogConstant::TYPE_MANUAL_CHANGE_BIND,// 手动换绑-绑定
                    ]);
                }
            } else {
                //绑定的
                $logData[] = [
                    'member_id' => $memberId,
                    'parent_id' => $agentId,
                    'is_agent' => $isAgent,
                    'old_parent_id' => 0,
                    'type' => CommissionRelationLogConstant::TYPE_MANUAL_BIND,// 手动绑定
                ];
            }
            if (!empty($logData)) {
                CommissionRelationLogModel::saveLog($logData);
            }

            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::AGENT_CHANGE_AGENT,
                CommissionLogConstant::getText(CommissionLogConstant::AGENT_CHANGE_AGENT),
                $memberId,
                [
                    'log_data' => [
                        'member_id' => $memberId,
                        'agent_id' => $agentId,
                    ],
                    'log_primary' => [
                        '会员id' => $memberId,
                        '上级id' => $agentId,
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::AGENT_CHANGE_AGENT,
                    ]
                ]
            );
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AGENT_FAIL, $exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 手动设置分销商
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualAgent()
    {
        $memberId = RequestHelper::get('id');
        if (empty($memberId)) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_MANUAL_PARAMS_ERROR);
        }

        $res = CommissionAgentService::manualAgent($memberId, $this->userId);
        if (is_error($res)) {
            throw new CommissionAgentException(CommissionAgentException::AGENT_MANUAL_FAIL, $res['message']);
        }
        return $this->success();
    }

    /**
     * 解绑上级分销商
     * @return array|\yii\web\Response
     * @throws CommissionAgentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUnbind()
    {

        $agentId = RequestHelper::post('agent_id', '0');
        $memberId = RequestHelper::post('member_id');
        if (empty($memberId) || $agentId == '') {
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AGENT_PARAMS_ERROR);
        }
        $member = MemberModel::findOne(['id' => $memberId]);
        // 为什么事务不进行封装 有什么特殊原因吗
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 删除上级  设置邀请人为0
            $member->inviter = $agentId;
            $member->invite_time = '0000-00-00 00:00:00';
            if ($member->save() === false) {
                return error('设置失败，' . $member->getErrorMessage());
            }
            // 获取之前的上级
            $oldAgent = CommissionAgentTotalModel::getOldAgentChildCount($memberId);
            // 更新所有上级关系
            CommissionRelationModel::modify($memberId, $agentId);

            // 如果该用户是分销商
            if (CommissionAgentModel::isAgent($memberId)) {
                // 更新上级
                CommissionAgentModel::updateAll(['agent_id' => $agentId], ['member_id' => $memberId]);
                // 更新所有上级的下级分销商数量
                CommissionAgentTotalModel::updateAgentChildCount($memberId, $oldAgent);
            }
            $transaction->commit();

            // 记录分销关系log
            $logData[] = [
                'member_id' => $memberId,
                'parent_id' => 0,
                'old_parent_id' => $oldAgent[0] ?? 0,
                'is_agent' => CommissionAgentModel::isAgent($memberId),
                'type' => CommissionRelationLogConstant::TYPE_MANUAL_UNBIND,
            ];
            CommissionRelationLogModel::saveLog($logData);

            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::AGENT_UNBIND_AGENT,
                CommissionLogConstant::getText(CommissionLogConstant::AGENT_UNBIND_AGENT),
                $memberId,
                [
                    'log_data' => [
                        'member_id' => $memberId,
                        'agent_id' => $agentId,
                    ],
                    'log_primary' => [
                        '会员id' => $memberId,
                        '上级id' => $agentId,
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::AGENT_UNBIND_AGENT,
                    ]
                ]
            );

        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new CommissionAgentException(CommissionAgentException::AGENT_CHANGE_AGENT_FAIL, $exception->getMessage());
        }
        return $this->success();

    }

}