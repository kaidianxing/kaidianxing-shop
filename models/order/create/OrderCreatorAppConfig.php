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

/**
 * Class OrderCreatorAppConfig
 * @package shopstar\models\order\create
 * @author 青岛开店星信息技术有限公司.
 */
class OrderCreatorAppConfig
{
    /**
     * @var array 应用规则列表
     */
    protected static array $appRules = [
//        // 积分商城
        'creditShop' => [
            'handlers' => [ //复写订单主体处理器 // TODO 青岛开店星信息技术有限公司 所有店铺的判断都在里面控制
                'init_handler' => 'shopstar\services\creditShop\handler\InitHandler',
                'goods_handler' => 'shopstar\services\creditShop\handler\GoodsHandler',
                'dispatch_handler' => 'shopstar\services\creditShop\handler\DispatchHandler',
                'collect_data_handler' => 'shopstar\services\creditShop\handler\CollectDataHandler',
                'order_save_handler' => 'shopstar\services\creditShop\handler\OrderSaveHandler',
            ],
            'activity_module' => 'shopstar\services\creditShop\OrderCreatorActivity',
        ],
    ];

    public static function loadAppProcessor(array $infoData = [])
    {
        // 此处可扩展 调用应用处理器
        if (isset($infoData['app_name']) && self::$appRules[$infoData['app_name']]) {
            return self::$appRules[$infoData['app_name']];
        }

        // 查不到就返回false 代表无处理器
        return false;
    }
}
