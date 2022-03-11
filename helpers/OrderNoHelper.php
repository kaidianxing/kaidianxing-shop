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



namespace shopstar\helpers;


use shopstar\components\payment\base\PayOrderTypeConstant;
use yii\helpers\Json;

class OrderNoHelper
{
    /**
     * 缓存前缀
     * @var string
     */
    public static $prefix = "kdx_shop_order_no_";

    /**
     * 商城订单
     * @author 青岛开店星信息技术有限公司
     * @var string
     */
    public static $corePrefix = "kdx_shop_core_order_no_";

    /**
     * 商城订单
     * @author 青岛开店星信息技术有限公司
     * @var string
     */
    public static $groupsPrefix = "kdx_shop_groups_no_";

    /**
     * 转账单号前缀
     * @var string
     * @author 青岛开店星信息技术有限公司.
     */
    public static $transferPrefix = "kdx_shop_Transfer_no_";

    /**
     * 获取订单号  如果获取到一个空的订单号之后，那么就存入redis10秒生存时间
     * @param int|string $orderType
     * @param string $channel 渠道
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderNo($orderType, string $channel)
    {
        $orderNoPrefix = $orderType . $channel;
        $orderNoPrefix .= substr(date('Y'), '-2');
        $orderNoPrefix .= date('mdHis');
        $orderCode = self::circular($orderNoPrefix, 6);

        $redis = \Yii::$app->redis;
        $redis->setex(self::$prefix . $orderNoPrefix . $orderCode, 10, 1);
        return $orderNoPrefix . $orderCode;
    }

    /**
     * 获取订单外部交易单号  每次订单的支付单号都是生成固定的 获取会员id的md5前4位 + md5 16位+前缀 22位
     * @param string $prefix 前缀
     * @param int $memberId
     * @param array $orderId
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOrderOutTradeNo(string $prefix, int $memberId, $orderType, array $orderId, array $orderNo, array $options = [])
    {
        $options = array_merge([
            'change_price_count' => 0 //改价次数
        ], $options);

        $orderNoPrefix = $prefix;
        $orderNoPrefix .= substr(date('Y'), '-2');

        $orderNo = array_filter($orderNo);

        //兼容预售
        if (!empty($orderNo)) {
            $md5String = Json::encode([$orderType, $orderNo]);
        } else {
            $md5String = Json::encode([$orderType, $orderId]);
        }

        //如果是普通商城订单支付拼接改价次数
        if ($orderType == PayOrderTypeConstant::ORDER_TYPE_ORDER) {
            $md5String .= 'change_price_count=' . $options['change_price_count'];
        }

        //加密
        $shortMd5 = StringHelper::shortMd5($md5String);

        $orderNoMd5 = substr(md5($memberId), 0, 4) . $shortMd5;

        return $orderNoPrefix . $orderNoMd5;
    }

    /**
     * 获取转账单号
     * @param int $memberId
     * @param int $id
     * @param int $type 转账类型
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getTransferNo(int $memberId, int $id, int $type)
    {
        $orderNoPrefix = 'TR';
        $orderNoPrefix .= substr(date('Y'), '-2');
        $md5String = Json::encode([$memberId, $id, $type]);

        //加密
        $shortMd5 = StringHelper::shortMd5($md5String);

        $orderNoMd5 = substr(md5($memberId), 0, 4) . $shortMd5;
        return $orderNoPrefix . $orderNoMd5;
    }

    /**
     * 获取不存在的订单号
     * @param string $prefix
     * @param int $length
     * @return string|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function circular($prefix = '', $length = 12)
    {
        $redis = \Yii::$app->redis;
        while (true) {
            $code = StringHelper::random($length, true);
            $isExist = $redis->get(self::$prefix . $prefix . $code);
            if (empty($isExist)) {
                return $code;
            }
        }

        return;
    }

    /**
     * 获取订单号  如果获取到一个空的订单号之后，那么就存入redis一天生存时间
     * 订单编号格式：
     *              短信类    SMS+年月日+随机补充，整体为16位
     *              插件及插件的购买  APP++年月日+随机补充，整体为16位。
     * @param string $type
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCoreOrderNo(string $type)
    {
        $orderNoPrefix = $type . date('Ymd');
        $orderCode = self::circular($orderNoPrefix, 5);

        $redis = \Yii::$app->redis;
        $redis->setex(self::$corePrefix . $orderNoPrefix . $orderCode, 86400, 1);
        return $orderNoPrefix . $orderCode;
    }

    /**
     * 获取拼团单号
     * @param string $prefix
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getGroupsNo($prefix = 'PT')
    {
        $orderNoPrefix = $prefix;
        $orderNoPrefix .= substr(date('Ymd'), '-2');
        $orderNoPrefix .= date('mdHis');
        $orderCode = self::circular($orderNoPrefix, 6);

        $redis = \Yii::$app->redis;
        $redis->setex(self::$groupsPrefix . $orderNoPrefix . $orderCode, 10, 1);
        return $orderNoPrefix . $orderCode;
    }

    /**
     * 获取礼品卡订单号
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberCardNo()
    {
        $orderNoPrefix = substr(date('Ymd'), '-2');
        $orderNoPrefix .= date('His');
        $orderCode = self::circular($orderNoPrefix, 2);

        $redis = \Yii::$app->redis;
        $redis->setex(self::$groupsPrefix . $orderNoPrefix . $orderCode, 10, 1);
        return $orderNoPrefix . $orderCode;
    }

}
