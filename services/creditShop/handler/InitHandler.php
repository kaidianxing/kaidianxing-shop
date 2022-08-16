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

use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\creditShop\CreditShopOrderException;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;

/**
 * 积分订单初始化
 * Class InitHandler.
 * @package shopstar\services\creditShop\handler
 */
class InitHandler implements HandlerInterface
{
    /**
     * 订单实体类
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public OrderCreatorKernel $orderCreatorKernel;

    /**
     * GoodsHandler constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 执行初始化
     * @return void
     * @throws CreditShopOrderException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 判断积分商城是否关闭
        $creditShopStatus = ShopSettings::get('credit_shop.status');
        if ($creditShopStatus == 0) {
            throw new CreditShopOrderException(CreditShopOrderException::INIT_HANDLER_CREDIT_SHOP_STATUS_ERROR);
        }

        // 积分商城订单归为活动
        $this->orderCreatorKernel->orderData['activity_type'] = OrderActivityTypeConstant::ACTIVITY_TYPE_CREDIT_SHOP;

        // 读取订单设置(自动关闭时间、自动收货时间、订单支持发票等)
        $this->orderCreatorKernel->shopOrderSettings = ShopSettings::get('sysset');

        // 店铺联系方式
        $this->orderCreatorKernel->shopContact = ShopSettings::get('contact');

        // 店铺同城配送设置
        $this->orderCreatorKernel->shopIntracity = ShopSettings::get('dispatch.intracity');

        // 是否支持发票
        $this->orderCreatorKernel->isInvoiceSupport = (int)!empty($this->orderCreatorKernel->shopOrderSettings['trade']['invoice']);

        // 订单自动关闭时间
        if ($this->orderCreatorKernel->shopOrderSettings['trade']['close_type'] == SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME) {
            $setAutoCloseTime = $this->orderCreatorKernel->shopOrderSettings['trade']['close_time'];
            if (!empty($setAutoCloseTime)) {

                //确认订单返回的数据
                if ($this->orderCreatorKernel->isConfirm) {
                    //自动关闭是否开启
                    $this->orderCreatorKernel->confirmData['auto_close_type'] = SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME;
                    //自动关闭时间不直接运用计算
                    $this->orderCreatorKernel->confirmData['auto_close_time'] = $setAutoCloseTime;
                }

                $this->orderCreatorKernel->autoCloseTime = date('Y-m-d H:i:s', strtotime($this->orderCreatorKernel->createTime) + ($setAutoCloseTime * 60));
            }
        }
    }
}
