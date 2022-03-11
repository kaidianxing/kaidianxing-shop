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

use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;

class VirtualAccountCheckHandler implements HandlerInterface
{

    /**
     * @var OrderCreatorKernel $orderCreatorKernel
     * @author 青岛开店星信息技术有限公司.
     */
    public $orderCreatorKernel;

    /**
     * HandlerInterface constructor.
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        return $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {

    }
}