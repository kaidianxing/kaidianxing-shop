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

class PosterException extends BaseException
{


    /******************** 后台 ********************/

    /**
     * @Message("海报不存在")
     */
    public const  POSTER_LIST_FORBIDDEN_RECORD_EMPTY = 431000;

    /**
     * @Message("海报不存在")
     */
    public const  POSTER_LIST_ACTIVE_RECORD_EMPTY = 431001;

    /**
     * @Message("海报不存在")
     */
    public const  POSTER_LIST_DELETE_RECORD_EMPTY = 431002;

    /**
     * @Message("海报不存在")
     */
    public const  POSTER_LIST_CHECK_TYPE_INVALID = 431003;


    /******************** 移动端 ********************/

    /**
     * @Message("openid不存在")
     */
    public const  POSTER_INDEX_ATTENTION_OPENID_INVALID = 435000;

    /**
     * @Message("获取QR失败")
     */
    public const  POSTER_INDEX_ATTENTION_GET_QR_INVALID = 435001;

    /**
     * @Message("插入二维码失败")
     */
    public const  POSTER_INDEX_ATTENTION_ADD_QR_INVALID = 435002;

    /**
     * @Message("消息类型错误")
     */
    public const  POSTER_RESPONSE_INDEX_MSG_TYPE_INVALID = 435003;
}