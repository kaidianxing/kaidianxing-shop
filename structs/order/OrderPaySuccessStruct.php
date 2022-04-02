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

namespace shopstar\structs\order;

use yii\base\BaseObject;

/**
 * 交易订单支付成功回调参数结构类
 * Class OrderPaySuccessStruct
 * @package shopstar\structs\order
 * @author 青岛开店星信息技术有限公司
 */
class OrderPaySuccessStruct extends BaseObject
{

    /**
     * @var string|null 业务订单编号
     */
    public $orderNo;

    /**
     * @var string|null 业务订单ID
     */
    public $orderId;

    /**
     * @var float|null 当前交易订单金额
     */
    public $orderPrice;

    /**
     * @var int|null 支付账号ID
     */
    public $accountId;

    /**
     * @var int|null 支付方式
     */
    public $payType;

    /**
     * @var float|null 实际支付金额
     */
    public $payPrice;

    /**
     * @var int|null 支付模板ID
     */
    public $paymentId;

    /**
     * @var string|null 内部交易订单号
     */
    public $tradeNo;

    /**
     * @var string|null 外部交易单号(支付宝\微信支付交易号)
     */
    public $outTradeNo;

    /**
     * 回调参数(旧版本支付)
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $callBack = [];

    /**
     * @author likexin
     */
    public function init()
    {
        /** @change likexin 如果是旧版本，支付回调进来，callback的数据转为类属性 * */
        if (!empty($this->callBack)) {
            // 外部交易单号
            if (isset($this->callBack['trans_id'])) {
                $this->outTradeNo = (string)$this->callBack['trans_id'];
            }

            // 支付金额
            if (isset($this->callBack['pay_price'])) {
                $this->payPrice = (float)$this->callBack['pay_price'];
            }

            // 回调回来的外部交易单号就是订单号
            if (isset($this->callBack['out_trade_no'])) {
                $this->orderNo = (string)$this->callBack['out_trade_no'];
            }
        }
    }
}