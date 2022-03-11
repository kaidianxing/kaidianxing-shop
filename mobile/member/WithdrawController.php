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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberLogPayTypeConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\exceptions\FinanceException;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;

class WithdrawController extends BaseMobileApiController
{
    /**
     * 申请提现
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = [];

        // 获取设置
        $set = $this->getSet();

        if ($set['withdraw_fee_type'] == 2) {
            $data['withdraw_fee'] = $set['withdraw_fee'] ?? 0; // 手续费
        }
        if ($set['free_fee_type'] == 2) {
            $data['free_fee_start'] = $set['free_fee_start'] ?? 0; // 免手续费
            $data['free_fee_end'] = $set['free_fee_end'] ?? 0; // 免手续费
        }
        if ($set['withdraw_limit_type'] == 2) {
            $data['withdraw_limit_money'] = $set['withdraw_limit_money'] ?? 0; // 提现限额
        }

        // 获取最后一次提现记录
        $lastWithdraw = MemberLogModel::getLast($this->memberId);

        // 获取用户当前余额
        $data['balance'] = MemberModel::getBalance($this->memberId);

        $data['withdraw_type'] = explode(',', $set['withdraw_type']);
        $data['withdraw_type'] = array_filter($data['withdraw_type']);
        $data['withdraw_type'] = array_combine($data['withdraw_type'], $data['withdraw_type']);

        if (true === in_array(MemberLogPayTypeConstant::ORDER_PAY_TYPE_WECHAT, $data['withdraw_type'])) {
            // 根据客户端判断  只有微信环境下有微信支付
            if ($this->clientType != ClientTypeConstant::CLIENT_WECHAT && $this->clientType != ClientTypeConstant::CLIENT_WXAPP) {
                unset($data['withdraw_type'][MemberLogPayTypeConstant::ORDER_PAY_TYPE_WECHAT]);
            }
        }
        $data['withdraw_type'] = array_values($data['withdraw_type']);

        // 如果支持支付宝提现
        if (in_array(MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY, $data['withdraw_type'])) {
            // 如果最后一次提现类型不是支付宝 获取最后一次支付宝提现
            if (!empty($lastWithdraw) && $lastWithdraw['pay_type'] != MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY) {
                $lastWithdraw = MemberLogModel::getLast($this->memberId, MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY);
            }
            if (!empty($lastWithdraw)) {
                // 上次提现支付宝账户
                $data['alipay_account'] = $lastWithdraw['alipay'];
                $data['realname'] = $lastWithdraw['real_name'];
            }
        }

        return $this->success(['data' => $data]);
    }

    /**
     * 提交申请
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit()
    {
        $set = $this->getSet();

        $post = RequestHelper::post();
        $money = bcadd($post['money'], 0, 2);
        $payType = $post['pay_type'];
        if (empty($payType)) {
            throw new FinanceException(FinanceException::WITHDRAW_SUBMIT_PARAM_ERROR);
        }
        $memberBalance = MemberModel::getBalance($this->memberId);
        if (bccomp($money, 0, 2) <= 0) {
            return $this->error('提现金额错误');
        }
        if (bccomp($money, $memberBalance, 2) == 1) {
            return $this->error('提现金额过大');
        }
        if ($set['withdraw_limit_type'] == 2 && bccomp($money, $set['withdraw_limit_money'], 2) < 0) {
            return $this->error('提现金额不满足最低提现额度');
        }

        $this->checkPayType($set, $payType);

        // TODO 并发...

        $data = [];

        // 支付宝
        if ($payType == MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY) {
            $realName = trim($post['realname']);
            $alipayAccount = trim($post['alipay_account']);
            $alipayAccountRepeat = trim($post['alipay_account_repeat']);

            if (empty($realName)) {
                return $this->error('请填写姓名');
            }
            if (empty($alipayAccount)) {
                return $this->error('请填写支付宝帐号');
            }
            if ($alipayAccountRepeat !== $alipayAccount) {
                return $this->error('支付宝帐号与确认帐号不一致');
            }

            $data['alipay'] = $alipayAccount;
            $data['real_name'] = $realName;
        }

        // 计算手续费
        $data['real_money'] = $money;
        if ($set['withdraw_fee_type'] == 2) {
            $data['withdraw_fee'] = $set['withdraw_fee'];
            // 计算手续费
            $res = $this->calculateFee($money, $set);
            $data['real_money'] = $res['realMoney'];
            $data['deduct_money'] = $res['deductMoney'];
            $data['charge'] = $set['withdraw_fee'];
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 订单号
            $data['log_sn'] = OrderNoHelper::getOrderNo('WD', $this->clientType);
            $data['pay_type'] = $payType;
            $data['member_id'] = $this->memberId;
            $data['type'] = MemberLogTypeConstant::ORDER_FROM_WITHDRAW;
            $data['status'] = 0;
            $data['money'] = $money;
            $data['remark'] = '余额提现';
            $data['client_type'] = RequestHelper::header('Client-Type');

            $log = new MemberLogModel();
            $log->setAttributes($data);
            if (!$log->insert()) {
                return $this->error('保存失败:' . $log->getErrorMessage());
            }
            // 更新用户余额
            MemberModel::updateCredit($this->memberId, $money, 0, 'balance', 2, '余额提现', MemberCreditRecordStatusConstant::BALANCE_STATUS_WITHDRAW);

            // 发送通知


            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), -1);
        }
        return $this->success('申请成功');
    }

    /**
     * 检查是否开启提现方式
     * @param $set
     * @param int $payType
     * @return bool
     * @throws FinanceException
     */
    private function checkPayType($set, $payType = 0)
    {

        $type = explode(',', $set['withdraw_type']);
        // 微信钱包 配合数据库字段定义
        if (!in_array($payType, $type)) {
            throw new FinanceException(FinanceException::WITHDRAW_PAY_TYPE_ERROR);

        }
        return true;
    }

    /**
     * 获取提现设置
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    private function getSet()
    {
        $set = ShopSettings::get('sysset.credit');
        if (empty($set['withdraw_state'])) {
            throw new FinanceException(FinanceException::WITHDRAW_CLOSE);
        }
        return $set;
    }

    /**
     * 计算手续费
     * @param $money
     * @param $set
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function calculateFee($money, $set)
    {
        $fee = $set['withdraw_fee'];

        $data = [];
        $data['deductMoney'] = bcmul($money, bcdiv($fee, 100, 4), 2);

        if ($set['free_fee_type'] == 2) {
            $begin = $set['free_fee_start'] ?? 0; // 免手续费
            $end = $set['free_fee_end'] ?? 0; // 免手续费
            // 当手续费在手续费免手续费区间时
            if (bccomp($data['deductMoney'], $begin, 2) >= 0 && bccomp($data['deductMoney'], $end, 2) <= 0) {
                $data['deductMoney'] = 0;
            }
        }

        $data['realMoney'] = bcsub($money, $data['deductMoney'], 2);

        return $data;
    }

    /**
     * 提现记录
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $member_id = $this->memberId;
        $type = MemberLogTypeConstant::ORDER_FROM_WITHDRAW;
        $result = MemberLogModel::getColl(
            [
                'select' => 'id, log_sn, created_at, status, pay_type, money,send_credit,real_money,charge,deduct_money,  remark',
                'where' => compact('member_id',  'type'),
                'orderBy' => ['created_at' => SORT_DESC]
            ],
            [
                'callable' => function(&$row) {
                    $row = MemberLogModel::decode($row);
                }
            ]
        );
        return $this->result($result);
    }
}
