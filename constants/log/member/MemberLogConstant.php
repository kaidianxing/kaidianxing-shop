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

namespace shopstar\constants\log\member;

use shopstar\bases\constant\BaseConstant;

/**
 * 会员日志
 * Class MemberLogConstant
 * @package shopstar\constants\log\member
 * @author 青岛开店星信息技术有限公司
 */
class MemberLogConstant extends BaseConstant
{
    /************ 会员 ***************/
    /**
     * @Text("会员-修改等级")
     */
    public const MEMBER_CHANGE_LEVEL = 210000;
    
    /**
     * @Text("会员-修改黑名单")
     */
    public const MEMBER_SET_BLACK = 210001;
    
    /**
     * @Text("会员-修改密码")
     */
    public const MEMBER_CHANGE_PASSWORD = 210002;
    
    /**
     * @Text("会员-修改手机号")
     */
    public const MEMBER_CHANGE_MOBILE = 210003;

    /**
     * @Text("会员-修改备注")
     */
    public const MEMBER_CHANGE_REMARK = 210006;
    
    /**
     * @Text("会员-修改标签组")
     */
    public const MEMBER_CHANGE_GROUP = 210004;
    
    /**
     * @Text("会员-删除会员")
     */
    public const MEMBER_DELETE = 210005;
    
    
    
    /********** 会员等级 *************/
    /**
     * @Text("会员-等级-添加")
     */
    public const MEMBER_LEVEL_ADD = 210100;
    
    /**
     * @Text("会员-等级-编辑")
     */
    public const MEMBER_LEVEL_EDIT = 210101;
    
    /**
     * @Text("会员-等级-删除")
     */
    public const MEMBER_LEVEL_DELETE = 210102;
    
    /**
     * @Text("会员-等级-修改状态")
     */
    public const MEMBER_LEVEL_CHANGE_STATE = 210103;
    
    
    /************ 会员标签组 *************/
    /**
     * @Text("会员-标签组-添加")
     */
    public const MEMBER_GROUP_ADD = 210200;
    
    /**
     * @Text("会员-标签组-编辑")
     */
    public const MEMBER_GROUP_EDIT = 210201;
    
    /**
     * @Text("会员-标签组-删除")
     */
    public const MEMBER_GROUP_DELETE = 210202;
    
    
    /************* 升级方式 ************/
    /**
     * @Text("会员-升级设置-修改")
     */
    public const MEMBER_LEVEL_UPGRADE = 210300;
    
    
    /************* 排行榜 ************/
    /**
     * @Text("会员-排行榜-修改")
     */
    public const MEMBER_RANK_EDIT = 210400;
    
    
}