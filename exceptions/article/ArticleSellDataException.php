<?php

namespace shopstar\exceptions\article;

use shopstar\bases\exception\BaseException;

/**
 * 引流销售异常类
 * Class ArticleSellDataException
 * @package shopstar\exceptions\article
 * @author yuning
 */
class ArticleSellDataException extends BaseException
{
    /**
     * @Message(错误的类型")
     */
    const TYPE_ERROR = 534001;
}