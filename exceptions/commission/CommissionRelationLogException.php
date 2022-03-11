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

namespace shopstar\exceptions\commission;

use shopstar\bases\exception\BaseException;

/**
 * 分享关系日志异常
 * Class CommissionAgentException
 * @method getMessage($code) static 获取文字
 * @package shopstar\exceptions\commission
 */
class CommissionRelationLogException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 33 分销
     * 61 分享关系日志
     * 01 错误码
     */
    /**
     * @Message("日志保存失败")
     */
    const LOG_SAVE_ERROR = 336101;


    /**
     * @Message("会员不存在")
     */
    const DETAIL_MEMBER_NOT_EXISTS = 336102;

    /**
     * @Message("参数错误")
     */
    const COMMISSION_INFO_MEMBER_ID_EMPTY = 336103;



}