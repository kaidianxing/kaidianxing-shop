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

namespace shopstar\components\wechat\helpers;

use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\channels\OfficialAccountChannel;
use shopstar\components\wechat\WechatComponent;

/**
 * 获取用户
 * Class OfficialAccountMessageTextHelp
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountFansHelper
{

    /**
     * 获取实例
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司.
     */
    private static function getInstance()
    {
        return WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;
    }

    /**
     * 获取用户
     * @param null $nextOpenId
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getList($nextOpenId = null)
    {
        try {
            /**
             * @var OfficialAccountChannel $instance
             */
            $instance = self::getInstance();
            $result = $instance->user->list($nextOpenId);
        } catch (\Exception $result) {
        }

        return Wechat::apiError($result);
    }

    /**
     * 黑名单
     * @param null $nextOpenId
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getBlackList($nextOpenId = null)
    {
        try {
            /**
             * @var OfficialAccountChannel $instance
             */
            $instance = self::getInstance();
            $result = $instance->user->blacklist($nextOpenId);
        } catch (\Exception $result) {
        }

        return Wechat::apiError($result);
    }

    /**
     * 获取用户信息(可传入多个)
     * @param $openid
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user
     */
    public static function getInfo($openid)
    {
        try {
            if (is_array($openid)) {
                $result = self::getInstance()->user->select($openid);
            } else {
                $result = self::getInstance()->user->get($openid);
            }
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 修改用户备注
     * @param string $openid 目标用户OPENID
     * @param string $remark 备注信息
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user
     */
    public static function remark(string $openid, string $remark)
    {
        try {
            $result = self::getInstance()->user->remark($openid, $remark);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 取消拉黑用户(可传入多个)
     * @param $openid
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user
     */
    public static function unblock($openid)
    {
        try {
            $result = self::getInstance()->user->unblock($openid);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 拉黑用户(可传入多个)
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user
     */
    public static function block($openid)
    {
        try {
            $result = self::getInstance()->user->block($openid);
        } catch (\Exception $result) {
        }

        return Wechat::apiError($result);
    }


    /**
     * 账号迁移 openid 转换
     * @param string $oldAppId 旧公众号AppId
     * @param array $openidList openid列表
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user
     */
    public static function changeOpenid(string $oldAppId, array $openidList)
    {
        try {
            $result = self::getInstance()->user->changeOpenid($oldAppId, $openidList);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 获取所有标签
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function getTagList()
    {
        try {
            $result = self::getInstance()->user_tag->list();
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 创建标签
     * @param string $tagName 标签名
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function createTag(string $tagName)
    {
        try {
            $result = self::getInstance()->user_tag->create($tagName);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 修改标签信息
     * @param int $tagId 标签ID
     * @param string $tagName 标签名称
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function updateTag(int $tagId, string $tagName)
    {
        try {
            $result = self::getInstance()->user_tag->update($tagId, $tagName);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 删除标签
     * @param int $tagId 标签ID
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function deleteTag(int $tagId)
    {
        try {
            $result = self::getInstance()->user_tag->delete($tagId);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 获取指定 openid 用户所属的标签
     * @param string $openid 目标用户OPENID
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function getUserTags(string $openid)
    {
        try {
            $result = self::getInstance()->user_tag->userTags($openid);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 获取标签下用户列表
     * @param int $tagId 标签ID
     * @param string $nextOpenId 拉取列表最后一个用户的openid
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function usersOfTag(int $tagId, $nextOpenId = '')
    {
        try {
            $result = self::getInstance()->user_tag->usersOfTag($tagId, $nextOpenId);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 批量为用户添加标签
     * @param array $openIds 目标用户OPENIDS
     * @param int $tagId 标签ID
     * @return array|bool|void
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/user-tag
     */
    public static function tagUsers(array $openIds, int $tagId)
    {
        try {
            $result = self::getInstance()->user_tag->tagUsers($openIds, $tagId);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }

    /**
     * 批量为用户移除标签
     * @param array $openIds 目标用户IDS
     * @param int $tagId 标签ID
     * @return array|bool|void
     */
    public static function untagUsers(array $openIds, int $tagId)
    {
        try {
            $result = self::getInstance()->user_tag->untagUsers($openIds, $tagId);
        } catch (\Exception $result) {
        }
        return Wechat::apiError($result);
    }


}