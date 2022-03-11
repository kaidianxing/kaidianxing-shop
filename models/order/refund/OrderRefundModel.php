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

namespace shopstar\models\order\refund;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\RefundConstant;

use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order_refund}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $order_goods_id 订单商品表id (单品维权用，整单为0)
 * @property int $member_id 用户id
 * @property string $refund_no 退款订单号
 * @property string $price 退款金额
 * @property int $is_contain_dispatch 维权金额是否包含运费
 * @property string $reason 退款原因
 * @property string $images 图片
 * @property string $content 详细描述
 * @property int $status -2:取消; -1:拒绝; 0:申请; 1:用户填写快递单号; 2:店家填写快递单号; 3:等待退款; 10:完成; 11:手动退款完成;
 * @property string $reply 商家回复
 * @property int $refund_type 维权方式 1:退款 2:退货退款 3:换货
 * @property int $refund_address_id 退货地址id
 * @property string $refund_address 退货地址
 * @property string $seller_accept_time 卖家同意申请时间
 * @property string $seller_message 卖家留言
 * @property string $member_express_code 用户填写快递公司代码
 * @property string $member_express_encoding 用户填写快递公司编码
 * @property string $member_express_name 用户填写快递公司名称
 * @property string $member_express_sn 用户填写快递单号
 * @property string $member_express_time 用户填写快递时间
 * @property string $seller_express_code 卖家 快递公司代码
 * @property string $seller_express_encoding 卖家填写快递公司编码
 * @property string $seller_express_name 卖家 快递公司名称
 * @property string $seller_express_sn 卖家 快递单号
 * @property string $seller_express_time 卖家 填写快递时间
 * @property string $reject_reason 卖家拒绝售后原因
 * @property string $created_at 创建时间
 * @property string $finish_time 维权完成时间 （取消 拒绝 同意退款 手动退款 时间）
 * @property int $is_history 是否历史维权(非最后一次维权)   0否 1是   当有多次维权时值为0的是最后一次
 * @property int $need_platform 需要平台介入 1 平台介入
 * @property string $refund_mobile 退货手机号 卖家
 * @property string $refund_name 退货姓名 卖家
 * @property int $credit 退积分 (暂时只有积分商城用)
 */
class OrderRefundModel extends BaseActiveRecord
{
    /**
     * 维权类型
     * 1退款  2退货退款  3换货
     * @var array
     */
    public static $refundMap = [
        '1' => 'refund',
        '2' => 'return',
        '3' => 'exchange'
    ];

    /**
     * 维权类型
     * 1退款  2退货退款  3换货
     * @var array
     */
    public static $refundTypeText = [
        '1' => '仅退款',
        '2' => '退货退款',
        '3' => '换货'
    ];

    /**
     * 维权信息
     * @var OrderRefundModel
     */
    public static $refundByOrder;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_refund}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_goods_id', 'member_id', 'is_contain_dispatch', 'status', 'refund_type', 'refund_address_id', 'is_history', 'need_platform', 'credit'], 'integer'],
            [['price'], 'number'],
            [['images', 'content', 'refund_address'], 'string'],
            [['seller_accept_time', 'member_express_time', 'seller_express_time', 'created_at', 'finish_time'], 'safe'],
            [['refund_no', 'member_express_code', 'member_express_encoding', 'member_express_name', 'member_express_sn', 'seller_express_code', 'seller_express_encoding', 'seller_express_name', 'seller_express_sn', 'refund_name'], 'string', 'max' => 50],
            [['reason', 'reply', 'seller_message', 'reject_reason'], 'string', 'max' => 255],
            [['refund_mobile'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单id',
            'order_goods_id' => '订单商品表id (单品维权用，整单为0)',
            'member_id' => '用户id',
            'refund_no' => '退款订单号',
            'price' => '退款金额',
            'is_contain_dispatch' => '维权金额是否包含运费',
            'reason' => '退款原因',
            'images' => '图片',
            'content' => '详细描述',
            'status' => '-2:取消; -1:拒绝; 0:申请; 1:用户填写快递单号; 2:店家填写快递单号; 3:等待退款; 10:完成; 11:手动退款完成;',
            'reply' => '商家回复',
            'refund_type' => '维权方式 1:退款 2:退货退款 3:换货',
            'refund_address_id' => '退货地址id',
            'refund_address' => '退货地址',
            'seller_accept_time' => '卖家同意申请时间',
            'seller_message' => '卖家留言',
            'member_express_code' => '用户填写快递公司代码',
            'member_express_encoding' => '用户填写快递公司编码',
            'member_express_name' => '用户填写快递公司名称',
            'member_express_sn' => '用户填写快递单号',
            'member_express_time' => '用户填写快递时间',
            'seller_express_code' => '卖家 快递公司代码',
            'seller_express_encoding' => '卖家填写快递公司编码',
            'seller_express_name' => '卖家 快递公司名称',
            'seller_express_sn' => '卖家 快递单号',
            'seller_express_time' => '卖家 填写快递时间',
            'reject_reason' => '卖家拒绝售后原因',
            'created_at' => '创建时间',
            'finish_time' => '维权完成时间 （取消 拒绝 同意退款 手动退款 时间）',
            'is_history' => '是否历史维权(非最后一次维权)   0否 1是   当有多次维权时值为0的是最后一次',
            'refund_mobile' => '退货手机号 卖家',
            'refund_name' => '退货姓名 卖家',
            'need_platform' => '需要平台介入 1 平台介入',
            'credit' => '退积分 (暂时只有积分商城用)',
        ];
    }

    /**
     * 维权商品关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getOrderGoods()
    {
        return $this->hasMany(OrderGoodsModel::class, ['order_id' => 'order_id']);
    }

    /**
     * 根据订单id获取有效的维权信息（不包括 拒绝、和已取消）
     * @param int $orderId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getValidRefundOrderByOrderId(int $orderId): bool
    {
        $refundInfo = self::getRefundByOrder($orderId);
        //如果是错误 return false 代表没有维权订单
        if (is_error($refundInfo)) {
            return false;
        }

        //无维权
        if ($refundInfo['status'] == RefundConstant::REFUND_STATUS_CANCEL || $refundInfo['status'] == RefundConstant::REFUND_STATUS_REJECT) {
            return false;
        }

        //有维权
        return true;
    }


    /**
     * 判断订单是否有维权中
     * @param int $orderId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkOrderRefunding(int $orderId): bool
    {
        return self::find()
            ->where([
                'order_id' => $orderId,
                'is_history' => 0,
            ])
            ->andWhere(['between', 'status', 0, 9])
            ->exists();
    }

    /**
     * 根据订单获取维权信息
     * 获取最后一次 (可能维权多次)
     * @param int $orderId 订单id
     * @param int $goodsId 订单商品id 单品维权用
     * @return array|OrderRefundModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRefundByOrder(int $orderId, int $goodsId = 0)
    {
        // 注释 因为可能上面查找的是全部的  下面查找的单品的  混合了 每次重新查
//        if (empty(self::$refundByOrder)) {
        $query = self::find()
            ->where(['order_id' => $orderId, 'is_history' => 0]);
        if (!empty($goodsId)) {
            $query->andWhere(['order_goods_id' => $goodsId]);
        }

        self::$refundByOrder = $query->one();

        if (empty(self::$refundByOrder)) {
            return error('维权信息不存在');
        }
//        }
        return self::$refundByOrder;
    }


    /**
     * 写入物流信息
     * 用户填写
     * @return array|OrderRefundModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function setExpress()
    {
        $post = RequestHelper::post();
        $refund = self::getRefundByOrder($post['order_id'], $post['order_goods_id'] ?? 0);
        if (is_error($refund)) {
            return $refund;
        }

        // 维权状态为完成 或 (换货 且 店家已寄出换货商品时)，不允许修改
        if ($refund->status == RefundConstant::REFUND_STATUS_SUCCESS
            || $refund->status == RefundConstant::REFUND_STATUS_MANUAL
            || ($refund->refund_type == RefundConstant::TYPE_EXCHANGE && $refund->status == RefundConstant::REFUND_STATUS_WAIT)) {

            return error('该维权状态不允许修改物流');
        }

        $refund->member_express_code = $post['express_code'];
        $refund->member_express_encoding = $post['express_encoding'];
        $refund->member_express_name = $post['express_name'];
        $refund->member_express_time = DateTimeHelper::now();
        $refund->member_express_sn = $post['express_sn'];
        // 退货退款 到等待完成状态
        if ($refund->refund_type == RefundConstant::TYPE_RETURN) {
            $refund->status = RefundConstant::REFUND_STATUS_WAIT;
        } else if ($refund->refund_type == RefundConstant::TYPE_EXCHANGE) {
            // 换货 到卖家填写单号状态
            $refund->status = RefundConstant::REFUND_STATUS_SHOP;
        }

        if ($refund->save() === false) {
            return error($refund->getErrorMessage());
        }
    }


    /**
     * 获取订单信息
     * @param int $orderId
     * @param int $orderGoodsId
     * @return OrderModel|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRefundOrder(int $orderId, int $orderGoodsId = 0)
    {
        $query = OrderModel::find()->where(['id' => $orderId]);

        $order = $query->one();
        if (empty($order)) {
            return error('订单不存在');
        }
        // 检查订单状态 是否符合同意退款
        if ($order->is_refund != 1) {
            return error('该订单未进行售后');
        }
        if ($orderGoodsId == 0 && $order->refund_type != 1) {
            return error('该订单未进行整单售后');
        } else if ($orderGoodsId != 0 && $order->refund_type != 2) {
            return error('该订单未进行单品售后');
        }
        return $order;
    }

    /**
     * 获取订单抵扣信息
     * @param OrderModel $order
     * @param int $orderGoodsId
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRefundDeductInfo(OrderModel $order, int $orderGoodsId = 0)
    {
        // 获取该次维权可用余额抵扣金额
        $data['balance_deduct'] = 0;
        // 获取该次维权最多可退积分
        $data['credit_deduct'] = 0;
        // 获取抵扣规则  积分抵扣比例用
        $rules = Json::decode($order->extra_discount_rules_package);
        // 积分抵扣规则
        $creditRule = [];
        // 积分抵扣比例
        $scale = 0;
        if (is_array($rules)) {
            foreach ($rules as $item) {
                if (!empty($item['credit'])) {
                    $creditRule[] = $item['credit'];
                    $scale = $item['credit']['scale'];
                }
            }
        }
        if (empty($orderGoodsId)) {
            // 整单余额抵扣信息
            $deductInfo = Json::decode($order->extra_price_package);
            if (isset($deductInfo['balance']) && $deductInfo['balance'] != 0) {
                $data['balance_deduct'] = $deductInfo['balance'];
            }

            // 积分抵扣
            if (isset($deductInfo['credit']) && $deductInfo['credit'] != 0) {
                foreach ($creditRule as $item) {
                    $data['credit_deduct'] = bcadd($data['credit_deduct'], $item['credit']);
                }
            }
            // 如果有礼品卡
            if ($deductInfo['gift_card'] != 0) {
                $data['gift_card_deduct'] = $deductInfo['gift_card'];
            }

        } else {
            // 如果是单品维权  获取订单商品信息
            $orderGoods = OrderGoodsModel::findOne(['id' => $orderGoodsId, 'order_id' => $order->id]);
            // 余额抵扣
            $deductInfo = Json::decode($orderGoods->activity_package);
            if (!empty($deductInfo['balance']['price'])) {
                $data['balance_deduct'] = $deductInfo['balance']['price'];
            }
            // 积分抵扣
            if (!empty($deductInfo['credit']['price'])) {
                $data['credit_deduct'] = ceil(bcmul($deductInfo['credit']['price'], $scale, 2));
            }
            // 礼品卡
            if (!empty($deductInfo['gift_card']['price'])) {
                $data['gift_card_deduct'] = $deductInfo['gift_card']['price'];
            }
        }

        return $data;
    }

    /**
     * 获取订单详情维权信息
     * @param int $orderId
     * @param int $orderGoodsId
     * @return array|OrderRefundModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderRefundInfo(int $orderId, int $orderGoodsId = 0)
    {
        $refundInfo = OrderRefundModel::getRefundByOrder($orderId, $orderGoodsId);
        if (is_error($refundInfo)) {
            return $refundInfo;
        }
        $refundInfo = $refundInfo->toArray();
        // 维权状态
        if ($refundInfo['status'] < 0 || $refundInfo['status'] > 9) {
            $refundInfo['refund_status_text'] = '维权完成';
        } else {
            $refundInfo['refund_status_text'] = '维权中';
        }
        // 维权类型
        $refundInfo['refund_type_text'] = self::$refundTypeText[$refundInfo['refund_type']];

        return $refundInfo;
    }

    /**
     * 关闭维权
     * @param int $orderId
     * @return OrderModel|string|null
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public static function cancelOrderRefund(int $orderId)
    {
        $db = \Yii::$app->db->beginTransaction();
        try {
            // 删除维权表的数据
            $refundInfo = OrderRefundModel::findOne(['order_id' => $orderId]);
            $refundInfo->delete();
            // 修改订单的维权状态，改为未维权
            $orderInfo = OrderModel::findOne(['id' => $orderId]);
            $orderInfo->refund_type = 0;
            $orderInfo->is_refund = 0;
            $orderInfo->save();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            return $e->getMessage();
        }
        return true;
    }

    /**
     * 检查订单是否在维权中
     * @param int $orderId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkOrderIsRefunds(int $orderId)
    {
        $isExists = self::find()
            ->where(['order_id' => $orderId, 'is_history' => 0])
            ->andWhere(['between', 'status', '0', '9'])
            ->exists();

        if ($isExists) {
            return true;
        }
        return false;
    }
}
