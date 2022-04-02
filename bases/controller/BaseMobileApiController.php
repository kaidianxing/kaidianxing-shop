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
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberSession;
use shopstar\models\shop\ShopSettings;
use shopstar\services\commission\CommissionAgentService;
use shopstar\services\commission\CommissionLevelService;

/**
 * 客户端接口基类
 * Class BaseMobileApiController
 * @package shopstar\bases\controller
 * @author 青岛开店星信息技术有限公司
 */
class BaseMobileApiController extends BaseApiController
{

    /**
     * @var array|null 当前店铺的基础信息
     */
    public $shop;

    /**
     * @var array 不需要绑定登录的控制器(子类可复写,传入*时当前Controller中全部Action都允许)
     */
    public $allowNotLoginController = false;

    /**
     * @var int|null 当前登录会员ID
     */
    public $memberId = 0;

    /**
     * @var array|null 当前登录会员基础信息
     */
    public $member;

    /**
     * @param $action
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function checkMember($action)
    {
        // 检测登录状态
        $memberSession = MemberSession::get((string)$this->sessionId, 'member');
        if (!$this->allowNotLoginController == '*' && is_array($this->configActions['allowNotLoginActions']) && !in_array($action->id, $this->configActions['allowNotLoginActions'])) {
            if (empty($memberSession)) {
                throw new MemberException(MemberException::MEMBER_NOT_LOGIN);
            }
        }

        if (!empty($memberSession)) {
            $this->memberId = $memberSession['id'];
            $member = MemberModel::find()
                ->where([
                    'id' => $this->memberId,
                ])
                ->select(['id', 'nickname', 'avatar', 'level_id', 'mobile', 'is_deleted', 'is_black'])
                ->first();

            if (empty($member)) {
                throw new MemberException(MemberException::MEMBER_IS_NO_EXISTS);
            }

            // 检测会员删除状态
            if ($member['is_deleted']) {
                throw new MemberException(MemberException::MEMBER_DELETED);
            }

            // 检测会员黑名单状态
            if ($member['is_black']) {
                throw new MemberException(MemberException::MEMBER_BLACK);
            }

            unset($member['is_black']);
            unset($member['is_deleted']);
            $this->member = $member;

            //获取渠道设置
            $registrySettings = ShopSettings::get('channel_setting.registry_settings', 0);

            // 检测是否需要检测用户是否绑定手机号
            if ($registrySettings['bind_method'] == 2 && is_array($this->configActions['needBindMobileActions']) && array_key_exists($action->id, $this->configActions['needBindMobileActions'])) {

                //获取当前方法的触发条件是否需要触发
                $trigger = $registrySettings['bind_scene'][$this->configActions['needBindMobileActions'][$action->id]] ?? 0;
                if ($trigger && empty($this->member['mobile'])) {
                    throw new MemberException(MemberException::MEMBER_MOBILE_NOT_EXIST);
                }
            }
        }

    }

    /**
     * @param $action
     * @return bool
     * @throws ChannelException
     * @throws \shopstar\bases\exception\BaseApiException
     * @throws \shopstar\exceptions\member\MemberException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
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
