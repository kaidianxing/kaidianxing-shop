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

namespace shopstar\exceptions\member;

use shopstar\bases\exception\BaseException;

/**
 * @author 青岛开店星信息技术有限公司
 */
class RankException extends BaseException
{
    /*************业务端异常开始*************/
    /**
     * 21 会员相关
     * 41 排行榜业务端
     * 01 错误码
     */

    /**
     * @Message("修改等级排行榜失败")
     */
    const CHANGE_RANK_FAIL = 214101;


    /*************业务端异常结束*************/

    /*************客户端异常开始*************/
    /**
     * 32 会员相关
     * 42 排行榜 客户端
     * 001 错误码
     */


    /*************客户端异常结束*************/

}