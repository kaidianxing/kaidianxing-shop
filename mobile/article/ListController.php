<?php

namespace shopstar\mobile\article;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\article\ArticleConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\article\ArticleThumpsUpModel;
use shopstar\services\article\ArticleService;

/**
 * 列表控制器
 * Class ListController
 * @package shopstar\mobile\article
 * @author yuning
 */
class ListController extends BaseMobileApiController
{

    public $configActions = [
        'allowNotLoginActions' => [
          'list'
        ],
    ];

    /**
     * 获取文章列表
     * @author yuning
     */
    public function actionList()
    {
        $params = RequestHelper::get();
        $ArticleService = new ArticleService( 0, $this->memberId);
        $list = $ArticleService->getList($params, true, false, $this->clientType, 0, $this->memberId);

        // 已登录获取具体点赞状态
        if ($this->memberId) {
            $this->getArticleThumpsUpStatus($list);
        }

        return $this->result($list);
    }

    /**
     * 获取具体点赞状态
     * @param array $list 文章列表
     * @return void
     * @author yuning
     */
    private function getArticleThumpsUpStatus(array &$list = []): void
    {
        $articleIds = array_column($list['list'], 'id');
        if (empty($articleIds)) {
            return;
        }
        // 只查询点赞的数据
        $where = [
            'member_id' => $this->memberId,
            'article_id' => $articleIds,
            'status' => ArticleConstant::ARTICLE_COMMON_OPEN,
        ];

        $thumpsData = ArticleThumpsUpModel::find()
            ->select('article_id,status')
            ->where($where)
            ->get();

        if ($thumpsData) {
            $thumpsData = array_column($thumpsData, null, 'article_id');
            // 处理数据
            foreach ($list['list'] as $index => $article) {
                if (!isset($thumpsData[$article['id']])) {
                    continue;
                }
                $list['list'][$index]['member_thump_up_status'] = $thumpsData[$article['id']]['status'];
            }
        }
    }
}