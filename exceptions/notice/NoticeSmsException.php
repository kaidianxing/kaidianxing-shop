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

namespace shopstar\exceptions\notice;

use shopstar\bases\exception\BaseException;

/**
 * @author 青岛开店星信息技术有限公司
 */
class NoticeSmsException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 01 短信设置
     */

    /**
     * @Message("参数错误")
     */
    const SMS_DETAIL_PARAMS_ERROR = 340101;

    /**
     * @Message("短信模板不存在")
     */
    const DETAIL_SMS_NOT_EXISTS = 340102;

    /**
     * @Message("保存失败")
     */
    const SMS_ADD_SAVE_FAIL = 340103;

    /**
     * @Message("参数错误")
     */
    const SMS_EDIT_PARAMS_ERROR = 340104;

    /**
     * @Message("参数错误")
     */
    const SMS_CHANGE_STATE_PARAMS_ERROR = 340105;

    /**
     * @Message("修改状态失败")
     */
    const SMS_CHANGE_STATE_FAIL = 340106;

    /**
     * @Message("参数错误")
     */
    const SMS_DELETE_PARAMS_ERROR = 340107;

    /**
     * @Message("参数错误")
     */
    const SMS_SEND_DATA_PARAMS_ERROR = 340108;

    /**
     * @Message("参数错误")
     */
    const SMS_SEND_DATA_DETAIL_NOT_EXISTS = 340109;

    /**
     * @Message("短信配置错误")
     */
    const SEND_DATA_SMS_SET_ERROR = 340110;

    /**
     * @Message("参数错误")
     */
    const TEST_SEND_PARAMS_ERROR = 340111;

    /**
     * @Message("手机号格式错误")
     */
    const TEST_SEND_MOBILE_ERROR = 340112;

    /**
     * @Message("数据为空")
     */
    const TEST_SEND_DATA_EMPTY = 340113;

    /**
     * @Message("短信模板不存在")
     */
    const TEST_SEND_SMS_NOT_EXISTS = 340114;

    /**
     * @Message("短信模板获取配置错误")
     */
    const TEST_SEND_CONFIG_ERROR = 340115;

    /**
     * @Message("短信发送失败")
     */
    const TEST_SEND_SMS_FAIL = 340116;

    /**
     * @Message("阿里云短信设置不能为空")
     */
    const ALIYUN_SET_NOT_EMPTY = 340117;

    /**
     * @Message("聚合短信设置不能为空")
     */
    const JUHE_SET_NOT_EMPTY = 340118;

    /**
     * @Message("短信配置保存失败")
     */
    const SMS_SET_SAVE_FAIL = 340119;

    /**
     * @Message("保存失败")
     */
    const CODE_SAVE_FAIL = 340120;

    /**
     * @Message("短信模板数量不足")
     */
    const SMS_TEMPLATE_QUANTITY_ERROR = 340121;

    /**
     * @Message("短信模板数量模块未找到")
     */
    const SMS_TEMPLATE_NUMBER_CORE_APP_ERROR = 340130;

    /**
     * @Message("短信数量模块未找到")
     */
    const SMS_NUMBER_CORE_APP_ERROR = 340130;


    /**
     * @Message("短信配置异常")
     */
    const SMS_THE_SEND_IS_ERROR = 340131;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 应该木有客户端
     */
    /*************客户端异常结束*************/
}
