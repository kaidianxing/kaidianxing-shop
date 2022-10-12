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
use shopstar\constants\member\MemberCreditRecordStatusConstant;

use shopstar\helpers\DateTimeHelper;


/**
 * This is the model class for table "{{%member_credit_record}}".
 *
 * @property int $id
 * @property int $member_id 用户id
 * @property int $type 类型 1:积分;2:余额;
 * @property string $num 变化数量
 * @property int $operator 操作人 0:后台; other: 会员id;
 * @property string $present_credit 当前余额(充值后)
 * @property string $module 模块
 * @property string $remark 备注
 * @property string $created_at 创建时间
 * @property int $status 1 后台操作 2 订单消费 3积分抵扣 4订单退还
 * @property int $order_id 订单id
 */
class MemberCreditRecordModel extends BaseActiveRecord
{
    public static $orderColumnsCredit = [
        ['title' => '会员id', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 12],
        ['title' => '等级名称', 'field' => 'level_name', 'width' => 12],
        ['title' => '分组名称', 'field' => 'group_name', 'width' => 12],
        ['title' => '变化数量', 'field' => 'num', 'width' => 24],
        ['title' => '操作员', 'field' => 'operator', 'width' => 24],
        ['title' => '变化后剩余', 'field' => 'present_credit', 'width' => 12],
        ['title' => '创建时间', 'field' => 'created_at', 'width' => 12],
        ['title' => '备注', 'field' => 'remark', 'width' => 12],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_credit_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'type', 'operator', 'status', 'order_id'], 'integer'],
            [['num', 'present_credit'], 'number'],
            [['created_at'], 'safe'],
            [['module'], 'string', 'max' => 30],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '用户id',
            'type' => '类型 1:积分;2:余额;',
            'num' => '变化数量',
            'operator' => '操作人 0:后台; other: 会员id;',
            'present_credit' => '当前余额(充值后)',
            'module' => '模块',
            'remark' => '备注',
            'status' => '消耗类型',
            'created_at' => '创建时间',
            'order_id' => '订单id',
        ];
    }

    /**
     * 本周的积分变化
     * @param $memberId
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWeekCredit($memberId)
    {
        $week = DateTimeHelper::getWeekDate(date('Y'), date('W'));
        $startWeek = $week[0];
        $endWeek = date('Y-m-d', strtotime($week[1]) + 86400);
        $weekCredit = MemberCreditRecordModel::find()
            ->where([
                'and',
                ['member_id' => $memberId],
                ['type' => 1],
                ['>', 'created_at', $startWeek],
                ['<', 'created_at', $endWeek]
            ])->asArray()
            ->all();

        return empty($weekCredit) ? 0 : array_sum(array_column($weekCredit, 'num'));
    }

    /**
     * 根据类型返回值
     * @param int $type
     * @param string $statusType
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSumByType(int $type, string $statusType)
    {
        return MemberCreditRecordModel::find()->where(['type' => $type, 'status' => MemberCreditRecordModel::$$statusType])->sum('num') ?? 0;
    }

    /**
     * @var array 余额发放类型
     */
    public static array $balanceSendType = [
        MemberCreditRecordStatusConstant::BALANCE_STATUS_RECHARGE, // 余额充值
        MemberCreditRecordStatusConstant::BALANCE_STATUS_BACKGROUND, // 后台余额充值
        MemberCreditRecordStatusConstant::NEW_MEMBER_SEND_BALANCE, // 新人送礼
        MemberCreditRecordStatusConstant::RECHARGE_REWARD_SEND_BALANCE, // 充值奖励
        MemberCreditRecordStatusConstant::BALANCE_STATUS_POSTER_SEND, // 关注海报余额赠送
        MemberCreditRecordStatusConstant::CONSUME_REWARD_SEND_BALANCE, // 消费奖励
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_SEND_BALANCE, // 购物奖励
        MemberCreditRecordStatusConstant::COMMENT_REWARD_SEND_BALANCE, // 评价奖励
        MemberCreditRecordStatusConstant::BALANCE_STATUS_GROUPS_REBATE, // 拼团返利
        MemberCreditRecordStatusConstant::PERFORMANCE_AWARD_BALANCE, // 分销直推奖

    ];

    /**
     * @var array 余额退回类型
     */
    public static array $balanceBackType = [
        MemberCreditRecordStatusConstant::CONSUME_REWARD_REFUND_BALANCE, // 消费奖励退回
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_REFUND_BALANCE, // 购物奖励退回
        MemberCreditRecordStatusConstant::BALANCE_STATUS_WITHDRAW, // 余额提现
    ];

    /**
     * @var array 余额使用类型
     */
    public static array $balanceUseType = [
        MemberCreditRecordStatusConstant::BALANCE_STATUS_DEDUCTION, // 余额抵扣
        MemberCreditRecordStatusConstant::BALANCE_STATUS_PAY, // 余额支付
    ];

    /**
     * @var array 余额退款类型
     */
    public static array $balanceRefundType = [
        MemberCreditRecordStatusConstant::BALANCE_STATUS_REFUND, // 余额退款
    ];

    /**
     * @var array 积分发放类型
     */
    public static array $creditSendType = [
        MemberCreditRecordStatusConstant::CREDIT_STATUS_BACKGROUND, // 后台充值
        MemberCreditRecordStatusConstant::NEW_MEMBER_SEND_CREDIT, // 新人送礼
        MemberCreditRecordStatusConstant::RECHARGE_REWARD_SEND_CREDIT, // 充值奖励
        MemberCreditRecordStatusConstant::CONSUME_REWARD_SEND_CREDIT, // 消费奖励
        MemberCreditRecordStatusConstant::CREDIT_STATUS_SEND_POSTER, // 关注海报积分赠送
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_SEND_CREDIT, // 购物奖励
        MemberCreditRecordStatusConstant::CREDIT_STATUS_GROUPS_REBATE, // 拼团返利
        MemberCreditRecordStatusConstant::COMMENT_REWARD_SEND_CREDIT, // 评价奖励
        MemberCreditRecordStatusConstant::ORDER_GIVE_CREDIT, // 购物送积分
        MemberCreditRecordStatusConstant::PERFORMANCE_AWARD_CREDIT, // 分销直推奖
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_DAY, // 签到日签奖励
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_INCREASING, // 签到递增奖励
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_CONTINUITY, // 签到连签奖励
    ];

    /**
     * @var array 积分返还类型
     */
    public static array $creditBackType = [
        MemberCreditRecordStatusConstant::CONSUME_REWARD_REFUND_CREDIT, // 消费奖励退回
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_REFUND_CREDIT, // 购物奖励退回
    ];

    /**
     * @var array 积分使用类型
     */
    public static array $creditUseType = [
        MemberCreditRecordStatusConstant::CREDIT_SHOP_PAY, // 积分商城支付
        MemberCreditRecordStatusConstant::CREDIT_STATUS_DEDUCTION, // 积分抵扣
    ];

    /**
     * @var array 积分退款类型
     */
    public static array $creditRefundType = [
        MemberCreditRecordStatusConstant::CREDIT_STATUS_REFUND, // 售后商品退还积分
        MemberCreditRecordStatusConstant::CREDIT_STATUS_CREDIT_SHOP_REFUND, // 积分商城售后退还积分
    ];

    /**
     * 移动端积分记录用
     * @var array 积分获得
     */
    public static array $creditGet = [
        MemberCreditRecordStatusConstant::CREDIT_STATUS_BACKGROUND, // 后台充值
        MemberCreditRecordStatusConstant::NEW_MEMBER_SEND_CREDIT, // 新人送礼
        MemberCreditRecordStatusConstant::RECHARGE_REWARD_SEND_CREDIT, // 充值奖励
        MemberCreditRecordStatusConstant::CONSUME_REWARD_SEND_CREDIT, // 消费奖励
        MemberCreditRecordStatusConstant::CREDIT_STATUS_SEND_POSTER, // 关注海报积分赠送
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_SEND_CREDIT, // 购物奖励
        MemberCreditRecordStatusConstant::CREDIT_STATUS_GROUPS_REBATE, // 拼团返利
        MemberCreditRecordStatusConstant::COMMENT_REWARD_SEND_CREDIT, // 评价奖励
        MemberCreditRecordStatusConstant::ORDER_GIVE_CREDIT, // 购物送积分
        MemberCreditRecordStatusConstant::CONSUME_REWARD_REFUND_CREDIT, // 消费奖励退回
        MemberCreditRecordStatusConstant::SHOPPING_REWARD_REFUND_CREDIT, // 购物奖励退回
        MemberCreditRecordStatusConstant::ARTICLE_REWARD_SEND_CREDIT, // 文章营销奖励
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT, // 积分签到
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_DAY, // 签到日签奖励
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_INCREASING, // 签到递增奖励
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_SEND_CREDIT_CONTINUITY, // 签到连签奖励
    ];

    /**
     * 移动端积分记录用
     * @var array 积分抵扣
     */
    public static array $creditDeduct = [

        MemberCreditRecordStatusConstant::CREDIT_STATUS_DEDUCTION, // 积分抵扣
        MemberCreditRecordStatusConstant::CREDIT_STATUS_REFUND, // 售后商品退还积分
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_CREDIT_CONSUME, // 积分签到补签
    ];

    /**
     * 移动端积分记录用
     * @var array 积分使用
     */
    public static array $creditPay = [
        MemberCreditRecordStatusConstant::CREDIT_SHOP_PAY, // 积分商城支付
        MemberCreditRecordStatusConstant::CREDIT_STATUS_CREDIT_SHOP_REFUND, // 积分商城售后退还积分
        MemberCreditRecordStatusConstant::CREDIT_SIGN_REWARD_CREDIT_CONSUME, // 积分签到
    ];

}