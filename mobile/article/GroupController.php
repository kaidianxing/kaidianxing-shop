<?php

namespace shopstar\mobile\article;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\services\article\ArticleGroupService;

/**
 * 分组控制器
 * Class GroupController
 * @package shopstar\mobile\article
 * @author yuning
 */
class GroupController extends BaseMobileApiController
{
    public array $allowNotLoginActions = [
        'list'
    ];

    /**
     * 获取分组列表
     * @author yuning
     */
    public function actionList()
    {
        $ArticleGroupService = new ArticleGroupService();
        $data = $ArticleGroupService->getList();
        return $this->result(['data' => $data]);
    }
}