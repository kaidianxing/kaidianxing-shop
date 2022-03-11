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

namespace shopstar\mobile\wxapp;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\models\wxapp\WxappUploadLogModel;

/**
 * 小程序码
 * Class QrCodeController
 * @package apps\wxapp\client
 */
class QrCodeController extends BaseMobileApiController
{
    public $configActions = [
         //允许不登录请求的Actions
        'allowActions' => [
            'get',
        ],
        // 允许不携带 Session-Id 请求的Actions
        'allowSessionActions' => [
            'get',
        ]
    ];

    /**
     * 获取二维码
     * @return array|int[]|\yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author likexin
     */
    public function actionGet()
    {
        return $this->result([
            'url' => WxappUploadLogModel::getWxappUnlimitedQRcode()
        ]);
    }

}