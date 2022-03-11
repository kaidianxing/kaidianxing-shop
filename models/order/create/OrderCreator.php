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

use shopstar\models\order\create\interfaces\OrderCreateAppActivityModuleInterface;

/**
 * 订单创建
 * Class OrderCreatorEventsConfig
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\models\order\create
 */
class OrderCreator extends OrderCreatorKernel
{
    /**
     * @var array 应用活动规则
     */
    protected $appActivityRule = [];

    /**
     * 应用活动处理模块
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    protected $appActivityModule = '';

    /**
     * 应用可执行活动
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    protected $appActivitys = [];

    /**
     * 加载应用处理器
     * @author 青岛开店星信息技术有限公司
     */
    protected function beforeApp()
    {
        //挂载应用组件
        $appProcessor = OrderCreatorAppConfig::loadAppProcessor($this->inputData);

        //如果应用不存在需要挂载处理器则直接返回false
        if ($appProcessor === false) {
            return;
        }
        // 此处可扩展适配控制器
        if ($appProcessor['handlers']) {
            foreach ($appProcessor['handlers'] as $index => $handler) {
                //复写只要处理器
                $this->kernelHandlers[$index] = $handler;
            }
        }

        //挂载应用活动处理模块
        $this->appActivityModule = $appProcessor['activity_module'] ?: '';

        //挂载应用可执行活动
        $this->appActivitys = $appProcessor['activitys'] ?: [];
    }

    /**
     * 初始化之前
     * @author 青岛开店星信息技术有限公司.
     */
    protected function beforeInit()
    {
        // 应用之前的事件
        $this->beforeApp();
    }

    /**
     * 订单创建前(处理活动等)
     * @throws \shopstar\exceptions\order\OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    protected function beforeCreate(): void
    {
        // 活动处理前
        $this->beforeActivity();

        //挂载应用活动模块
        $appActivityModule = $this->appActivityModule;

        if (!empty($appActivityModule) && class_exists($appActivityModule)) {

            /**
             * 加载应用活动模块
             * @var OrderCreateAppActivityModuleInterface $appActivityModule
             */
            (new $appActivityModule($this))->init();

            return;
        }

        //挂载基础活动处理器
        (new OrderCreatorActivity($this))->init();
    }

    /**
     * 活动处理前
     * @author 青岛开店星信息技术有限公司
     */
    protected function beforeActivity()
    {

    }

}
