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

namespace shopstar\components\payment;

use shopstar\components\payment\base\PayClientConstant;
use Yii;
use yii\base\Component;

/**
 * 支付组件
 * Class StorageComponent
 * @package shopstar\components\payment
 * @author 青岛开店星信息技术有限公司
 */
class PayComponent extends Component
{


    // orderID
    // payType wechat alipay
    // client wxapp wechat h5
    // order MemberLogModel CouponLogModel OrderModel

    /**
     * 获取实例
     * @param array $config
     * @return array|object
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getInstance(array $config)
    {
        // 获取存储驱动
        $client = PayClientConstant::getClient($config['client_type']);
        if (!$client) {
            return error("`{$client}` Pay Client not Found.");
        }

        $config['class'] = $client['class'];
        return Yii::createObject($config);
    }
}
