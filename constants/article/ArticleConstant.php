<?php
namespace shopstar\constants\article;

use shopstar\bases\constant\BaseConstant;

/**
 * 文章常量
 * Class ArticleConstant
 * @package shopstar\constants\article
 * @author yuning
 */
class ArticleConstant extends BaseConstant
{
    /**
     * @Text("未发布")
     */
    const ARTICLE_STATUS_HIDE = 0;

    /**
     * @Text("已发布")
     */
    const ARTICLE_STATUS_SHOW = 1;


    /**
     * @Text("关闭")
     */
    const ARTICLE_COMMON_CLOSE = 0;

    /**
     * @Text("开启")
     */
    const ARTICLE_COMMON_OPEN = 1;

    /**
     * @Text("关闭")
     */
    const ARTICLE_DOWNLOAD_CLOSE = 0;

    /**
     * @Text("开启")
     */
    const ARTICLE_DOWNLOAD_OPEN = 1;


    /**
     * @Text("无奖励")
     */
    const ARTICLE_REWARD_TYPE_NONE = 0;

    /**
     * @Text("积分")
     */
    const ARTICLE_REWARD_TYPE_CREDIT = 1;

    /**
     * @Text("余额")
     */
    const ARTICLE_REWARD_TYPE_BALANCE = 2;

    /**
     * @Text("优惠券")
     */
    const ARTICLE_REWARD_TYPE_COUPON = 3;


    /**
     * @Text("已删除")
     */
    const ARTICLE_IS_DELETE = 1;

    /**
     * @Text("未删除")
     */
    const ARTICLE_NOT_DELETE = 0;

    /**
     * @Text("置顶数量限制")
     */
    const ARTICLE_TOPPING_NUM_LIMIT = 4;
    /**
     * @Text("文章内商品数量限制")
     */
    const ARTICLE_SAVE_GOODS_NUM_LIMIT = 5;


}