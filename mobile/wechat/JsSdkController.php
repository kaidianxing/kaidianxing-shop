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

namespace shopstar\mobile\wechat;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\wechat\helpers\OfficialAccountJsSdkHelper;
use shopstar\exceptions\base\wechat\WechatException;
use shopstar\helpers\RequestHelper;

/**
 * 获取jssdk
 * Class JsSdkController
 * @package apps\wechat\client
 * @author 青岛开店星信息技术有限公司
 */
class JsSdkController extends BaseMobileApiController
{

    public $configActions = [
        'allowSessionActions' => [
            '*',
        ],
        'allowActions' => [
            '*',
        ]
    ];

    /**
     * 获取
     * @return array|int[]|\yii\web\Response
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function actionIndex()
    {
        $url = RequestHelper::get('url');

        /** @change likexin 此处兼容前端传入携带undefined导致注册分享链接与实际分享链接不一致，分享自定义信息失败 * */
        $url = rtrim($url, '#undefined');

        $config = OfficialAccountJsSdkHelper::buildConfig($url);

        if (is_error($config)) {
            throw new WechatException(WechatException::WECHAT_PARAMETER_IS_WRONG, $config['message']);

        }
        return $this->result([
            'config' => $config,
        ]);
    }

}
