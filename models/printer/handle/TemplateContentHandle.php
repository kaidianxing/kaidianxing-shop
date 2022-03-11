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

namespace shopstar\models\printer\handle;

use shopstar\components\payment\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\constants\printer\PrinterTypeConstant;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\form\FormLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\printer\PrinterTemplateModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

class TemplateContentHandle
{

    /**
     * @var PrinterTemplateModel 模板
     */
    public $template;

    /**
     * @var string 模板内容
     */
    public $template_content;

    /**
     * @var array 订单
     */
    public $order;

    /**
     * @var array 商品信息
     */
    public $goods_info;

    /**
     * @var array 会员信息
     */
    public $member_info;

    /**
     * @var int 打印次数
     */
    public $times;

    /**
     * @var int 打印机类型
     */
    public $printer_type;

    /**
     * @var int 商品编码或规格
     */
    public $goods_title_code_or_option;

    /**
     * 商品编码
     */
    const goods_sku = 1;

    /**
     * 商品规格
     */
    const GOODS_OPTION = 2;

    /**
     * 下单表单
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $orderFormData = [];

    /**
     * 打印时隐藏 运费 的订单类型: 虚拟商品, 虚拟卡密, 预约到店
     * @var array
     */
    private $hiddenDispatchPrice = [
        OrderTypeConstant::ORDER_TYPE_VIRTUAL,
        OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT,
    ];

    /**
     * 打印时隐藏 配送方式 的订单类型: 虚拟卡密
     * @var array
     */
    private $hiddenDispatchType = [
        OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT,
    ];

    public function __construct(int $templateId, int $orderId, int $printerType, $times = 1)
    {

        $this->goods_title_code_or_option = 0;


        $this->printer_type = $printerType;

        // 模板
        $this->template = PrinterTemplateModel::find()->where(['id' => $templateId, 'is_deleted' => 0])->one();

        // 订单
        $this->order = OrderModel::getOrderGoodsInfo($orderId);

        //表单
        $orderForm = FormLogModel::where([
            'order_id' => $orderId,
            'goods_id' => 0,
        ])->first();

        $this->orderFormData = $orderForm ? Json::decode($orderForm['content']) : [];

        // 订单商品
        $this->goods_info = Json::decode($this->order['goods_info']);

        // 订单商品合计
        $this->order['total'] = array_sum(array_column($this->goods_info, 'total'));

        // 优惠价格
        $this->order['price_discount'] = number_format(array_sum(array_column($this->goods_info, 'price_discount')), 2);

        // 会员信息
        $this->member_info = MemberModel::find()
            ->where([
                'id' => $this->order['member_id'],
                'is_deleted' => 0
            ])
            ->with('level')
            ->first();

        $this->verifyInfo = [];

        $this->times = $times;
    }

    /**
     * 根据模板返回打印内容
     * @param PrinterTemplateModel $template
     * @author 青岛开店星信息技术有限公司
     */
    public function getTemplatePrintContent()
    {
        // 模板内容
        $this->template_content = Json::decode($this->template->content);

        if ($this->printer_type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
            $content = $this->ylyContent();
        } else {
            $content = $this->feyContent();
        }
        return $content;
    }

    private function ylyContent()
    {
        $content = '';
        $content .= "<MN>$this->times</MN>";
        $content .= "\r\n";

        foreach ($this->template_content as $item) {
            switch ($item['type']) {
                case 'header_info':
                    $headerInfoLine = false;
                    foreach ($item['children'] as $child) {

                        if ($child['type'] == 'shop_name' && $child['status'] == 1) {
                            $headerInfoLine = true;
                            // 头部信息-商城名称
                            $headerInfo = ShopSettings::get('sysset.mall.basic.name');
                            $content .= "<FS2><center>$headerInfo</center></FS2>";
                        }
                    }
                    if ($headerInfoLine) {
                        $content .= str_repeat('.', 32);
                    }
                    break;

                case 'goods_info':
                    $content = $this->getGoodsInfo($item, $content);

                    break;
                case 'calculate_info':
                    // 合计 运费 优惠金额 实付金额
                    $calculateInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_original_price' && $child['status'] == 1) {
                            $calculateInfoLine = true;
                            // 合计
                            $originGoodsPrice = $this->order['original_goods_price'] ?? '';
                            $content .= "合计            x{$this->order['total']}      ￥$originGoodsPrice";
                            $content .= "\r\n";
                        }

                        // 预约到店, 虚拟, 卡密商品不打印运费
                        // 到店核销物流方式不打印运费
                        if ($child['type'] == 'dispatch_price' && $child['status'] == 1 && !in_array($this->order['order_type'], $this->hiddenDispatchPrice) && $this->order['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
                            $calculateInfoLine = true;
                            // 运费
                            $dispatchPrice = $this->order['dispatch_price'] ?? '';
                            $content .= "<LR>运费,￥$dispatchPrice</LR>";
                        }

                        if ($child['type'] == 'order_discounts_price' && $child['status'] == 1) {
                            $calculateInfoLine = true;
                            // 优惠金额
                            $discountPrice = $this->getDiscountPrice();
                            $content .= "<LR>优惠金额,$discountPrice</LR>";
                        }

                        if ($child['type'] == 'order_pay_price' && $child['status'] == 1) {
                            $calculateInfoLine = true;
                            // 实付金额
                            $payPrice = $this->order['pay_price'] ?? '';
                            $content .= "<LR>实付金额,￥$payPrice</LR>";
                        }
                    }
                    if ($calculateInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'order_info':
                    $orderInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_no' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 订单编号
                            $orderNo = $this->order['order_no'] ?? '';
                            $content .= "订单编号：$orderNo";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'pay_type' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 支付方式
                            $payType = $this->order['pay_type'] ?? '';
                            $payType = !empty($payType) ? $payType = PayTypeConstant::getMessage($payType) : '';
                            $content .= "支付方式：$payType";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'pay_channel' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 支付渠道
                            $payChannel = $this->order['create_from'] ?? '';
                            $payChannel = !empty($payChannel) ? $payChannel = ClientTypeConstant::getText($payChannel) : '';
                            $content .= "支付渠道：$payChannel";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'created_at' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 下单时间
                            $createTime = $this->order['created_at'] == 0 ? '' : $this->order['created_at'];
                            $content .= "下单时间：$createTime";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'pay_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 付款时间
                            $payTime = $this->order['pay_time'] == 0 ? '' : $this->order['pay_time'];
                            $content .= "付款时间：$payTime";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'finish_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 收货时间
                            $finishTime = $this->order['finish_time'] == 0 ? '' : $this->order['finish_time'];
                            $content .= "收货时间：$finishTime";
                            $content .= "\r\n";
                        }

                        //配送方式 虚拟卡密不打印
                        if ($child['type'] == 'dispatch_type' && $child['status'] == 1 && !in_array($this->order['order_type'], $this->hiddenDispatchType)) {
                            $orderInfoLine = true;
                            $content .= "配送方式：" . OrderDispatchExpressConstant::getText($this->order['dispatch_type']);
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'delivery_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 联系地址
                            $ex = Json::decode($this->order['extra_package']);
                            if (!empty($ex['delivery_time']) && $ex['delivery_time'] != '0000-00-00 00:00:00') {
                                $time = $ex['delivery_time'];
                                $content .= "配送/自提时间：$time";
                                $content .= "\r\n";
                            }
                        }

                        // 表单
                        if ($child['type'] == 'order_form' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            if (!empty($this->orderFormData)) {
                                foreach ($this->orderFormData as $item) {
                                    if ($item['type'] == 'checkboxes') { //多选

                                        $content .= $item['params']['title'] . ": ";
                                        foreach ((array)$item['params']['value'] as $itemItem) {
                                            $content .= $itemItem . ';';
                                        }
                                        $content .= "\r\n";
                                    } elseif ($item['type'] == 'timerange' || $item['type'] == 'daterange') { //时间范围

                                        $content .= $item['params']['title'] . ": " . $item['params']['start']['value'] . ' ' . $item['params']['end']['value'];
                                        $content .= "\r\n";
                                    } elseif ($item['type'] == 'city') {

                                        $content .= $item['params']['title'] . ": " . $item['params']['province'] . ' ' . $item['params']['city'] . ' ' . $item['params']['area'];
                                        $content .= "\r\n";
                                    } else { //普通

                                        $content .= $item['params']['title'] . ": " . ($item['type'] != 'pictures' ? $item['params']['value'] : '略');
                                        $content .= "\r\n";
                                    }
                                }
                            }
                        }
                    }

                    if ($orderInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'member_info':
                    $memberInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'nickname' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 会员昵称
                            $nickname = $this->member_info['nickname'] ?? '';
                            $content .= "会员昵称：$nickname";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 联系方式
                            $mobile = $this->member_info['mobile'] ?? '';
                            $content .= "联系方式：$mobile";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'level' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 会员等级
                            $level = $this->member_info['level']['level_name'] ?? '';
                            $content .= "会员等级：$level";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'commission_level' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 分销商等级
                            $agentInfo = CommissionAgentModel::find()->where(['member_id' =>
                                $this->member_info['id']])->select('level_id')->with('level')->first();
                            $level = $agentInfo['level']['name'] ?? '';
                            $content .= "分销商等级：$level";
                            $content .= "\r\n";
                        }
                    }

                    if ($memberInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'mark_info': //备注信息
                    $markInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'customer_mark' && $child['status'] == 1) {
                            $markInfoLine = true;
                            // 买家留言
                            $buyerRemark = $this->order['buyer_remark'] ?? '';
                            $content .= "买家留言：<FB>$buyerRemark</FB>";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'saler_mark' && $child['status'] == 1) {
                            $markInfoLine = true;
                            // 卖家留言
                            $remark = $this->order['remark'] ?? '';
                            $content .= "卖家留言：<FB>$remark</FB>";
                            $content .= "\r\n";
                        }
                    }

                    if ($markInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'customer_info': //买家信息
                    $customerInfo = false;
                    $addressInfo = Json::decode($this->order['address_info']);
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'name' && $child['status'] == 1) {
                            $customerInfo = true;
                            // 买家姓名
                            $buyerName = $this->order['buyer_name'] ?? '';
                            $content .= "买家姓名：$buyerName";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $customerInfo = true;
                            // 联系方式
                            $buyerMobile = $this->order['buyer_mobile'] ?? '';
                            $content .= "联系方式：$buyerMobile";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            $customerInfo = true;
                            // 联系地址
                            $buyerAddress = $addressInfo['province'] . $addressInfo['city'] . $addressInfo['area'] .
                                $addressInfo['address_detail'];
                            $content .= "联系地址：$buyerAddress";
                            $content .= "\r\n";
                        }
                    }

                    if ($customerInfo) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'shop_info': //商城信息
                    $shopInfoLine = false;
                    foreach ($item['children'] as $child) {
                        $shopInfo = ShopSettings::get('contact');
                        if ($child['type'] == 'qrcode' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            // 商城二维码
                            $content .= "\r\n";
                            $content .= "\r\n";
                            $content .= "<QR>{$this->template->qrcode}</QR>";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            // 联系方式
                            $tel = $shopInfo['tel1'] ?? '';
                            $content .= "<center>$tel</center>";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            //地址
                            $address = $shopInfo['address']['province'] . $shopInfo['address']['city'] . $shopInfo['address']['area'] . $shopInfo['address']['detail'];
                            $content .= "<center>$address</center>";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'verify_point' && $child['status'] == 1 && (isset($this->verifyInfo['verify_point_id']) && empty(!$this->verifyInfo['verify_point_id']))) {
                            $shopInfoLine = true;
                            // 核销点
                            $content .= "<center>核销点:" . $this->verifyInfo['title'] . "</center>";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'verify_point_address' && $child['status'] == 1 && (isset($this->verifyInfo['verify_point_id']) && empty(!$this->verifyInfo['verify_point_id']))) {
                            $shopInfoLine = true;
                            // 核销地址
                            $address = $this->verifyInfo['province'] . $this->verifyInfo['city'] . $this->verifyInfo['area'] . $this->verifyInfo['address'];
                            $content .= "<center>$address</center>";
                            $content .= "\r\n";
                        }
                    }

                    if ($shopInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
            }
        }

        if (!empty($this->template->footer)) {
            $content .= "\r\n";
            $content .= "<center>{$this->template->footer}</center>";
        }

        return $content;
    }

    private function feyContent()
    {
        $content = '';

        foreach ($this->template_content as $item) {
            switch ($item['type']) {
                case 'header_info':
                    $headerInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'shop_name' && $child['status'] == 1) {
                            $headerInfoLine = true;
                            // 头部信息-商城名称
                            $headerInfo = ShopSettings::get('sysset.mall.basic.name');
                            $content .= "<CB>$headerInfo</CB><BR>";
                        }
                    }

                    if ($headerInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'goods_info':
                    $content = $this->getGoodsInfo($item, $content);

                    break;
                case 'calculate_info':
                    // 合计 运费 优惠金额 实付金额
                    $goodsOrderInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_original_price' && $child['status'] == 1) {
                            $goodsOrderInfoLine = true;
                            // 合计
                            $originGoodsPrice = $this->order['original_goods_price'] ?? '';
                            $content .= "合计        x{$this->order['total']}       ￥$originGoodsPrice<BR>";
                        }

                        // 预约, 虚拟, 虚拟卡密商品不打印运费
                        // 到店核销物流方式不打印运费
                        if ($child['type'] == 'dispatch_price' && $child['status'] == 1 && !in_array($this->order['order_type'], $this->hiddenDispatchPrice) && $this->order['dispatch_type'] != OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {

                            $goodsOrderInfoLine = true;
                            // 运费
                            $dispatchPrice = $this->order['dispatch_price'] ?? '';
                            $content .= "运费                 ￥$dispatchPrice<BR>";
                        }

                        if ($child['type'] == 'order_discounts_price' && $child['status'] == 1) {
                            $goodsOrderInfoLine = true;
                            // 优惠金额
                            $discountPrice = $this->getDiscountPrice();
                            $content .= "优惠金额             $discountPrice<BR>";
                        }

                        if ($child['type'] == 'order_pay_price' && $child['status'] == 1) {
                            $goodsOrderInfoLine = true;
                            // 实付金额
                            $payPrice = $this->order['pay_price'] ?? '';
                            $content .= "实付金额             ￥$payPrice<BR>";
                        }
                    }

                    if ($goodsOrderInfoLine) {
                        $content .= str_repeat('.', 32);
                    }
                    break;
                case 'order_info':
                    $orderInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_no' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 订单编号
                            $orderNo = $this->order['order_no'] ?? '';
                            $content .= "订单编号：$orderNo";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'pay_type' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 支付方式
                            $payType = $this->order['pay_type'] ?? '';
                            $payType = !empty($payType) ? $payType = PayTypeConstant::getMessage($payType) : '';
                            $content .= "支付方式：$payType";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'pay_channel' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 支付渠道
                            $payChannel = $this->order['create_from'] ?? '';
                            $payChannel = !empty($payChannel) ? $payChannel = ClientTypeConstant::getText($payChannel) : '';
                            $content .= "支付渠道：$payChannel";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'created_at' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 下单时间
                            $createTime = $this->order['created_at'] == 0 ? '' : $this->order['created_at'];
                            $content .= "下单时间：$createTime";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'pay_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 付款时间
                            $payTime = $this->order['pay_time'] == 0 ? '' : $this->order['pay_time'];
                            $content .= "付款时间：$payTime";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'finish_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 收货时间
                            $finishTime = $this->order['finish_time'] == 0 ? '' : $this->order['finish_time'];
                            $content .= "收货时间：$finishTime";
                            $content .= "<BR>";
                        }

                        //配送方式 虚拟卡密不打印
                        if ($child['type'] == 'dispatch_type' && $child['status'] == 1 && !in_array($this->order['order_type'], $this->hiddenDispatchType)) {
                            $orderInfoLine = true;
                            $content .= "配送方式：" . OrderDispatchExpressConstant::getText($this->order['dispatch_type']);
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'delivery_time' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            // 联系地址
                            $ex = Json::decode($this->order['extra_package']);
                            if (!empty($ex['delivery_time']) && $ex['delivery_time'] != '0000-00-00 00:00:00') {
                                $time = $ex['delivery_time'];
                                $content .= "配送/自提时间：$time";
                                $content .= "\r\n";
                            }
                        }

                        // 表单
                        if ($child['type'] == 'order_form' && $child['status'] == 1) {
                            $orderInfoLine = true;
                            if (!empty($this->orderFormData)) {
                                foreach ($this->orderFormData as $item) {
                                    if ($item['type'] == 'checkboxes') { //多选
                                        $content .= $item['params']['title'] . ": ";
                                        foreach ((array)$item['params']['value'] as $itemItem) {
                                            $content .= $itemItem . ';';
                                        }
                                        $content .= "\r\n";
                                    } elseif ($item['type'] == 'timerange' || $item['type'] == 'daterange') { //时间范围
                                        $content .= $item['params']['title'] . ": " . $item['params']['start']['value'] . ' ' . $item['params']['end']['value'];
                                        $content .= "\r\n";
                                    } elseif ($item['type'] == 'city') {

                                        $content .= $item['params']['title'] . ": " . $item['params']['province'] . ' ' . $item['params']['city'] . ' ' . $item['params']['area'];
                                        $content .= "\r\n";
                                    } else { //普通
                                        $content .= $item['params']['title'] . ": " . ($item['type'] != 'pictures' ? $item['params']['value'] : '略');
                                        $content .= "\r\n";
                                    }
                                }
                            }
                        }
                    }

                    if ($orderInfoLine) {
                        $content .= str_repeat('.', 32);

                    }

                    break;
                case 'member_info':
                    $memberInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'nickname' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 会员昵称
                            $nickname = $this->member_info['nickname'] ?? '';
                            $content .= "会员昵称：$nickname";
                            $content .= "<BR>";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 联系方式
                            $mobile = $this->member_info['mobile'] ?? '';
                            $content .= "联系方式：$mobile";
                            $content .= "<BR>";
                        }
                        if ($child['type'] == 'level' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 会员等级
                            $level = $this->member_info['level']['level_name'] ?? '';
                            $content .= "会员等级：$level";
                            $content .= "<BR>";
                        }
                        if ($child['type'] == 'commission_level' && $child['status'] == 1) {
                            $memberInfoLine = true;
                            // 分销商等级
                            $agentInfo = CommissionAgentModel::find()->where(['member_id' =>
                                $this->member_info['id']])->select('level_id')->with('level')->first();
                            $level = $agentInfo['level']['name'] ?? '';
                            $content .= "分销商等级：$level";
                            $content .= "<BR>";
                        }
                    }

                    if ($memberInfoLine) {
                        $content .= str_repeat('.', 32);

                    }

                    break;
                case 'mark_info': //备注信息
                    $markInfoLine = false;
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'customer_mark' && $child['status'] == 1) {
                            $markInfoLine = true;
                            // 买家留言
                            $buyerRemark = $this->order['buyer_remark'] ?? '';
                            $content .= "买家留言：<BOLD>$buyerRemark</BOLD>";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'saler_mark' && $child['status'] == 1) {
                            $markInfoLine = true;
                            // 卖家留言
                            $remark = $this->order['remark'] ?? '';
                            $content .= "卖家留言：<BOLD>$remark</BOLD>";
                            $content .= "<BR>";
                        }
                    }

                    if ($markInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'customer_info': //买家信息
                    $customerInfoLine = false;
                    $addressInfo = Json::decode($this->order['address_info']);
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'name' && $child['status'] == 1) {
                            $customerInfoLine = true;
                            // 买家姓名
                            $buyerName = $this->order['buyer_name'] ?? '';
                            $content .= "买家姓名：$buyerName";
                            $content .= "<BR>";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $customerInfoLine = true;
                            // 联系方式
                            $buyerMobile = $this->order['buyer_mobile'] ?? '';
                            $content .= "联系方式：$buyerMobile";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            $customerInfoLine = true;
                            // 联系地址
                            $buyerAddress = $addressInfo['province'] . $addressInfo['city'] . $addressInfo['area'] .
                                $addressInfo['address_detail'];
                            $content .= "联系地址：$buyerAddress";
                            $content .= "<BR>";
                        }
                    }

                    if ($customerInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
                case 'shop_info': //商城信息
                    $shopInfoLine = false;
                    foreach ($item['children'] as $child) {
                        $shopInfo = ShopSettings::get('contact');
                        if ($child['type'] == 'qrcode' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            // 商城二维码
                            $content .= "<BR>";
                            $content .= "<BR>";
                            $content .= "<QR>{$this->template->qrcode}</QR>";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            // 联系方式
                            $tel = $shopInfo['tel1'] ?? '';
                            $content .= "<C>$tel</C>";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            $shopInfoLine = true;
                            //地址
                            $address = $shopInfo['address']['province'] . $shopInfo['address']['city'] . $shopInfo['address']['area'] . $shopInfo['address']['detail'];
                            $content .= "<C>$address</C>";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'verify_point' && $child['status'] == 1 && (isset($this->verifyInfo['verify_point_id']) && empty(!$this->verifyInfo['verify_point_id']))) {
                            $shopInfoLine = true;
                            // 核销点
                            $content .= "<C>核销点:" . $this->verifyInfo['title'] . "</C>";
                            $content .= "<BR>";
                        }

                        if ($child['type'] == 'verify_point_address' && $child['status'] == 1 && (isset($this->verifyInfo['verify_point_id']) && empty(!$this->verifyInfo['verify_point_id']))) {
                            $shopInfoLine = true;
                            // 核销点地址
                            $address = $this->verifyInfo['province'] . $this->verifyInfo['city'] . $this->verifyInfo['area'] . $this->verifyInfo['address'];
                            $content .= "<C>$address</C>";
                            $content .= "<BR>";
                        }

                    }

                    if ($shopInfoLine) {
                        $content .= str_repeat('.', 32);
                    }

                    break;
            }
        }

        if (!empty($this->template->footer)) {
            $content .= "<C>{$this->template->footer}</C>";
        }

        return $content;
    }

    /**
     * @param $selectItem
     * @param $content
     * @author 青岛开店星信息技术有限公司
     */
    private function getGoodsInfo($selectItem, $content)
    {
        $tableHead = [];
        $goodsInfoLine = false;
        foreach ($selectItem['children'] as $child) {
            if ($child['type'] == 'goods_name' && $child['status'] == 1) {
                $goodsInfoLine = true;
                // 商品信息-名称
                $tableHead[] = 'goods_name';
            }

            if ($child['type'] == 'goods_num' && $child['status'] == 1) {
                $goodsInfoLine = true;
                // 商品信息-数量
                $tableHead[] = 'goods_num';
            }

            if ($child['type'] == 'goods_stock' && $child['status'] == 1) {
                $goodsInfoLine = true;
                // 商品信息-库存
                $tableHead[] = 'goods_stock';
            }

            if ($child['type'] == 'goods_price' && $child['status'] == 1) {
                $goodsInfoLine = true;
                // 商品信息-价格
                $tableHead[] = 'goods_price';
            }

            // 商品编码
            if ($child['type'] == 'goods_sku' && $child['status'] == 1) {
                $this->goods_title_code_or_option = self::goods_sku;
            }

            // 商品规格
            if ($child['type'] == 'goods_option' && $child['status'] == 1) {
                $this->goods_title_code_or_option = self::GOODS_OPTION;
            }
        }

        // 只有一列
        if (count($tableHead) == 1) {
            // 判断是什么列
            if ($tableHead[0] == 'goods_name') {
                $content .= '商品名称';
                $content .= "\r\n";
                $content .= str_repeat('.', 32);

                foreach ($this->goods_info as $goods) {
                    // 优先获取短标题 没有获取商品标题取12个汉字
                    $title = self::getTitle($goods);
                    $content .= "$title";
                }
            }
        } // 只有两列
        elseif (count($tableHead) == 2) {

            if ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_num') {
                $content .= "商品名称               数量";
                $content .= "\r\n";
                $content .= str_repeat('.', 32);

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $num = self::getNum($goods);
                    $content .= "$title                        $num";
                    $content .= "\r\n";
                }
            } elseif ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_price') {
                $content .= "商品名称             金额";
                $content .= "\r\n";
                $content .= str_repeat('.', 32);

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $price = self::getPrice($goods);
                    $content .= "$title                     $price";
                    $content .= "\r\n";
                }
            } elseif ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_stock') {
                $content .= "商品名称             库存";
                $content .= "\r\n";
                $content .= str_repeat('.', 32);

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $stock = $goods['stock'];
                    $content .= "$title                     $stock";
                    $content .= "\r\n";
                }
            }
        } elseif (count($tableHead) == 3) {

            if ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_num' && $tableHead[2] == 'goods_stock') {
                $content .= '商品名称';
                $content .= str_repeat(' ', 5);
                $content .= '数量';
                $content .= str_repeat(' ', 6);
                $content .= '库存';
                $content .= "\r\n";
                $content .= str_repeat('.', 32);
                $content .= "\r\n";

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $num = self::getNum($goods);
                    $stock = $goods['stock'];
                    $content .= "$title                $num        $stock";
                    $content .= "\r\n";
                }

            } elseif ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_stock' && $tableHead[2] == 'goods_price') {
                $content .= '商品名称';
                $content .= str_repeat(' ', 5);
                $content .= '库存';
                $content .= str_repeat(' ', 6);
                $content .= '金额';
                $content .= "\r\n";
                $content .= str_repeat('.', 32);
                $content .= "\r\n";

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $stock = $goods['stock'];
                    $price = self::getPrice($goods);
                    $content .= "$title            $stock      $price";
                    $content .= "\r\n";
                }

            } elseif ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_num' && $tableHead[2] == 'goods_price') {
                $content .= '商品名称';
                $content .= str_repeat(' ', 5);
                $content .= '数量';
                $content .= str_repeat(' ', 6);
                $content .= '金额';
                $content .= "\r\n";
                $content .= str_repeat('.', 32);
                $content .= "\r\n";

                foreach ($this->goods_info as $goods) {
                    $title = self::getTitle($goods);
                    $num = self::getNum($goods);
                    $price = self::getPrice($goods);
                    $content .= "$title                $num    $price";
                    $content .= "\r\n";
                }
            }
        } // 四列
        elseif (count($tableHead) == 4) {
            $content .= '商品名称';
            $content .= str_repeat(' ', 3);
            $content .= '数量';
            $content .= str_repeat(' ', 2);
            $content .= '库存';
            $content .= str_repeat(' ', 2);
            $content .= '金额';
            $content .= "\r\n";
            $content .= str_repeat('.', 32);
            $content .= "\r\n";

            foreach ($this->goods_info as $goods) {
                $title = self::getTitle($goods);
                $num = self::getNum($goods);
                $stock = $goods['stock'];
                $price = self::getPrice($goods);
                $content .= "$title           $num   $stock  $price";
                $content .= "\r\n";
            }

        }

        if ($goodsInfoLine) {
            $content .= str_repeat('.', 32);
        }

        return $content;
    }

    private function getTitle($goods)
    {
        // 优先获取短标题 没有获取商品标题取12个汉字
        if (!empty($goods['short_name'])) {
            $title = mb_substr($goods['short_name'], 0, 16);
        } else {
            $title = mb_substr($goods['title'], 0, 16);
        }

        $title .= "\r\n";

        if ($this->goods_title_code_or_option == self::goods_sku && !empty($goods['goods_sku'])) {
            $goodsCode = mb_substr($goods['goods_sku'], 0, 32);
            $title .= $goodsCode;
            $title .= "\r\n";
        }

        if ($this->goods_title_code_or_option == self::GOODS_OPTION && !empty($goods['option_title'])) {
            $goodsOption = mb_substr($goods['option_title'], 0, 16);
            $title .= "[$goodsOption]";
            $title .= "\r\n";
        }

        return $title;
    }

    private function getNum($goods)
    {
        $num = 'x' . $goods['total'];
        // 补充5个占位符
        $gap = 1 - mb_strlen($num);

        $gap > 0 && $num .= str_repeat(' ', $gap);

        return $num;
    }

    private function getPrice($goods)
    {
        $price = '￥' . $goods['price_unit'];

        // 补11个占位符

        $gap = 8 - mb_strlen($price);

        $gap > 0 && $price .= str_repeat(' ', $gap);

        return $price;
    }

    private function getDiscountPrice()
    {
        $discountPrice = $this->order['price_discount'] ?? 0;

        $discountPrice = '￥' . $discountPrice;

        if ($discountPrice > 0) {
            $discountPrice = '-' . $discountPrice;
        }

        return $discountPrice;
    }

    /**
     * [统计字符串字节数补空格，实现左右排版对齐]
     * @param  [string] $str_left    [左边字符串]
     * @param  [string] $str_right   [右边字符串]
     * @param  [int]    $length      [输入当前纸张规格一行所支持的最大字母数量]
     *                               58mm的机器,一行打印16个汉字,32个字母;76mm的机器,一行打印22个汉字,33个字母,80mm的机器,一行打印24个汉字,48个字母
     *                               标签机宽度50mm，一行32个字母，宽度40mm，一行26个字母
     * @return [string]              [返回处理结果字符串]
     */
    private function LR($str_left, $str_right, $length)
    {
        $kw = '';
        $str_left_lenght = strlen(iconv("UTF-8", "GBK//IGNORE", $str_left));
        $str_right_lenght = strlen(iconv("UTF-8", "GBK//IGNORE", $str_right));
        $k = $length - ($str_left_lenght + $str_right_lenght);
        for ($q = 0; $q < $k; $q++) {
            $kw .= ' ';
        }
        return $str_left . $kw . $str_right;
    }
}