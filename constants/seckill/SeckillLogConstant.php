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

namespace shopstar\constants\seckill;

use shopstar\bases\constant\BaseConstant;

/**
 * 秒杀日志
 * Class SeckillLogConstant
 * @package shopstar\constants\seckill
 * @author 青岛开店星信息技术有限公司
 */
class SeckillLogConstant extends BaseConstant
{
    /**
     * @Text("秒杀-设置-修改")
     */
    public const CHANGE_SETTING = 400000;
    
    /**
     * @Text("秒杀-活动-删除")
     */
    public const DELETE = 400001;
    
    /**
     * @Text("秒杀-活动-手动停止")
     */
    public const STOP = 400002;
    
    /**
     * @Text("秒杀-活动-编辑")
     */
    public const EDIT = 400003;
    
    /**
     * @Text("秒杀-活动-新增")
     */
    public const ADD = 400004;
    
    
}