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

use Exception;
use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\HttpHelper;
use yii\helpers\Json;

/**
 * 自定义交易组件助手类
 * Class MiniProgramWxTransactionComponentHelper.
 * @package shopstar\components\wechat\helpers
 * @author 青岛开店星信息技术有限公司
 */
class MiniProgramWxTransactionComponentHelper
{
    /**
     * 报错码集合
     * @var array
     */
    public static array $errorArray = [
        '1000012' => 'updateGoods'
    ];

    /**
     * 小程序自定义交易组件接口地址
     * @var string
     */
    private static string $baseUrl = 'https://api.weixin.qq.com/shop/';

    /**
     * 缓存前缀 wechat_cat_list_
     * @var string
     */
    private static string $cacheKey = 'wechat_cat_list_';

    /**
     * 物流公司的缓存前缀
     * 缓存前缀 wechat_cat_list_
     * @var string
     */
    private static string $companyCacheKey = 'wechat_company_list_';

    /**
     * 上传图片 (获取临时链接、再上传商品)
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadImg(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::getJson(self::$baseUrl . 'img/upload?access_token=' . $token['access_token'] . '&resp_type=1&upload_type=1&img_url=' . $data['img_url'], [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        } catch (Exception $exception) {
            $result = $exception;
        }
        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取商品全部类目
     * @return array|bool|mixed|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCategory()
    {
        // 查询缓存
        $cacheKey = self::$cacheKey;
        $cache = CacheHelper::get($cacheKey);
        if ($cache) {
            return Json::decode($cache);
        }

        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'cat/get?access_token=' . $token['access_token'], Json::encode(''), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            if ($result['errcode'] == 0) {
                // 缓存一天
                CacheHelper::set($cacheKey, Json::encode($result), 86400);
            }
        } catch (Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取商品资质审核结果
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAuditCategory(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'audit/result?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        } catch (Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 上传类目资质审核
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadAuditCategory(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'audit/audit_category?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        } catch (Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 商品添加审核
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function add(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'spu/add?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 更新
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function update(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'spu/update?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 商品上下架
     * @param array $data
     * @param int $status
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStatus(array $data, int $status = 2)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            switch ($status) {
                case 1: // 下架
                    $result = HttpHelper::postJson(self::$baseUrl . 'spu/delisting?access_token=' . $token['access_token'], Json::encode($data), [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ]
                    ]);
                    break;
                case 2: // 上架
                    $result = HttpHelper::postJson(self::$baseUrl . 'spu/listing?access_token=' . $token['access_token'], Json::encode($data), [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ]
                    ]);
                    break;
            }

        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 删除自定义交易组件中台中的商品
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function delete(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'spu/del?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取商品列表
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getApproved(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];
            $result = HttpHelper::postJson(self::$baseUrl . 'spu/get_list?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 商品添加审核
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function exemptionUpdate(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'spu/update_without_audit?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 撤销审核
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function resetAudit(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'spu/del_audit?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 检验当前场景值是否需要校验
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkScene(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'scene/check?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 上传订单
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadOrder(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'order/add?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取支付参数
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPaymentParams(array $data = [])
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'order/getpaymentparams?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 更新订单地址
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadOrderAddress(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'order/update_address?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取订单
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrder(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'order/get?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取快递公司列表(同步微信订单的快递公司列表)
     * @param array $data
     * @return array|bool|mixed|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCourierCompany(array $data)
    {
        // 查询缓存
        $cacheKey = self::$companyCacheKey;
        $cache = CacheHelper::get($cacheKey);
        if ($cache) {
            return Json::decode($cache);
        }

        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'delivery/get_company_list?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
                CacheHelper::set($cacheKey, Json::encode($result), 86400);
            }
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 确认发货
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendOrder(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'delivery/send?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 订单确认收货
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function confirmOrderStatus(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'delivery/recieve?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 获取售后单详情
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRefundDetail(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/get?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 关闭订单
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function closeOrder(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'order/close?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 用户取消售后单
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function cancelRefund(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/cancel?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 创建维权
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function refundOrder(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/add?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 更新售后单
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateRefund(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/update?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 用户上传物流信息(维权退货)
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadReturnInfo(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/uploadreturninfo?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 拒绝售后
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function rejectRefund(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/reject?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 同意退货
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function acceptReturn(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/acceptreturn?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }

    /**
     * 同意退款
     * @param array $data
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function returnAccept(array $data)
    {
        try {
            $token = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_MINI_PROGRAM, [])->factory->access_token->getToken();
            // 微信开放平台参数兼容
            $token['access_token'] = $token['authorizer_access_token'] ?? $token['access_token'];

            $result = HttpHelper::postJson(self::$baseUrl . 'ecaftersale/acceptrefund?access_token=' . $token['access_token'], Json::encode($data), [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result, 'wx_transaction_component');
    }
}
