<?php

namespace shopstar\exceptions\article;

use shopstar\bases\exception\BaseException;

/**
 * 文章分组异常类
 * Class ArticleGroupException
 * @package shopstar\exceptions\article
 * @author yuning
 */
class ArticleGroupException extends BaseException
{
    /**
     * @Message("未查询到对应分组信息")
     */
    const FIND_ERROR = 532001;

    /**
     * @Message("分组状态错误")
     */
    const STATUS_ERROR_HIDE = 532002;

    /**
     * @Message("数据错误")
     */
    const SAVE_PARAMS_ERROR = 532003;

    /**
     * @Message("分组名称已存在")
     */
    const SAVE_PARAMS_NAME_EXIST = 532004;

    /**
     * @Message("分组保存失败")
     */
    const SAVE_ERROR = 532005;

    /**
     * @Message("删除分组失败")
     */
    const DELETE_ERROR = 532006;

    /**
     * @Message("分组保存失败")
     */
    const SAVE_UPDATE_ARTICLE_ERROR = 532007;

    /**
     * @Message("分组保存失败")
     */
    const SAVE_UPDATE_ARTICLE_RESET_GROUP_ERROR = 532008;


}