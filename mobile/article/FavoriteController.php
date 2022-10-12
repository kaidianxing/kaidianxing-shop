<?php

namespace shopstar\mobile\article;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\article\ArticleException;
use shopstar\helpers\RequestHelper;
use shopstar\models\article\ArticleFavoriteModel;
use shopstar\models\article\ArticleModel;
use yii\web\Response;

/**
 * 收藏控制器
 * Class FavoriteController
 * @package shopstar\mobile\article
 * @author yuning
 */
class FavoriteController extends BaseMobileApiController
{

    /**
     * 收藏列表
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionList()
    {
        $params = [
            'select' => [
                'favorite.*',
                'a.title',
                'a.cover',
                'a.digest',
                'a.top_thumb',
            ],
            'alias' => 'favorite',
            'where' => ['favorite.member_id' => $this->memberId, 'a.status' => 1],
            'leftJoin' => [ArticleModel::tableName() . ' a', "a.id=favorite.article_id "],
            'orderBy' => ['favorite.id' => SORT_DESC]
        ];

        $list = ArticleFavoriteModel::getColl($params, []);
        return $this->result($list);
    }


    /**
     * 添加收藏
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionAdd()
    {
        $articleId = RequestHelper::postInt('article_id');
        if (!$articleId) {
            return $this->error('参数错误');
        }
        $result = ArticleFavoriteModel::easyAdd([
            'attributes' => [
                'member_id' => $this->memberId,
            ],
            'beforeSave' => function ($result) {
                $exist = ArticleFavoriteModel::findOne(['member_id' => $result->member_id, 'article_id' => $result->article_id]);
                if (!empty($exist)) {
                    throw new ArticleException(ArticleException::ARTICLE_REPEAT_FAVORITE_ACTIVITY_ERROR);
                }
            }

        ]);

        return $this->result($result);
    }

    /**
     * 取消收藏
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionCancel()
    {
        $articleId = RequestHelper::postInt('article_id');
        if (!$articleId) {
            return $this->error('参数错误');
        }
        ArticleFavoriteModel::deleteAll(['article_id' => $articleId, 'member_id' => $this->memberId]);
        return $this->success();

    }

}