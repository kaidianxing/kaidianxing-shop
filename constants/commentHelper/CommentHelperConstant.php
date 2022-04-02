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

namespace shopstar\constants\commentHelper;

use shopstar\bases\constant\BaseConstant;

/**
 * 商品助手常量
 * Class CommentHelperConstant
 * @package shopstar\constants\commentHelper
 * @author 青岛开店星信息技术有限公司
 */
class CommentHelperConstant extends BaseConstant
{
    /**
     * @Text("默认顺序")
     */
    const CONTENT_TYPE_DEFAULT = 0;

    /**
     * @Text("好评")
     */
    const CONTENT_TYPE_GOODS = 1;

    /**
     * @Text("仅抓取带图")
     */
    const CONTENT_TYPE_IMAGES = 2;

    /**
     * @Text("仅抓取文字")
     */
    const CONTENT_TYPE_TEXT = 3;

    /**
     * @Text("渠道选择-淘宝")
     */
    const TYPE_TAOBAO = 'taobao';

    /**
     * @Text("渠道选择-天猫")
     */
    const TYPE_TMALL = 'tmall';

    /**
     * @Text("渠道选择-苏宁")
     */
    const TYPE_SUNING = 'suning';

    /**
     * @Text("渠道选择-京东")
     */
    const TYPE_JD = 'jd';
}