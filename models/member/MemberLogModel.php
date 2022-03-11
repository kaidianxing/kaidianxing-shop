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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberLogPayTypeConstant;
use shopstar\constants\member\MemberLogStatusConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\rechargeReward\RechargeRewardLogModel;
use shopstar\structs\order\OrderPaySuccessStruct;

/**
 * This is the model class for table "{{%member_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property string $log_sn 系统单号
 * @property int $type 订单类型 1充值 2提现
 * @property string $created_at 创建时间
 * @property int $status 订单状态 0生成 10成功 11手动打款 20失败 30充值退款 40提现拒绝
 * @property string $money 金额
 * @property string $send_credit 充值赠送积分
 * @property int $pay_type 提现/充值  类型 10后台 20微信 30支付宝 40银行卡
 * @property string $trans_id 商户单号
 * @property string $real_money 实际金额
 * @property string $charge 手续费 记录当时的比例
 * @property string $deduct_money 扣除的金额
 * @property string $alipay 支付宝号
 * @property string $back_name 银行名称
 * @property string $back_card 银行卡号
 * @property string $real_name 实名
 * @property string $remark 备注
 * @property string $updated_at 更新时间
 * @property int $client_type 客户端类型
 */
class MemberLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'type', 'status', 'pay_type', 'client_type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['money', 'send_credit', 'real_money', 'charge', 'deduct_money'], 'number'],
            [['log_sn'], 'string', 'max' => 50],
            [['trans_id'], 'string', 'max' => 60],
            [['alipay', 'back_card'], 'string', 'max' => 100],
            [['back_name', 'remark'], 'string', 'max' => 255],
            [['real_name'], 'string', 'max' => 20],
            [['log_sn'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'log_sn' => '系统单号',
            'type' => '订单类型 1充值 2提现',
            'created_at' => '创建时间',
            'status' => '订单状态 0生成 10成功 11手动打款 20失败 30充值退款 40提现拒绝',
            'money' => '金额',
            'send_credit' => '充值赠送积分',
            'pay_type' => '提现/充值  类型 10后台 20微信 30支付宝 40银行卡',
            'trans_id' => '商户单号',
            'real_money' => '实际金额',
            'charge' => '手续费 记录当时的比例',
            'deduct_money' => '扣除的金额',
            'alipay' => '支付宝号',
            'back_name' => '银行名称',
            'back_card' => '银行卡号',
            'real_name' => '实名',
            'remark' => '备注',
            'updated_at' => '更新时间',
            'client_type' => '客户端类型'
        ];
    }

    /**
     * getMemberInfo
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMemberInfo()
    {
        return $this->hasOne(MemberModel::class, ['id' => 'member_id']);
    }

    /**
     * decode 解析订单数据
     * @param $order
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function decode($order)
    {
        if (empty($order)) {
            return false;
        }

        //支付方式
        if (isset($order['pay_type'])) {
            $order['pay_type_text'] = MemberLogPayTypeConstant::getAllColumnFixedIndex('message')[$order['pay_type']];
        }
        //状态信息
        if (isset($order['status'])) {
            $order['status_text'] = MemberLogStatusConstant::getAllColumnFixedIndex('message')[$order['status']];
        }
        return $order;
    }

    /**
     * @var array
     */
    public static $orderPayType = [
        1 => '后台',
        2 => '微信',
        3 => '支付宝',
        4 => '银行卡'
    ];
    /**
     * @var array
     */
    protected static $orderStatus = [
        0 => '未支付',
        1 => '成功',
        2 => '失败',
        3 => '退款'
    ];

    public static $orderColumnsRe = [
        ['title' => '会员id', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 12],
        ['title' => '会员等级', 'field' => 'level_name', 'width' => 12],
        ['title' => '会员分组', 'field' => 'group_name', 'width' => 12],
        ['title' => '订单编号', 'field' => 'log_sn', 'width' => 24],
        ['title' => '商家订单号', 'field' => 'trans_id', 'width' => 24],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 12],
        ['title' => '创建时间', 'field' => 'created_at', 'width' => 12],
        ['title' => '支付状态', 'field' => 'status_text', 'width' => 12],
        ['title' => '充值金额', 'field' => 'money', 'width' => 12],
        ['title' => '支付类型', 'field' => 'pay_type_name', 'width' => 12],
        ['title' => '实际支付金额', 'field' => 'real_money', 'width' => 12],
        ['title' => '备注', 'field' => 'remark', 'width' => 12],
    ];

    public static $orderColumnsWi = [
        ['title' => '会员id', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 12],
        ['title' => '订单编号', 'field' => 'log_sn', 'width' => 24],
        ['title' => '商家订单号', 'field' => 'trans_id', 'width' => 24],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 12],
        ['title' => '会员等级', 'field' => 'level_name', 'width' => 12],
        ['title' => '会员分组', 'field' => 'group_name', 'width' => 12],
        ['title' => '创建时间', 'field' => 'created_at', 'width' => 12],
        ['title' => '提现状态', 'field' => 'status_text', 'width' => 12],
        ['title' => '提现金额', 'field' => 'money', 'width' => 12],
        ['title' => '提现类型', 'field' => 'pay_type_name', 'width' => 12],
        ['title' => '实际支付金额', 'field' => 'real_money', 'width' => 12],
        ['title' => '手续费', 'field' => 'charge', 'width' => 12],
        ['title' => '支付宝账号', 'field' => 'alipay', 'width' => 12],
        ['title' => '银行名称', 'field' => 'back_name', 'width' => 24],
        ['title' => '银行卡号', 'field' => 'back_card', 'width' => 24],
        ['title' => '真实姓名', 'field' => 'real_name', 'width' => 12],
        ['title' => '备注', 'field' => 'remark', 'width' => 12],
    ];

    public static function writeLog($data = [])
    {
        (new self)->setAttributes($data);
    }

    /**
     * 获取最后一次交易
     * @param int $memberId
     * @param int $payType 不为0时 获取该类型最后一次交易信息
     * @return array|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getLast(int $memberId, int $payType = 0)
    {
        $query = self::find()
            ->where(['member_id' => $memberId, 'type' => 2]);

        if ($payType != 0) {
            $query->andWhere(['pay_type' => $payType]);
        }
        return $query->orderBy(['id' => SORT_DESC])->asArray()->one();
    }

    /**
     * 插入日志
     * @param float $money 金额
     * @param int $payType 充值方式
     * @param int $memberId
     * @param string $logSn 充值单号
     * @param int $type 1.充值 2.提现
     * @param string $remark 备注
     * @param int $status 0生成 10成功 11手动打款 20失败 30充值退款 40提现拒绝
     * @param int $clientType 客户端类型
     * @return MemberLogModel
     * @throws MemberException
     */
    public static function insertLog(float $money, int $payType,
                                     int   $memberId, string $logSn, int $type, string $remark, $status = 0, $clientType = 0)
    {
        $data['log_sn'] = $logSn;
        $data['member_id'] = $memberId;
        $data['type'] = $type;
        $data['status'] = $status;
        $data['money'] = $money;
        $data['real_money'] = $money;
        $data['pay_type'] = $payType;
        $data['remark'] = $remark;
        $data['client_type'] = $clientType;
        $log = new self();
        $log->setAttributes($data);
        if (!$log->save()) {
            throw new MemberException(MemberException::MEMBER_LOG_WRITE_FAIL);
        }

        return $log;
    }

    /**
     * 充值成功
     * @param OrderPaySuccessStruct $orderPaySuccessStruct
     * @return array|bool|MemberModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function paySuccess(OrderPaySuccessStruct $orderPaySuccessStruct)
    {
        $order = self::findOne([
            'id' => $orderPaySuccessStruct->orderId,
            'member_id' => $orderPaySuccessStruct->accountId,
        ]);

        if ($order === null || $order->status != 0) {
            return error('订单未找到或状态错误');
        }
        if ($order['money'] != $orderPaySuccessStruct->payPrice) {
            return error('金额错误');
        }
        $order->status = MemberLogStatusConstant::ORDER_STATUS_SUCCESS;
        $order->trans_id = $orderPaySuccessStruct->outTradeNo;

        if ($order->save() === false) {
            return error($order->getErrorMessage());
        }

        //获取用户
        $member = MemberModel::findOne(['id' => $orderPaySuccessStruct->accountId]);

        if ($orderPaySuccessStruct->payPrice > 0) {
            $memRes2 = MemberModel::updateCredit($orderPaySuccessStruct->accountId, $orderPaySuccessStruct->payPrice, 0, 'balance', 1, '充值获得', MemberCreditRecordStatusConstant::BALANCE_STATUS_RECHARGE);
            if (is_error($memRes2)) {
                return $memRes2;
            }

            // 充值送礼
            RechargeRewardLogModel::sendReward($orderPaySuccessStruct->accountId, $orderPaySuccessStruct->orderId, $order->client_type);

            //消息通知
            $result = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_PAY_RECHARGE, [
                'member_nickname' => $member['nickname'],
                'recharge_price' => $orderPaySuccessStruct->payPrice,
                'recharge_method' => '在线充值',
                'balance_change_reason' => '余额充值',
                'recharge_time' => DateTimeHelper::now(),
                'recharge_pay_method' => PayTypeConstant::getText($orderPaySuccessStruct->payType),
                'member_balance' => $memRes2->balance,
                'change_time' => DateTimeHelper::now(),// 变动时间
                'change_reason' => '在线充值',
            ]);

            if (!is_error($result)) {
                $result->sendMessage($order->member_id);
            }

        }
        return true;

    }

    /**
     * 获取提现方式
     * @param $record
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWithdrawAccount($record)
    {
        $pay_type = isset($record['pay_type']) ? $record['pay_type'] : '';

        if (empty($pay_type) || !array_key_exists($pay_type, MemberLogPayTypeConstant::getAll())) {
            return $record;
        }

        switch ($pay_type) {
            case MemberLogPayTypeConstant::ORDER_PAY_TYPE_WECHAT:
                $record['withdraw']['pay_account'] = '';
                $record['withdraw']['real_name'] = $record['real_name'];
                $record['withdraw_text'] = $record['real_name'];
                break;
            case MemberLogPayTypeConstant::ORDER_PAY_TYPE_ALIPAY:
                $record['withdraw']['real_name'] = $record['real_name'];
                $record['withdraw']['pay_account'] = $record['alipay'];
                $record['withdraw_text'] = $record['real_name'] . ',' . $record['alipay'];
                break;
        }

        return $record;
    }

    /**
     * 更新记录状态
     * @param $orderId
     * @param $changeStatus
     * @return MemberLogModel|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStatus($orderId, $changeStatus)
    {
        $order = self::findOne(['id' => $orderId]);

        if (empty($order)) {
            return error('记录不存在');
        }

        $order->status = $changeStatus;
        if ($order->save() === false) {
            return error('保存失败');
        }

        return $order;
    }


}
