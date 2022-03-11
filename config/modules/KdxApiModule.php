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
   
namespace shopstar\config\modules;

use shopstar\bases\module\BaseModule;
use modules\utility\config\UtilityModule;

/**
 * 商城模块配置
 * Class KdxApiModule
 * @package shop\config
 */
class KdxApiModule extends BaseModule
{

    /**
     * @var bool 是否手机端接口
     */
    public $isMobile = false;

    /**
     * 初始化商城模块
     * @author likexin
     */
    public function init()
    {
        // 设置控制器命名空间
        $this->setControllerNamespace('shopstar\\' . ($this->isMobile ? 'mobile' : 'admin'), false);

        // 注册模块
        //$this->setModules([
        //    // 工具模块
        //    'utility' => [
        //        'class' => UtilityModule::class,
        //    ],
        //]);
    }

}