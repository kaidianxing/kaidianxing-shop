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
 * 分销佣金提现异常
 * Class CommissionApplyException
 * @package shopstar\exceptions\commission
 */
class CommissionApplyException extends BaseException
{

    /**
     * 33130X 手机端申请提现
     */

    /**
     * @Message("请选择提现方式")
     */
    public const  SUBMIT_PARAMS_TYPE_EMPTY = 331300;
    /**
     * @Message("无效的提现方式")
     */
    public const  SUBMIT_PARAMS_TYPE_INVALID = 331301;

    /**
     * @Message("请填写支付宝账号")
     */
    public const  SUBMIT_PARAMS_ALIPAY_EMPTY = 331302;

    /**
     * @Message("请填写姓名")
     */
    public const  SUBMIT_PARAMS_REAL_NAME_EMPTY = 331303;

    /**
     * @Message("请填写提现金额")
     */
    public const  SUBMIT_PARAMS_PRICE_EMPTY = 331304;

    /**
     * @Message("不满足最小提现金额")
     */
    public const  SUBMIT_PARAMS_PRICE_LIMIT = 331305;

    /**
     * @Message("可提现佣金不足")
     */
    public const  SUBMIT_COMMISSION_PRICE_NOT_OK = 331306;

    /**
     * @Message("提交申请失败")
     */
    public const SUBMIT_FAIL = 331310;


    /**
     * 33132X 后台提现审核
     */

    /**
     * @Message("参数错误")
     */
    public const CHECK_AGREED_PARAMS_ID_EMPTY = 331320;

    /**
     * @Message("未找到提现记录")
     */
    public const CHECK_AGREED_RECORD_NOT_FOUND = 331321;

    /**
     * @Message("参数错误")
     */
    public const CHECK_REFUSE_PARAMS_ID_EMPTY = 331330;

    /**
     * @Message("未找到提现记录")
     */
    public const CHECK_REFUSE_RECORD_NOT_FOUND = 331331;

    /**
     * @Message("返还佣金失败")
     */
    public const CHECK_REFUSE_SEND_BACK_COMMISSION_FAIL = 331332;

    /**
     * @Message("参数错误")
     */
    public const MANUAL_REMIT_PARAMS_ID_EMPTY = 331340;

    /**
     * @Message("未找到提现记录")
     */
    public const MANUAL_REMIT_RECORD_NOT_FOUND = 331341;

    /**
     * @Message("参数错误")
     */
    public const REMIT_PARAMS_ID_EMPTY = 331350;

    /**
     * @Message("未找到提现记录")
     */
    public const REMIT_RECORD_NOT_FOUND = 331351;

    /**
     * @Message("当前状态不可操作打款")
     */
    public const REMIT_RECORD_STATUS_ERROR = 331352;

    /**
     * @Message("提现类型错误")
     */
    public const WITHDRAW_TYPE_ERROR = 331353;

    /**
     * @Message("执行打款失败")
     */
    public const WITHDRAW_APPLY_ERROR = 331354;

    /**
     * @Message("该申请不支持红包打款，请选择其他打款方式")
     */
    public const WITHDRAW_APPLY_NOT_ALLOW_RED_PACK = 331355;


    /**
     * 33136x 重新审核提现
     */

    /**
     * @Message("参数错误")
     */
    public const CHECK_AGAIN_PARAM_ID_EMPTY = 331360;

    /**
     * @Message("未找到提现记录")
     */
    public const CHECK_AGAIN_RECORD_NOT_FOUND = 331361;

    /**
     * @Message("当前状态无效")
     */
    public const CHECK_AGAIN_APPLY_STATUS_INVALID = 331362;

    /**
     * @Message("审核失败")
     */
    public const CHECK_AGAIN_FAIL = 331363;

    /**
     * @Message("导出失败")
     */
    public const APPLY_EXPORT_FAIL = 331364;


}