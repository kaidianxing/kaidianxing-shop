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

namespace shopstar\exceptions\diypage;

use shopstar\bases\exception\BaseException;

/**
 * 店铺装修异常
 * Class DiypageException
 * @package shopstar\exceptions\diypage
 * @author 青岛开店星信息技术有限公司
 */
class DiypageException extends BaseException
{

    /**
     * @Message("会员未登录")
     */
    public const CLIENT_PAGE_ACCESS_NOT_LOGIN = 321000;

    /**
     * @Message("会员无权限访问")
     */
    public const CLIENT_PAGE_ACCESS_LIMIT = 321001;


}