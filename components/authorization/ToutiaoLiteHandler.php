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

use shopstar\components\byteDance\bases\ByteDanceConstant;
use shopstar\components\byteDance\ByteDanceComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLoginModel;
use shopstar\models\member\MemberToutiaoLiteModel;

class ToutiaoLiteHandler extends AuthorizationHandlerInterface
{
    /**
     * @param array $options
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function auth( array $options = [])
    {
        $code = RequestHelper::post('code') ?: RequestHelper::get('code');
        $anonymousCode = RequestHelper::post('anonymousCode') ?: RequestHelper::get('anonymousCode');
        
        return ByteDanceComponent::getInstance( ByteDanceConstant::CHANNEL_TOUTIAO_LITE)->factory->auth->session($code, $anonymousCode);
    }
    
    /**
     * @param string $sessionId
     * @return bool|string
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function login(string $sessionId)
    {
        $info = RequestHelper::post() ?: RequestHelper::get();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 解密
            $result = MemberToutiaoLiteModel::getUserInfo(trim($info['encryptedData']), trim($info['session_key']), trim($info['iv']), trim($info['rawData']), trim($info['signature']));
            // 注册/更新 用户信息
            $memberInfo = MemberToutiaoLiteModel::checkMember($result, ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE);
            
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new MemberException($exception->getCode(),$exception->getMessage());
        }
        return MemberLoginModel::login($memberInfo['id'], $sessionId, $memberInfo, ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE);
    }
    
}