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
 * 分销商
 * Class CommissionAgentException
 * @method getMessage($code) static 获取文字
 * @package shopstar\exceptions\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionAgentException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 33 分销
     * 11 分销商管理 业务端
     * 01 错误码
     */

    /**
     * @Message("参数错误")
     */
    const AGENT_CANCEL_PARAMS_ERROR = 331101;

    /**
     * @Message("操作失败")
     */
    const AGENT_CHANGE_STATUS_FAIL = 331102;

    /**
     * @Message("参数错误")
     */
    const AGENT_DETAIL_PARAMS_ERROR = 331103;

    /**
     * @Message("分销商不存在")
     */
    const ANENT_MEMBER_NOT_EXISTS = 331104;

    /**
     * @Message("参数错误")
     */
    const AGENT_CHILD_LIST_PARAMS_ERROR = 331105;

    /**
     * @Message("该用户不是分销商")
     */
    const AGENT_IS_NOT_COMMISSION = 331106;

    /**
     * @Message("参数错误")
     */
    const AGENT_CHANGE_LEVEL_PARAMS_ERROR = 331107;

    /**
     * @Message("修改分销等级失败")
     */
    const AGENT_CHANGE_LEVEL_FAIL = 331108;

    /**
     * @Message("修改自动升级错误")
     */
    const AGENT_CHANGE_AUTO_UPGRADE_FAIL = 331109;

    /**
     * @Message("参数错误")
     */
    const AGENT_CHANGE_AGENT_PARAMS_ERROR = 331110;

    /**
     * @Message("修改上级分销商错误")
     */
    const AGENT_CHANGE_AGENT_FAIL = 331111;

    /**
     * @Message("参数错误")
     */
    const AGENT_MANUAL_PARAMS_ERROR = 331112;

    /**
     * @Message("手动设置分销商错误")
     */
    const AGENT_MANUAL_FAIL = 331113;

    /**
     * @Message("导出失败")
     */
    const AGENT_EXPORT_FAIL = 331114;

    /**
     * @Message("导出失败")
     */
    const WAIT_AGENT_EXPORT_FAIL = 331115;

    /**
     * @Message("选中的分销商与该分销商存在下级关系,无法修改为上级")
     */
    const AGENT_CHANGE_AGENT_HAVE_RELATION = 331116;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 33 分销
     * 12 分销商管理 客户端
     * 01 错误码
     */

    /**
     * @Message("当前会员不是分销商")
     */
    public const MEMBER_NOT_IS_AGENT = 331200;

    /**
     * @Message("请填写姓名")
     */
    public const AGENT_REGISTER_EMPTY_NAME = 331201;

    /**
     * @Message("手机号格式不正确")
     */
    public const AGENT_REGISTER_MOBILE_ERROR = 331202;

    /**
     * @Message("已经是分销商")
     */
    public const AGENT_REGISTER_MEMBER_IS_AGENT = 331203;

    /**
     * @Message("申请失败")
     */
    public const AGENT_REGISTER_SAVE_FAIL = 331204;

    /**
     * @Message("等待审核")
     */
    public const AGENT_REGISTER_WAIT_AUDIT = 331205;

    /**
     * @Message("层级错误")
     */
    public const AGENT_DOWN_LINE_LEVEL_ERROR = 331206;

    /**
     * @Message("请填写手机号")
     */
    public const AGENT_REGISTER_EMPTY_MOBILE = 331207;


    /*************客户端异常结束*************/
}