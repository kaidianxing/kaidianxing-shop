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

namespace shopstar\constants\commission;

use shopstar\bases\constant\BaseConstant;

/**
 * 分销日志
 * Class CommissionLogConstant
 * @package shopstar\constants\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionLogConstant extends BaseConstant
{
    /***************** 分销商 *******************/
    /**
     * @Text("分销-分销商管理-审核")
     */
    public const AGENT_AUDIT = 331000;
    
    /**
     * @Text("分销-分销商管理-修改自动升级")
     */
    public const AGENT_CHANGE_AUTO_UPGRADE = 331001;
    
    /**
     * @Text("分销-分销商管理-修改上级分销商")
     */
    public const AGENT_CHANGE_AGENT = 331002;
    
    /**
     * @Text("分销-分销商管理-手动设置分销商")
     */
    public const AGENT_MANUAL_AGENT = 331003;
    
    /**
     * @Text("分销-分销商管理-取消分销商资格")
     */
    public const AGENT_CANCEL_AGENT = 331004;

    /**
     * @Text("分销-分销商管理-解绑上级分销商")
     */
    public const AGENT_UNBIND_AGENT = 331005;
    
    
    
    /***************** 分销等级 *******************/
    /**
     * @Text("分销-分销等级-新增")
     */
    public const LEVEL_ADD = 332000;
    
    /**
     * @Text("分销-分销等级-修改")
     */
    public const LEVEL_EDIT = 332001;
    
    /**
     * @Text("分销-分销等级-修改等级状态")
     */
    public const LEVEL_CHANGE_STATUS = 332002;
    
    /**
     * @Text("分销-分销等级-删除等级")
     */
    public const LEVEL_DELETE = 332003;
    
    
    
    /***************** 提现管理 *******************/
    /**
     * @Text("分销-提现管理-审核")
     */
    public const WITHDRAW_AGREE = 333000;
    
    /**
     * @Text("分销-提现管理-打款")
     */
    public const WITHDRAW_PAY = 333001;
    
    
    
    /***************** 分销设置 *******************/
    /**
     * @Text("分销-分销设置-修改分销设置")
     */
    public const COMMISSION_SETTING_EDIT = 334000;
    
    /**
     * @Text("分销-分销设置-修改结算设置")
     */
    public const COMMISSION_SETTLEMENT_EDIT = 334001;
    
    
    
    /***************** 基础设置 *******************/
    /**
     * @Text("分销-基础设置-修改排行榜设置")
     */
    public const RANK_EDIT = 335000;
    
    /**
     * @Text("分销-基础设置-修改其他设置")
     */
    public const OTHER_EDIT = 335001;
    
    /***************** 分销订单 *******************/
    /**
     * @Text("分销-分销订单-修改佣金")
     */
    public const CHANGE_COMMISSION = 336000;
    
    
    
}