<?php

namespace shopstar\services\article;

/**
 * 文章service基类
 * Class ArticleBaseService
 * @package shopstar\services\article
 * @author yuning
 */
class ArticleBaseService
{
    /**
     * 用户id
     * @var int
     */
    protected $userId = 0;

    /**
     * 会员id
     * @var int|mixed
     */
    protected $memberId = 0;


    public function __construct($userId = 0, $memberId = 0)
    {
        $this->userId = $userId ?? 0;
        $this->memberId = $memberId ?? 0;
    }
}