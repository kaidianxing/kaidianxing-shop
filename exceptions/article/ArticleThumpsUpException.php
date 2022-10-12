<?php

namespace shopstar\exceptions\article;

use shopstar\bases\exception\BaseException;

/**
 * 点赞异常类
 * Class ArticleThumpsUpException
 * @package shopstar\exceptions\article
 * @author yuning
 */
class ArticleThumpsUpException extends BaseException
{
    /**
     * @Message("点赞信息获取失败")
     */
    const FIND_ERROR = 535001;

    /**
     * @Message("点赞状态错误")
     */
    const STATUS_ERROR = 535002;

    /**
     * @Message("点赞保存失败")
     */
    const SAVE_ERROR = 535003;
}