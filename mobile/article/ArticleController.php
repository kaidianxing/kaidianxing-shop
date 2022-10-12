<?php

namespace shopstar\mobile\article;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\constants\article\ArticleConstant;
use shopstar\exceptions\article\ArticleException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\article\ArticleFavoriteModel;
use shopstar\models\article\ArticleModel;
use shopstar\models\article\ArticleThumpsUpModel;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\services\article\ArticleService;
use yii\base\Exception;
use yii\web\Response;

/**
 * 移动端文章
 * Class ArticleController
 * @package shopstar\mobile\article
 * @author yuning
 */
class ArticleController extends BaseMobileApiController
{
    /**
     * 获取文章详情
     * @throws ArticleException
     * @author yuning
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            throw new ArticleException(ArticleException::ARTICLE_ID_EMPTY);
        }

        $ArticleService = new ArticleService(0, $this->memberId);
        $field = [
            'id',
            'group_id',
            'title',
            'cover',
            'digest',
            'author',
            'display_order',
            'content',
            'goods_ids',
            'coupon_ids',
            'read_number_status',
            'read_number',
            'read_number_step',
            'thumps_up_status',
            'thumps_up_number',
            'share_number',
            'member_level_limit_type',
            'member_level_limit_ids',
            'commission_level_limit_type',
            'commission_level_limit_ids',
            'status',
            'is_top',
            'created_at',
            'top_thumb',
            'top_thumb_all',
            'top_thumb_type',

        ];

        // 获取详情
        $article = $ArticleService->getDetail($id, $field, true, 0, $this->clientType, $this->memberId);

        // 阅读权限
        if ($article['member_level_limit_type'] || $article['commission_level_limit_type']) {
            $this->checkReadAuth($article['member_level_limit_type'], $article['member_level_limit_ids'], $this->member['level_id'], $article['commission_level_limit_type'], $article['commission_level_limit_ids']);
        }

        // 点赞
        $existThumpUp = ArticleThumpsUpModel::getModel($article['id'], $this->memberId);
        $article['member_thump_up_status'] = $existThumpUp && $existThumpUp->status ? 1 : 0;

        // 增加阅读数
        $article['read_number'] += $this->increaseRead($id, $article['read_number_step']);

        // 海报url
        $article['poster_url'] = ShopUrlHelper::wap('/pagesArticle/detail/index', [
            'inviter_id' => $this->memberId,
            'id' => $id,
            'f' => 1,
        ], true);


        // 查询收藏数
        $article['favorite_count'] = ArticleFavoriteModel::find()->where([
            'article_id' => $article['id'],
        ])->count();

        // 查询当前登录的人 是否收藏
        $article['is_favorite'] = ArticleFavoriteModel::findOne(['article_id' => $article['id'], 'member_id' => $this->memberId]) ? 1 : 0;

        // 删除多于字段
        unset($article['read_number_step']);

        return $this->result(['data' => $article]);
    }

    /**
     * 增加阅读数
     * @param int $articleId
     * @param int $readNumberStep
     * @return int 增加的数量
     * @author yuning
     */
    private function increaseRead(int $articleId = 0, int $readNumberStep = 0): int
    {
        $num = 1;
        if ($readNumberStep > 1) {
            $num = mt_rand(1, $readNumberStep);
        }
        ArticleModel::updateAllCounters(['read_number' => $num, 'read_number_real' => 1], ['id' => $articleId]);
        return $num;
    }

    /**
     * 检测阅读权限
     * @param int $memberLimitType 会员等级限制类型 0 不限制 1 限制
     * @param array $memberLevelIds 允许的会员等级
     * @param int $levelId 当前会员等级
     * @param int $commissionLimitType 分销商等级限制类型 0 不限制 1 限制
     * @param array $commissionLevelIds 允许的分销商等级
     * @return void
     * @throws ArticleException
     * @author yuning
     */
    private function checkReadAuth(int $memberLimitType = 0, array $memberLevelIds = [], int $levelId = 0, int $commissionLimitType = 0, array $commissionLevelIds = []): void
    {
        // 阅读权限
        $readAuth = 0;
        // 会员等级
        if ($memberLimitType == 1 && !empty($memberLevelIds) && in_array($levelId, $memberLevelIds)) {
            $readAuth++;
        }

        // 会员标签 取交集
        if ($memberLimitType == 2 && !empty($memberLevelIds)) {
            $memberGroup = MemberGroupModel::find()
                ->alias('m')
                ->select([
                    'm.id'
                ])
                ->leftJoin(MemberGroupMapModel::tableName() . ' gm', 'gm.group_id=m.id')
                ->where(['gm.member_id' => $this->memberId])
                ->get();

            $groupIds = array_column($memberGroup, 'id');

            if (array_intersect($groupIds, $memberLevelIds)) {
                $readAuth++;
            }
        }


        // 分销等级

        // 查询当前用户分销等级
        $commission = CommissionAgentModel::find()
            ->where(['member_id' => $this->memberId, 'status' => 1, 'is_black' => 0])
            ->select('level_id')
            ->asArray()
            ->first();
        // 是分销商
        // 验证分销商等级限制
        if ($commission && in_array($commission['level_id'], $commissionLevelIds)) {
            $readAuth++;
        }
        if ($readAuth <= 0) {
            throw new ArticleException(ArticleException::ARTICLE_READ_LIMIT);
        }

    }

    /**
     * 增加分享次数
     * @return array|int[]|Response
     * @throws ArticleException
     * @author yuning
     */
    public function actionIncreaseShare()
    {
        $id = RequestHelper::postInt('id');
        if (!$id) {
            throw new ArticleException(ArticleException::ARTICLE_ID_EMPTY);
        }

        $where = [
            'id' => $id,
            'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
        ];
        $res = ArticleModel::updateAllCounters(['share_number' => 1, 'share_number_real' => 1], $where);
        if (!$res) {
            throw new ArticleException(ArticleException::ARTICLE_SAVE_INCREASE_SHARE_ERROR);
        }

        return $this->success();
    }

    /**
     * 点赞
     * @throws ArticleException
     * @author yuning
     */
    public function actionThumpsUp()
    {
        $id = RequestHelper::postInt('id');
        if (!$id) {
            throw new ArticleException(ArticleException::ARTICLE_ID_EMPTY);
        }

        $status = RequestHelper::post('status');

        $ArticleService = new ArticleService(0, $this->memberId);
        $res = $ArticleService->thumpsUp($id, $status);
        if (is_error($res)) {
            return $this->result($res);
        }
        return $this->success();
    }

    /**
     * 发放奖励
     * @throws ArticleException
     * @author yuning
     */
    public function actionSendReward()
    {
        $id = RequestHelper::postInt('id');
        if (!$id) {
            throw new ArticleException(ArticleException::ARTICLE_ID_EMPTY);
        }

        $shareMemberId = RequestHelper::postInt('article_share_member_id');
        if (!$shareMemberId) {
            throw new ArticleException(ArticleException::ARTICLE_SHARE_MEMBER_ID_EMPTY);
        }

        $ArticleService = new ArticleService(0, $this->memberId);
        $res = $ArticleService->sendReward($id, $shareMemberId);
        if (is_error($res)) {
            return $this->result($res);
        }
        return $this->success();
    }

    /**
     * 获取小程序二维码
     * @return array|int[]|Response
     * @throws ArticleException
     * @throws Exception
     * @author yuning
     */
    public function actionGetWxappQrcode()
    {
        $articleId = RequestHelper::getInt('id');
        if (!$articleId) {
            throw new ArticleException(ArticleException::ARTICLE_ID_EMPTY);
        }

        $article = ArticleModel::getModel($articleId, 'id');

        //文件名
        $fileName = md5($articleId . '_' . $this->memberId) . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $fileName, [], true);
        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'inviter_id' => $this->memberId,
                'id' => $article->id,
                'f' => 1,
            ]), [
                'page' => 'pagesArticle/detail/index',
                'directory' => $savePatchDir,
                'fileName' => $fileName
            ]);

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        return $this->success(['patch' => $accessPatch]);
    }

}