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

namespace shopstar\models\order\create\handler;

use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\services\goods\GoodsActivityService;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;

class GoodsHandler implements HandlerInterface
{
    /**
     * 订单实体类
     * @author 青岛开店星信息技术有限公司
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public $orderCreatorKernel;
    /**
     * @var array 加载商品的字段
     */
    protected $getGoodsField = [
        'id',
        'type',
        'title',
        'sub_name',
        'short_name',
        'thumb',
        'price',
        'unit',
        'stock',
        'reduction_type',//减库存方式
        'weight',
        'dispatch_type',
        'dispatch_price',
        'dispatch_id',
        'goods_sku',
        'bar_code',
        'ext_field',
        'has_option',
        'deduction_credit_type',
        'deduction_credit',
        'deduction_credit_repeat',
        'deduction_balance',
        'deduction_balance_type',
        'buy_level_perm',
        'buy_tag_perm',
        'deduction_balance_repeat',
        'single_full_unit_switch',
        'single_full_unit',
        'single_full_quota_switch',
        'single_full_quota',
        'auto_deliver',
        'auto_deliver_content',
        'member_level_discount_type',
        'is_commission',
        'dispatch_express',
        'dispatch_intracity',
        'form_id',
        'form_status',
        'is_all_verify',
        'dispatch_verify',
        'cost_price',
        'virtual_account_id',
    ];

    /**
     * @var array 加载规格的字段
     */
    protected $getOptionField = [
        'id as option_id',
        'goods_id as id',
        'title as option_title',
        'bar_code',
        'price',
        'stock',
        'weight',
        'goods_sku',
        'thumb as option_thumb',
    ];

    /**
     * GoodsHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return mixed
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        $goodsList = GoodsModel::find()
            ->where([
                'id' => $this->orderCreatorKernel->goodsIds,
                'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY],
            ])
            ->select($this->getGoodsField)->asArray()->all();

        //判断商品是否缺少
        if (array_diff($this->orderCreatorKernel->goodsIds, array_column($goodsList, 'id'))) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_NOT_FOUND_ERROR);
        }

        // 判断是否是虚拟商品
        $goodsInfoType = array_column($goodsList, 'type');
        if (in_array(GoodsTypeConstant::GOODS_TYPE_VIRTUAL, $goodsInfoType)) {
            $this->orderCreatorKernel->isVirtual = true;
            // 订单类型
            $this->orderCreatorKernel->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_VIRTUAL;
        }
        // 判断是否虚拟卡密类型
        if (in_array(GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT, $goodsInfoType)) {
            $this->orderCreatorKernel->isVirtual = true;
            // 订单类型
            $this->orderCreatorKernel->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT;
        }

        //商品权限
        $this->checkGoodsPerm($goodsList);

        // 拆分为带规格商品与不带规格商品
        $withoutOptionGoodsList = [];
        $withOptionGoodsList = [];

        foreach ($goodsList as $goods) {
            //格式化商品扩展数据
            $goods['ext_field'] = Json::decode($goods['ext_field']) ?? [];

            if ((int)$goods['has_option'] === 1) {
                $withOptionGoodsList[] = $goods;
                continue;
            }
            $goods['option_title'] = '';
            $goods['option_id'] = '0';
            $withoutOptionGoodsList[] = $goods;
        }

        //如果有规格商品
        if (!empty($this->orderCreatorKernel->optionIds)) {
            $optionList = GoodsOptionModel::find()
                ->where([
                    'id' => $this->orderCreatorKernel->optionIds,
                ])
                ->select($this->getOptionField)
                ->asArray()
                ->all();

            //判断规格是否缺少
            if (empty($optionList) || array_diff(array_column($optionList, 'option_id'), $this->orderCreatorKernel->optionIds)) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_OPTION_NOT_FOUND_ERROR);
            }

            // 规格商品合并到商品中
            $withOptionGoodsListTemp = [];
            foreach ($withOptionGoodsList as $withOptionGoods) {
                foreach ($optionList as $option) {
                    if ($withOptionGoods['id'] === $option['id']) {
                        $withOptionGoodsListTemp[] = array_merge($withOptionGoods, $option);
                    }
                }
            }
            $withOptionGoodsList = $withOptionGoodsListTemp;
        }

        // 合并商品到goods中
        $this->orderCreatorKernel->goods = array_merge($withOptionGoodsList, $withoutOptionGoodsList);

        // 检查商品合并结果的合法性
        if (empty($this->orderCreatorKernel->goods)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_INVALID_ERROR);
        }

        unset($goodsList, $goods);

        // 初始化订单商品
        $this->initOrderGoods();

        //二次判断支付方式
        if (!$this->orderCreatorKernel->isConfirm) {
            //这里判断如果所有的支付方式都未开启则报错，如果只开了货到付款则到 goodsHandel 继续处理
            if (empty($this->orderCreatorKernel->payment) && $this->orderCreatorKernel->deliveryPay == 0) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_PAYMENT_HANDLER_USABLE_PAYMENT_EMPTY_TWO_ERROR);
            }
        }


        return;
    }

    /**
     * 检测商品购买权限
     * @param array $orderGoods
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    protected function checkGoodsPerm(array $orderGoods)
    {
        //获取需要检测权限的商品
        $checkPermGoodsId = [];

        foreach ($orderGoods as $goodsIndex => $goodsItem) {
            if ($goodsItem['buy_level_perm'] == GoodsPermMapModel::PERM_BUY || $goodsItem['buy_tag_perm'] == GoodsPermMapModel::PERM_BUY) {
                $checkPermGoodsId[] = $goodsItem['id'];
            }
        }

        if (empty($checkPermGoodsId)) {
            return;
        }

        //获取有权限的商品id
        $goodsId = GoodsPermMapModel::getHasPermGoodsId($checkPermGoodsId, $this->orderCreatorKernel->memberId, GoodsPermMapModel::PERM_BUY);
        if ($goodsId === false || count(array_unique($goodsId)) != count(array_unique($checkPermGoodsId))) {
            // 权限不足
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_NOT_PERM_ERROR);
        }
    }

    /**
     * 初始化订单商品数据
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    protected function initOrderGoods(): void
    {
        // 商品合计价格
        $goodsPrice = 0;
        //商品成本价
        $costPrice = 0;

        // 定义库存预警消息
        $inventoryWarningMessages = [];

        // 生成基础的OrderGoods结构，部分等下单时再塞入
        foreach ($this->orderCreatorKernel->goods as &$goods) {

            $cartKey = $goods['option_id'] > 0 ? 'option_' . $goods['option_id'] : 'goods_' . $goods['id'];
            $cartGoods = $this->orderCreatorKernel->cartGoods[$cartKey];

            //计算商品价格
            $price = round2($goods['price'] * $cartGoods['total']);

            //货到付款
            $goods['is_delivery_pay'] = $goods['ext_field']['is_delivery_pay'];
            //发票
            $goods['invoice'] = $goods['ext_field']['invoice'];

            $goodsData = [
                'goods_id' => $goods['id'],
                'type' => $goods['type'],
                'ext_goods_id' => 0,
                'stock' => $goods['stock'],
                'price' => $price,
                'price_unit' => $goods['price'],
                'price_original' => $price,
                'price_discount' => 0,
                'price_change' => 0,
                'credit' => 0,
                'credit_unit' => 0,
                'reduction_type' => $goods['reduction_type'],//付款减库存
                'credit_original' => 0,
                'coupon_price' => 0,
                'total' => $cartGoods['total'],
                'weight' => $goods['weight'],
                'title' => $goods['title'],
                'sub_name' => $goods['sub_name'],
                'short_name' => $goods['short_name'],
                'option_id' => $goods['option_id'],
                'ext_option_id' => 0,
                'option_title' => $goods['option_title'],
                'thumb' => !empty($goods['option_thumb']) ? $goods['option_thumb'] : $goods['thumb'],
                'goods_sku' => $goods['goods_sku'],
                'bar_code' => $goods['bar_code'],
                'unit' => $goods['unit'] ?: '件',
                'deduct_credit' => 0,
                'add_credit' => 0,
                'refund_status' => 0,
                'refund_type' => 0,
                'refund_id' => 0,
                'package_id' => 0,
                'package_cancel_reason' => '',
                'comment_status' => 0,
                'activity_package' => [],
                'dispatch_info' => [
                    'type' => $goods['dispatch_type'],
                    'template_id' => $goods['dispatch_id'],
                    'rules' => [],
                    'exec_price' => $goods['dispatch_price'],
                ],
                'ext_field' => $goods['ext_field'],
                'single_full_unit_switch' => $goods['single_full_unit_switch'],
                'single_full_unit' => $goods['single_full_unit'],
                'single_full_quota_switch' => $goods['single_full_quota_switch'],
                'single_full_quota' => $goods['single_full_quota'],
                'auto_deliver' => $goods['auto_deliver'],
                'auto_deliver_content' => $goods['auto_deliver'] ? $goods['auto_deliver_content'] : '',
                'member_level_discount_type' => $goods['member_level_discount_type'],//会员折扣
                'plugin_identification' => [ // 插件标识
                    'is_commission' => $goods['is_commission'], // 是否参与分销
                ],
                'dispatch_express' => $goods['dispatch_express'], // 是否支持快递 0否1是
                'dispatch_intracity' => $goods['dispatch_intracity'], // 是否支持同城配送 0否1是
                'form_id' => $goods['form_id'],
                'form_status' => $goods['form_status'],
                'cost_price' => $goods['cost_price'],
            ];

            // 虚拟卡密添加卡密库id和是否开启邮箱设置
            if ($goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                $goodsData['virtual_account_id'] = $goods['virtual_account_id'];
                $goodsData['virtual_account_mailer_setting'] = VirtualAccountModel::checkMailer($goods['virtual_account_id']) ? 1 : 0;
            }

            // 处理多规格的关联卡密库id
            if ($goods['option_id'] != 0 && $goods['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                $goodsData['virtual_account_id'] = GoodsOptionModel::getInfoById($goods['option_id']);
                $goodsData['virtual_account_mailer_setting'] = VirtualAccountModel::checkMailer($goodsData['virtual_account_id']) ? 1 : 0;
            }

            // 商品数量
            if ($goodsData['total'] <= 0) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_STOCK_INVALID_ERROR);
            }

            // 库存不足
            if ($goods['stock'] < $goodsData['total']) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_GOODS_UNDER_STOCK_ERROR, $goods['title'] . '库存不足');
            }

            /**检测商品是否支持配送方式**/
            if ($this->orderCreatorKernel->orderData['dispatch_type'] ==
                OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS && empty($goods['dispatch_express'])) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_EXPRESS_UNABLE);

            }

            if ($this->orderCreatorKernel->orderData['dispatch_type'] ==
                OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY && empty($goods['dispatch_intracity'])) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_INTRACITY_UNABLE);

            }

            if (!$this->orderCreatorKernel->isConfirm && $this->orderCreatorKernel->shopOrderSettings['trade']['stock_warning_state'] == SyssetTypeConstant::STOCK_WARNING_NOTICE_OPEN) {
                if ($this->orderCreatorKernel->shopOrderSettings['trade']['stock_warning_num'] >= $goods['stock']) {
                    $inventoryWarningMessages[] = [
                        'id' => $goods['id'],
                        'title' => $goods['title'],
                        'stock' => $goods['stock'],
                    ];
                }
            }

            // 计算商品合计价格
            $goodsPrice += $price;
            $costPrice += $goods['cost_price'];

            // 塞入订单商品
            $this->orderCreatorKernel->orderGoodsData[] = $goodsData;
        }

        // 2020-8-7 青岛开店星信息技术有限公司 注释  放到活动后执行
//        //检测商品限购
//        $this->checkBuyLimit();

        //设置商品价格
        $this->orderCreatorKernel->orderData['goods_price'] = round2($goodsPrice);

        //设置成本价
        $this->orderCreatorKernel->orderData['cost_price'] = round2($costPrice);

        //设置商品原价
        $this->orderCreatorKernel->orderData['original_goods_price'] = round2($goodsPrice);

        // 如果当前订单支持货到付款，处理商品的货到付款
        $isDeliveryPay = array_column($this->orderCreatorKernel->goods, 'is_delivery_pay');

        // 如果有一个商品不支持，或者配送方式不是快递的，设置整个订单不支持
        if (!in_array(0, $isDeliveryPay)) {
            $this->orderCreatorKernel->deliveryPay = 1;
        }

        // 如果当前订单支持发票，处理商品的发票
        $invoice = array_column($this->orderCreatorKernel->goods, 'invoice');

        // 如果有一个商品支持，设置整个订单支持
        if ($this->orderCreatorKernel->isInvoiceSupport == 1 && !in_array(1, $invoice)) {
            $this->orderCreatorKernel->isInvoiceSupport = 0;
        }

        //如果支持发票的话 设置发票类型
        $this->orderCreatorKernel->isInvoiceSupport == 1 && $this->orderCreatorKernel->invoiceType = $this->orderCreatorKernel->shopOrderSettings['trade']['invoice'];

        // 塞入creator的库存预警消息通知，下完单统一发送
        if (!empty($inventoryWarningMessages)) {
            $this->orderCreatorKernel->inventoryWarningMessages = $inventoryWarningMessages;
        }

        // 处理虚拟订单的自定义关闭时间
        $this->getAutoCloseTime();
    }

    /**
     * 检测商品限购
     * @author 青岛开店星信息技术有限公司
     */
    public function checkBuyLimit()
    {
        return;
    }

    /**
     * 更新库存和销量
     * @throws OrderCreatorException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function updateStock()
    {
        $result = GoodsService::updateQty(true, $this->orderCreatorKernel->orderData['id'], [], GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_ORDER, [
            'transaction' => false,
            'reason' => '下单减库存',
            'presell_activity_id' => $this->orderCreatorKernel->orderData['activity_return_data']['presell']['id'] ?? 0
        ]);
        if (is_error($result)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_HANDLER_UPDATE_STOCK_ERROR, $result['message']);
        }
    }

    /**
     * 获取关闭时间
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function getAutoCloseTime()
    {
        // 因init接口获取不到商品类型,故放在商品挂载后处理关闭时间
        // 如果是虚拟卡密,查询是否有单独设置关闭时间
        if ($this->orderCreatorKernel->goods[0]['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
            $setting = ShopSettings::get('virtual_setting');
            if ($setting['close_type'] != 0) {
                $this->orderCreatorKernel->autoCloseTime = date('Y-m-d H:i:s', strtotime($this->orderCreatorKernel->createTime) + ($setting['close_time'] * 60));
                //确认订单返回的数据
                if ($this->orderCreatorKernel->isConfirm) {
                    //自动关闭是否开启
                    $this->orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME;
                    //自动关闭时间不直接运用计算
                    $this->orderCreatorKernel->confirmData['auto_close_time'] = $setting['close_time'];
                }
            }
        }
    }

    /**
     * 自定义购买按钮(价格面议)商品阻止加入购物车/购买/下单
     * @return bool
     * @throws \shopstar\exceptions\goods\GoodsException
     * @author nizengchao
     */
    public function buyButtonGoodsBuyBlock(): bool
    {
        $goods = $this->orderCreatorKernel->goods;
        // 循环检测
        foreach ($goods as $good) {
            // 没有预热活动时, 走价格面议拦截
            $res = GoodsActivityService::getPreheatActivity($good['id'], 0, $this->orderCreatorKernel->clientType, 1);
            if (!$res) {
                GoodsService::buyButtonGoodsBuyBlock($good['ext_field']);
            }
        }

        return true;
    }
}
