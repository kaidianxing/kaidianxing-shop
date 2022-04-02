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

namespace shopstar\components\authorization;

use Overtrue\Socialite\AuthorizeFailedException;
use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\member\MemberLoginModel;
use shopstar\models\member\MemberWechatModel;
use yii\helpers\Json;

/**
 * 微信公众号授权
 * Class WechatHandler
 * @package shopstar\components\authorization
 * @author 青岛开店星信息技术有限公司
 */
class WechatHandler extends AuthorizationHandlerInterface
{

    /**
     * 授权
     * @param array $options
     * @return mixed|void
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function auth(array $options = [])
    {
        $options = array_merge([
            'url' => '',
        ], $options);

        $baseUrl = ShopUrlHelper::wap('/kdxLogin/dealWechat', [], true) . '?a=a';

        if (!empty($options['target_params'])) {
            $paramsData = http_build_query(['target_params' => $options['target_params']]);
            $baseUrl .= '&' . $paramsData;
        }

        if (!empty($options['target_url'])) {
            $baseUrl .= '&' . http_build_query(['target_url' => $options['target_url']]);
        }

        $result = (WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->oauth->scopes(['snsapi_userinfo'])->redirect($baseUrl));
        $ret = (Json::decode(Json::encode($result->headers)));
        if (RequestHelper::isPost()) {
            return current($ret['location']);
        } else {
            echo "<a href='" . current($ret['location']) . "'>点击登录</a>";
            exit();
        }
    }

    /**
     * 登录操作
     * @param string $sessionId
     * @return mixed
     * @throws MemberException|\yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function login(string $sessionId)
    {
        //公众号特殊处理
        try {
            $userInfo = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->oauth->user();
            $original = $userInfo->getOriginal();
            $userInfo = MemberWechatModel::checkMember($original, ClientTypeConstant::CLIENT_WECHAT);
        } catch (AuthorizeFailedException $exception) {

            return Wechat::apiError($exception->body);

        }

        return MemberLoginModel::login($userInfo['id'], $sessionId, $userInfo, ClientTypeConstant::CLIENT_WECHAT);
    }

}
