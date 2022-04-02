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
class CommissionSetException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 33 分销
     * 31 设置 业务端
     * 01 错误码
     */

    /**
     * @Message("分销设置保存失败")
     */
    const COMMISSION_SET_SAVE_FAIL = 333101;

    /**
     * @Message("商品选择不能为空")
     */
    const COMMISSION_BECOME_GOODS_NOT_EMPTY = 333102;

    /**
     * @Message("消费条件不能为空")
     */
    const COMMISSION_BECOME_MONEY_NOT_EMPTY = 333103;

    /**
     * @Message("消费条件不能为空")
     */
    const COMMISSION_BECOME_COUNT_NOT_EMPTY = 333104;

    /**
     * @Message("结算设置保存失败")
     */
    const COMMISSION_SETTLEMENT_SAVE_FAIL = 333105;

    /**
     * @Message("最低提现额度错误")
     */
    const COMMISSION_SETTLEMENT_WITHDRAW_LIMIT_ERROR = 333106;

    /**
     * @Message("提现手续费错误")
     */
    const COMMISSION_SETTLEMENT_WITHDRAW_TYPE_ERROR = 333107;

    /**
     * @Message("免手续费开始区间不能为负数")
     */
    const COMMISSION_SETTLEMENT_WITHDRAW_MONEY_NOT_FEE_START_ERROR = 333108;

    /**
     * @Message("免手续费区间开始不能大于结束")
     */
    const COMMISSION_SETTLEMENT_WITHDRAW_MONEY_NOT_FEE_ERROR = 333109;

    /**
     * @Message("结算天数不能为空")
     */
    const COMMISSION_SETTLEMENT_CALCULATE_DAYS_NOT_EMPTY = 333110;

    /**
     * @Message("结算天数设置错误")
     */
    const COMMISSION_SETTLEMENT_CALCULATE_DAYS_ERROR = 333111;

    /**
     * @Message("自动审核分销商等级不能为空")
     */
    const COMMISSION_SETTLEMENT_LEVEL_NOT_EMPTY = 333112;

    /**
     * @Message("自动审核提现金额错误")
     */
    const COMMISSION_SETTLEMENT_AUTO_WITHDRAW_LIMIT_ERROR = 333113;

    /**
     * @Message("保存失败")
     */
    const SET_SAVE_FAIL = 333114;

    /**
     * @Message("保存失败")
     */
    const NOTICE_SET_SAVE_FAIL = 333115;

    /**
     * @Message("商品选择超过限制数量")
     */
    const COMMISSION_BECOME_GOODS_MAX_ERROR = 333116;

    /**
     * @Message("成为分销商和成为下线条件冲突")
     */
    const COMMISSION_SET_CHILD_AND_BECOME_ERROR = 333117;

    /**
     * @Message("竞争分销应用暂无权限")
     */
    const COMMISSION_SET_COMPETE_COMMISSION_NOT_PERM = 333118;

    /**
     * @Message("自定义保护期错误")
     */
    const COMMISSION_SET_COMPETE_SAFE_DAYS_ERROR = 333119;

    /**
     * @Message("分销类型错误")
     */
    const COMMISSION_SET_COMMISSION_TYPE_ERROR = 333120;

    /**
     * @Message("分销商层级不正确")
     */
    const COMMISSION_CENG_LEVEL_ERROR = 333121;

    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 33 分销
     * 32 等级 客户端
     * 01 错误码
     */

    /**
     * @Message("未开启分销")
     */
    const COMMISSION_IS_CLOSE = 333201;

    /*************客户端异常结束*************/
}