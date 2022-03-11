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

namespace shopstar\components\payment\base;

/**
 * 支付宝错误解析
 * Class AlipayError
 * @package shopstar\components\payment\base
 */
class AlipayError
{
    /**
     * @var array 错误码对应错误信息
     */
    private static $codeMap = [
        '20000' => '服务不可用',
        '20001' => '授权权限不足',
        '40001' => '缺少必选参数',
        '40002' => '非法的参数',
        '40004' => '业务处理失败',
        '40006' => '权限不足',
    ];
    
    /**
     * 解析支付宝错误
     * @param array $raw
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function analysisError(array $raw)
    {
        return AlipayError::$codeMap[$raw['alipay_fund_trans_uni_transfer_response']['code']].':'.$raw['alipay_fund_trans_uni_transfer_response']['sub_msg'];
    }
    
}