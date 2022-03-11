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

namespace shopstar\traits;

use shopstar\exceptions\member\MemberException;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberSession;
use shopstar\models\shop\ShopSettings;

/**
 * 客户端(手机端)
 * Trait ClientTrait
 * @package shopstar\traits
 */
trait ClientTrait
{

    /**
     * @var int|null 当前登录会员ID
     */
    public $memberId = 0;

    /**
     * @var array|null 当前登录会员基础信息
     */
    public $member;

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
            
            if (empty($member)){
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

}
