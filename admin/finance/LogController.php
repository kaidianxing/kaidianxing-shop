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

namespace shopstar\admin\finance;


use shopstar\constants\ClientTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberLogPayTypeConstant;
use shopstar\constants\member\MemberLogStatusConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\exceptions\FinanceException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\bases\KdxAdminApiController;
use shopstar\services\member\MemberLogService;

/**
 * 会员充值、提现类
 * Class LogController
 * @package shop\manage\member
 */
class LogController extends KdxAdminApiController
{
    public $configActions = [
        'allowHeaderActions' => [
            'recharge',
            'withdraw',
        ],
        'allowPermActions' => [
            'label'
        ]
    ];

    /**
     * 充值记录
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRecharge()
    {
        //是否是导出
        $export = RequestHelper::getInt('export', 0);

        $params = [
            'searchs' => [
                ['log.created_at', 'between', 'created_at'],
                ['m.level_id', 'int', 'level_id'],
                ['log.status', 'int', 'status'],
                ['log.pay_type', 'int', 'pay_type'],
                [['m.nickname', 'm.realname', 'm.mobile', 'log.log_sn'], 'like', 'keyword']
            ],
            'where' => [
                'log.type' => MemberLogTypeConstant::ORDER_TYPE_RECHARGE,
            ],
            'select' => [
                'log.id as order_id',
                'm.id as member_id',
                'm.avatar',
                'm.mobile',
                'm.nickname',
                'm.realname',
                'm.level_id',
                'm.source',
                'level.level_name',
                'log.money',
                'log.created_at',
                'log.log_sn',
                'log.pay_type',
                'log.status',
                'log.remark',
                'level.is_default'
            ],
            'alias' => 'log',
            'leftJoins' => [
                [MemberModel::tableName() . ' m', 'log.member_id = m.id'],
                [MemberLevelModel::tableName() . ' level', 'm.level_id = level.id'],

            ],
            'orderBy' => [
                'log.created_at' => SORT_DESC
            ]
        ];

        // 获取列表
        $records = MemberLogModel::getColl($params, [
            'callable' => function (&$row) {
                $row = MemberLogModel::decode($row);
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
            },
            'pager' => $export == 0,
            'onlyList' => $export == 1
        ]);

        //如果是导出
        if ($export == 1) {
            $this->exportRecharge($records);
        }

        return $this->result($records);
    }

    private function exportRecharge($records)
    {
        ExcelHelper::export($records, [
            [
                'field' => 'member_id',
                'title' => '会员id',
            ],
            [
                'field' => 'nickname',
                'title' => '昵称',
            ],
            [
                'field' => 'realname',
                'title' => '姓名',
            ],
            [
                'field' => 'level_name',
                'title' => '会员等级',
            ],
            [
                'field' => 'money',
                'title' => '充值金额',
            ],
            [
                'field' => 'created_at',
                'title' => '充值时间',
            ],
            [
                'field' => 'pay_type_text',
                'title' => '充值方式',
            ],
            [
                'field' => 'status_text',
                'title' => '状态',
            ],
            [
                'field' => 'remark',
                'title' => '备注',
            ],


        ], '充值记录导出');

        return true;
    }

    /**
     * 提现申请列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWithdraw()
    {
        //是否是导出
        $export = RequestHelper::getInt('export', 0);

        $params = [
            'searchs' => [
                ['log.created_at', 'between', 'created_at'],
                ['level.id', 'int', 'level_id'],
                ['log.status', 'int', 'status'],
                ['log.id', 'int', 'order_id'],
                ['log.pay_type', 'int', 'pay_type'],
                [['m.nickname', 'm.realname', 'm.mobile'], 'like', 'keyword']
            ],
            'where' => [
                'log.type' => MemberLogTypeConstant::ORDER_FROM_WITHDRAW
            ],
            'select' => [
                'log.id as order_id',
                'm.id as member_id',
                'm.avatar',
                'm.nickname',
                'm.level_id',
                'level.level_name',
                'm.source',
                'log.status',
                'log.pay_type',
                'log.alipay',
                'log.back_name',
                'log.back_card',
                'log.real_name',
                'log.money',
                'log.deduct_money',
                'log.real_money',
                'log.created_at',
                'log.log_sn',
                'level.is_default'
            ],
            'alias' => 'log',
            'leftJoins' => [
                [MemberModel::tableName() . ' m', 'log.member_id = m.id'],
                [MemberLevelModel::tableName() . ' level', 'm.level_id = level.id'],

            ],
            'orderBy' => [
                'log.created_at' => SORT_DESC
            ]
        ];

        // 获取列表
        $records = MemberLogModel::getColl($params, [
            'callable' => function (&$row) {
                $row = MemberLogModel::decode($row);
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
                $row = MemberLogModel::getWithdrawAccount($row);
                unset($row['alipay']);
                unset($row['back_name']);
                unset($row['back_card']);
                unset($row['level_id']);
            },
            'pager' => $export == 0,
            'onlyList' => $export == 1
        ]);

        //如果是导出
        if ($export == 1) {
            $this->exportWithdraw($records);
        }

        return $this->result($records);
    }

    private function exportWithdraw($records)
    {
        ExcelHelper::export($records, [
            [
                'field' => 'member_id',
                'title' => '会员id',
            ],
            [
                'field' => 'nickname',
                'title' => '昵称',
            ],
            [
                'field' => 'source_name',
                'title' => '来源',
            ],
            [
                'field' => 'level_name',
                'title' => '会员等级',
            ],
            [
                'field' => 'status_text',
                'title' => '提现状态',
            ],
            [
                'field' => 'pay_type_text',
                'title' => '提现方式',
            ],
            [
                'field' => 'withdraw_text',
                'title' => '提现账号',
            ],
            [
                'field' => 'money',
                'title' => '申请金额',
            ],

            [
                'field' => 'deduct_money',
                'title' => '手续费'
            ],
            [
                'field' => 'real_money',
                'title' => '到账金额'
            ],
            [
                'field' => 'created_at',
                'title' => '申请时间',
            ]


        ], '提现记录导出');

        return true;
    }

    /**
     * 提现手动审核、拒绝
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdateStatus()
    {
        // 获取参数
        $params = RequestHelper::post(['order_id', 'status']);

        // 参数校验
        if (empty($params['order_id']) ||
            empty($params['status']) ||
            !in_array($params['status'],
                [MemberLogStatusConstant::ORDER_STATUS_MANUAL_SUCCESS, MemberLogStatusConstant::ORDER_WITHDRAW_REFUND])) {
            throw new FinanceException(FinanceException::LOG_UPDATE_STATUS_PARAMS_ERROR);
        }

        $trans = \Yii::$app->db->beginTransaction();
        try {
            // 更新状态
            $result = MemberLogModel::updateStatus($params['order_id'], $params['status']);
            if (is_error($result)) {
                throw new FinanceException(FinanceException::LOG_UPDATE_STATUS_FAIL, $result['message']);
            }

            // 拒绝返还积分
            if ($params['status'] == MemberLogStatusConstant::ORDER_WITHDRAW_REFUND) {
                $memberRes = MemberModel::updateCredit($result->member_id, $result->money, $this->userId,
                    'balance', 1,
                    '余额退款', MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND);
                if (is_error($memberRes)) {
                    throw new FinanceException(FinanceException::LOG_UPDATE_STATUS_FAIL, $result['message']);
                }
            }

            $trans->commit();
        } catch (\Throwable $e) {
            $trans->rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }


        return $this->success();
    }

    /**
     * 充值退款
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRechargeRefund()
    {
        // 获取参数
        $orderId = RequestHelper::postInt('order_id');
        if (empty($orderId)) {
            throw new FinanceException(FinanceException::MEMBER_LOG_REFUND_PARAMS_ERROR);
        }

        // 查询充值记录
        $order = MemberLogModel::findOne([
            'id' => $orderId,
            'type' => MemberLogTypeConstant::ORDER_TYPE_RECHARGE,
        ]);
        if (empty($order)) {
            throw  new FinanceException(FinanceException::MEMBER_LOG_NOT_EXISTS);
        }

        // 调用退款方法
        $result = MemberLogService::refund($order);
        if (is_error($result)) {
            throw new FinanceException(FinanceException::MEMBER_LOG_RECHARGE_REFUND_FAILED, $result['message']);
        }

        return $this->success();
    }

    /**
     * 提现申请
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWithdrawApply()
    {
        // 获取参数
        $orderId = RequestHelper::post('order_id');

        if (empty($orderId)) {
            throw new FinanceException(FinanceException::MEMBER_LOG_WITHDRAW_APPLY_PARAMS_ERROR);
        }

        $order = MemberLogModel::findOne(['id' => $orderId, 'type' => MemberLogTypeConstant::ORDER_FROM_WITHDRAW]);
        if (empty($order)) {
            throw  new FinanceException(FinanceException::MEMBER_LOG_WITHDRAW_APPLY_NOT_EXISTS);
        }

        $result = MemberLogService::transfer($order);

        if (is_error($result)) {
            throw new FinanceException(FinanceException::MEMBER_LOG_WITHDRAW_APPLY_FAILED, $result['message']);
        }

        return $this->success();
    }

    /**
     * 获取申请提现筛选标签
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLabel()
    {
        $label = [];

        $label['type'] = MemberLogStatusConstant::getAllColumnFixedIndex('message');

        $label['pay_type'] = MemberLogPayTypeConstant::getAllColumnFixedIndex('message');

        $label['levels'] = ArrayHelper::map(MemberLevelModel::find()->select('id, level_name')->get(), 'id', 'level_name');

        $label['groups'] = ArrayHelper::map(MemberGroupModel::getMemberGroupMap(), 'id', 'group_name');

        return $this->result($label);
    }

}
