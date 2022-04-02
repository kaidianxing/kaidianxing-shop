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

namespace shopstar\exceptions\member;

use shopstar\bases\exception\BaseException;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GroupException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 21 会员相关
     * 21 标签组业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAM_ERROR = 212101;

    /**
     * @Message("标签组不存在")
     */
    const DETAIL_GROUP_NOT_EXISTS = 212102;

    /**
     * @Message("标签组保存失败")
     */
    const ADD_GROUP_SAVE_FAIL = 212103;

    /**
     * @Message("参数错误")
     */
    const EDIT_PARAM_ERROR = 212104;

    /**
     * @Message("参数错误")
     */
    const DELETE_PARAM_ERROR = 212104;

    /**
     * @Message("删除失败")
     */
    const DELETE_FAIL = 212105;

    /**
     * @Message("标签组保存失败")
     */
    const EDIT_GROUP_SAVE_FAIL = 212106;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 21会员相关
     * 22 标签组客户端
     * 01 错误码
     */


    /*************客户端异常结束*************/

}