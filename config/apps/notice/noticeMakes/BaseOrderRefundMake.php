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

class BaseOrderRefundMake extends BaseMake
{

    /**
     * 预留字段 ，用于字段名转化
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $reserveField = [
        'member_nickname' => '[用户昵称]',
        'order_no' => '[订单编号]',
        'refund_type' => '[售后类型]',
        'apply_time' => '[申请时间]',
        'refund_price' => '[退款金额]',
        'refund_address' => '[退货地址]',
        'member_express_name' => '[换货快递公司]',
        'member_express_no' => '[换货快递单号]',
        'refund_price_status' => '[退款状态]',
        'buyer_mobile' => '[买家电话]',
        'buyer_name' => '[买家姓名]',
    ];
}
