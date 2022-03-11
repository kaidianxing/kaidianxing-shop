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


use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLoginModel;
use shopstar\models\member\MemberWxappModel;
use EasyWeChat\Kernel\Exceptions\HttpException;

class WxappHandler extends AuthorizationHandlerInterface
{
    public function auth(array $options = [])
    {
        $code = RequestHelper::post('code') ?: RequestHelper::get('code');
        try {
            $info = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->auth->session($code);
        } catch (HttpException $exception) {

            $info = $exception->formattedResponse;
        }
        
        return Wechat::apiError($info);
    }

    /**
     * @param string $sessionId
     * @return array|string
     * @throws MemberException
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @author 青岛开店星信息技术有限公司
     */
    public function login(string $sessionId)
    {
        $info = RequestHelper::post() ?: RequestHelper::get();
        $trans = \Yii::$app->db->beginTransaction();
        try {
            $result = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->encryptor->decryptData(trim($info['session_key']), trim($info['iv']), trim($info['encryptedData']));
            //如果是数组而且不是获取手机号的时候
            if (is_array($result) && $info['get_mobile'] == 0) {

                //兼容新旧授权方式
                empty($result['openId']) && $result['openId'] = $info['open_id'];

                //判断unionid
                if (empty($result['unionId']) && $info['union_id'] && $info['union_id'] != 'undefined') {
                    $result['unionId'] = $info['union_id'];
                }

                //openid不存在
                if (empty($result['openId'])) {
                    throw new MemberException(MemberException::WXAPP_AUTH_OPEN_ID_EMPTY);
                }

                $userInfo = MemberWxappModel::checkMember($result, ClientTypeConstant::CLIENT_WXAPP);
                $trans->commit();
                return MemberLoginModel::login($userInfo['id'], $sessionId, $userInfo, ClientTypeConstant::CLIENT_WXAPP);
            }
        } catch (\Throwable $e) {
            $trans->rollBack();
            throw new MemberException($e->getCode(), $e->getMessage());
        }

        return $result;
    }
}
