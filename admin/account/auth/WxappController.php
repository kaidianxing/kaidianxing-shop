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

namespace shopstar\admin\account\auth;


use shopstar\bases\exception\BaseApiException;
use shopstar\exceptions\base\wechat\WechatException;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\bases\KdxAdminAccountApiController;
use shopstar\bases\KdxAdminApiController;

/**
 * 微信小程序授权登录
 * Class WxappController
 * @package modules\account\manage\auth
 */
class WxappController extends KdxAdminApiController
{
    public $configActions = [
        'allowActions' => ['*'],
        'allowClientActions' => ['*']
    ];

    /**
     * 登录分发
     * @throws MemberException
     * @throws BaseApiException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAuth()
    {
        $data = RequestHelper::post();
        $namespace = "shopstar\components\authorization\\";
        $class = $namespace . ucfirst(trim($data['type'])) . 'Handler';

        if (!class_exists($class) || !method_exists($class, 'auth')) {
            throw new MemberException(MemberException::AUTHORIZATION_AUTH_COMPONENTS_NOT_FOUND);
        }

        $info = (new $class())->auth($data);
        $this->result($info);
    }

    /**
     * 获取用户信息分发
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     * @action login
     */
    public function actionLogin()
    {
        $type = RequestHelper::post('type');

        $namespace = "shopstar\components\authorization\\";
        $class = $namespace . ucfirst(trim($type)) . 'Handler';
        if (!class_exists($class) || !method_exists($class, 'login')) {
            throw new MemberException(MemberException::AUTHORIZATION_LOGIN_COMPONENTS_NOT_FOUND);
        }

        $result = (new $class())->login($this->sessionId);
        if (is_error($result)){
            throw new WechatException(WechatException::WECHAT_PARAMETER_IS_WRONG,$result['message']);
        }
        $this->result($result);
    }
}