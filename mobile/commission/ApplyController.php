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
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\RequestHelper;
use shopstar\mobile\commission\CommissionClientApiController;
use shopstar\models\order\OrderGoodsModel;
use Exception;
use shopstar\constants\commission\CommissionApplyTypeConstant;
use shopstar\exceptions\commission\CommissionApplyException;
use shopstar\models\commission\CommissionAgentTotalModel;
use shopstar\models\commission\CommissionApplyModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\constants\components\notice\NoticeTypeConstant;
use yii\helpers\Json;

/**
 * 分销佣金提现
 * Class ApplyController
 * @package apps\commission\client
 */
class ApplyController extends CommissionClientApiController
{

    /**
     * @var array 需要POST的Action
     */
    public $configActions = [
        'postActions' => [
            'submit',
        ]
    ];


    /**
     * 申请提现信息返回
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        $settings = CommissionSettings::get('settlement');

        // 获取提现方式列表
        $typeList = [];

        foreach ((array)$settings['withdraw_type'] as $type) {

            // 根据客户端判断  只有微信环境下有微信支付
            if ($type == PayTypeConstant::PAY_TYPE_WECHAT && $this->clientType != ClientTypeConstant::CLIENT_WECHAT && $this->clientType != ClientTypeConstant::CLIENT_WXAPP) {
                continue;
            }

            $typeList[] = [
                'type' => $type,
                'name' => CommissionApplyTypeConstant::getMessage($type),
            ];

        }


        return $this->result([
            'data' => [
                // 提现方式列表
                'type_list' => $typeList,

                // 最少提现数量
                'withdraw_limit' => (float)$settings['withdraw_limit'],

                // 可提现佣金
                'can_withdraw_commission' => CommissionAgentTotalModel::getCanWithdrawPrice($this->memberId),

                // 包含的阶梯佣金
                'ladder_commission' => CommissionAgentTotalModel::getCanWithdrawPrice($this->memberId, 'order_goods.ladder_commission'),
            ],
        ]);
    }

    /**
     * 提交申请
     * @return array|\yii\web\Response
     * @throws CommissionApplyException
     * @author likexin
     */
    public function actionSubmit()
    {

        // 提现类型
        $type = RequestHelper::postInt('type');

        // 支付宝账号
        $alipay = RequestHelper::post('alipay');

        // 真实姓名
        $realname = RequestHelper::post('realname');

        // 验证传参的正确性能
        if (empty($type)) {
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_TYPE_EMPTY);
        }

        // 验证支付宝信息
        if ($type == CommissionApplyTypeConstant::TYPE_ALIPAY) {
            if (empty($alipay)) {
                throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_ALIPAY_EMPTY);
            } else if (empty($realname)) {
                throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_REAL_NAME_EMPTY);
            }
        }

        // 提现金额
        $price = RequestHelper::postFloat('price');
        if ($price <= 0) {
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_PRICE_EMPTY);
        }

        // 读取分销结算设置
        $settings = CommissionSettings::get('settlement');

        // 验证开启的提现类型
        $allowType = $settings['withdraw_type'];
        if (empty($allowType) || !in_array($type, $allowType)) {
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_TYPE_INVALID);
        }

        // 验证最小提现金额
        $withdrawLimit = (float)$settings['withdraw_limit'];
        if ($withdrawLimit > 0 && $price < $withdrawLimit) {
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_PARAMS_PRICE_LIMIT, '最少提现金额为' . $withdrawLimit . '元');
        }

        $where = [
            'and',
            [
                'order_goods.agent_id' => $this->memberId,
                'order_data.agent_id' => $this->memberId,
                'order_goods.is_count_refund' => 1
            ],
            ['<=', 'order.account_time', DateTimeHelper::now()],
            ['<>', 'order.account_time', 0],
            ['>', 'order_goods.can_withdraw_commission', 0],
        ];

        // 查询可提现订单
        $params = [
            'alias' => 'order_goods',
            'leftJoins' => [
                [CommissionOrderDataModel::tableName() . ' as order_data', 'order_data.order_id = order_goods.order_id'],
                [OrderGoodsModel::tableName() . ' as shop_order_goods', 'shop_order_goods.id = order_goods.order_goods_id'],
                [CommissionOrderModel::tableName() . ' as order', 'order.order_id = order_goods.order_id']
            ],
            'where' => $where,
            'orderBy' => [
                'id' => SORT_ASC
            ]
        ];

        // 分销订单商品明细
        $orderGoodsList = CommissionOrderGoodsModel::getColl($params, [
            'pager' => false,
            'onlyList' => true,
        ]);

        // 获取可提现全部分销佣金
        $canWithdrawCommission = 0;
        // 获取所有分销订单ID
        foreach ($orderGoodsList as $orderGoods) {
            $canWithdrawCommission += (float)$orderGoods['can_withdraw_commission'];
        }

        // 判断可提现佣金
        if (round2($price, 2) > round2($canWithdrawCommission, 2)) {
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_COMMISSION_PRICE_NOT_OK);
        }

        // 计算提现手续费
        $chargeDeduction = 0;
        if ($settings['withdraw_fee_type'] == 2 && $settings['withdraw_fee'] > 0) {
            $chargeDeduction = round2($price * $settings['withdraw_fee'] / 100);
        }

        // 处理免手续费
        if ($settings['free_fee_type'] == 2) {
            if ($price >= $settings['free_fee_start'] && $price <= $settings['free_fee_end']) {
                $chargeDeduction = 0;
            }
        }

        // 最终佣金 提现金额 - 手续费
        $finalCommission = max(round2((float)$price - $chargeDeduction), 0);

        // apply_data 申请提现数据
        $data = [
            'commission' => [],
        ];

        // CommissionOrderGoods 扣除可提现佣金
        $orderIds = [];
        $temporaryCommission = 0;
        $ladderCommission = 0;
        foreach ($orderGoodsList as $item) {
            // 已满足不处理了
            if ($temporaryCommission >= $price) {

                break;
            }
            $temporaryCommission += $item['can_withdraw_commission'];
            if ($temporaryCommission >= $price) {
                $one = [
                    'id' => $item['id'],
                    'commission' => round2($item['can_withdraw_commission'] - ($temporaryCommission - $price), 2),
                    'surplus_commission' => round2($temporaryCommission - $price, 2)
                ];
            } else {
                $one = [
                    'id' => $item['id'],
                    'commission' => $item['can_withdraw_commission'],
                    'surplus_commission' => 0
                ];
            }

            // 第一次处理该条数据, 记录下阶梯佣金
            if ($item['can_withdraw_commission'] == $item['commission'] && $item['ladder_commission'] > 0) {
                $one['ladder_commission'] = $item['ladder_commission'];
                $ladderCommission = bcadd($ladderCommission, $item['ladder_commission'],2);
            }

            $data['commission'][] = $one;
            if (!in_array($item['id'], $orderIds)) {
                $orderIds[] = $item['id'];
            }
        }

        $applyData = $data;
        foreach ($applyData['commission'] as &$applyDatum) {
            unset($applyDatum['surplus_commission']);
        }

        $apply = new CommissionApplyModel();
        $apply->setAttributes([
            'member_id' => $this->memberId,
            'client_type' => $this->clientType,
            'apply_no' => OrderNoHelper::getOrderNo('CA', $this->clientType),
            'type' => $type,
            'order_ids' => '',
            'apply_commission' => $price,
            'ladder_commission' => $ladderCommission,
            'charge_setting' => (float)$settings['withdraw_charge'],
            'charge_deduction' => $chargeDeduction,
            'charge_begin' => (float)$settings['withdraw_begin'],
            'charge_end' => (float)$settings['withdraw_end'],
            'apply_time' => DateTimeHelper::now(),
            'alipay' => $alipay,
            'realname' => $realname,
            'final_commission' => $finalCommission,
            'apply_data' => Json::encode($applyData)
        ]);

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('提交失败 ' . $apply->getErrorMessage());
            }
            foreach ($data['commission'] as $datum) {
                $update = CommissionOrderGoodsModel::updateAll(['can_withdraw_commission' => $datum['surplus_commission']], ['id' => $datum['id']]);
                if (!$update) {
                    throw new Exception('更新分销佣金失败');
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new CommissionApplyException(CommissionApplyException::SUBMIT_FAIL, $e->getMessage());
        }

        // 自动审核
        if ($settings['withdraw_audit'] == 2) {
            CommissionApplyModel::autoCheckApply($apply, $settings, (int)$this->agent['level_id']);
        }

        // 发送通知
        $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_SELLER_WITHDRAW, [
            'withdraw_money' => $apply->apply_commission,
            'change_time' => DateTimeHelper::now(),
            'apply_price' => $apply->apply_commission,
        ], 'commission');
        if (!is_error($result)) {
            $result->sendMessage();
        }


        return $this->result([
            'apply_id' => $apply->id,
        ]);
    }

}