<?php

namespace shopstar\constants\article;

use shopstar\bases\constant\BaseConstant;

/**
 * 文章分组常量
 * Class ArticleGroupConstant
 * @package shopstar\constants\article
 * @author yuning
 */
class ArticleGroupConstant extends BaseConstant
{
    /**
     * @Text("编辑")
     */
    const GROUP_SCENE_EDIT = 1;

    /**
     * @Text("删除")
     */
    const GROUP_SCENE_DELETE = 2;

    /**
     * @Text("隐藏")
     */
    const GROUP_STATUS_HIDE = 0;

    /**
     * @Text("显示")
     */
    const GROUP_STATUS_SHOW = 1;
}