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

namespace shopstar\exceptions\sysset;

use shopstar\bases\exception\BaseException;

/**
 * 积分余额异常
 * Class CreditException
 * @package shopstar\bases\exception
 */
class CreditException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置相关
     * 10 积分余额异常
     * 01 错误码
     */
    
    /**
     * @Message("积分定义文字不能为空")
     */
    const CREDIT_TEXT_EMPTY = 131001;
    
    /**
     * @Message("积分上限不能为空")
     */
    const MOST_CREDIT_EMPTY = 131002;
    
    /**
     * @Message("积分上限必须是正整数")
     */
    const MOST_CREDIT_NOT_INT = 131003;
    
    /**
     * @Message("积分上限最大支持9999999")
     */
    const MOST_CREDIT_BIG = 131004;
    
    /**
     * @Message("余额定义文字不能为空")
     */
    const BALANCE_TEXT_EMPTY = 131005;
    
    /**
     * @Message("充值余额赠送积分不能为空")
     */
    const RECHARGE_GIVE_CREDIT_EMPTY = 131006;
    
    /**
     * @Message("充值余额赠送积分必须是正整数")
     */
    const RECHARGE_GIVE_CREDIT_NOT_INT = 131007;
    
    /**
     * @Message("最低充值余额为0.1")
     */
    const RECHARGE_MONEY_LOW = 131008;
    
    /**
     * @Message("余额提现金额不能为空")
     */
    const WITHDRAW_MONEY_EMPTY = 131009;
    
    /**
     * @Message("余额提现金额不能为负数")
     */
    const WITHDRAW_MONEY_ERROR = 131010;
    
    /**
     * @Message("提现手续费设置错误")
     */
    const WITHDRAW_MONEY_FEE_ERROR = 131011;
    
    /**
     * @Message("免手续费区间开始不能大于结束")
     */
    const WITHDRAW_MONEY_NOT_FEE_ERROR = 131012;
    
    /**
     * @Message("免手续费开始区间不能为负数")
     */
    const WITHDRAW_MONEY_NOT_FEE_START_ERROR = 131013;
    
    /**
     * @Message("保存失败")
     */
    const CREDIT_SAVE_FAIL = 131014;
    
    
    /*************业务端异常结束*************/
    
    
    /*************客户端异常开始*************/
    /**
     * 设置应该木有客户端
     */
    /*************客户端异常结束*************/
  
}