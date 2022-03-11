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

namespace shopstar\models\order\create;

use shopstar\constants\form\FormTypeConstant;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\events\OrderCreatorEvents;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\printer\AutoPrinterOrder;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\consumeReward\ConsumeRewardLogModel;
use shopstar\models\form\FormModel;
use shopstar\models\form\FormTempModel;
use shopstar\models\order\create\handler\GoodsCartHandler;
use shopstar\models\order\create\handler\PaymentHandler;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\shoppingReward\ShoppingRewardLogModel;
use shopstar\services\commission\CommissionService;
use shopstar\services\order\OrderService;
use shopstar\services\sale\CouponMemberService;
use shopstar\structs\order\OrderPaySuccessStruct;

/**
 * 订单创建主要核心处理器
 * Class OrderCreatorKernel
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\models\order\create
 */
class OrderCreatorKernel
{

    /**
     * @var array 店铺订单设置
     */
    public $shopOrderSettings = [];

    /**
     * @var int 会员ID
     */
    public $memberId;

    /**
     * @var array 用户输入数据
     */
    public $inputData = [];

    /**
     * @var array 商品
     */
    public $goods = [];

    /**
     * @var array 商品IDs
     */
    public $goodsIds = [];

    /**
     * @var array 商城商品IDs
     */
    public $shopGoodsIds = [];

    /**
     * @var array 商品规格IDs
     */
    public $optionIds = [];

    /**
     * @var array 商品篮子
     */
    public $cartGoods = [];

    /**
     * @var array 订单数据
     */
    public $orderData = [];

    /**
     * @var array 确认订单时返回的额外数据(除orderData的数据)
     */
    public $confirmData = [];

    /**
     * @var array 订单商品数据
     */
    public $orderGoodsData = [];

    /**
     * @var array 会员信息
     */
    public $member = [];

    /**
     * @var array 收货地址
     */
    public $address = [];

    /**
     * @var array 支付方式
     */
    public $payment = [];

    /**
     * @var bool 是否可用货到付款
     */
    public $deliveryPay = 0;

    /**
     * @var int 是否支持发票
     */
    public $isInvoiceSupport = 0;

    /**
     * @var array 发票类型
     */
    public $invoiceType = [];

    /**
     * @var array 库存预警消息
     */
    public $inventoryWarningMessages = [];

    /**
     * @var string 订单自动关闭时间
     */
    public $autoCloseTime = 0;

    /**
     * @var string 订单自动收货时间
     */
    public $autoReceiveTime = 0;

    /**
     * @var bool 是否是确认订单页
     */
    public $isConfirm = true;

    /**
     * @var string 渠道
     */
    public $clientType;

    /**
     * @var int 是否原价购买
     */
    public $isOriginalBuy;
    /**
     * 购物车实例
     * @author 青岛开店星信息技术有限公司
     * @var GoodsCartHandler
     */
    public $cartHandlerExample;

    /**
     * 订单活动信息
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $orderActivity = [];

    /**
     * @var string 订单创建时间
     */
    public $createTime;
    /**
     * @var array 店铺联系方式
     */
    public $shopContact = [];

    /**
     * @var array 店铺同城配送配置
     */
    public $shopIntracity = [];

    /**
     * @var bool 虚拟商品
     */
    public $isVirtual = false;

    /**
     * 子商户
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $subShop = [];

    /**
     * 子店铺商品信息和单独的订单信息
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $subShopOrderData = [];

    /**
     * 所有保存完的订单信息
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $saveAfterOrderInfo = [];

    /**
     * 事件处理器 可复写
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    protected $kernelHandlers = [
        'init_handler' => 'shopstar\models\order\create\handler\InitHandler',
        'member_handler' => 'shopstar\models\order\create\handler\MemberHandler',
        'goods_handler' => 'shopstar\models\order\create\handler\GoodsHandler',
        'address_handler' => 'shopstar\models\order\create\handler\AddressHandler',
        'dispatch_handler' => 'shopstar\models\order\create\handler\DispatchHandler',
        'dispatch_intracity_handler' => 'shopstar\models\order\create\handler\DispatchIntracityHandler',
        'form_handler' => 'shopstar\models\order\create\handler\FormHandler',
        'order_save_handler' => 'shopstar\models\order\create\handler\OrderSaveHandler',
        'collect_data_handler' => 'shopstar\models\order\create\handler\CollectDataHandler',
        'verify_handler' => 'apps\verify\models\OrderVerifyHandler',
        'virtual_account_check_handler' => 'shopstar\models\order\create\handler\VirtualAccountCheckHandler',
    ];

    /**
     * 获取处理器
     * @param $handler
     * @return HandlerInterface|null
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司.
     */
    public function callHandler($handler)
    {
        //获取处理器明明空间
        $classPath = $this->kernelHandlers[$handler];

        //判断处理器是否有效
        if (!class_exists($classPath)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_HANDLER_INVALID_ERROR);
        }

        return new $classPath($this);
    }

    /**
     * OrderCreatorKernel constructor.
     * @param array $goodsData 商品数据
     * @param array $inputData 用户输入数据
     * @throws OrderCreatorException
     */
    public function __construct(array $goodsData = [], array $inputData = [])
    {
        //检测关键参数
        if (empty($inputData) || empty($inputData['member_id'])) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_PARAMS_INVALID_ERROR);
        }

        // 取出数据
        $this->memberId = $inputData['member_id'];
        unset($inputData['member_id']);

        // 定义传入数据
        $this->inputData = $inputData;

        //写入渠道
        $this->clientType = $inputData['client_type'];

        // 是否原价购买
        $this->isOriginalBuy = (int)$inputData['is_original_buy'];

        // 实例购物车处理器
        $this->cartHandlerExample = new GoodsCartHandler($this->memberId, $goodsData, $inputData['is_cart']);

        // 获取全部商品ID
        $this->goodsIds = $this->cartHandlerExample->getGoodsIds();

        // 获取全部规格映射
        $this->optionIds = $this->cartHandlerExample->getOptionIds();

        // 商品信息映射关系
        $this->cartGoods = $this->cartHandlerExample->getGoodsMap();

        // 订单类型
        $this->orderData['order_type'] = OrderTypeConstant::ORDER_TYPE_ORDINARY;

        // 订单配送类型
        $this->orderData['dispatch_type'] = (int)$this->inputData['dispatch_type'];

        // 订单额外的支付价格
        $this->orderData['extra_pay_price'] = [];

        // 同城配送超出范围是否使用普通快递
        $this->orderData['over_scope'] = ShopSettings::get('dispatch.intracity.over_scope');

        // 额外的数据包(表单数据\自动发货信息\会员卡信息等下单后不会修改的额外数据)
        $this->orderData['extra_package'] = [];

        // 定义创建订单的时间
        $this->createTime = DateTimeHelper::now();
    }

    /**
     * 初始化
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function init()
    {
        // before init
        $this->beforeInit();

        $this->callHandler('init_handler')->processor();
        //挂载创建订单事件
        new OrderCreatorEventsConfig();

        //触发init事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_INIT, $this);

    }

    /**
     * 开始处理下单
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    protected function start(): void
    {

        $this->init();

        //检测支付方式
        $paymentHandler = new PaymentHandler($this);
        $paymentHandler->check();

        // 获取会员信息并挂载到订单数据
        $this->callHandler('member_handler')->processor();

        // 加载商品并挂载到订单数据
        $this->callHandler('goods_handler')->processor();

        // 判断是否虚拟卡密订单并判断应用是否可用
        $this->callHandler('virtual_account_check_handler')->processor();

        // 获取地址并挂载到订单数据
        $this->callHandler('address_handler')->processor();

        // 活动执行前进行计算运费并挂载到订单数据
        $this->callHandler('dispatch_handler')->processor();

        // 活动执行前进行计算同城配送运费并挂载到订单数据
        $this->callHandler('dispatch_intracity_handler')->processor();

        // 核销
        if ($this->orderData['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
            $this->callHandler('verify_handler')->processor();
        }

        // 下单前事件 执行订单活动
        $this->beforeCreate();

        // 处理活动冲突
        $this->handleActivityConflict();


        // 最后一次计算运费并挂载到订单数据
        $this->callHandler('dispatch_handler')->processor();

        //触发订单创建前事件
        OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_BEFORE_CREATE, $this);
    }

    /**
     * 确认订单页面(预览数据)
     * @return array
     * @throws OrderCreatorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function confirm()
    {
        $this->isConfirm = true;

        // 执行创建
        $this->create();

        $return = ArrayHelper::only($this->orderData, [
            'member_id',
            'member_nickname',
            'member_mobile',
            'member_avatar',
            'buyer_remark',
            'buyer_mobile',
            'buyer_name',
            'dispatch_type',

            'address_id',
            'address_state',
            'address_city',
            'address_area',
            'address_detail',
            'address_name',
            'opening_type',
            'opening_rule',
            'verify_info',
            'dispatch_verify_point_count',
            'verify_setting',

            'activity_type',
            'order_type',

            'goods_price',
            'pay_price',
            'dispatch_price',
            'pay_credit',

            'is_invoice_support',
            'invoice_type',
            'goods_info',

            'extra_discount_rules_package',
            'extra_price_package',

            //商品已购买个数
            'buy_goods_num',

            //确认订单返回的活动数据
            'activity_return_data',

            // 选择的优惠券id
            'select_coupon_id',

            // 同城配送
            'dispatch_intracity_error',
            'dispatch_area',

            'over_scope',

            'sub_shop',

        ]);

        // 是否可以货到付款
        $return['delivery_pay'] = $this->deliveryPay;

        // 发票相关
        $return['is_invoice_support'] = $this->isInvoiceSupport;

        // 确认订单的发票信息
        if ($this->isInvoiceSupport) {
            $return['invoice_info'] = $this->orderData['invoice_info'];
        }

        // 发票类型 0:不支持  1:纸质 2：电子 3：全选
        $return['invoice_type'] = $this->invoiceType;

        // 会员当前积分
        $return['member_credit'] = $this->member['credit'];

        // 如果会员积分小于最大抵扣积分，则最大抵扣积分就是会员当前积分
        if ($this->member['credit'] < $this->confirmData['max_deduction_credit']) {
            $this->confirmData['max_deduction_credit'] = $this->member['credit'];
        }

        // 会员当前 余额
        $return['member_balance'] = $this->member['balance'];

        // 如果会员积分小于最大抵扣积分，则最大抵扣积分就是会员当前积分
        if ($this->member['balance'] < $this->confirmData['max_deduction_balance']) {
            $this->confirmData['max_deduction_balance'] = $this->member['balance'];
        }

        // 选择的优惠券id
        $return['select_coupon_id'] = $this->confirmData['select_coupon_id'];

        // 计算本单为您节省
//        $return['save_money_total'] = $this->orderData['original_price'] - $this->orderData['pay_price'];
//        if ($return['save_money_total'] < 0) {
//            $return['save_money_total'] = 0;
//        }

        // 合并订单数据(orderData)与确认订单时需返回的额外数据(confirmData)

        $return = array_merge($return, $this->confirmData);

        return $return;
    }

    /**
     * 执行创建
     * @throws OrderCreatorException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    protected function create()
    {
        $this->isConfirm == false && $tr = \Yii::$app->db->beginTransaction();

        try {

            $this->start();

            // 收集订单数据
            $this->callHandler('collect_data_handler')->processor();

            // 确认订单页请止步
            if ($this->isConfirm) {
                return;
            }

            // 订单保存前事件
            $this->beforeSave();

            // 保存订单
            $order = $this->callHandler('order_save_handler')->processor();

            // 更新保存订单后的数据给类变量
            $this->orderData['id'] = $order['id'];
            $this->orderData['pay_price'] = $order['pay_price'];
            $this->orderData['order_no'] = $order['order_no'];

            if (isset($order['single_order_pay_price'])) {
                $this->orderData['single_order_pay_price'] = $order['single_order_pay_price'];
            }

            //触发订单创建后事件
            OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_AFTER_CREATE, $this);

            // 订单创建后的事件
            $this->afterCreate();

            // 提交事务
            $this->isConfirm == false && $tr->commit();

            // 订单创建提交事务后处理
            $this->afterCreateCommit();

            //触发创建订单提交事务后事件
            OrderCreatorEventAssistant::trigger(OrderCreatorEvents::EVENT_AFTER_CREATE_COMMIT, $this);

        } catch (\Exception $exception) {

            // 回滚事务
            $this->isConfirm == false && $tr->rollBack();

            // 转抛异常
            throw new OrderCreatorException($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * 创建前处理
     * @author 青岛开店星信息技术有限公司
     */
    protected function beforeCreate(): void
    {
    }

    /**
     * 保存前处理
     * @author 青岛开店星信息技术有限公司
     */
    protected function beforeSave(): void
    {
    }

    /**
     * 处理活动限制冲突
     * 有些限制以活动为主,需要把之前的检测拿到这里进行判断要不要执行
     * 比如 商品预售里检测每人限购数量 不管商品设置
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    private function handleActivityConflict()
    {
        // 活动类型
        $type = array_column($this->orderActivity, 'type');
        // 商品预售 秒杀 与 商品限购冲突 单独执行
        if (!in_array('presell', $type) && !in_array('seckill', $type) && !in_array('groups', $type)) {
            $this->callHandler('goods_handler')->checkBuyLimit();
        }

        // 没有活动时, 并且不是从购物车进行的购买,处理价格面议商品的下单拦截
        if (empty($type) && !$this->inputData['is_cart']) {
            $this->callHandler('goods_handler')->buyButtonGoodsBuyBlock();
        }

    }


    /**
     * 创建订单编号
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function createOrderNo()
    {
        return OrderNoHelper::getOrderNo($this->orderData['order_type'], (string)$this->clientType);
    }

    /**
     * 订单创建后
     * @throws OrderCreatorException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     */
    protected function afterCreate(): void
    {
        // 录入订单活动统计表
        $this->saveOrderActivity();

        // 变更库存和销量
        $this->updateStock();

        // 删除购物车中的商品
        $this->updateCart();

        // 订单参加的活动
        $orderActivity = array_keys($this->orderData['extra_price_package']);

        // 支持分销
        $isCommission = true;

        // 需要检查是否参与分销的活动
        $checkCommissionActivity = [
            'presell', // 预售
            'seckill', // 秒杀
            'groups',//拼团
            'full_reduce', // 满减折
        ];
        // 取活动交集
        $activityIntersect = array_intersect($orderActivity, $checkCommissionActivity);
        // 分销  如果活动交集为空 或者 活动的设置参与分销 则 进入分销

        // 如果有活动  检测活动是否支持分销
        if (!empty($activityIntersect)) {
            // 如果包含预售
            if (in_array('presell', $activityIntersect)) {
                // 不支持
                $this->orderData['activity_return_data']['presell']['is_commission'] == 0 && $isCommission = false;
            } else {
                // 其他活动
                $activityIntersect = array_values($activityIntersect);
                // 取订单参与的活动
                $activity = array_column($this->orderData['extra_discount_rules_package'], $activityIntersect[0]);
                // 不支持
                if (!isset($activity[0]['rules']) || !$activity[0]['rules']['is_commission']) {
                    $isCommission = false;
                }
            }
        }

        // 积分商城不支持分销
        if ($this->orderData['activity_type'] == OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            $isCommission = false;
        }

        // 支持分销 且有权限
        if ($isCommission) {
            $this->commissionHandler($this->orderData);
        }


        // 消费奖励 积分商城不支持
        if ($this->orderData['activity_type'] != OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            ConsumeRewardLogModel::createLog($this->memberId, $this->orderData['id'], $this->clientType, 1);
        }

        // 购物奖励
        if ($this->orderData['activity_type'] != OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP) {
            ShoppingRewardLogModel::createLog($this->memberId, $this->orderData['id'], $this->clientType);
        }

        //小程序直播
        if ($this->orderData['scene'] == OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST) {
            //增加小程序直播商品销量
            BroadcastRoomGoodsMapModel::addSales($this->orderData['scene_value'], $this->orderGoodsData);
        }
        $formExists = false;
        // 获取订单表单
        $formExists = FormModel::find()
            ->where([
                'type' => FormTypeConstant::FORM_TYPE_ORDER,
                'is_deleted' => 0,
                'status' => 1
            ])->count();


        // 打印小票
        // @change 倪增超 没有订单表单时, 才去打印, 否则在表单插件里打印
        if (!$formExists) {
            QueueHelper::push(new AutoPrinterOrder([
                'job' => [
                    'scene' => PrinterSceneConstant::PRINTER_ORDER,
                    'order_id' => $this->orderData['id']
                ]
            ]));
        }

        //商品表单收集
        FormTempModel::orderCreate($this->orderData['id'], $this->memberId, (array)$this->orderData['goods_info'], $this->cartHandlerExample->getIsCart() ? $this->cartHandlerExample->getCartIds() : []);

        // 优惠券
        $this->updateMemberCoupon($this->orderData);

        //判断 如果订单支付金额小于等于0直接调用支付完成后方法
        if (is_array($this->orderData['id'])) {
            foreach ($this->saveAfterOrderInfo as $index => $item) {
                //paysuccess结构体
                $orderPaySuccessStruct = \Yii::createObject([
                    'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                    'accountId' => $this->memberId,
                    'orderId' => $item['id'],
                ]);

                /**
                 * @var OrderPaySuccessStruct $orderPaySuccessStruct
                 */

                $item['pay_price'] <= 0 && OrderService::paySuccess($orderPaySuccessStruct);
            }
        } else {
            //paysuccess结构体
            $orderPaySuccessStruct = \Yii::createObject([
                'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                'accountId' => $this->memberId,
                'orderId' => $this->orderData['id'],
            ]);

            /**
             * @var OrderPaySuccessStruct $orderPaySuccessStruct
             */

            $this->orderData['pay_price'] <= 0 && OrderService::paySuccess($orderPaySuccessStruct);
        }
    }

    /**
     * 保存订单活动信息
     * @return bool
     * @throws OrderCreatorException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function saveOrderActivity()
    {
        if (empty($this->orderActivity)) {
            return true;
        }
        $column = ['order_id', 'activity_id', 'activity_type', 'rule_index'];
        $data = [];

        // 订单活动
        foreach ($this->orderActivity as &$activity) {
            if (!$activity['id']) {
                continue;
            }

            $data[] = [
                !is_array($this->orderData['id']) ? $this->orderData['id'] : $this->orderData['id'][0], // 兼容多商户 订单id是数组 只取第一个 现在活动只允许一个商品 只会有一个订单id
                $activity['id'] ?? 0, //
                $activity['type'] ?? '', //
                $activity['rule_index'] ?? 0
            ];
        }

        if (empty($data)) {
            return;
        }

        $result = \Yii::$app->db->createCommand()
            ->batchInsert(OrderActivityModel::tableName(), $column, $data)
            ->execute();

        if (empty($result)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_ORDER_ACTIVITY_ERROR);
        }
    }

    /**
     * 分销处理
     * @param array $orderData
     * @author 青岛开店星信息技术有限公司
     */
    protected function commissionHandler(array $orderData)
    {
        // 分销
        CommissionService::orderCreate($orderData['id'], $this->member['id']);
    }

    /**
     * 使用优惠券
     * @param array $orderData
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    protected function updateMemberCoupon(array $orderData)
    {
        // 判断有没有使用优惠券
        CouponMemberService::useCoupon($orderData);
    }

    /**
     * 更新库存
     * @throws OrderCreatorException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    protected function updateStock(): void
    {
        // 调用商品处理器更新库存
        $this->callHandler('goods_handler')->updateStock();
    }

    /**
     * 删除购物车
     * @author 青岛开店星信息技术有限公司
     */
    protected function updateCart(): void
    {
        $this->cartHandlerExample->deleteCart();
    }

    /**
     * 订单创建提交事务后执行
     * @author 青岛开店星信息技术有限公司
     */
    protected function afterCreateCommit(): void
    {
        if (!empty($this->inventoryWarningMessages)) {
            // TODO 青岛开店星信息技术有限公司 统一发送 库存预警
        }
    }

    /**
     * 提交创建订单
     * @return array
     * @throws OrderCreatorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 青岛开店星信息技术有限公司
     */
    public function submit()
    {
        $this->isConfirm = false;

        // 执行创建
        $this->create();

        return [
            'id' => $this->orderData['id'],
            'pay_price' => $this->orderData['pay_price'],
            'single_order_pay_price' => $this->orderData['single_order_pay_price'] ?? [],//每个订单自己的支付金额
            'payment' => $this->payment,
        ];
    }
}
