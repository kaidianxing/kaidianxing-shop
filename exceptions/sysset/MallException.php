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
 * 商城基础设置异常
 * Class MallException
 * @package shopstar\bases\exception
 */
class MallException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置
     * 30 基础设置
     * 01 错误码
     */
    
    /**
     * @Message("商城基础设置保存失败")
     */
    const BASIC_SAVE_FAIL = 133001;
    
    /**
     * @Message("自定义标题不能为空")
     */
    const SHARE_CUSTOMER_TITLE_NOT_EMPTY = 133002;
    
    /**
     * @Message("自定义图标不能为空")
     */
    const SHARE_CUSTOMER_LOGO_NOT_EMPTY = 133003;
    
    /**
     * @Message("自定义跳转链接不能为空")
     */
    const SHARE_CUSTOMER_LINK_NOT_EMPTY = 133004;
    
    /**
     * @Message("自定义分享描述不能为空")
     */
    const SHARE_CUSTOMER_DESCRIPTION_NOT_EMPTY = 133005;
    
    /**
     * @Message("商城分享设置保存失败")
     */
    const SHARE_SAVE_FAIL = 133006;
    
    /**
     * @Message("参数错误")
     */
    const NOTICE_DETAIL_PARAMS_ERROR = 133007;
    
    /**
     * @Message("公告不存在")
     */
    const NOTICE_DETAIL_NOT_EXISTS = 133008;
    
    /**
     * @Message("新增失败")
     */
    const NOTICE_ADD_FAIL = 133009;
    
    /**
     * @Message("参数错误")
     */
    const NOTICE_EDIT_PARAMS_ERROR = 133010;
    
    /**
     * @Message("修改失败")
     */
    const NOTICE_EDIT_FAIL = 133011;
    
    /**
     * @Message("参数错误")
     */
    const NOTICE_CHANGE_STATUS_PARAMS_ERROR = 133012;
    
    /**
     * @Message("修改失败")
     */
    const NOTICE_CHANGE_STATUS_FAIL = 133013;
    
    /**
     * @Message("参数错误")
     */
    const NOTICE_DELETE_PARAMS_ERROR = 133014;
    
    /**
     * @Message("删除失败")
     */
    const NOTICE_DELETE_FAIL = 133015;

    /**
     * @Message("联系人不能为空")
     */
    const LINK_CONTACT_INVALID = 133016;

    /**
     * @Message("联系地址不能为空")
     */
    const LINK_ADDRESS_INVALID = 133017;

    /**
     * @Message("联系方式不能为空")
     */
    const LINK_TEL_INVALID = 133017;

    /**
     * @Message("高德KEY不能为空")
     */
    const AMAP_KEY_INVALID = 133018;

    /**
     * @Message("商城状态未开启")
     * @MessageWithCode("true")
     */
    const SHOP_STATUS_CLOSE = 133019;


    /**
     * @Message("高德WEB_KEY不能为空")
     */
    const WEB_KEY_INVALID = 133020;



    /*************业务端异常结束*************/
    
    /*************客户端异常开始*************/
    /**
     * 设置应该木有客户端
     */
    /*************客户端异常结束*************/
   
}