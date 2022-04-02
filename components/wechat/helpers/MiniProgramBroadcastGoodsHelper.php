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
 *  附录：错误码
 *  -1：系统错误
 *
 *  1003：商品id不存在
 *
 *  47001：入参格式不符合规范
 *
 *  200002：入参错误
 *
 *  300001：禁止创建/更新商品（如：商品创建功能被封禁）
 *
 *  300002：名称长度不符合规则
 *
 *  300003：价格输入不合规（如：现价比原价大、传入价格非数字等）
 *
 *  300004：商品名称存在违规违法内容
 *
 *  300005：商品图片存在违规违法内容
 *
 *  300006：图片上传失败（如：mediaID过期）
 *
 *  300007：线上小程序版本不存在该链接
 *
 *  300008：添加商品失败
 *
 *  300009：商品审核撤回失败
 *
 *  300010：商品审核状态不对（如：商品审核中）
 *
 *  300011：操作非法（API不允许操作非API创建的商品）
 *
 *  300012：没有提审额度（每天500次提审额度）
 *
 *  300013：提审失败
 *
 *  300014：审核中，无法删除（非零代表失败）
 *
 *  300017：商品未提审
 *
 *  300021：商品添加成功，审核失败
 */


/**
 * 小程序直播商品助手
 * Class MiniProgramBroadcastGoodsHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class MiniProgramBroadcastGoodsHelper
{
    /**
     * 添加审核
     * 调用此接口上传并提审需要直播的商品信息，审核通过后商品录入【小程序直播】商品库
     * 注意：开发者必须保存【商品ID】与【审核单ID】，如果丢失，则无法调用其他相关接口
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function add(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token=' . $token['access_token'], Json::encode(['goodsInfo' => $data]), [
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
     * 撤销审核
     * 调用此接口，可撤回直播商品的提审申请，消耗的提审次数不返还
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function resetAudit(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit?access_token=' . $token['access_token'], Json::encode($data), [
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
     * 重新审核
     * 调用此接口可以对已撤回提审的商品再次发起提审申请
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function audit(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/audit?access_token=' . $token['access_token'], Json::encode($data), [
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
     * 删除商品
     * 调用此接口，可删除【小程序直播】商品库中的商品，删除后直播间上架的该商品也将被同步删除，不可恢复
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function delete(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/delete?access_token=' . $token['access_token'], Json::encode($data), [
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
     * 更新商品
     * 调用此接口可以更新商品信息，审核通过的商品仅允许更新价格类型与价格，审核中的商品不允许更新，未审核的商品允许更新所有字段， 只传入需要更新的字段。
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function update(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/update?access_token=' . $token['access_token'], Json::encode($data), [
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
     * 获取商品状态
     * 调用此接口可获取商品的信息与审核状态
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsWarehouse(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::postJson('https://api.weixin.qq.com/wxa/business/getgoodswarehouse?access_token=' . $token['access_token'], Json::encode($data), [
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
     * 获取商品列表
     * 调用此接口可获取商品列表
     * @param array $data
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function getApproved(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            $result = HttpHelper::getJson('https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token=' . $token['access_token'] . '&' . http_build_query($data), [
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
