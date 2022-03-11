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
namespace shopstar\config\apps\notice\noticeMakes;


use shopstar\components\notice\bases\BaseMake;

class BaseOrderMake extends BaseMake
{
    /**
     * 预留字段 ，用于字段名转化
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $reserveField = [
        'shop_name' => '[商城名称]',
        'goods_title' => '[商品名称]',
        'goods_detail' => '[商品详情]',
        'member_nickname' => '[会员昵称]',
        'order_no' => '[订单编号]',
        'pay_price' => '[支付金额]',
        'dispatch_price' => '[运费]',
        'express_name' => '[快递公司]',
        'express_no' => '[快递单号]',
        'buyer_name' => '[买家姓名]',
        'buyer_mobile' => '[买家电话]',
        'address_info' => '[收货地址]',
        'created_at' => '[下单时间]',
        'pay_time' => '[支付时间]',
        'send_time' => '[发货时间]',
        'finish_time' => '[收货时间]',
        'clear_time' => '[取消时间]',
        'credit_change' => '[积分变动]',
        'credit_balance' => '[积分变动]',
        'status' => '[订单状态]',
        'remark' => '[买家备注]',
        'refund_price' => '[退款金额]',
        'member_balance' => '[账户余额]',
    ];
}
