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

use shopstar\bases\service\BaseService;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionGoodsModel;
use shopstar\models\commission\CommissionGoodsOptionModel;
use shopstar\models\commission\CommissionOrderDataModel;
use shopstar\models\commission\CommissionOrderGoodsModel;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\order\OrderPackageModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CommissionOrderService extends BaseService
{

    /**
     * 计算分销订单佣金
     * @param int $orderId
     * @param bool $recalculate 重新计算
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function calculate(int $orderId, bool $recalculate = false)
    {
        if (empty($orderId)) {
            return error('参数错误');
        }
        $orderData = OrderModel::find()->where(['id' => $orderId])->first();
        $orderData['goods_info'] = Json::decode($orderData['goods_info']);

        // 获取分销设置
        $commissionSet = CommissionSettings::get('set');
        // 结算设置
        $commissionSettlement = CommissionSettings::get('settlement');
        // 获取&判断是否开启分销
        $level = $commissionSet['commission_level'];
        if (empty($level)) {
            return error('未开启分销');
        }

        // 获取佣金计算方式  1 商品折扣价  2实际支付
        $calculateType = $commissionSettlement['calculate_type'];

        // 是否开启内购
        $selfBuy = $commissionSet['self_buy'];
        //判断下单人是否是分销商
        $buyMemberIsAgent = CommissionAgentModel::isAgent($orderData['member_id']);
        if (!$buyMemberIsAgent) {
            $selfBuy = 0;
        }

        //查询orderData是否存在该数据 存在使用当时的状态
        $commissionOrderData = CommissionOrderDataModel::find()->where(['order_id' => $orderData['id']])->asArray()->one();
        if (!empty($commissionOrderData)) {
            $selfBuy = $commissionOrderData['self_buy'];
        }

        // 订单上级 内购是自己
        $orderAgentId = $selfBuy ? $orderData['member_id'] : CommissionRelationModel::getParentId($orderData['member_id']);
        // 1级分销商
        if (empty($orderAgentId)) {
            $orderAgentId = 0; // 上级ID为空时置为0
        }

        // 一级分销商
        $orderAgent1 = CommissionAgentModel::getAgentInfo($orderAgentId);
        if (is_error($orderAgent1)) {
            return error('非分销订单(上线不存在或不是分销商)');
        }

        // 三级分销商的ID
        $orderAgentIds = [];

        // 默认分销规则
        if (!empty($orderAgent1['commission_level'])) {
            $commissionRule[1] = [
                'level_id' => $orderAgent1['commission_level']['id'],
                'num' => bcdiv($orderAgent1['commission_level']['commission_1'], 100, 4), // 数量  比例或金额
                'member_id' => $orderAgent1['member_id'],
                'type' => 1, // 折扣方式 1折扣  2固定金额  默认的都是折扣
            ];
        } else {
            // 分销等级被禁用
            $commissionRule[1] = [
                'level_id' => 0,
                'member_id' => $orderAgent1['member_id'],
            ];
        }

        $orderAgentIds[] = $orderAgent1['member_id'];

        // 分销关系
        // 二级分销商
        if ($level > 1) {
            $orderAgent2Id = CommissionRelationModel::getParentId($orderAgentId);
            if (!empty($orderAgent2Id)) {
                // 二级分销商信息
                $orderAgent2 = CommissionAgentModel::getAgentInfo($orderAgent2Id);
                if (!empty($orderAgent2['commission_level'])) {
                    $commissionRule[2] = [
                        'level_id' => $orderAgent2['commission_level']['id'],
                        'num' => bcdiv($orderAgent2['commission_level']['commission_2'], 100, 4),
                        'member_id' => $orderAgent2['member_id'],
                        'type' => 1,
                    ];
                } else {
                    // 分销等级被禁用
                    $commissionRule[2] = [
                        'level_id' => 0,
                        'member_id' => $orderAgent2['member_id'],
                    ];
                }

                $orderAgentIds[] = $orderAgent2['member_id'];
            }
        }

        // 订单完成时间
        $orderFinishTime = '0';
        if (!empty($orderData['finish_time'])) {
            $orderFinishTime = $orderData['finish_time'];
        }

        // 业绩考核的订单阶梯佣金
        $ladderCommissionTotal = 0;
        // 业绩考核的阶梯佣金比例
        $ladderCommissionRate = 0;
        // 业绩考核的阶梯佣金具体规则
        $ladderCommissionRule = [];
        // 业绩考核阶梯佣金订单
        $assessmentOrderData = [];
        // 参与的业绩考核id
        $assessmentId = 0;

        // 订单佣金
        $commissionTotal = 0;

        // 分销订单数据
        $commissionOrderData = [];

        // 分销订单商品数据
        $commissionOrderGoodsData = [];

        // 订单上的佣金明细
        $orderLevelCommission = [];

        // 是否有参与分销的商品  遍历完成后为false 则跳出
        $isJoin = false;
        // 查找订单商品
        $orderGoodsInfo = OrderGoodsModel::find()
            ->select(['id', 'goods_id', 'activity_package', 'dispatch_info', 'price', 'option_id', 'price_unit', 'total'])
            ->where(['order_id' => $orderId])
            ->get();
        // 遍历商品
        // 参与分销的商品名称
        $goodsTitle = [];
        foreach ($orderGoodsInfo as $orderGoods) {

            // 获取商品信息 参与分销
            $goodsInfo = GoodsModel::findOne(['id' => $orderGoods['goods_id'], 'is_commission' => 1]);
            if (empty($goodsInfo)) {
                continue;
            }

            // 分销商品标题
            $goodsTitle[] = $goodsInfo['title'];
            // 参与分销
            $isJoin = true;
            // 获取商品分销信息
            $commissionGoods = CommissionGoodsModel::find()->where(['goods_id' => $orderGoods['goods_id']])->asArray()->one();
            $commissionGoodsOption = [];
            if ($commissionGoods['type'] == 2) {
                // 多规格规则  TODO 青岛开店星信息技术有限公司 where 条件 一期不做
                $commissionGoodsOption = CommissionGoodsOptionModel::find()->where([])->asArray()->one();
            }

            // 遍历 重新组装规则
            foreach ($commissionRule as $index => $item) {
                // 分销等级被禁用 佣金为0
                if ($item['level_id'] == 0) {
                    $commission = 0;
                } else {
                    // 如果不使用系统默认设置
                    if ($commissionGoods['type'] != 0) {
                        // 使用商品设置分销
                        if ($commissionGoods['type'] == 1) {
                            $rule = Json::decode($commissionGoods['commission_rule']);
                        } else {
                            // 按规格
                            $rule = Json::decode($commissionGoodsOption['commission_rule']);
                        }
                        // 取该分销层级的佣金信息
                        $item['type'] = $rule['commission_' . $index]['level_' . $item['level_id']]['type'];
                        if ($item['type'] == 1) {
                            $item['num'] = bcdiv($rule['commission_' . $index]['level_' . $item['level_id']]['num'], 100, 4);
                        } else {
                            $item['num'] = $rule['commission_' . $index]['level_' . $item['level_id']]['num'];
                        }
                    }

                    $memberPrice = 0; // 会员折扣
                    $balancePrice = 0; // 余额抵扣金额

                    // 获取折扣信息
                    if (!empty($orderGoods['activity_package'])) {
                        $discountInfo = Json::decode($orderGoods['activity_package']);
                        // 会员折扣
                        if (!empty($discountInfo['member_price']['price'])) {
                            $memberPrice = $discountInfo['member_price']['price'];
                        }
                        // 余额抵扣
                        if (!empty($discountInfo['balance']['price'])) {
                            $balancePrice = $discountInfo['balance']['price'];
                        }
                    }

                    // 计算该等级该商品的佣金
                    // 商品折扣价 原价-会员价
                    if ($calculateType == 1) {
                        $goodsPrice = bcsub(bcmul($orderGoods['price_unit'], $orderGoods['total'], 2), $memberPrice, 2);
                    } else {
                        // 实际支付金额
                        $goodsPrice = bcadd($orderGoods['price'], $balancePrice, 2);

                    }

                    // 按比例类型
                    if ($item['type'] == 1) {
                        // 佣金
                        if ($item['num'] != 0) {
                            $commission = bcmul($goodsPrice, $item['num'], 2);
                        } else {
                            $commission = 0;
                        }
                    } else {
                        // 按固定金额类型 乘商品数量
                        $commission = bcmul($item['num'], $orderGoods['total'], 2);
                    }
                }

                // 阶梯佣金计算, 只有一级上级可拿
                if ($index == 1 && $ladderCommissionRate > 0) {
                    $ladderCommission = bcmul($goodsPrice, $ladderCommissionRate, 2);//阶梯佣金

                    // 佣金需加上阶梯佣金
                    $commission = bcadd($commission, $ladderCommission, 2);

                    // 阶梯订单数据
                    $assessmentOrderData = [
                        'order_id' => $orderData['id'],
                        'assessment_id' => $assessmentId,
                        'member_id' => $item['member_id'],
                        'commission_level_id' => $joinData['commission_level_id'] ?? 0,
                        'ladder_commission_rate' => bcmul($ladderCommissionRate, 100, 2),// 转换为%
                        'ladder_commission' => $ladderCommission,
                        'rule' => $joinData['assessment_rule'] ?? '',
                        'step_rule' => Json::encode($ladderCommissionRule),
                    ];
                } else {
                    $ladderCommission = 0;
                }


                // 佣金为0 跳出   2020-04-27 注释  佣金为0也记录
//                if ($commission == 0) {
//                    continue;
//                }

                // 分销商品
                $commissionOrderGoodsData[] = [
                    'order_id' => $orderData['id'],
                    'goods_id' => $orderGoods['goods_id'],
                    'member_id' => $orderData['member_id'], // 下单的用户
                    'agent_id' => $item['member_id'], // 拿佣金的用户
                    'option_id' => $orderGoods['option_id'],
                    'order_goods_id' => $orderGoods['id'],
                    'level' => $index,
                    'commission' => $commission,
                    'original_commission' => $commission,
                    'can_withdraw_commission' => $commission,
                    'ladder_commission' => $ladderCommission,// 阶梯佣金
                    'original_ladder_commission' => $ladderCommission,
                    'assessment_id' => $index == 1 ? $assessmentId : 0,// 考核id
                ];

                // 分销订单数据
                if (empty($commissionOrderData[$index])) {
                    $commissionOrderData[$index] = [
                        'order_id' => $orderData['id'],
                        'member_id' => $orderData['member_id'],
                        'agent_id' => $item['member_id'], // 拿佣金的用户
                        'level' => $index,
                        'commission' => $commission,
                        'original_commission' => $commission,
                        'ladder_commission' => $ladderCommission,
                        'original_ladder_commission' => $ladderCommission,
                        'assessment_id' => $index == 1 ? $assessmentId : 0,// 考核id
                        'self_buy' => $selfBuy,
                        'order_no' => $orderData['order_no']
                    ];
                } else {
                    $commissionOrderData[$index]['commission'] = bcadd($commissionOrderData[$index]['commission'], $commission, 2);
                    $commissionOrderData[$index]['original_commission'] = bcadd($commissionOrderData[$index]['original_commission'], $commission, 2);
                    // 业绩考核的阶梯佣金
                    $commissionOrderData[$index]['ladder_commission'] = bcadd($commissionOrderData[$index]['ladder_commission'], $commission, 2);
                    $commissionOrderData[$index]['origin_ladder_commission'] = bcadd($commissionOrderData[$index]['origin_ladder_commission'], $commission, 2);
                }


                // 合计
                $commissionTotal = bcadd($commissionTotal, $commission, 2);
                // 阶梯佣金合计
                $ladderCommissionTotal = bcadd($ladderCommissionTotal, $ladderCommission, 2);
                // 每个层级的佣金
                $orderLevelCommission['level_' . $index] = bcadd($orderLevelCommission['level_' . $index], $commission, 2);
            }

        }

        // 如果没有商品参与分销  跳出
        if (!$isJoin) {
            return error('没有参与分销的商品');
        }

        // 更新则删除之前的数据
        if ($recalculate) {
            // 获取历史数据
            $oldOrderData = CommissionOrderDataModel::find()
                ->select('level, commission,ladder_commission')
                ->where(['order_id' => $orderId])
                ->indexBy('level')
                ->get();
            CommissionOrderDataModel::deleteAll([
                'and',
                ['order_id' => $orderData['id']],
                ['in', 'agent_id', $orderAgentIds]
            ]);
            CommissionOrderGoodsModel::deleteAll([
                'and',
                ['order_id' => $orderData['id']],
                ['in', 'agent_id', $orderAgentIds]
            ]);

            CommissionOrderModel::deleteAll(['order_id' => $orderData['id']]);
        }

        // TODO 青岛开店星信息技术有限公司 失败可以记录日志
        try {
            // 批量插入
            // commission_order_data
            $commissionOrderDataFields = ['order_id', 'member_id', 'agent_id', 'level', 'commission', 'original_commission', 'ladder_commission', 'original_ladder_commission', 'assessment_id', 'self_buy', 'order_no'];
            $commissionOrderDataResult = CommissionOrderDataModel::batchInsert($commissionOrderDataFields, array_values($commissionOrderData));

            if (!$commissionOrderDataResult) {
                throw new Exception('执行失败 commission_order_data');
            }
            // commission_order_goods
            $commissionOrderGoodsFields = ['order_id', 'goods_id', 'member_id', 'agent_id', 'option_id', 'order_goods_id', 'level', 'commission', 'original_commission', 'can_withdraw_commission', 'ladder_commission', 'original_ladder_commission', 'assessment_id'];
            $commissionOderGoodsResult = CommissionOrderGoodsModel::batchInsert($commissionOrderGoodsFields, array_values($commissionOrderGoodsData));
            if (!$commissionOderGoodsResult) {
                throw new Exception('执行失败 commission_order_goods');
            }
            // commission_order
            $commissionOrder = new CommissionOrderModel();
            $commissionOrder->setAttributes([
                'order_id' => $orderData['id'],
                'member_id' => $orderData['member_id'],
                'commission' => $commissionTotal,
                'commission_level' => Json::encode($orderLevelCommission),
                'order_finish_time' => $orderFinishTime,
                'ladder_commission' => $ladderCommissionTotal,
                'ladder_commission_rule' => Json::encode($ladderCommissionRule),
                'assessment_id' => $assessmentId,
            ]);
            if ($commissionOrder->save() === false) {
                throw new Exception('执行失败 commission_order');
            }

            // 更新分销商表的累计佣金字段
            $subLadderCommission = 0;//阶梯佣金
            foreach ($commissionOrderData as $index => $item) {
                if ($index == 1) {
                    // 取用户当前佣金
                    if ($recalculate) {
                        $sub = bcsub($item['commission'], $oldOrderData[1]['commission'], 2);
                        $subLadderCommission = bcsub($item['ladder_commission'], $oldOrderData[1]['ladder_commission'], 2);
                    } else {
                        $sub = $item['commission'];
                        $subLadderCommission = $item['ladder_commission'];
                    }
                    CommissionAgentModel::updateAllCounters(['commission_total' => $sub], ['member_id' => $orderAgent1['member_id'],]);
                    // 1级上级, 需要额外记录阶梯佣金
                    CommissionAgentModel::updateAllCounters(['ladder_commission_total' => $subLadderCommission], ['member_id' => $orderAgent1['member_id'],]);
                }
                if ($index == 2) {
                    // 取用户当前佣金
                    if ($recalculate) {
                        $sub = bcsub($item['commission'], $oldOrderData[2]['commission'], 2);
                    } else {
                        $sub = $item['commission'];
                    }
                    CommissionAgentModel::updateAllCounters(['commission_total' => $sub], ['member_id' => $orderAgent2['member_id']]);
                }
            }

            // 发送通知 订单付款通知 / 卖家新增分销订单通知
            if ($recalculate) {
                $member = MemberModel::findOne(['id' => $orderData['member_id']]);

                foreach ($commissionOrderData as $item) {
                    // 内购跳出
                    if ($item['member_id'] == $item['agent_id']) {
                        continue;
                    }
                    if ($item['self_buy'] == 1) {
                        $downLevel = ($item['level'] - 1) . '级';
                    } else {
                        $downLevel = $item['level'] . '级';
                    }
                    // 订单下级付款通知
                    $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_CHILD_PAY, [
                        'order_no' => $orderData['order_no'],
                        'down_line_nickname' => $member->nickname,
                        'order_price' => $orderData['pay_price'],
                        'commission' => $item['commission'],
                        'pay_time' => $orderData['pay_time'],
                        'junior_level' => $downLevel,
                    ], 'commission');

                    if (!is_error($result)) {
                        $result->sendMessage([], ['commission_level' => $item['level'], 'is_self_buy' => $item['self_buy'], 'member_id' => $item['agent_id']]);
                    }
                }

                // 发送通知 卖家新增分销订单通知
                $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_SELLER_ADD_COMMISSION_ORDER, [
                    'order_no' => $orderData['order_no'],
                    'member_nickname' => $member->nickname,
                    'goods_title' => implode(',', $goodsTitle),
                    'order_price' => $orderData['pay_price'],
                    'pay_time' => $orderData['pay_time'],
                    'shop_name' => ShopSettings::get('sysset.mall.basic')['name'],
                ], 'commission');
                if (!is_error($result)) {
                    $result->sendMessage();
                }
            }

        } catch (\Throwable $exception) {
            return error($exception->getMessage());
        }

        return true;
    }

    /**
     * 维权订单处理
     * @param int $memberId
     * @param int $orderId
     * @param int $orderGoodsId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateRefundStatus(int $memberId, int $orderId, int $orderGoodsId = 0)
    {
        // 订单非分销订单或已结算
        if (!CommissionOrderModel::isCanEditCommission($memberId, $orderId)) {
            return error('非分销订单或已结算');
        }
        // 是否维权的最后一件商品
        $isLastGoods = false;

        // 单商品的话 是否参与分销
        if (!empty($orderGoodsId)) {
            $orderGoods = CommissionOrderGoodsModel::find()
                ->where(['order_id' => $orderId, 'order_goods_id' => $orderGoodsId])
                ->indexBy('level')
                ->get();
            if (empty($orderGoods)) {
                return error('该商品非分销商品');
            }
            // 更新状态
            CommissionOrderGoodsModel::updateAll(
                ['is_count_refund' => 0],
                [
                    'and',
                    ['in', 'level', [1, 2, 3]],
                    ['order_id' => $orderId],
                    ['order_goods_id' => $orderGoodsId],
                ]
            );

            // 判断是否有其他商品 可结算
            $otherOrderGoods = CommissionOrderGoodsModel::find()
                ->select('level, sum(commission)')
                ->where(['order_id' => $orderId, 'is_count_refund' => 1])
                ->andWhere(['<>', 'order_goods_id', $orderGoodsId])
                ->groupBy('level')
                ->indexBy('level')
                ->get();

            // 获取是否有未维权的商品
            if (empty($otherOrderGoods)) {
                // 维权的最后一件商品 跳到下面执行
                $isLastGoods = true;
            } else {
                // 获取orderData
                $orderData = CommissionOrderDataModel::find()
                    ->where(['order_id' => $orderId, 'member_id' => $memberId, 'is_count_refund' => 1])
                    ->indexBy('level')
                    ->get();

                // order 和 order_data 减去该商品佣金
                foreach ($orderGoods as $item) {
                    // 现在的佣金
                    $commission = bcsub($orderData[$item['level']]['commission'], $item['commission'], 2);
                    if ($commission < 0) {
                        $commission = 0;
                    }
                    $isCount = 1;
                    // 判断该等级是不是只有该商品有佣金
                    if (!isset($otherOrderGoods[$item['level']])) {
                        $isCount = 0;
                    }
                    // 更新到order_data
                    CommissionOrderDataModel::updateAll(
                        ['commission' => $commission, 'is_count_refund' => $isCount],
                        ['order_id' => $orderId, 'member_id' => $memberId, 'level' => $item['level']]
                    );
                    // 更新用户佣金
                    CommissionAgentModel::updateAllCounters(['commission_total' => -$item['commission'], 'ladder_commission_total' => -$item['ladder_commission']], ['member_id' => $item['agent_id']]);
                }
                // 获取当前佣金 更新到order
                $commissionTotal = CommissionOrderDataModel::find()
                    ->where(['order_id' => $orderId, 'member_id' => $memberId])
                    ->sum('commission');
                CommissionOrderModel::updateAll(['commission' => $commissionTotal], ['order_id' => $orderId, 'member_id' => $memberId]);
            }
        }

        // 如果整单维权 或 单品维权最后已经商品
        if (empty($orderGoodsId) || $isLastGoods) {
            // 获取旧的佣金
            $oldOrderData = CommissionOrderDataModel::find()
                ->select('agent_id, level, commission,ladder_commission,assessment_id')
                ->where(['order_id' => $orderId, 'member_id' => $memberId, 'is_count_refund' => 1])
                ->get();

            // 整单维权 全部置空
            CommissionOrderModel::updateAll(['is_count_refund' => 0], ['order_id' => $orderId, 'member_id' => $memberId]);
            CommissionOrderDataModel::updateAll(['is_count_refund' => 0], ['order_id' => $orderId, 'member_id' => $memberId]);
            CommissionOrderGoodsModel::updateAll(['is_count_refund' => 0], ['order_id' => $orderId, 'member_id' => $memberId]);
            // 更新用户佣金
            foreach ($oldOrderData as $item) {
                CommissionAgentModel::updateAllCounters(
                    ['commission_total' => -$item['commission'], 'ladder_commission_total' => -$item['ladder_commission']],
                    ['member_id' => $item['agent_id']]
                );
            }
        }
        return true;
    }

    /**
     * 导出分销订单
     * @param array $where
     * @param array $searchs
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function export(array $where, array $searchs)
    {
        //查询订单
        $list = OrderGoodsModel::getColl([
            'alias' => 'og',
            'leftJoins' => [
                [OrderModel::tableName() . ' order', 'order.id=og.order_id'],
                [OrderPackageModel::tableName() . ' package', 'package.id=og.package_id'],
                [CommissionOrderDataModel::tableName() . 'order_data', 'order_data.order_id = order.id'],
                [CommissionOrderModel::tableName() . ' commission_order', 'order.id = commission_order.order_id'],
            ],
            'searchs' => $searchs,
            'andWhere' => $where,
            'select' => [
                'order.id as order_id',
                'order.order_no',
                'order.create_from',
                'order.buyer_name',
                'order.buyer_mobile',
                'order.member_id',
                'order.member_realname',
                'order.member_mobile',
                'order.goods_price',
                'order.address_state',
                'order.address_city',
                'order.address_area',
                'order.address_detail',
                'order.change_price',
                'order.change_dispatch',
                'order.dispatch_price',
                'order.pay_price',
                'order.status',
                'order.refund_price',
                'order.created_at',
                'order.pay_time',
                'order.send_time',
                'order.finish_time',
                'order.finish_time',
                'order.extra_price_package',
                'order.extra_discount_rules_package',
                'order.activity_type',
                'og.title as goods_title',
                'og.option_title',
                'og.total',
                'og.goods_sku',
                'og.price_unit as order_goods_price',
                'og.refund_status',
                'og.refund_type',
                'package.express_com',
                'package.express_id',
                'package.express_sn',
                'commission_order.account_time', // 到账时间
            ],
            'orderBy' => [
                'order.created_at' => SORT_DESC
            ]
        ], [
            'pager' => false,
            'onlyList' => true,
            'callable' => function (&$row) {
                //快递公司
                $row['express_name'] = CoreExpressModel::getNameById($row['express_id']);
                $row = OrderModel::decode($row);
                $row = OrderGoodsModel::decode($row);
                //优惠金额 非预售
                if ($row['activity_type'] != OrderActivityTypeConstant::ACTIVITY_TYPE_PRESELL) {
                    $row['discount_price'] = array_sum(array_values($row['extra_price_package'] ?: []));
                } else {
                    // 预售
                    $row['discount_price'] = $row['extra_discount_rules_package'][0]['presell']['actual_deduct'];
                    if ($row['extra_discount_rules_package'][0]['presell']['presell_type'] == 0) {
                        $row['pay_price'] += $row['extra_discount_rules_package'][0]['presell']['front_money'];
                    }
                }


                //折扣
                $extraPriceStr = '';
                foreach ((array)$row['extra_price_package_text'] as $extraPriceIndex => $extraPriceItem) {
                    if ($extraPriceIndex == '商品预售') {
                        // 看 extra_discount_rules_package 字段
                        $extraPriceStr .= $extraPriceIndex . ':' . $row['extra_discount_rules_package'][0]['presell']['actual_deduct'] . "\n, ";
                    } else {
                        // 非商品预售
                        $extraPriceStr .= $extraPriceIndex . ':' . $extraPriceItem . "\n, ";
                    }

                }
                $row['extra_price_package_text'] = $extraPriceStr;
                //订单来源
                $row['create_from'] = $row['create_from_text'];
                $row['anent_info'] = CommissionOrderDataModel::getAgentInfo($row['order_id']);
                // 一级分销商
                if (!empty($row['anent_info']['agent_level1'])) {
                    $row['agent_level1_nickname'] = trim($row['anent_info']['agent_level1']['member']['nickname'], '=');
                    $row['agent_level1_mobile'] = $row['anent_info']['agent_level1']['member']['mobile'];
                    $row['agent_level1_commission'] = $row['anent_info']['agent_level1']['commission'];
                }
                // 二级分销商
                if (!empty($row['anent_info']['agent_level2'])) {
                    $row['agent_level2_nickname'] = trim($row['anent_info']['agent_level2']['member']['nickname'], '=');
                    $row['agent_level2_mobile'] = $row['anent_info']['agent_level2']['member']['mobile'];
                    $row['agent_level2_commission'] = $row['anent_info']['agent_level2']['commission'];
                }
                // 三级分销商
                if (!empty($row['anent_info']['agent_level3'])) {
                    $row['agent_level3_nickname'] = trim($row['anent_info']['agent_level3']['member']['nickname'], '=');
                    $row['agent_level3_mobile'] = $row['anent_info']['agent_level3']['member']['mobile'];
                    $row['agent_level3_commission'] = $row['anent_info']['agent_level3']['commission'];
                }
                // 到账状态
                if ($row['account_time'] <= DateTimeHelper::now() && $row['account_time'] != 0) {
                    $row['commission_status'] = '已到账';
                    $row['commission_time'] = $row['account_time'];
                } else {
                    $row['commission_status'] = '未到账';
                }
            },
        ]);

        $memberId = array_column($list, 'member_id');
        $MemberLevel = MemberModel::find()
            ->alias('member')
            ->leftJoin(MemberLevelModel::tableName() . 'member_level', 'member_level.id=member.level_id')
            ->where([
                'member.id' => $memberId,
            ])->indexBy('id')->asArray()->select('member.id,member_level.level_name')->all();

        foreach ($list as $listIndex => &$listItem) {
            //会员等级名称
            $listItem['member_level_name'] = $MemberLevel[$listItem['member_id']]['level_name'];
        }
        unset($listItem);

        $diffFields = [
            'goods_title',
            'goods_sku',
            'total',
            'option_title',
            'price_discount',
            'add_credit',
            'price_change',
            'refund_status_text',
            'express_name',
            'express_sn',
        ];

        $list = ExcelHelper::exportFilter($list, $diffFields, 'order_id');

        ExcelHelper::export($list, self::$exportField, '分销订单数据导出');
        die;
    }

    /**
     * 默认导出的字段
     * @var array[]
     */
    public static $exportField = [
        ['title' => '会员id', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员姓名', 'field' => 'member_realname', 'width' => 12],
        ['title' => '会员等级', 'field' => 'member_level_name', 'width' => 12],
        ['title' => '会员手机号', 'field' => 'member_mobile', 'width' => 12],
        ['title' => '订单编号', 'field' => 'order_no', 'width' => 12],
        ['title' => '订单来源', 'field' => 'create_from', 'width' => 12],
        ['title' => '收货人姓名', 'field' => 'buyer_name', 'width' => 12],
        ['title' => '收货人电话', 'field' => 'buyer_mobile', 'width' => 12],
        ['title' => '收货地址省份', 'field' => 'address_state', 'width' => 24],
        ['title' => '收货人地址城市', 'field' => 'address_city', 'width' => 24],
        ['title' => '收货人地址地区', 'field' => 'address_area', 'width' => 24],
        ['title' => '收货地址', 'field' => 'address_detail', 'width' => 24],
        ['title' => '商品名称', 'field' => 'goods_title', 'width' => 36],
        ['title' => '商品编码', 'field' => 'goods_sku', 'width' => 24],
        ['title' => '商品规格', 'field' => 'option_title', 'width' => 12],
        ['title' => '商品数量', 'field' => 'total', 'width' => 12],
        ['title' => '商品小计', 'field' => 'goods_price', 'width' => 12],
        ['title' => '优惠详情', 'field' => 'extra_price_package_text', 'width' => 12],
        ['title' => '优惠金额', 'field' => 'discount_price', 'width' => 12],
        ['title' => '运费', 'field' => 'dispatch_price', 'width' => 12],
        ['title' => '订单改价', 'field' => 'change_price', 'width' => 12],
        ['title' => '运费改价', 'field' => 'change_dispatch', 'width' => 12],
        ['title' => '应收款', 'field' => 'pay_price', 'width' => 12],
        ['title' => '状态', 'field' => 'status_text', 'width' => 12],
        ['title' => '维权金额', 'field' => 'refund_price', 'width' => 12],
        ['title' => '维权状态', 'field' => 'refund_status_text', 'width' => 12],
        ['title' => '一级分销商', 'field' => 'agent_level1_nickname', 'width' => 12],
        ['title' => '一级手机号', 'field' => 'agent_level1_mobile', 'width' => 12],
        ['title' => '一级佣金', 'field' => 'agent_level1_commission', 'width' => 12],
        ['title' => '二级分销商', 'field' => 'agent_level2_nickname', 'width' => 12],
        ['title' => '二级手机号', 'field' => 'agent_level2_mobile', 'width' => 12],
        ['title' => '二级佣金', 'field' => 'agent_level2_commission', 'width' => 12],
        ['title' => '三级分销商', 'field' => 'agent_level3_nickname', 'width' => 12],
        ['title' => '三级手机号', 'field' => 'agent_level3_mobile', 'width' => 12],
        ['title' => '三级佣金', 'field' => 'agent_level3_commission', 'width' => 12],
        ['title' => '分销状态', 'field' => 'commission_status', 'width' => 12],
        ['title' => '到账时间', 'field' => 'commission_time', 'width' => 12],
    ];
}