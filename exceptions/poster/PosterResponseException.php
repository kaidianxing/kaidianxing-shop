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

namespace shopstar\exceptions\poster;

use shopstar\bases\exception\BaseException;

/**
 * 海报智能回复异常
 * Class PosterResponseException
 * @package shopstar\exceptions\poster
 * @author 青岛开店星信息技术有限公司
 */
class PosterResponseException extends BaseException
{
    /**
     * @Message("推荐者海报奖励积分修改失败")
     */
    public const  POSTER_RESPONSE_AWARD_REC_CREDIT_RES_FAILED = 435000;

    /**
     * @Message("推荐者海报奖励余额修改失败")
     */
    public const  POSTER_RESPONSE_AWARD_REC_CASH_RES_FAILED = 435001;

    /**
     * @Message("推荐者海报奖励发送优惠券失败")
     */
    public const  POSTER_RESPONSE_AWARD_REC_COUPON_RES_FAILED = 435002;

    /**
     * @Message("关注者海报奖励积分修改失败")
     */
    public const  POSTER_RESPONSE_AWARD_SUB_CREDIT_RES_FAILED = 435003;

    /**
     * @Message("关注者海报奖励余额修改失败")
     */
    public const  POSTER_RESPONSE_AWARD_SUB_CASH_RES_FAILED = 435004;

    /**
     * @Message("关注者海报奖励发送优惠券失败")
     */
    public const  POSTER_RESPONSE_AWARD_SUB_COUPON_RES_FAILED = 435005;
}