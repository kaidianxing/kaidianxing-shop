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
 * @author 青岛开店星信息技术有限公司
 */
class CommissionLevelException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 33 分销
     * 21 等级 业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const LEVEL_DELETE_PARAMS_ERROR = 332101;

    /**
     * @Message("删除等级失败")
     */
    const LEVEL_DELETE_FAIL = 332102;

    /**
     * @Message("新增等级失败")
     */
    const LEVEL_ADD_FAIL = 332103;

    /**
     * @Message("参数错误")
     */
    const LEVEL_EDIT_PARAMS_ERROR = 332104;

    /**
     * @Message("修改等级失败")
     */
    const LEVEL_EDIT_FAIL = 332105;

    /**
     * @Message("参数错误")
     */
    const LEVEL_DETAIL_PARAMS_ERROR = 332106;

    /**
     * @Message("参数错误")
     */
    const LEVEL_CHANGE_STATUS_PARAMS_ERROR = 332107;

    /**
     * @Message("修改状态失败")
     */
    const LEVEL_CHANGE_STATUS_FAIL = 332108;

    /**
     * @Message("不能移入该等级下")
     */
    const LEVEL_DELETE_NOT_REPEAT = 332109;

    /**
     * @Message("无默认分销等级")
     */
    const LEVEL_DELETE_NOT_EXISTS_DEFAULT_LEVEL = 332110;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 33 分销
     * 22 等级 客户端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const MEMBER_LEVEL_DELETE_PARAMS_ERROR = 332201;

    /**
     * @Message("等级不存在")
     */
    const MEMBER_LEVEL_DELETE_NOT_EXISTS = 332202;


    /*************客户端异常结束*************/
}