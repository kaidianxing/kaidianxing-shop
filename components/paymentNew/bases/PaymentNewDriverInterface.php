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

namespace shopstar\components\paymentNew\bases;

/**
 * 支付组件驱动接口类
 * Interface PaymentNewDriverInterface
 * @package shopstar\components\paymentNew\bases
 * @author likexin
 */
interface PaymentNewDriverInterface
{

    /**
     * 获取客户端映射
     * @return array
     * @author likexin
     */
    public function getClientMap(): array;

    /**
     * 统一下单(此处可根据业务逻辑进行转发)
     * @return array
     * @author likexin
     */
    public function unify(): array;
//
//    /**
//     * 查询订单状态
//     * @param string $method
//     * @return array
//     * @author likexin
//     */
//    public function query(string $method): array;
//
    /**
     * 关闭订单
     * @return array
     * @author likexin
     */
    public function close(): array;

    /**
     * 订单退款
     * @param float $orderPrice 订单总金额
     * @param float $refundPrice 退款金额
     * @param string $refundNo 退款编号
     * @return array
     * @author likexin
     */
    public function refund(float $orderPrice, float $refundPrice, string $refundNo): array;

    /**
     * 验签
     * @param $data
     * @return array
     * @author likexin
     */
    public function verifySign($data): array;

//
//    /**
//     * 查询退款订单
//     * @return array
//     * @author likexin
//     */
//    public function refundQuery(): array;
//
//    /**
//     * 转账
//     * @return array
//     * @author likexin
//     */
//    public function transfer(): array;
//
//    /**
//     * 查询转账订单
//     * @return array
//     * @author likexin
//     */
//    public function transferQuery(): array;

}