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
class LevelException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 21 会员相关
     * 31 等级业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const DETAIL_PARAM_ERROR = 213101;

    /**
     * @Message("添加等级失败")
     */
    const ADD_LEVEL_FAIL = 213102;

    /**
     * @Message("参数错误")
     */
    const EDIT_PARAM_ERROR = 213103;

    /**
     * @Message("等级保存失败")
     */
    const EDIT_LEVEL_FAIL = 213104;

    /**
     * @Message("等级不存在")
     */
    const DETAIL_LEVEL_NOT_EXISTS = 213105;

    /**
     * @Message("等级已存在")
     */
    const LEVEL_IS_EXISTS = 213106;

    /**
     * @Message("参数错误")
     */
    const DELETE_LEVEL_FAIL = 213107;

    /**
     * @Message("等级删除失败")
     */
    const DELETE_FAIL = 213108;

    /**
     * @Message("参数错误")
     */
    const CHANGE_STATE_LEVEL_FAIL = 213109;

    /**
     * @Message("修改等级失败")
     */
    const CHANGE_LEVEL_STATE_FAIL = 213110;

    /**
     * @Message("修改等级升级方式失败")
     */
    const CHANGE_UPDATE_TYPE_FAIL = 213111;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 21 会员相关
     * 32 等级客户端
     * 01 错误码
     */


    /*************客户端异常结束*************/

}