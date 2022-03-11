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
 * 地址物流
 * Class CreditException
 * @package shopstar\bases\exception
 */
class ExpressException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 13 设置
     * 20 地址物流
     * 01 错误码
     */
    
    /**
     * @Message("地址设置保存失败")
     */
    const ADDRESS_SET_SAVE_FAIL = 132001;
    
    /**
     * @Message("物流信息配置保存失败")
     */
    const EXPRESS_SAVE_FAIL = 132002;
    
    /**
     * @Message("退货地址新增失败")
     */
    const REFUND_ADDRESS_ADD_SAVE_FAIL = 132003;
    
    /**
     * @Message("修改失败")
     */
    const REFUND_ADDRESS_EDIT_SAVE_FAIL = 132004;
    
    /**
     * @Message("参数错误")
     */
    const REFUND_ADDRESS_DETAIL_PARAMS_ERROR = 132005;
    
    /**
     * @Message("退货地址不存在")
     */
    const REFUND_ADDRESS_DETAIL_ADDRESS_NOT_EXISTS = 132006;
    
    /**
     * @Message("删除失败")
     */
    const REFUND_ADDRESS_DELETE_FAIL = 132007;
    
    /**
     * @Message("参数错误")
     */
    const REFUND_ADDRESS_CHANGE_DEFAULT_FAIL = 132009;
    
    
    /*************业务端异常结束*************/
    
    /*************客户端异常开始*************/
    /**
     * 设置应该木有客户端
     */
    /*************客户端异常结束*************/
    
}