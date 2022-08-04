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

namespace shopstar\mobile\pc;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\channels\PcChannel;
use shopstar\components\wechat\WechatComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\member\MemberLoginModel;
use shopstar\models\pc\MemberWechatPcModel;
use shopstar\models\shop\ShopSettings;
use yii\base\InvalidConfigException;
use yii\web\Response;

class WxLoginController extends BaseMobileApiController
{
    public $configActions = [
        // 允许不携带Session头访问
        'allowSessionActions' => [
            'redirect-url',
        ],
        // 允许不登录访问的Actions
        'allowActions' => [
            '*',
        ],
        'allowClientActions' => [
            'redirect-url',
        ],
        'allowPcClientActions' => [
            'redirect-url',
        ],
    ];

    /**
     * 获取微信扫码登录的url
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetRedirectUrl()
    {
        // 前端跳转URL
        $frontRedirectUrl = RequestHelper::post('redirectUrl', '');

        $frontRedirectUrl = base64_encode($frontRedirectUrl);

        // 跳转URL
        $redirectUrl = ShopUrlHelper::wap('api/pc/wx-login/redirect-url', ['front_url' => $frontRedirectUrl, 'session_id' => $this->sessionId], true);

        $redirectUrl = urlencode($redirectUrl);
        $account = ShopSettings::get('channel_setting.wxpc');
        $appId = (string)$account['app_id'];
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid={$appId}&redirect_uri={$redirectUrl}&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect";

        return $this->success(['data' => [
            'url' => $url,
        ]]);
    }

    /**
     * 用户扫码后，跳回的url，注意，这个不是一个api。
     * @return array|int[]|void|Response
     * @throws MemberException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRedirectUrl()
    {
        // 获取参数
        $code = RequestHelper::get('code');
        if (empty($code)) {
            return $this->error('参数错误 code不能为空');
        }
        $this->sessionId = RequestHelper::get('session_id');
        if (empty($this->sessionId)) {
            return $this->error('参数错误 session_id不能为空');
        }
        $front_url = RequestHelper::get('front_url');

        if (empty($front_url)) {
            return $this->error('参数错误 front_url不能为空');
        }
        // 跳转的前端页面URL。
        $front_url = base64_decode($front_url);

        /**
         * @var PcChannel
         */
        $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_PC, []);

        // 获取access_token
        $accessToken = $instance->getAccessToken($code);
        if (is_error($accessToken)) {
            return $this->result($accessToken);
        }

        // 获取登录信息
        $original = $instance->getUserByToken($accessToken);

        //var_dump($original);exit;

        // 检测并注册会员
        $memberInfo = MemberWechatPcModel::checkMember($original);

        // 执行登录
        MemberLoginModel::login($memberInfo['id'], $this->sessionId, $memberInfo, ClientTypeConstant::CLIENT_PC);

        $this->redirect($front_url);
    }
}
