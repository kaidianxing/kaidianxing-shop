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

namespace shopstar\exceptions\seckill;

use shopstar\bases\exception\BaseException;

/**
 * 秒杀异常
 * Class SeckillException
 * @package shopstar\exceptions\seckill
 * @author 青岛开店星信息技术有限公司
 */
class SeckillException extends BaseException
{
    /**
     * @Message("添加失败")
     */
    const SECKILL_ADD_FAIL = 460100;
    
    /**
     * @Message("参数错误")
     */
    const SECKILL_DETAIL_PARAMS_ERROR = 460101;
    
    /**
     * @Message("活动不存在")
     */
    const SECKILL_DETAIL_ACTIVITY_NOT_EXISTS = 460102;
    
    /**
     * @Message("活动不存在")
     */
    const SECKILL_EDIT_PARAMS_ERROR = 460103;
    
    /**
     * @Message("参数错误")
     */
    const SECKILL_MANUAL_STOP_PARAMS_ERROR = 460104;
    
    /**
     * @Message("停止活动失败")
     */
    const SECKILL_MANUAL_STOP_FAIL = 460105;
    
    /**
     * @Message("参数错误")
     */
    const SECKILL_DELETE_PARAMS_ERROR = 460106;
    
    /**
     * @Message("删除失败")
     */
    const SECKILL_DELETE_FAIL = 460107;
    
    /**
     * @Message("活动不存在")
     */
    const SECKILL_EDIT_ACTIVITY_NOT_EXISTS = 460108;
    
    /**
     * @Message("保存失败")
     */
    const SECKILL_EDIT_FAIL = 460109;
    
    /**
     * @Message("商品信息错误")
     */
    const ORDER_SECKILL_GOODS_INFO_ERROR = 460110;
    
    /**
     * @Message("库存不足")
     */
    const ORDER_SECKILL_CONFIRM_GOODS_STOCK_NOT_ENOUGH = 460111;
    
    /**
     * @Message("库存不足")
     */
    const ORDER_SECKILL_GOODS_STOCK_NOT_ENOUGH = 460112;
    
    /**
     * @Message("超出购买件数")
     */
    const ORDER_SECKILL_BUY_LIMIT = 460113;
    
    /**
     * @Message("参数错误")
     */
    const STATISTICS_PARAMS_ERROR = 460114;
    
    /**
     * @Message("商品信息错误")
     */
    const ORDER_SECKILL_REDUCTION_TYPE_ERROR = 460115;
    
    /**
     * @Message("修改时间不能小于当前结束时间")
     */
    const NEW_EDIT_EMD_TIME_NOT_LESS_THAN_OLD_END_TIME_ERROR = 460116;


}