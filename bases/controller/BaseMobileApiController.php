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

namespace shopstar\bases\controller;

use shopstar\constants\ClientTypeConstant;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\exceptions\ChannelException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\commission\CommissionAgentService;
use shopstar\services\commission\CommissionLevelService;
use shopstar\traits\ClientTrait;

/**
 * 客户端接口基类
 * Class BaseMobileApiController
 * @package shopstar\bases\controller
 */
class BaseMobileApiController extends BaseApiController
{
    /**
     * @var int 店铺类型
     * @author 青岛开店星信息技术有限公司
     */
    public $shopType = 0;

    /**
     * @var array|null 当前店铺的基础信息
     */
    public $shop;
    /**
     * @var array 需要绑定手机号的控制器方法(子类可复写)
     */
    //public $needBindMobileActions = [];

    /**
     * @var array 不需要绑定登录的控制器(子类可复写,传入*时当前Controller中全部Action都允许)
     */
    public $allowNotLoginController = false;

    /**
     * @var array 不需要绑定登录的控制器方法(子类可复写)
     */
    //public $allowNotLoginActions = [];

    /**
     * @var array 允许店铺关闭访问的方法(子类可复写)
     */
    //public $allowShopCloseActions = [];

    /**
     * 引用Trait
     */
    use ClientTrait;

    /**
     * @param $action
     * @return bool
     * @throws \ReflectionException
     * @throws \yii\web\BadRequestHttpException
     * @throws \shopstar\bases\exception\BaseApiException
     * @throws \Throwable
     * @author likexin
     */
    public function beforeAction($action)
    {

        // 检测客户端类型
        if (
            !isset($this->configActions['allowClientActions']) || !is_array($this->configActions['allowClientActions'])
            ||
            (!in_array('*', $this->configActions['allowClientActions']) && !in_array($action->id, $this->configActions['allowClientActions']))
        ) {
            $this->checkClientType();
        }

        if (
            (is_array($this->configActions['allowShopCloseActions']) && !in_array($action->id, $this->configActions['allowShopCloseActions']))
            &&
            (is_array($this->configActions['allowClientActions']) && !in_array($action->id, $this->configActions['allowClientActions']))
        ) {
            //检测店铺状态
            //$mallStatus = ShopSettings::get('sysset.mall.basic.mall_status');
            //if ($mallStatus == SyssetTypeConstant::SHOP_STATUS_CLOSE) {
            //    throw new MallException(MallException::SHOP_STATUS_CLOSE);
            //}
            //判断渠道是否开启
            $channel = ShopSettings::get('channel.' . ClientTypeConstant::getIdentify($this->clientType));
            if (!$channel) {
                //根据渠道报错
                if ($this->clientType == ClientTypeConstant::CLIENT_H5) {

                    throw new ChannelException(ChannelException::SHOP_CHANNEL_H5_NOT_OPEN);
                } else {

                    throw new ChannelException(ChannelException::SHOP_CHANNEL_NOT_OPEN);
                }
            }

            // 检查小程序维护状态
            if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
                $wxappSetting = ShopSettings::get('channel_setting.wxapp.maintain');
                if ($wxappSetting) {
                    throw new ChannelException(ChannelException::SHOP_CHANNEL_WXAPP_NOT_OPEN);
                }
            }
        }

        // 检测SessionId
        if (!isset($this->configActions['allowSessionActions']) || !is_array($this->configActions['allowSessionActions'])
            || (!in_array('*', $this->configActions['allowSessionActions']) && !in_array($action->id, $this->configActions['allowSessionActions']))
        ) {
            $this->checkSession();
        }

        // 检测会员状态(会员状态、登录状态、黑名单状态)
        $this->checkAccess($action, function () use ($action) {
            $this->checkMember($action);
        });

        // 登录
        if (!empty($this->memberId)) {

            // 分销关系处理
            $inviterId = RequestHelper::header('inviter-id');
            // 有邀请人
            if (!empty((int)$inviterId)) {
                CommissionRelationModel::handle($this->memberId, $inviterId, CommissionRelationLogConstant::TYPE_BIND);
            }

            // 每一分钟检测一次
            $redis = \Yii::$app->redis;
            $key = 'kdx_shop_' . '_' . $this->sessionId . '_handler_relation';
            $isExists = $redis->get($key);
            if (!$isExists) {
                $expireTime = 60;
                $redis->setex($key, $expireTime, DateTimeHelper::now());
                // 检测分销商注册
                CommissionAgentService::register($this->memberId);
                // 检测分销商升级
                CommissionLevelService::agentUpgrade($this->memberId);
            }

            // 更新最近浏览时间
            MemberModel::updateLastTime($this->memberId, $this->sessionId);
        }

        return parent::beforeAction($action);
    }

}
