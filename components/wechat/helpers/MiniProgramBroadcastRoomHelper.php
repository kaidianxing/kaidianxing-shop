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
use shopstar\components\wechat\WechatComponent;
use shopstar\helpers\HttpHelper;
use yii\helpers\Json;

/**
 * 附录：错误码
 *  -1：系统错误
 *
 *  1：未创建直播间
 *
 *  1003：商品id不存在
 *
 *  47001：入参格式不符合规范
 *
 *  200002：入参错误
 *
 *  300001：禁止创建/更新商品 或 禁止编辑&更新房间
 *
 *  300002：名称长度不符合规则
 *
 *  300006：图片上传失败（如：mediaID过期）
 *
 *  300022：此房间号不存在
 *
 *  300023：房间状态 拦截（当前房间状态不允许此操作）
 *
 *  300024：商品不存在
 *
 *  300025：商品审核未通过
 *
 *  300026：房间商品数量已经满额
 *
 *  300027：导入商品失败
 *
 *  300028：房间名称违规
 *
 *  300029：主播昵称违规
 *
 *  300030：主播微信号不合法
 *
 *  300031：直播间封面图不合规
 *
 *  300032：直播间分享图违规
 *
 *  300033：添加商品超过直播间上限
 *
 *  300034：主播微信昵称长度不符合要求
 *
 *  300035：主播微信号不存在
 *
 *  300036: 主播微信号未实名认证
 */

/**
 * 直播间助手
 * Class MiniProgramBroadcastRoomHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class MiniProgramBroadcastRoomHelper
{
    /**
     * 创建直播间
     * 直播间管理接口，是小程序直播提供给开发者对直播房间进行批量操作的接口能力。 开发者可以创建直播间、获取直播间信息、获取直播间回放以及往直播间导入商品
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function create(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 获取直播间列表
     * 调用此接口获取直播间列表及直播间信息 | 调用接口获取已结束直播间的回放源视频（一般在直播结束后10分钟内生成，源视频无评论等内容）
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function getLiveInfo(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 添加商品
     * 调用接口往指定直播间导入已入库的商品
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function addGoods(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }
}
