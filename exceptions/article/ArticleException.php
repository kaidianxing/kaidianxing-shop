<?php

namespace shopstar\exceptions\article;

use shopstar\bases\exception\BaseException;

/**
 * 文章异常类
 * Class ArticleException
 * @package shopstar\exceptions\article
 * @author yuning
 */
class ArticleException extends BaseException
{
    /**
     * @Message("文章保存失败")
     */
    const SAVE_FAIL = 531001;

    /**
     * @Message("获取文章失败: 错误的文章id")
     */
    const SAVE_GET_ARTICLE_ERROR = 531002;

    /**
     * @Message("缺少必填参数")
     */
    const SAVE_PARAMS_EMPTY = 531003;

    /**
     * @Message("排序参数错误")
     */
    const SAVE_PARAMS_DISPLAY_ORDER_ERROR = 531004;

    /**
     * @Message("已存在相同标题的文章")
     */
    const SAVE_PARAMS_TITLE_EXIST = 531005;

    /**
     * @Message("会员等级错误")
     */
    const SAVE_PARAMS_MEMBER_LEVEL_ERROR = 531006;


    /**
     * @Message("店铺下没有已启用的会员等级")
     */
    const SAVE_PARAMS_MEMBER_LEVEL_SHOP_OPEN_EMPTY = 531007;

    /**
     * @Message("缺少会员等级")
     */
    const SAVE_PARAMS_MEMBER_LEVEL_EMPTY = 531008;

    /**
     * @Message("分销商等级错误")
     */
    const SAVE_PARAMS_COMMISSION_LEVEL_ERROR = 531009;


    /**
     * @Message("店铺下没有已启用的分销商等级")
     */
    const SAVE_PARAMS_COMMISSION_LEVEL_SHOP_OPEN_EMPTY = 531010;

    /**
     * @Message("缺少分销商等级")
     */
    const SAVE_PARAMS_COMMISSION_LEVEL_EMPTY = 531011;

    /**
     * @Message("缺少奖励规则")
     */
    const SAVE_PARAMS_REWARD_RULE_EMPTY = 531012;

    /**
     * @Message("缺少积分奖励规则")
     */
    const SAVE_PARAMS_REWARD_RULE_CREDIT_EMPTY = 531013;

    /**
     * @Message("缺少余额奖励规则")
     */
    const SAVE_PARAMS_REWARD_RULE_BALANCE_EMPTY = 531014;

    /**
     * @Message("积分奖励规则错误")
     */
    const SAVE_PARAMS_REWARD_RULE_CREDIT_ERROR = 531015;

    /**
     * @Message("余额奖励规则错误")
     */
    const SAVE_PARAMS_REWARD_RULE_BALANCE_ERROR = 531016;

    /**
     * @Message("文章不存在")
     */
    const ARTICLE_GET_ERROR = 531017;

    /**
     * @Message("置顶文章数量已达上限")
     */
    const ARTICLE_TOPPING_NUM_LIMIT = 531018;

    /**
     * @Message("获取文章内容失败, 请确认文章能正常打开")
     */
    const ARTICLE_GET_WX_ARTICLE_CONTENT_ERROR = 531019;

    /**
     * @Message("商品数量超过限制")
     */
    const ARTICLE_SAVE_GOODS_NUM_LIMIT = 531020;

    /**
     * @Message("商品重复")
     */
    const ARTICLE_SAVE_GOODS_REPEAT_ERROR = 531021;

    /**
     * @Message("商品数据异常:商品已下架/不存在")
     */
    const ARTICLE_SAVE_GOODS_ERROR = 531022;
    /**
     * @Message("优惠券重复")
     */
    const ARTICLE_SAVE_COUPON_REPEAT_ERROR = 531023;

    /**
     * @Message("优惠券数据异常:优惠券不存在")
     */
    const ARTICLE_SAVE_COUPON_ERROR = 531024;

    /**
     * @Message("缺少文章id")
     */
    const ARTICLE_ID_EMPTY = 531025;

    /**
     * @Message("缺少点赞状态")
     */
    const ARTICLE_THUMPS_UP_STATUS_ERROR = 531026;

    /**
     * @Message("没有阅读权限")
     */
    const ARTICLE_READ_LIMIT = 531027;

    /**
     * @Message("缺少分享者id")
     */
    const ARTICLE_SHARE_MEMBER_ID_EMPTY = 531028;

    /**
     * @Message("奖励类型错误")
     */
    const ARTICLE_SEND_REWARD_TYPE_ERROR = 531029;

    /**
     * @Message("参数错误")
     */
    const SAVE_PARAMS_ERROR = 531030;

    /**
     * @Message("奖励不能自己发给自己")
     */
    const ARTICLE_REWARD_SEND_ERROR_SAME_MEMBER = 531031;

    /**
     * @Message("奖励规则错误")
     */
    const ARTICLE_SEND_REWARD_RULE_ERROR = 531032;

    /**
     * @Message("被奖励人信息获取失败")
     */
    const ARTICLE_SEND_REWARD_TO_MEMBER_ERROR = 531033;

    /**
     * @Message("奖励人重复触发奖励")
     */
    const ARTICLE_SEND_REWARD_FROM_MEMBER_REPEAT = 531034;

    /**
     * @Message("奖励到达上限")
     */
    const ARTICLE_SEND_REWARD_NUMBER_LIMIT = 531035;

    /**
     * @Message("奖励发放记录失败")
     */
    const ARTICLE_REWARD_LOG_SAVE_ERROR = 531036;

    /**
     * @Message("获取文章信息错误")
     */
    const SAVE_SELL_DATA_ARTICLE_ERROR = 531037;

    /**
     * @Message("奖励发放数量错误")
     */
    const ARTICLE_SEND_REWARD_NUMBER_ERROR = 531038;

    /**
     * @Message("您的点赞过于频繁，请稍后再试")
     */
    const ARTICLE_THUMPS_UP_LIMIT = 531039;

    /**
     * @Message("积分达到系统设置的可获取上限")
     */
    const ARTICLE_SEND_REWARD_SHOP_NUMBER_LIMIT = 531040;

    /**
     * @Message("分享次数增加失败")
     */
    const ARTICLE_SAVE_INCREASE_SHARE_ERROR = 531041;

    /**
     * @Message("获取配置信息失败")
     */
    const ARTICLE_SEND_REWARD_GET_SETTINGS_ERROR = 531042;

    /**
     * @Message("发放奖励时间限制")
     */
    const ARTICLE_SEND_REWARD_TIME_LIMIT_ERROR = 531043;
    /**
     * @Message("奖励规则错误")
     */
    const ARTICLE_SEND_REWARD_RULE_EMPTY = 531044;

    /**
     * @Message("当前不在活动签到时间")
     */
    const ARTICLE_SIGN_TIME_ERROR = 531045;

    /**
     * @Message("请先报名活动")
     */
    const ARTICLE_PLACE_JOIN_ACTIVITY_ERROR = 531046;

    /**
     * @Message("请勿重复报名")
     */
    const ARTICLE_REPEAT_JOIN_ACTIVITY_ERROR = 531047;

    /**
     * @Message("当前不在活动报名时间")
     */
    const ARTICLE_JOIN_ACTIVITY_TIME_ERROR = 531048;


    /**
     * @Message("请勿重复收藏")
     */
    const ARTICLE_REPEAT_FAVORITE_ACTIVITY_ERROR = 531049;
}