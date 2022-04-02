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
use shopstar\constants\member\MemberLogStatusConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\constants\tradeOrder\TradeOrderTypeConstant;
use shopstar\exceptions\FinanceException;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberDouyinModel;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberToutiaoLiteModel;
use shopstar\models\member\MemberToutiaoModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\tradeOrder\TradeOrderService;

/**
 * @author 青岛开店星信息技术有限公司
 */
class RechargeController extends BaseMobileApiController
{
    /**
     * 充值
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): \yii\web\Response
    {
        $data = [];

        $rechargeSet = $this->getRechargeSet();

        // 最低充值额度 默认为1
        $data['recharge_money_low'] = $rechargeSet['recharge_money_low'] ?? 1;
        // 当前余额
        $data['balance'] = MemberModel::getBalance($this->memberId);
        // 支付设置
        $payList = ShopSettings::getOpenPayType(ClientTypeConstant::getIdentify(RequestHelper::header('Client-Type')));
        foreach ($payList as $payType => $payTypeSet) {
            if ($payType == 'balance' || $payType == 'delivery') {
                continue;
            }

            if ($payTypeSet['enable'] == 1) {
                $data['pay_list'][] = $payType;
            }
        }

        return $this->success(['data' => $data]);
    }

    /**
     * 提交余额充值
     * @return \yii\web\Response
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit(): \yii\web\Response
    {
        $memberId = $this->memberId;
        $rechargeSet = $this->getRechargeSet();

        // 最低充值金额
        $minRecharge = $rechargeSet['recharge_money_low'] ?? 1;
        $money = RequestHelper::postFloat('money', 2, 0);
        if ($money <= 0) {
            throw new FinanceException(FinanceException::RECHARGE_SUBMIT_MONEY_ERROR);
        }
        if (bccomp($money, $minRecharge, 2) < 0) {
            throw new FinanceException(FinanceException::RECHARGE_SUBMIT_MONEY_LOW_ERROR, '最低充值金额为' . $minRecharge . '元');
        }

        // 支付类型
        $payType = RequestHelper::post('pay_type');
        if (empty($payType)) {
            throw new FinanceException(FinanceException::RECHARGE_SUBMIT_PARAM_PAY_TYPE_EMPTY);
        }
        // 转为code，为空时说明传入pay_type无效
        $payTypeCode = \shopstar\constants\base\PayTypeConstant::getPayTypeCodeByIdentity($payType);
        if (empty($payTypeCode)) {
            throw new FinanceException(FinanceException::RECHARGE_SUBMIT_PARAM_PAY_TYPE_INVALID);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 创建订单号
            $logSn = OrderNoHelper::getOrderNo('RC', $this->clientType);

            // 插入充值记录
            $log = MemberLogModel::insertLog(
                $money,
                $payTypeCode,
                $memberId,
                $logSn,
                MemberLogTypeConstant::ORDER_TYPE_RECHARGE,
                '余额充值',
                MemberLogStatusConstant::ORDER_STATUS_NOT, $this->clientType
            );

            // 根据渠道获取会员openid
            $openid = '';
            if ($this->clientType == ClientTypeConstant::CLIENT_WECHAT) {
                $openid = MemberWechatModel::getOpenId($this->memberId);
            } else if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
                $openid = MemberWxappModel::getOpenId($this->memberId);
            } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN) {
                $openid = MemberDouyinModel::getOpenId($this->memberId);
            } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO) {
                $openid = MemberToutiaoModel::getOpenId($this->memberId);
            } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE) {
                $openid = MemberToutiaoLiteModel::getOpenId($this->memberId);
            }

            /** @change likexin 调用交易订单服务获取支付参数 * */
            $result = TradeOrderService::pay([
                'type' => TradeOrderTypeConstant::TYPE_MEMBER_RECHARGE,     // 交易订单类型(交易类型)
                'payType' => $payTypeCode,      // 支付类型code
                'payTypeIdentity' => $payType,  // 支付类型string

                'clientType' => $this->clientType,  // 客户端类型
                'accountId' => $memberId,       // 充值账号ID(会员ID)
                'openid' => $openid,        // 会员OPENID

                'orderId' => $log->id,        // 订单ID(充值记录ID)
                'orderNo' => $logSn,        // 订单编号(充值单号)
                'orderPrice' => $money,     // 订单金额(充值金额)

                'callbackUrl' => RequestHelper::post('return_url'), // 回调URL
            ])->unify();

            $transaction->commit();

            return $this->result([
                'result' => ['data' => $result['pay_params']['pay_url'] ?? $result['pay_params']],
                'order_id' => $log->id
            ]);

        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * 检查支付状态
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheck()
    {
        $id = RequestHelper::post('id');
        $result = MemberLogModel::find()
            ->where(['id' => $id])
            ->andWhere([
                'and',
                ['>', 'status', 0]
            ])->one();
        if ($result === null) {
            return $this->error('支付还未成功', -1);
        }
        return $this->result('支付成功');
    }

    /**
     * 获取充值配置
     * @return array|mixed|string|\yii\db\ActiveRecord
     * @throws FinanceException
     * @author 青岛开店星信息技术有限公司
     */
    private function getRechargeSet()
    {
        $set = ShopSettings::get('sysset.credit');
        if ($set['recharge_state' == 0]) {
            throw new FinanceException(FinanceException::RECHARGE_CLOSE);
        }
        return $set;
    }
}
