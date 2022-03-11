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

namespace shopstar\models\order\create\interfaces;

use shopstar\models\order\create\OrderCreatorKernel;

/**
 * 应用活动执行模块接口
 * Interface OrderCreateAppActivityModuleInterface
 * @package shopstar\models\order\create\interfaces
 * @author 青岛开店星信息技术有限公司.
 */
interface OrderCreateAppActivityModuleInterface
{
    /**
     * OrderCreateAppActivityModuleInterface constructor.
     * @param OrderCreatorKernel $orderCreatorKernel
     */
    public function __construct(OrderCreatorKernel $orderCreatorKernel);

    /**
     * 初始化
     * @return mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public function init();

    /**
     * 活动执行前
     * @return mixed
     * @author 青岛开店星信息技术有限公司..
     */
    public function beforeActivity();

    /**
     * 执行活动
     * @return mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public function execActivity();

    /**
     * 活动执行后
     * @return mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public function afterActivity();
}
