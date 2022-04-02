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

namespace shopstar\components\payment\base;

use shopstar\bases\constant\BaseConstant;
use shopstar\constants\ClientTypeConstant;

/**
 * 支付客户端常量
 * Class PayClientConstant
 * @package shopstar\components\payment\base
 * @author 青岛开店星信息技术有限公司
 */
class PayClientConstant extends BaseConstant
{

    /**
     * @message("微信公众号")
     */
    public const CLIENT_WECHAT = 'wechat';

    /**
     * @message("微信小程序")
     */
    public const CLIENT_WXAPP = 'wxapp';

    /**
     * @message("H5")
     */
    public const CLIENT_H5 = 'h5';

    /**
     * @message("字节跳动小程序")
     */
    public const CLIENT_BYTE_DANCE = 'byte_dance';

    /**
     * @Message("商家端");
     */
    public const MANAGE_PC = 'manage_pc';

    /**
     * @var array 映射Map
     */
    public static $map = [
        self::CLIENT_WECHAT => [
            'name' => '微信公众号',
            'class' => 'shopstar\components\payment\client\Wechat'
        ],
        self::CLIENT_WXAPP => [
            'name' => '微信小程序',
            'class' => 'shopstar\components\payment\client\Wxapp'
        ],
        self::CLIENT_H5 => [
            'name' => 'H5',
            'class' => 'shopstar\components\payment\client\H5'
        ],
        self::CLIENT_BYTE_DANCE => [
            'name' => 'byte_dance',
            'class' => 'shopstar\components\payment\client\ByteDance'
        ],
        self::MANAGE_PC => [
            'name' => 'PC',
            'class' => 'shopstar\components\payment\client\ManagePc'
        ],

    ];

    /**
     * 获取客户端
     * @param string $type 客户端类型
     * @return bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getClient(string $clientTypeCode)
    {
        $clientType = ClientTypeConstant::getIdentify($clientTypeCode);
        return self::$map[$clientType] ?? false;
    }
}
