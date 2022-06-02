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

use Exception;
use shopstar\bases\KdxAdminApiController;
use shopstar\components\notice\NoticeComponent;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\components\payment\base\WithdrawOrderTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\commission\CommissionApplyStatusConstant;
use shopstar\constants\commission\CommissionApplyTypeConstant;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\MemberTypeConstant;
use shopstar\exceptions\commission\CommissionApplyException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * 佣金提现管理
 * Class ApplyController
 * @package shopstar\admin\commission
 */
class ApplyController extends KdxAdminApiController
{

    /**
     * @var array 需要POST的Action
     */
    public $configActions = [
        'postActions' => [
            'check-agreed',
            'check-again',
            'check-refuse',
            'remit',
            'manual-remit',
        ],
        'allowHeaderActions' => [
            'get-wait-check-list',
            'get-wait-remit-list',
            'get-success-list',
            'get-invalid-list',
        ]
    ];

    /**
     * @var array[]
     */
    public $columns = [
        ['title' => '提现单号', 'field' => 'apply_no', 'width' => 12],
        ['title' => '会员ID', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 24],
        ['title' => '分销商等级', 'field' => 'agent_level_name', 'width' => 18],
        ['title' => '提现方式', 'field' => 'type', 'width' => 18],
        ['title' => '申请佣金', 'field' => 'apply_commission', 'width' => 24],
        ['title' => '手续费', 'field' => 'charge_deduction', 'width' => 24],
        ['title' => '实际提现佣金', 'field' => 'final_commission', 'width' => 24],
        ['title' => '申请时间', 'field' => 'apply_time', 'width' => 24],
        ['title' => '审核通过时间', 'field' => 'check_time', 'width' => 24],
        ['title' => '打款时间', 'field' => 'pay_time', 'width' => 24],
        ['title' => '提现姓名', 'field' => 'realname', 'width' => 24],
        ['title' => '支付宝账号', 'field' => 'alipay', 'width' => 24],
    ];

    /**
     * 获取初始化数据
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionInitList()
    {
        return $this->result([
            'level_list' => CommissionLevelModel::getSimpleList(),
            'type_list' => CommissionApplyTypeConstant::getList('type'),
        ]);
    }

    /**
     * 获取待审核列表
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionGetWaitCheckList()
    {
        return $this->list([
            'apply.status' => CommissionApplyStatusConstant::STATUS_DEFAULT,
        ]);
    }

    /**
     * 获取待打款列表
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionGetWaitRemitList()
    {
        return $this->list([
            'apply.status' => CommissionApplyStatusConstant::STATUS_CHECK_AGREED
        ]);
    }

    /**
     * 获取打款成功列表
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionGetSuccessList()
    {
        return $this->list([
            'apply.status' => [
                CommissionApplyStatusConstant::STATUS_REMIT_SUCCESS,
                CommissionApplyStatusConstant::STATUS_REMIT_MANUAL,
            ]
        ]);
    }

    /**
     * 获取无效列表
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionGetInvalidList()
    {
        return $this->list([
            'apply.status' => [
                CommissionApplyStatusConstant::STATUS_CHECK_REFUSE,
                CommissionApplyStatusConstant::STATUS_INVALID,
            ]
        ]);
    }

    /**
     * 公用获取列表
     * @param array $andWhere
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    private function list(array $andWhere = [])
    {
        $export = RequestHelper::get('export', 0);

        $params = [
            'alias' => 'apply',
            'leftJoins' => [
                [MemberModel::tableName() . 'as member', 'member.id = apply.member_id'],
                [CommissionAgentModel::tableName() . 'as agent', 'agent.member_id = apply.member_id'],
                [CommissionLevelModel::tableName() . 'as level', 'level.id = agent.level_id'],
            ],
            'where' => [],
            'andWhere' => [
                $andWhere,
            ],
            'select' => [
                'apply.id',
                'apply.member_id',
                'member.nickname',
                'member.avatar',
                'member.mobile',
                'apply.apply_no',
                'apply.client_type',
                'apply.type',
                'apply.status',
                'apply.apply_commission',
                'apply.final_commission',
                'apply.charge_deduction',
                'apply.apply_time',
                'apply.check_time',
                'agent.level_id as agent_level',
                'level.name as agent_level_name',
            ],
            'searchs' => [
                ['apply.type', 'int', 'type'],
                ['agent.level_id', 'int', 'level_id'],
                [['member.nickname', 'member.realname', 'member.mobile'], 'like', 'keywords'],
                ['apply.apply_time', 'between', 'apply_time'],
            ],
            'orderBy' => ['apply.id' => SORT_DESC]
        ];

        $options = [
            'callable' => function (&$row) use ($export) {
                $row['client_type_text'] = ClientTypeConstant::getText($row['client_type']);
                if ($export) {
                    $row['type'] = CommissionApplyTypeConstant::getMessage($row['type']);
                }
            },
            'pager' => !$export,
            'onlyList' => (bool)$export,

        ];

        // 获取列表
        $result = CommissionApplyModel::getColl($params, $options);

        // 导出
        if ($export) {
            try {
                $columns = $this->columns;

                ExcelHelper::export($result, $columns, '提现数据导出');
            } catch (\Throwable $exception) {
                throw new CommissionApplyException(CommissionApplyException::APPLY_EXPORT_FAIL);
            }
            die;
        }

        return $this->result($result);
    }

    /**
     * 同意申请
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionCheckAgreed()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGREED_PARAMS_ID_EMPTY);
        }

        // 查找提现记录
        $apply = CommissionApplyModel::find()->where([
            'id' => $id,
        ])->all();
        if (empty($apply)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGREED_RECORD_NOT_FOUND);
        }

        $now = DateTimeHelper::now();

        $successCount = 0;

        foreach ($apply as $item) {

            // 如果状态不是待审核则返回
            if ($item->status != CommissionApplyStatusConstant::STATUS_DEFAULT) {
                continue;
            }

            // 更新审核状态、审核时间、审核佣金
            $item->setAttributes([
                'status' => CommissionApplyStatusConstant::STATUS_CHECK_AGREED,
                'check_time' => $now,
                'check_commission' => $item['final_commission'],
            ]);

            if ($item->save()) {
                $successCount++;
            }

            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::WITHDRAW_AGREE,
                CommissionLogConstant::getText(CommissionLogConstant::WITHDRAW_AGREE),
                $item->id,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => [
                        'id' => $item->id,
                        '提现编号' => $item->apply_no,
                        '审核操作' => '通过'
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::WITHDRAW_AGREE,
                    ]
                ]
            );
        }

        return $this->result([
            'success_count' => $successCount,
        ]);
    }

    /**
     * 重新审核
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionCheckAgain()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGAIN_PARAM_ID_EMPTY);
        }

        // 查找提现记录
        /**
         * @var CommissionApplyModel $apply
         */
        $apply = CommissionApplyModel::find()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (empty($apply)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGAIN_RECORD_NOT_FOUND);
        }

        // 判断当前审核状态是否是已经审核
        if ($apply->status != CommissionApplyStatusConstant::STATUS_CHECK_AGREED) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGAIN_APPLY_STATUS_INVALID);
        }

        // 将状态置位默认申请中
        $apply->status = CommissionApplyStatusConstant::STATUS_DEFAULT;
        $apply->check_time = '0000-00-00 00:00:00';

        // 执行更新
        if (!$apply->save()) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_AGAIN_FAIL);
        }

        // 日志
        LogModel::write(
            $this->userId,
            CommissionLogConstant::WITHDRAW_AGREE,
            CommissionLogConstant::getText(CommissionLogConstant::WITHDRAW_AGREE),
            $apply->id,
            [
                'log_data' => $apply->attributes,
                'log_primary' => [
                    'id' => $apply->id,
                    '提现编号' => $apply->apply_no,
                    '审核操作' => '重新审核'
                ],
                'dirty_identity_code' => [
                    CommissionLogConstant::WITHDRAW_AGREE,
                ]
            ]
        );

        return $this->result([
            'id' => $apply->id
        ]);
    }

    /**
     * 拒绝申请
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionCheckRefuse()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_REFUSE_PARAMS_ID_EMPTY);
        }

        // 查找提现记录
        $apply = CommissionApplyModel::find()->where([
            'id' => $id,
        ])->all();
        if (empty($apply)) {
            throw new CommissionApplyException(CommissionApplyException::CHECK_REFUSE_RECORD_NOT_FOUND);
        }

        $now = DateTimeHelper::now();

        $successCount = 0;

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            foreach ($apply as $item) {

                // 如果状态不是待审核则返回
                if ($item->status != CommissionApplyStatusConstant::STATUS_DEFAULT) {
                    continue;
                }

                // 解析出申请的数据，执行返还佣金数量
                $applyData = Json::decode($item['apply_data']);
                // 返还分销佣金
                foreach ($applyData['commission'] as $datum) {
                    $updateRes = CommissionOrderGoodsModel::updateAll(['can_withdraw_commission' => new Expression("can_withdraw_commission + {$datum['commission']}")], ['id' => $datum['id']]);
                    if (!$updateRes) {
                        throw new CommissionApplyException(CommissionApplyException::CHECK_REFUSE_SEND_BACK_COMMISSION_FAIL);
                    }
                }

                // 更新审核状态、审核时间、审核佣金
                $item->setAttributes([
                    'status' => CommissionApplyStatusConstant::STATUS_CHECK_REFUSE,
                    'check_time' => $now,
                ]);

                if ($item->save()) {
                    $successCount++;
                }
                // 日志
                LogModel::write(
                    $this->userId,
                    CommissionLogConstant::WITHDRAW_AGREE,
                    CommissionLogConstant::getText(CommissionLogConstant::WITHDRAW_AGREE),
                    $item->id,
                    [
                        'log_data' => $item->attributes,
                        'log_primary' => [
                            'id' => $item->id,
                            '提现编号' => $item->apply_no,
                            '审核操作' => '拒绝'
                        ],
                        'dirty_identity_code' => [
                            CommissionLogConstant::WITHDRAW_AGREE,
                        ]
                    ]
                );
                // 发送通知 拒绝申请
                $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY_FAIL, [
                    'withdraw_money' => $item->apply_commission,
                    'change_time' => DateTimeHelper::now(),
                ], 'commission');
                if (!is_error($result)) {
                    $result->sendMessage($item->member_id);
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return $this->error($e->getMessage());
        }

        return $this->result([
            'success_count' => $successCount,
        ]);
    }

    /**
     * 执行打款
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionRemit()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CommissionApplyException(CommissionApplyException::REMIT_PARAMS_ID_EMPTY);
        }

        // 查找提现记录
        /** @var CommissionApplyModel $apply */
        $apply = CommissionApplyModel::find()->where([
            'id' => $id,
        ])->one();
        if (empty($apply)) {
            throw new CommissionApplyException(CommissionApplyException::REMIT_RECORD_NOT_FOUND);
        }

        // 验证当前状态
        if ($apply['status'] != CommissionApplyStatusConstant::STATUS_CHECK_AGREED) {
            throw new CommissionApplyException(CommissionApplyException::REMIT_RECORD_STATUS_ERROR);
        }

        switch ($apply['type']) {
            // 提现到余额
            case CommissionApplyTypeConstant::TYPE_BALANCE:
                $result = MemberModel::updateCredit($apply['member_id'], $apply['final_commission'], 0, 'balance', MemberTypeConstant::RECHARGE_CHANGE_TYPE_ADD, '分销商佣金提现打款￥' . $apply['final_commission'], MemberCreditRecordStatusConstant::COMMISSION_STATUS_WITHDRAW);
                break;
            // 提现到微信 支付宝
            case CommissionApplyTypeConstant::TYPE_WECHAT:
            case CommissionApplyTypeConstant::TYPE_ALIPAY:
                $config = [
                    'transfer_fee' => $apply->final_commission,
                    'transfer_desc' => '佣金提现',
                    'transfer_type' => $apply->type,
                    'order_no' => $apply->apply_no,
                    'client_type' => $apply->client_type,
                    'withdraw_order_type' => WithdrawOrderTypeConstant::WITHDRAW_ORDER_COMMISSION
                ];
                // 小程序、公众号支付宝微信都支持
                if ($apply->client_type == ClientTypeConstant::CLIENT_WECHAT || $apply->client_type == ClientTypeConstant::CLIENT_WXAPP) {
                    if ($apply->type == PayTypeConstant::PAY_TYPE_WECHAT) {
                        // 根据设置获取openid
                        // 打款方式是红包  都获取公众号的openid  如果是转账 则根据提现账户打款
                        // 获取设置
                        $settings = ShopSettings::get('sysset.payment.payset');
                        // 企业打款
                        if ($settings['pay_type_commission'] == 1) {
                            if ($apply->client_type == ClientTypeConstant::CLIENT_WXAPP) {
                                $config['openid'] = MemberWxappModel::getOpenId($apply->member_id);
                            } else if ($apply->client_type == ClientTypeConstant::CLIENT_WECHAT) {
                                $config['openid'] = MemberWechatModel::getOpenId($apply->member_id);
                            }
                        } else if ($settings['pay_type_commission'] == 2) {
                            // 红包打款
                            $config['openid'] = MemberWechatModel::getOpenId($apply->member_id);
                            if (empty($config['openid'])) {
                                throw new CommissionApplyException(CommissionApplyException::WITHDRAW_APPLY_NOT_ALLOW_RED_PACK);
                            }
                        }
                    }
                    if ($apply->type == PayTypeConstant::PAY_TYPE_ALIPAY) {
                        $config['alipay'] = $apply->alipay;
                        $config['real_name'] = $apply->realname;
                    }
                }
                // H5只支持支付宝提现
                if ($apply->client_type == ClientTypeConstant::CLIENT_H5) {
                    $config['alipay'] = $apply->alipay;
                    $config['real_name'] = $apply->realname;
                }
                $payDriver = PayComponent::getInstance($config);
                $result = $payDriver->transfer();
                break;
            default:
                throw new CommissionApplyException(CommissionApplyException::WITHDRAW_TYPE_ERROR);
        }

        if (is_error($result)) {
            return $this->error($result);
        }

        // 修改打款状态
        /**
         * @var CommissionApplyModel $apply
         */
        $apply = CommissionApplyModel::find()->where([
            'id' => $id,
        ])->one();
        $now = DateTimeHelper::now();
        // 更新审核状态、审核时间、审核佣金
        $apply->setAttributes([
            'status' => CommissionApplyStatusConstant::STATUS_REMIT_SUCCESS,
            'pay_time' => $now,
            'final_commission' => $apply['final_commission'],
        ]);

        if ($apply->save() === false) {
            throw new CommissionApplyException(CommissionApplyException::WITHDRAW_APPLY_ERROR);
        }

        // 更新用户已打款佣金字段
        CommissionAgentModel::updateAllCounters(
            ['commission_pay' => $apply->final_commission,],
            ['member_id' => $apply->member_id]
        );

        /**
         * @var MemberModel $member
         */
        $member = MemberModel::find()->where(['id' => $apply->member_id])->select(['id', 'nickname'])->one();

        // 发送通知 拒绝申请
        $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_PAY, [
            'withdraw_money' => $apply->apply_commission,
            'change_time' => DateTimeHelper::now(),
            'name' => $member->nickname,
        ], 'commission');
        if (!is_error($result)) {
            $result->sendMessage($apply->member_id);
        }

        // 日志
        LogModel::write(
            $this->userId,
            CommissionLogConstant::WITHDRAW_PAY,
            CommissionLogConstant::getText(CommissionLogConstant::WITHDRAW_PAY),
            $apply->id,
            [
                'log_data' => $apply->attributes,
                'log_primary' => [
                    'id' => $apply->id,
                    '提现编号' => $apply->apply_no,
                    '操作' => '打款'
                ],
                'dirty_identity_code' => [
                    CommissionLogConstant::WITHDRAW_PAY,
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 手动打款
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionManualRemit()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CommissionApplyException(CommissionApplyException::MANUAL_REMIT_PARAMS_ID_EMPTY);
        }

        // 查找提现记录
        $apply = CommissionApplyModel::find()->where([
            'id' => $id,
        ])->all();
        if (empty($apply)) {
            throw new CommissionApplyException(CommissionApplyException::MANUAL_REMIT_RECORD_NOT_FOUND);
        }

        $now = DateTimeHelper::now();

        $successCount = 0;

        foreach ($apply as $item) {

            // 如果状态不是待审核则返回
            if ($item->status != CommissionApplyStatusConstant::STATUS_CHECK_AGREED) {
                continue;
            }

            // 更新审核状态、审核时间、审核佣金
            $item->setAttributes([
                'status' => CommissionApplyStatusConstant::STATUS_REMIT_MANUAL,
                'pay_time' => $now,
            ]);

            if ($item->save()) {
                $successCount++;
            }

            // 更新用户已打款佣金字段
            CommissionAgentModel::updateAllCounters(
                ['commission_pay' => $item->final_commission,],
                ['member_id' => $item->member_id]
            );

            // 日志
            LogModel::write(
                $this->userId,
                CommissionLogConstant::WITHDRAW_PAY,
                CommissionLogConstant::getText(CommissionLogConstant::WITHDRAW_PAY),
                $item->id,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => [
                        'id' => $item->id,
                        '提现编号' => $item->apply_no,
                        '操作' => '手动打款'
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::WITHDRAW_PAY,
                    ]
                ]
            );
        }

        return $this->result([
            'success_count' => $successCount,
        ]);
    }

    /**
     * 获取详情
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 查询提现记录
        $apply = CommissionApplyModel::find()
            ->where([
                'id' => $id,
            ])
            ->select([
                'id',
                'apply_no',
                'member_id',
                'type',
                'status',
                'apply_commission',
                'ladder_commission',
                'charge_deduction',
                'final_commission',
                'apply_time',
                'check_time',
                'pay_time',
                'alipay',
                'realname',
                'apply_data',
                'client_type',
            ])
            ->first();
        if (empty($apply)) {
            return $this->error('提现记录不存在');
        }

        // 客户端类型文字
        $apply['client_type_text'] = ClientTypeConstant::getMessage($apply['client_type']);

        // 获取文字
        $apply['type_text'] = CommissionApplyTypeConstant::getMessage($apply['type']);
        $apply['status_text'] = CommissionApplyStatusConstant::getMessage($apply['status']);

        // 解析出申请的数据
        $applyData = Json::decode($apply['apply_data']);
        $commissionOrderGoodsId = array_column((array)$applyData['commission'], 'id');
        if (empty($commissionOrderGoodsId)) {
            return $this->error('分销订单商品数据错误');
        }


        // 根据提现申请时订单商品ID查询出订单商品信息
        $params = [
            'alias' => 'cog',
            'leftJoins' => [
                [OrderGoodsModel::tableName() . ' as og', 'og.id = cog.order_goods_id'],
                [MemberModel::tableName() . ' as m', 'm.id = og.member_id']
            ],
            'where' => [
                'cog.id' => $commissionOrderGoodsId,
            ],
            'select' => [
                'cog.order_id',
                'cog.id',
                'cog.order_no',
                'og.title',
                'og.option_title',
                'og.thumb',
                'og.created_at',
                'og.price',
                'cog.level',
                'cog.commission',
                'cog.member_id',
                'm.nickname',
            ],
        ];

        $orderGoodsList = CommissionOrderGoodsModel::getColl($params, [
            'pager' => false,
            'onlyList' => true,
            'callable' => function (&$row) use ($applyData) {
                foreach ($applyData['commission'] as $item) {

                    if ($item['id'] == $row['id']) {
                        $row['real_commission'] = $item['commission'];
                    }

                }
            }
        ]);

        // 分销商信息
        $agentInfo = CommissionAgentModel::find()
            ->alias('agent')
            ->leftJoin(MemberModel::tableName() . ' as member', 'member.id = agent.member_id')
            ->leftJoin(CommissionLevelModel::tableName() . ' as level', 'level.id = agent.level_id')
            ->where([
                'agent.member_id' => $apply['member_id'],
            ])
            ->select([
                'member.avatar',
                'member.nickname',
                'member.mobile',
                'agent.commission_total',
                'level.name as level_name',
                'level.commission_1 as level_commission_1',
                'level.commission_2 as level_commission_2',
            ])
            ->first();

        // 待打款佣金
        $agentInfo['wait_remit_commission'] = CommissionAgentTotalModel::getWaitRemitPrice($apply['member_id']);
        // 待入账佣金
        $agentInfo['wait_settlement_commission'] = CommissionAgentTotalModel::getWaitSettlementPrice($apply['member_id']);

        return $this->result([
            'apply_info' => $apply, // 提现信息
            'agent_info' => $agentInfo, // 分销商信息
            'order_info' => $orderGoodsList, // 分销订单信息
            'order_count' => count($orderGoodsList), // 分销订单数量
        ]);
    }

}
