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

namespace shopstar\services\creditShop\handler;

use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\creditShop\CreditShopConstant;
use shopstar\constants\creditShop\CreditShopGoodsTypeConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\exceptions\creditShop\CreditShopOrderException;
use shopstar\helpers\ValueHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopGoodsOptionModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\services\creditShop\CreditShopOrderService;
use yii\helpers\Json;

/**
 * 商品信息
 * Class GoodsHandler.
 * @package shopstar\services\creditShop\handler
 */
class GoodsHandler implements HandlerInterface
{
    /**
     * 订单实体类
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public OrderCreatorKernel $orderCreatorKernel;

    /**
     * @var array 加载商品的字段
     */
    protected array $getGoodsField = [
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
        'goods_code',
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
    protected array $getOptionField = [
        'id as option_id',
        'goods_id as id',
        'title as option_title',
        'bar_code',
        'price',
        'cost_price',
        'stock',
        'weight',
        'goods_code',
        'thumb as option_thumb',
    ];

    /**
     * GoodsHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 防止别处添加莫名其妙的方法
     * @param string $name
     * @param array $arguments
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function __call(string $name, array $arguments)
    {

    }

    /**
     * 处理商品信息
     * @return void
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 积分商城商品
        $creditShopGoods = CreditShopGoodsModel::find()
            ->where([
                'id' => $this->orderCreatorKernel->goodsIds,
                'status' => 1
            ])->first();

        if (empty($creditShopGoods)) {
            throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_GOODS_NOT_EXISTS);
        }

        $creditShopGoods['option_id'] = 0;
        $creditShopGoods['credit_shop_type'] = $creditShopGoods['type'];

        // 查找规格
        if ($this->orderCreatorKernel->optionIds) {
            $creditShopGoodsOption = CreditShopGoodsOptionModel::find()
                ->select(['id as option_id', 'option_id shop_option_id', 'credit_shop_credit', 'credit_shop_price', 'credit_shop_stock'])
                ->where(['id' => $this->orderCreatorKernel->optionIds, 'credit_shop_goods_id' => $this->orderCreatorKernel->goodsIds])
                ->first();

            if (empty($creditShopGoodsOption)) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_GOODS_NOT_EXISTS);
            }

            $creditShopGoods = array_merge($creditShopGoods, $creditShopGoodsOption);
        }

        // 返回积分商品信息
        $this->orderCreatorKernel->orderData['activity_return_data']['credit_shop'] = $creditShopGoods;

        // 商品信息
        $goodsInfo = [];

        // 商品类型
        if ($creditShopGoods['credit_shop_type'] == CreditShopGoodsTypeConstant::GOODS) {
            // 查找原商品
            $originalGoods = GoodsModel::find()
                ->where([
                    'id' => $creditShopGoods['goods_id'],
                    'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY],
                ])
                ->select($this->getGoodsField)->first();

            // 原商品不存在
            if (empty($originalGoods)) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_GOODS_NOT_EXISTS);
            }

            // 按原商品类型
            // 虚拟商品
            if (GoodsTypeConstant::GOODS_TYPE_VIRTUAL == $originalGoods['type']) {
                $this->orderCreatorKernel->isVirtual = true;
                // 订单类型
                $this->orderCreatorKernel->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_VIRTUAL;
            }
            // 判断是否虚拟卡密类型
            if (GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT == $originalGoods['type']) {
                $this->orderCreatorKernel->isVirtual = true;
                // 订单类型
                $this->orderCreatorKernel->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT;
            }

            //格式化商品扩展数据
            $originalGoods['ext_field'] = Json::decode($originalGoods['ext_field']);
            $originalGoods['option_title'] = '';
            $originalGoods['option_id'] = '0';

            // 查找规格
            // 多规格
            if (!empty($creditShopGoodsOption)) {
                $goodsOption = GoodsOptionModel::find()->select($this->getOptionField)->where(['id' => $creditShopGoodsOption['shop_option_id']])->first();
                $originalGoods = array_merge($originalGoods, $goodsOption);
            }

            // 赋值原商品id
            $this->orderCreatorKernel->shopGoodsIds[] = $originalGoods['id'];

            $goodsInfo = $originalGoods;
        } else {
            // 优惠券类型
            // 查找原优惠券
            $shopCoupon = CouponModel::find()->where(['id' => $creditShopGoods['goods_id'], 'state' => 1])->first();

            if (empty($shopCoupon)) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_GOODS_NOT_EXISTS);
            }

            // 剩余库存
            if ($shopCoupon['stock_type'] == 0) {
                $shopCoupon['stock'] = 99999999; // 给定最大值
            } else {
                $shopCoupon['stock'] = $shopCoupon['stock'] - $shopCoupon['get_total'];
            }

            $shopCoupon['option_title'] = '';
            $shopCoupon['option_id'] = 0;

            // 组装商品信息
            $goodsInfo = $shopCoupon;

            // 存放优惠券部分信息
            $goodsInfo['ext_field'] = [
                'is_credit_shop_coupon' => 1,
                'coupon_sale_type' => $shopCoupon['coupon_sale_type'],
                'refund' => 1,
                'return' => 0,
                'exchange' => 0,
            ];

            if ($shopCoupon['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                $goodsInfo['ext_field']['content'] = '满' . ValueHelper::delZero($shopCoupon['enough']) . '减' . ValueHelper::delZero($shopCoupon['discount_price']);
            } else {
                // 打折类型
                $goodsInfo['ext_field']['content'] = '满' . ValueHelper::delZero($shopCoupon['enough']) . '享' . ValueHelper::delZero($shopCoupon['discount_price']) . '折';
            }

            // 积分兑换的优惠券订单类型 优惠券特殊处理
            $this->orderCreatorKernel->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON;
        }

        $goodsInfo['reduction_type'] = 1; // 默认都是付款减库存

        // 判断购买权限 会员等级和标签的限制 读自己设置的
        // 会员等级限制
        if ($creditShopGoods['member_level_limit_type'] != CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_NOT_LIMIT) {
            $limitLevelId = explode(',', $creditShopGoods['member_level_id']);
            // 无权限
            if (($creditShopGoods['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && !in_array($this->orderCreatorKernel->member['level_id'], $limitLevelId))
                || ($creditShopGoods['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && in_array($this->orderCreatorKernel->member['level_id'], $limitLevelId))) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_NOT_BUY_LIMIT);
            }
        }

        // 标签限制
        if ($creditShopGoods['member_group_limit_type'] != CreditShopConstant::MEMBER_GROUP_LIMIT_TYPE_NOT_LIMIT) {
            $limitGroupId = explode(',', $creditShopGoods['member_group_id']);
            // 获取会员标签
            $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($this->orderCreatorKernel->memberId);
            // 判断有没有交集
            $isIntersect = array_intersect($limitGroupId, $memberGroupId);
            // 无权限
            if (($creditShopGoods['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && !$isIntersect)
                || ($creditShopGoods['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && $isIntersect)) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_NOT_BUY_LIMIT);
            }
        }

        // 考虑原商品id的字段  后面只要有原商品id  就不用现在的
        $goodsInfo['shop_goods_id'] = $goodsInfo['id'];
        $goodsInfo['shop_option_id'] = $goodsInfo['option_id'];
        $goodsInfo['shop_stock'] = $goodsInfo['stock'];
        $goodsInfo['shop_goods_type'] = $goodsInfo['type'];
        $goodsInfo['shop_goods_dispatch_type'] = $goodsInfo['dispatch_type'];

        $goodsInfo = array_merge($goodsInfo, $creditShopGoods);
        $goodsInfo['price'] = $goodsInfo['credit_shop_price'];
        $goodsInfo['credit'] = $goodsInfo['credit_shop_credit'];
        $goodsInfo['stock'] = $goodsInfo['credit_shop_stock'];

        // 塞入kernel里
        $this->orderCreatorKernel->goods[] = $goodsInfo;

        // 构造 orderGoodsData
        // 设置订单价格等
        $this->initOrderGoods();

        $this->orderCreatorKernel->deliveryPay = 0;

        // 虚拟商品自动关闭时间
        $this->getAutoCloseTime();
    }

    /**
     * 订单商品
     * @return void
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    protected function initOrderGoods()
    {
        // 生成基础的OrderGoods结构，部分等下单时再塞入
        foreach ($this->orderCreatorKernel->goods as &$goods) {
            $cartKey = $goods['option_id'] > 0 ? 'option_' . $goods['option_id'] : 'goods_' . $goods['id'];
            $cartGoods = $this->orderCreatorKernel->cartGoods[$cartKey];
            //计算商品价格
            $price = round2($goods['price'] * $cartGoods['total']);
            // 积分
            $credit = round2($goods['credit'] * $cartGoods['total']);

            //货到付款
            $goods['is_delivery_pay'] = 0;
            //发票
            $goods['invoice'] = $goods['ext_field']['invoice'];

            // 商品
            if ($goods['credit_shop_type'] == CreditShopGoodsTypeConstant::GOODS) {
                // 商品单位
                $unitSystem = '件';
                $goodsData = [
                    'goods_id' => $goods['id'],
                    'type' => $goods['shop_goods_type'],
                    'ext_goods_id' => 0,
                    'stock' => $goods['stock'],
                    'price' => $price,
                    'price_unit' => $goods['price'],
                    'price_original' => $price,
                    'price_discount' => 0,
                    'price_change' => 0,
                    'credit' => $credit, // 积分价
                    'credit_unit' => $goods['credit'],
                    'reduction_type' => $goods['reduction_type'],
                    'credit_original' => 0,
                    'coupon_price' => 0,
                    'total' => $cartGoods['total'],
                    'weight' => $goods['weight'],
                    'title' => $goods['title'],
                    'sub_name' => $goods['sub_name'],
                    'short_name' => $goods['short_name'],
                    'option_id' => $goods['option_id'],
                    'option_title' => $goods['option_title'],
                    'thumb' => !empty($goods['option_thumb']) ? $goods['option_thumb'] : $goods['thumb'],
                    'goods_code' => $goods['goods_code'],
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
                        'type' => $goods['shop_goods_dispatch_type'],
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
                        'is_credit_shop' => 1,
                    ],
                    'dispatch_express' => $goods['dispatch_express'], // 是否支持快递 0否1是
                    'dispatch_intracity' => $goods['dispatch_intracity'], // 是否支持同城配送 0否1是
                    'form_id' => $goods['form_id'],
                    'form_status' => $goods['form_status'],
                    'cost_price' => $goods['cost_price'],
                    'shop_goods_id' => $goods['shop_goods_id'],
                    'shop_option_id' => $goods['shop_option_id'],
                    'shop_stock' => $goods['shop_stock'],
                    'is_all_verify' => $goods['is_all_verify'] ?: 0,
                    'goods_unit' => $unitSystem,
                ];

                // 虚拟卡密添加卡密库id和是否开启邮箱设置
                if ($goods['shop_goods_type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                    $goodsData['virtual_account_id'] = $goods['virtual_account_id'];
                    $goodsData['virtual_account_mailer_setting'] = VirtualAccountModel::checkMailer($goods['virtual_account_id']) ? 1 : 0;
                }

                // 处理多规格的关联卡密库id
                if ($goods['option_id'] != 0 && $goods['shop_goods_type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                    $goodsData['virtual_account_id'] = GoodsOptionModel::getInfoById($goods['shop_option_id']);
                    $goodsData['virtual_account_mailer_setting'] = VirtualAccountModel::checkMailer($goodsData['virtual_account_id']) ? 1 : 0;
                }
            } else {
                // 优惠券
                $goodsData = [
                    'goods_id' => $goods['id'],
                    'type' => GoodsTypeConstant::GOODS_TYPE_CREDIT_SHOP_COUPON,
                    'ext_goods_id' => 0,
                    'stock' => $goods['stock'],
                    'title' => $goods['coupon_name'],
                    'auto_deliver' => 1, // 优惠券自动发货
                    'option_id' => $goods['option_id'],
                    'price' => $price,
                    'price_unit' => $goods['price'],
                    'price_original' => $price,
                    'price_discount' => 0,
                    'price_change' => 0,
                    'credit' => $credit, // 积分价
                    'credit_unit' => $goods['credit'],
                    'reduction_type' => $goods['reduction_type'],
                    'total' => $cartGoods['total'],
                    'refund_status' => 0,
                    'refund_type' => 0,
                    'refund_id' => 0,
                    'activity_package' => [],
                    'plugin_identification' => [ // 插件标识
                        'is_credit_shop' => 1,
                    ],
                    'ext_field' => $goods['ext_field'],
                    'shop_goods_id' => $goods['shop_goods_id'],
                    'shop_option_id' => $goods['shop_option_id'],
                    'shop_stock' => $goods['shop_stock'],
                ];
            }

            // 库存不足 判断永不减库存
            if (($goods['stock'] < $goodsData['total'] || $goods['shop_stock'] < $goodsData['total'])) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_STOCK_INVALID_ERROR);
            }

            // 不支持同城配送
            if ($this->orderCreatorKernel->orderData['dispatch_type'] ==
                OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY && empty($goods['dispatch_intracity'])) {
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_CREDIT_SHOP_INTRACITY_UNABLE);
            }

            // 塞入订单商品
            $this->orderCreatorKernel->orderGoodsData[] = $goodsData;
        }

        //设置商品价格
        $this->orderCreatorKernel->orderData['goods_price'] = round2($price);

        //设置商品原价
        $this->orderCreatorKernel->orderData['original_goods_price'] = round2($price);

        //设置积分价格
        $this->orderCreatorKernel->orderData['credit'] = round2($credit);

        // 如果是提交支付 检测用户剩余积分
        if (!$this->orderCreatorKernel->isConfirm && $this->orderCreatorKernel->member['credit'] < $credit) {
            throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_SUBMIT_MEMBER_CREDIT_NOT_ENOUGH);
        }

        // 如果当前订单支持发票，处理商品的发票
        $invoice = array_column($this->orderCreatorKernel->goods, 'invoice');

        // 如果有一个商品支持，设置整个订单支持
        if ($this->orderCreatorKernel->isInvoiceSupport == 1 && !in_array(1, $invoice)) {
            $this->orderCreatorKernel->isInvoiceSupport = 0;
        }

        //如果支持发票的话 设置发票类型
        $this->orderCreatorKernel->isInvoiceSupport == 1 && $this->orderCreatorKernel->invoiceType = $this->orderCreatorKernel->shopOrderSettings['trade']['invoice'];

        // 处理虚拟订单的自定义关闭时间
        $this->getAutoCloseTime();
    }

    /**
     * 检测限购 购买次数限制
     * @return void
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function checkBuyLimit()
    {
        // 开启限购
        if ($this->orderCreatorKernel->goods[0]['goods_limit_type'] != CreditShopConstant::GOODS_LIMIT_TYPE_NOT_LIMIT) {
            $limitDay = $this->orderCreatorKernel->goods[0]['goods_limit_type'] == CreditShopConstant::GOODS_LIMIT_TYPE_LIMIT_DAY ? $this->orderCreatorKernel->goods[0]['goods_limit_day'] : 0;
            $buyNum = CreditShopOrderService::getBuyTotal($this->orderCreatorKernel->goods[0]['id'], $this->orderCreatorKernel->memberId, $limitDay);

            //如果是确认订单
            if ($this->orderCreatorKernel->isConfirm) {
                $this->orderCreatorKernel->orderData['buy_goods_num'] = $buyNum;
                $this->orderCreatorKernel->orderData['activity_return_data']['credit_shop']['buy_count'] = $buyNum;
            }

            if (($this->orderCreatorKernel->orderGoodsData[0]['total'] + $buyNum) > $this->orderCreatorKernel->goods[0]['goods_limit_num']) {
                // 超过购买限制
                throw new CreditShopOrderException(CreditShopOrderException::GOODS_HANDLER_GOODS_MAX_BUY_ERROR);
            }
        }
    }

    /**
     * 积分商城自己的减库存方法
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function updateStock()
    {
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
}
