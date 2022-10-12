<?php

namespace shopstar\exceptions\article;

use shopstar\bases\exception\BaseException;

/**
 * 设置异常类
 * Class ArticleSettingsException
 * @package shopstar\exceptions\article
 * @author yuning
 */
class ArticleSettingsException extends BaseException
{
    /**
     * @Message("页面标题必填")
     */
    const SETTINGS_PARAMS_TITLE_EMPTY = 533001;

    /**
     * @Message("奖励时间必填")
     */
    const SETTINGS_PARAMS_REWARD_TIME_LIMIT_EMPTY = 533002;

    /**
     * @Message("奖励时间区间错误")
     */
    const SETTINGS_PARAMS_REWARD_TIME_LIMIT_ERROR = 533003;

    /**
     * @Message("图片数量错误")
     */
    const SETTINGS_PARAMS_BANNER_NUM_ERROR = 533004;
}