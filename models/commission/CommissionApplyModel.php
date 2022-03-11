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

namespace shopstar\models\commission;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\commission\CommissionApplyStatusConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;


/**
 * This is the model class for table "{{%commission_apply}}".
 *
 * @property int $id
 * @property int $member_id 会员ID
 * @property int $client_type 客户端类型
 * @property string $apply_no 佣金提现单号
 * @property int $type 提现类型 10: 余额 20: 微信 30: 支付宝
 * @property int $status 状态 0: 申请中 10: 申请通过 20: 打款成功 21: 手动处理 30: 拒绝审核 31: 失效(其他原因)
 * @property string $apply_commission 申请佣金
 * @property string $ladder_commission 申请时包含的阶梯佣金
 * @property string $check_commission 审核佣金
 * @property string $final_commission 最终打款佣金
 * @property string $charge_setting
 * @property string $charge_deduction 扣除的手续费
 * @property string $charge_begin 免税开始金额
 * @property string $charge_end 免税结束金额
 * @property string $apply_time 申请时间
 * @property string $check_uid 审核人用户ID -1: 自动审核
 * @property string $check_time 审核时间
 * @property string $pay_uid 打款人用户ID -1: 自动打款
 * @property string $pay_time 打款时间
 * @property string $alipay 提现至支付宝帐号
 * @property string $realname 真实姓名
 * @property string $apply_data 申请提现订单和申请提现金额数据json格式
 */
class CommissionApplyModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_commission_apply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'client_type', 'type', 'status', 'check_uid', 'pay_uid'], 'integer'],
            [['apply_data'], 'string'],
            [['apply_commission', 'ladder_commission', 'check_commission', 'final_commission', 'charge_setting', 'charge_deduction', 'charge_begin', 'charge_end'], 'number'],
            [['apply_time', 'check_time', 'pay_time'], 'safe'],
            [['apply_data'], 'required'],
            [['apply_no', 'alipay', 'realname'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员ID',
            'client_type' => '客户端类型',
            'apply_no' => '佣金提现单号',
            'type' => '提现类型 10: 余额 20: 微信 30: 支付宝',
            'status' => '状态 0: 申请中 10: 申请通过 20: 打款成功 21: 手动处理 30: 拒绝审核 31: 失效(其他原因)',
            'apply_commission' => '申请佣金',
            'ladder_commission' => '申请时包含的阶梯佣金',
            'check_commission' => '审核佣金',
            'final_commission' => '最终打款佣金',
            'charge_setting' => 'Charge Setting',
            'charge_deduction' => '扣除的手续费',
            'charge_begin' => '免税开始金额',
            'charge_end' => '免税结束金额',
            'apply_time' => '申请时间',
            'check_uid' => '审核人用户ID -1: 自动审核',
            'check_time' => '审核时间',
            'pay_uid' => '打款人用户ID -1: 自动打款',
            'pay_time' => '打款时间',
            'alipay' => '提现至支付宝帐号',
            'realname' => '真实姓名',
            'apply_data' => '申请提现订单和申请提现金额数据json格式',
        ];
    }

    /**
     * 自动审核申请
     * @param CommissionApplyModel $apply 申请记录对象
     * @param array $settings 结算设置
     * @param int $levelId 分销商当前等级ID
     * @return array|bool
     * @author likexin
     */
    public static function autoCheckApply(CommissionApplyModel $apply, array $settings, int $levelId = 0)
    {
        // 如果申请金额 大于 自动审核金额 跳出
        if ($apply->apply_commission > (float)$settings['auto_check_price']) {
            return error('申请金额大于自动审核金额');
        }

        // 验证分销商等级
        if (!empty($settings['auto_check_level']) && $levelId > 0) {
            $agentLevel = CommissionLevelModel::find()
                ->where([
                    'id' => $levelId,
                    'status' => 1,
                ])
                ->select(['id', 'level'])
                ->first();
            if (empty($agentLevel)) {
                return error('分销商等级未找到');
            }

            // 查询目标等级
            $level = CommissionLevelModel::find()
                ->where([
                    'id' => $settings['auto_check_level'],
                    'status' => 1,
                ])
                ->select(['id', 'level'])
                ->first();
            if (empty($level)) {
                return error('自动审核分销商等级未找到');
            }

            // 分销商等级不够
            if ($agentLevel['level'] < $level['level']) {
                return error('当前分销商等级低于自动审核等级');
            }
        }

        // 审核成功
        $apply->status = CommissionApplyStatusConstant::STATUS_CHECK_AGREED;
        $apply->check_time = DateTimeHelper::now();
        $apply->check_uid = -1;
        if (!$apply->save()) {
            return error($apply->getErrorMessage());
        }

        return true;
    }

    /**
     * 获取分销佣金
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCommissionInfo()
    {
        $applyData = CommissionApplyModel::find()
            ->select('status, sum(final_commission) as final_commission')
            ->where([
                'and',
                ['in', 'status', [CommissionApplyStatusConstant::STATUS_DEFAULT,
                    CommissionApplyStatusConstant::STATUS_CHECK_AGREED,
                    CommissionApplyStatusConstant::STATUS_REMIT_SUCCESS, CommissionApplyStatusConstant::STATUS_REMIT_MANUAL]]
            ])
            ->groupBy('status')
            ->asArray()
            ->all();

        $applyData = array_column($applyData, 'final_commission', 'status');

        // 提现待审核

        // 提现待打款

        // 提现成功佣金
        $final['pre_check'] = ArrayHelper::arrayGet($applyData, CommissionApplyStatusConstant::STATUS_DEFAULT, 0);
        $final['check_agree'] = ArrayHelper::arrayGet($applyData, CommissionApplyStatusConstant::STATUS_CHECK_AGREED, 0);
        $final['remit_success'] = bcadd(
            ArrayHelper::arrayGet($applyData, CommissionApplyStatusConstant::STATUS_REMIT_SUCCESS, 0),
            ArrayHelper::arrayGet($applyData, CommissionApplyStatusConstant::STATUS_REMIT_MANUAL, 0), 2);

        return $final;
    }

    /**
     * 获取用户申请提现佣金
     * @param int $memberId
     * @param string $degradeTime 降级时间
     * @return bool|int|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberApplyCommission(int $memberId, string $degradeTime = '')
    {
        $andWhere = [];
        // 有降级时间, 查找降级时间之后的数据
        if (!empty($degradeTime)) {
            $andWhere = ['>=', 'apply_time', $degradeTime];
        }
        return self::find()
                ->where(['member_id' => $memberId])
                ->andWhere([
                    'or',
                    ['status' => 20],
                    ['status' => 21],
                ])
                ->andWhere($andWhere)
                ->sum('apply_commission') ?? 0;
    }

}