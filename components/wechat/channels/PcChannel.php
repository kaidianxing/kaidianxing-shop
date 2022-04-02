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

namespace shopstar\components\wechat\channels;

use Overtrue\Socialite\AccessTokenInterface;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\SocialiteManager;
use shopstar\components\wechat\bases\BaseWechatChannel;
use shopstar\components\wechat\bases\WechatChannelInterface;
use shopstar\models\shop\ShopSettings;

/**
 * PC渠道
 * Class PcChannel
 * @package shopstar\components\wechat\channels
 * @author 青岛开店星信息技术有限公司
 */
class PcChannel extends BaseWechatChannel implements WechatChannelInterface
{
    /**
     * @var \EasyWeChat\OpenPlatform\Application
     */
    public $factory;

    /**
     * @var string 公众号AppID
     */
    public $app_id;

    /**
     * @var string 公众号Secret
     */
    public $secret;

    /**
     * @var string[] 日志参数
     */
    public $log = [
        'level' => 'debug',
        'file' => SHOP_STAR_RUNTIME_PATH . '/wechat_logs/pc.log',
    ];

    /**
     * 自动加载店铺设置
     * @return mixed
     * @author likexin
     */
    public function autoloadConfig()
    {
        $account = ShopSettings::get('channel_setting.wxpc');
        $this->app_id = (string)$account['app_id'];
        $this->secret = (string)$account['secret'];
    }

    /**
     * 创建工厂
     * @author likexin
     */
    public function makeFactory()
    {
        $socialite = new SocialiteManager([
            'wechat' => [
                'client_id' => $this->app_id,
                'client_secret' => $this->secret,
            ],
        ]);

        $this->factory = $socialite->driver('wechat');
    }

    /**
     * 获取AccessToken
     * @param string $code
     * @return array|\Overtrue\Socialite\AccessToken|\Overtrue\Socialite\AccessTokenInterface
     */
    public function getAccessToken(string $code)
    {
        try {
            $token = $this->factory->getAccessToken($code);
        } catch (AuthorizeFailedException $exception) {
            return error($exception->body['errmsg'] . '(' . $exception->body['errcode'] . ')', $exception->body['errcode']);
        }

        return $token;
    }

    /**
     * 获取用户信息
     * @param AccessTokenInterface $token
     * @return array
     */
    public function getUserByToken(AccessTokenInterface $token): array
    {
        try {
            $user = $this->factory->user($token);
        } catch (AuthorizeFailedException $exception) {
            return error($exception->body['errmsg'] . '(' . $exception->body['errcode'] . ')', $exception->body['errcode']);
        }
        return $user->toArray();
    }


}