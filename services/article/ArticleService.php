<?php

namespace shopstar\services\article;

use shopstar\components\notice\NoticeComponent;
use shopstar\constants\article\ArticleConstant;
use shopstar\constants\article\ArticleLogConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\goods\GoodsDeleteConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\article\ArticleException;
use shopstar\exceptions\article\ArticleGroupException;
use shopstar\exceptions\article\ArticleThumpsUpException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\article\ArticleGroupModel;
use shopstar\models\article\ArticleModel;
use shopstar\models\article\ArticleRewardLogModel;
use shopstar\models\article\ArticleThumpsUpModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\user\UserModel;
use shopstar\services\goods\GoodsActivityService;
use shopstar\services\goods\GoodsListActivityHandler;
use shopstar\services\goods\GoodsService;
use shopstar\services\sale\CouponMemberService;
use shopstar\services\sale\CouponService;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * 文章服务类
 * Class ArticleService
 * @package shopstar\services\article
 * @author yuning
 */
class ArticleService extends ArticleBaseService
{

    /**
     * 日志logPrimary
     * @var array
     */
    private array $logPrimary = [];
    /**
     * 商品ids
     * @var array
     */
    private array $goodsIds = [];
    /**
     * 优惠券ids
     * @var array
     */
    private array $couponIds = [];

    /**
     * 客户端type
     * @var int
     */
    private int $clientType = 0;
    /**
     * 店铺类型
     * @var int
     */
    private int $shopType = 0;


    /**
     * 保存时验证
     * @param array $data
     * @return bool
     * @throws ArticleException
     * @throws ArticleGroupException
     * @author yuning
     */
    public function saveValidate(array $data = []): bool
    {
        // 必填项
        if (empty($data['title']) || empty($data['cover']) || empty($data['group_id']) || empty($data['content']) || empty($data['content_origin'])) {
            throw new ArticleException(ArticleException::SAVE_PARAMS_EMPTY);
        }

        // 单选
        if (
            (!isset($data['status']) && !in_array($data['status'], [ArticleConstant::ARTICLE_STATUS_HIDE, ArticleConstant::ARTICLE_STATUS_SHOW])) ||
            (!isset($data['read_number_status']) && !in_array($data['read_number_status'], [ArticleConstant::ARTICLE_COMMON_CLOSE, ArticleConstant::ARTICLE_COMMON_OPEN])) ||
            (!isset($data['thumps_up_status']) && !in_array($data['thumps_up_status'], [ArticleConstant::ARTICLE_COMMON_CLOSE, ArticleConstant::ARTICLE_COMMON_OPEN])) ||
            (!isset($data['member_level_limit_type']) && !in_array($data['member_level_limit_type'], [ArticleConstant::ARTICLE_COMMON_CLOSE, ArticleConstant::ARTICLE_COMMON_OPEN])) ||
            (!isset($data['commission_level_limit_type']) && !in_array($data['commission_level_limit_type'], [ArticleConstant::ARTICLE_COMMON_CLOSE, ArticleConstant::ARTICLE_COMMON_OPEN])) ||
            (!isset($data['reward_type']) && !in_array($data['reward_type'], [ArticleConstant::ARTICLE_REWARD_TYPE_NONE, ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT, ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE]))
        ) {
            throw new ArticleException(ArticleException::SAVE_PARAMS_ERROR);
        }

        // 排序
        if ($data['display_order'] > 9999 || $data['display_order'] < 0) {
            throw new ArticleException(ArticleException::SAVE_PARAMS_DISPLAY_ORDER_ERROR);
        }

        // 商品和优惠券校验
        $this->saveValidateExtData($data);

        // 标题去重
        ArticleModel::saveCheckTitleExist($data['article_id'] ?? 0, $data['title']);

        // 分组检测
        $this->saveCheckGroup($data['group_id']);

        // 权限检测
        $this->saveValidateReadPrem($data);

        // 奖励检测
        $this->saveValidateReward($data);

        return true;
    }

    /**
     * 保存时奖励检测
     * @param array $data
     * @return void
     * @throws ArticleException
     * @author yuning
     */
    private function saveValidateReward(array $data = []): void
    {
        if ($data['reward_type'] != 0) {
            $this->logPrimary['reward'] = [];

            $rewardRule = Json::decode($data['reward_rule']) ?? [];
            if (empty($rewardRule)) {
                throw new ArticleException(ArticleException::SAVE_PARAMS_REWARD_RULE_EMPTY);
            }
            // 积分验证
            if ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT) {
                if (!isset($rewardRule['credit'])) {
                    throw new ArticleException(ArticleException::SAVE_PARAMS_REWARD_RULE_CREDIT_EMPTY);
                }
                if (!isset($rewardRule['credit']['once'])
                    || $rewardRule['credit']['once'] <= 0
                    || !isset($rewardRule['credit']['max'])
                    || $rewardRule['credit']['max'] <= 0
                    || $rewardRule['credit']['max'] < $rewardRule['credit']['once']
                ) {
                    throw new ArticleException(ArticleException::SAVE_PARAMS_REWARD_RULE_CREDIT_ERROR);
                }

                // log
                $this->logPrimary['reward']['type'] = '积分';
                $this->logPrimary['reward']['once'] = $rewardRule['credit']['once'];
                $this->logPrimary['reward']['max'] = $rewardRule['credit']['max'];
            }

            // 余额验证
            if ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE) {
                if (!isset($rewardRule['balance'])) {
                    throw new ArticleException(ArticleException::SAVE_PARAMS_REWARD_RULE_BALANCE_EMPTY);
                }
                if (!isset($rewardRule['balance']['once'])
                    || $rewardRule['balance']['once'] <= 0
                    || !isset($rewardRule['balance']['max'])
                    || $rewardRule['balance']['max'] <= 0
                    || $rewardRule['balance']['max'] < $rewardRule['balance']['once']
                ) {
                    throw new ArticleException(ArticleException::SAVE_PARAMS_REWARD_RULE_BALANCE_ERROR);
                }

                // log
                $this->logPrimary['reward']['type'] = '余额';
                $this->logPrimary['reward']['once'] = $rewardRule['balance']['once'];
                $this->logPrimary['reward']['max'] = $rewardRule['balance']['max'];
            }
        }
    }

    /**
     * 保存时阅读权限检测
     * @param array $data
     * @return bool
     * @throws ArticleException
     * @author yuning
     */
    public function saveValidateReadPrem(array $data = []): bool
    {
        $this->logPrimary['member_limit'] = [];
        $this->logPrimary['member_limit']['status'] = '否';
        $this->logPrimary['commission_limit'] = [];
        $this->logPrimary['commission_limit']['status'] = '否';

        // 会员等级
        if ($data['member_level_limit_type'] == ArticleConstant::ARTICLE_COMMON_OPEN) {
            if (empty($data['member_level_limit_ids'])) {
                throw new ArticleException(ArticleException::SAVE_PARAMS_MEMBER_LEVEL_EMPTY);
            }
            // 获取所有等级
            $levels = MemberLevelModel::getAllLevel();
            if (!$levels) {
                throw new ArticleException(ArticleException::SAVE_PARAMS_MEMBER_LEVEL_SHOP_OPEN_EMPTY);
            }
            $levelsIds = array_column($levels, 'id');

            // 查看是否存在level_id
            $levelIdsArray = Json::decode($data['member_level_limit_ids']);
            foreach ($levelIdsArray as $level) {
                if (!in_array($level, $levelsIds)) {
                    throw new ArticleException(ArticleException::SAVE_PARAMS_MEMBER_LEVEL_ERROR);
                }
            }

            $this->logPrimary['member_limit']['status'] = '是';
            $this->logPrimary['member_limit']['level'] = implode(',', array_column($levels, 'level_name'));

        }

        // 分销商等级
        if (empty($data['commission_level_limit_ids'])) {
            throw new ArticleException(ArticleException::SAVE_PARAMS_COMMISSION_LEVEL_EMPTY);
        }

        // 获取所有等级
        $levels = CommissionLevelModel::getSimpleList();
        if (!$levels) {
            throw new ArticleException(ArticleException::SAVE_PARAMS_COMMISSION_LEVEL_SHOP_OPEN_EMPTY);
        }
        $levelsIds = array_column($levels, 'id');

        // 查看是否存在level_id
        $levelIdsArray = Json::decode($data['commission_level_limit_ids']);
        foreach ($levelIdsArray as $level) {
            if (!in_array($level, $levelsIds)) {
                throw new ArticleException(ArticleException::SAVE_PARAMS_COMMISSION_LEVEL_ERROR);
            }
        }
        $this->logPrimary['commission_limit']['status'] = '是';
        $this->logPrimary['commission_limit']['level'] = implode(',', array_column($levels, 'name'));

        return true;
    }


    /**
     * 保存数据格式化
     * @param array $data
     * @return array
     * @author yuning
     */
    public function saveFormat(array $data = []): array
    {
        // 初始化值
        // 会员等级限制和分销等级限制
        if ($data['member_level_limit_type'] == ArticleConstant::ARTICLE_COMMON_CLOSE) {
            $data['member_level_limit_ids'] = '';
        }
        if ($data['commission_level_limit_type'] == ArticleConstant::ARTICLE_COMMON_CLOSE) {
            $data['commission_level_limit_ids'] = '';
        }
        if ($data['download_limit_type'] == ArticleConstant::ARTICLE_DOWNLOAD_CLOSE) {
            $data['download_limit_ids'] = '';
        }
        if ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_NONE) {
            $data['reward_rule'] = '{"credit":{},"balance":{},"coupon":{}}';
        }

        // 奖励规则 初始化
        $rewardKey = null;
        if ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT) {
            $rewardKey = 'balance';
        } elseif ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE) {
            $rewardKey = 'credit';
        } elseif ($data['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_COUPON) {
            $rewardKey = 'coupon';
        }

        if ($rewardKey) {
            $rule = Json::decode($data['reward_rule']);

            if ($data['reward_type'] != ArticleConstant::ARTICLE_REWARD_TYPE_COUPON) {
                $rule[$rewardKey] = [
                    'once' => 0,
                    'max' => 0,
                ];
            }
            $data['reward_rule'] = Json::encode($rule);
        }

        // goods_ids 和 coupon_ids转为字符串
        if (!empty($data['goods_ids'])) {
            $data['goods_ids'] = implode(',', Json::decode($data['goods_ids']));
        }
        if (!empty($data['coupon_ids'])) {
            $data['coupon_ids'] = implode(',', Json::decode($data['coupon_ids']));
        }

        // level_ids
        if (!empty($data['member_level_limit_ids'])) {
            $data['member_level_limit_ids'] = implode(',', Json::decode($data['member_level_limit_ids']));
        }
        if (!empty($data['commission_level_limit_ids'])) {
            $data['commission_level_limit_ids'] = implode(',', Json::decode($data['commission_level_limit_ids']));
        }

        return $data;
    }

    /**
     * 保存文章
     * @param array $data
     * @return bool
     * @throws ArticleException
     * @throws ArticleGroupException
     * @author yuning
     */
    public function save(array $data = []): bool
    {

        // 验证
        $this->saveValidate($data);
        // 格式化
        $data = $this->saveFormat($data);

        // 获取model
        if (!isset($data['article_id']) || empty($data['article_id'])) {
            $ArticleModel = new ArticleModel();
        } else {
            $ArticleModel = ArticleModel::findOne($data['article_id']);
            if (!$ArticleModel || $ArticleModel->is_deleted) {
                throw new ArticleException(ArticleException::SAVE_GET_ARTICLE_ERROR);
            }
        }

        // 处理阅读数与点赞数
        if ($data['read_number_init'] != $ArticleModel->read_number_init) {
            $data['read_number'] = $data['read_number_init'] + $ArticleModel->read_number_real;
        }
        if ($data['thumps_up_number_init'] != $ArticleModel->thumps_up_number_init) {
            $data['thumps_up_number'] = $data['thumps_up_number_init'] + $ArticleModel->thumps_up_number_real;
        }

        // 保存
        $ArticleModel->setAttributes($data);
        //如果保存失败 则抛出异常
        if (!$ArticleModel->save()) {
            throw new ArticleException(ArticleException::SAVE_FAIL);
        }


        // 生成商品和优惠券日志数据
        if (!empty($data['goods_ids'])) {
            $where = [
                'id' => explode(',', $data['goods_ids']),
            ];
            $goods = GoodsModel::find()->where($where)->select('id,title')->get();
            if (!empty($goods)) {
                $this->logPrimary['goods'] = $goods;
            }
        }

        if (!empty($data['coupon_ids'])) {
            $where = [
                'id' => explode(',', $data['coupon_ids']),
            ];
            $coupons = CouponModel::find()->where($where)->select('id,coupon_name as name')->get();
            if (!empty($coupons)) {
                $this->logPrimary['coupons'] = $coupons;
            }
        }


        // 日志
        $logPrimary = [
            'id' => $ArticleModel->id,
            'title' => $ArticleModel->title,
            'cover' => $ArticleModel->cover,
            'digest' => $ArticleModel->digest,
            'author' => $ArticleModel->author,
            'status' => $ArticleModel->status == 1 ? '显示' : '隐藏',
        ];
        $this->logPrimary = array_merge($logPrimary, $this->logPrimary);

        // 记录日志
        $logConst = $data['article_id'] && $data['article_id'] > 0 ? ArticleLogConstant::ARTICLE_EDIT : ArticleLogConstant::ARTICLE_ADD;
        LogModel::write(
            $this->userId,
            $logConst,
            ArticleLogConstant::getText($logConst),
            $ArticleModel->id,
            [
                'log_data' => $ArticleModel->attributes,
                'log_primary' => $ArticleModel->getLogAttributeRemark($this->logPrimary),
                'dirty_identify_code' => [
                    ArticleLogConstant::ARTICLE_EDIT,
                    ArticleLogConstant::ARTICLE_ADD,
                ]
            ]
        );

        return true;

    }


    /**
     * 获取文章信息
     * @param int $articleId
     * @param array $field 字段
     * @param bool $isMobile 移动端请求, 需要获取更详细的信息
     * @param int $shopType 店铺类型
     * @param int $clientType 客户端
     * @param int $memberId
     * @return array
     * @throws ArticleException
     * @author yuning
     */
    public function getDetail(int $articleId = 0, array $field = [], bool $isMobile = false, int $shopType = 0, int $clientType = 0, int $memberId = 0): array
    {
        $this->clientType = $clientType ?? $this->clientType;
        $this->shopType = $shopType ?? $this->shopType;
        // 获取文章
        $where = [
            'id' => $articleId,
            'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE
        ];

        $article = ArticleModel::find()
            ->select($field ?: ArticleModel::$articleField)
            ->where($where)
            ->first();

        if (!$article) {
            throw new ArticleException(ArticleException::ARTICLE_GET_ERROR);
        }

        // 格式化
        if (isset($article['goods_ids'])) {
            $article['goods_ids'] = !empty($article['goods_ids']) ? explode(',', $article['goods_ids']) : [];
        }
        if (isset($article['coupon_ids'])) {
            $article['coupon_ids'] = !empty($article['coupon_ids']) ? explode(',', $article['coupon_ids']) : [];
        }
        if (isset($article['member_level_limit_ids'])) {
            $article['member_level_limit_ids'] = !empty($article['member_level_limit_ids']) ? explode(',', $article['member_level_limit_ids']) : [];
        }

        if (isset($article['download_limit_ids'])) {
            $article['download_limit_ids'] = !empty($article['download_limit_ids']) ? explode(',', $article['download_limit_ids']) : [];
        }
        if (isset($article['commission_level_limit_ids'])) {
            $article['commission_level_limit_ids'] = !empty($article['commission_level_limit_ids']) ? explode(',', $article['commission_level_limit_ids']) : [];
        }
        if (isset($article['reward_rule'])) {
            $article['reward_rule'] = Json::decode($article['reward_rule']);
        }

        // 手机端额外处理
        if ($isMobile) {
            $article['goods'] = $article['coupons'] = [];

            // 商品
            if (!empty($article['goods_ids'])) {
                $this->goodsIds = $article['goods_ids'];
                $article = current($this->processClientArticleGoods([$article], ['memberId' => $memberId, 'shopType' => $shopType]));
            }

            // 优惠券
            if (!empty($article['coupon_ids'])) {
                $this->couponIds = $article['coupon_ids'];
                $article = $this->processClientArticleCoupons($article);
            }

        }

        // 处理视频
        if (!empty($article['content'])) {
            $articleContent = Json::decode($article['content']);
            foreach ($articleContent as $k => $item) {
                if ($item['type'] !== 'text') {
                    continue;
                }
                $articleContent[$k]['value'] = VideoHelper::parseRichTextTententVideo($item['value']);
            }
            $article['content'] = Json::encode($articleContent);
        }

        if (!empty($article['content_origin'])) {
            $article['content_origin'] = VideoHelper::parseRichTextTententVideo($article['content_origin']);
        }

        return $article;
    }

    /**
     * 文章列表
     * @param array $param
     * @param bool $isMobile 是否是移动端调用
     * @param bool $getPublisher 获取发布人
     * @param int $clientType 客户端类型
     * @param int $shopType 商城类型
     * @param int $memberId
     * @return array
     * @author yuning
     */
    public function getList(array $param = [], bool $isMobile = false, bool $getPublisher = true, int $clientType = 0, int $shopType = 0, int $memberId = 0): array
    {
        $param = array_merge([
            'start_time' => '',
            'end_time' => '',
        ], $param);

        $this->clientType = $clientType ?? $this->clientType;
        $this->shopType = $shopType ?? $this->shopType;

        // 查询字段
        $select = ArticleModel::$ListField;

        // 连表
        $leftJoins = [
            [ArticleGroupModel::tableName() . ' group', 'group.id = article.group_id'],
        ];

        $andWhere = [];
        // 创建时间搜索
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $andWhere[] = ['between', 'article.create_time', $param['start_time'], $param['end_time']];
        }

        $orderBy = [
            'article.is_top' => SORT_DESC,
            'article.display_order' => SORT_DESC,
            'article.id' => SORT_DESC,
        ];

        if ($ids = RequestHelper::get('id')) {
            $idsArray = ArrayHelper::explode(',', $ids) ?: [];
            //重置排序
            $orderBy = [
                new \yii\db\Expression('FIELD (article.id,' . $ids . ')'),
                'article.id' => SORT_DESC,
            ];
            $andWhere[] = ['article.id' => $idsArray];
        }

        // 如果前端传排序 再重制排序
        $field = RequestHelper::get('field');
        $sort = RequestHelper::get('sort', SORT_DESC);
        if ($field) {
            $orderBy = [
                $field => $sort,
            ];
        }

        // 支持传数组
        $groupId = RequestHelper::get('group_id');

        // 兼容字符串数据类型
        if (is_string($groupId)) {
            $groupId = ArrayHelper::explode(',', $groupId) ?? [];
        }

        if ($groupId) {
            $andWhere[] = ['article.group_id' => $groupId];
        }

        // 搜索
        $searchs = [
            ['article.title', 'like', 'title'],
            ['article.status', 'int', 'status'],
            ['article.type', 'int', 'type'],
        ];

        $where = [
            'article.is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
        ];


        $params = [
            'searchs' => $searchs,
            'select' => $select,
            'alias' => 'article',
            'where' => $where,
            'andWhere' => $andWhere,
            'leftJoins' => $leftJoins,
            'orderBy' => $orderBy,
        ];

        // 获取列表信息
        $list = ArticleModel::getColl($params, [
            'callable' => function (&$row) use ($isMobile, $getPublisher) {
                // 处理分组名称
                if ($row['group_id'] == 0) {
                    $row['group_name'] = '全部';
                }

                if ($getPublisher) {
                    // 系统操作员名称初始化
                    $row['publisher'] = '';
                }

                // 移动端额外处理
                if ($isMobile) {
                    // 储存存在的商品ids
                    $row['goods'] = [];

                    // 获取商品ids
                    if (!empty($row['goods_ids'])) {
                        $row['goods_ids'] = explode(',', $row['goods_ids']);
                        $this->goodsIds = array_merge_recursive($this->goodsIds, $row['goods_ids']);
                    }

                    // 删除多于数据
                    unset($row['read_number_real'], $row['thumps_up_number_real'], $row['share_number_real']);

                    // 点赞状态
                    $row['member_thump_up_status'] = 0;

                }

            }
        ]);

        // 额外处理
        if (!empty($list['list'])) {
            // 创建人
            if ($getPublisher) {
                $this->getArticlePublisher($list);
            }

            // 客户端接口调用
            if ($isMobile) {
                // 去重
                $this->goodsIds = !empty($this->goodsIds) ? array_values(array_unique($this->goodsIds)) : [];
                // 获取文章下的商品信息
                if (!empty($this->goodsIds)) {
                    $list['list'] = $this->processClientArticleGoods($list['list'], ['shopType' => $this->shopType, 'memberId' => $memberId]);
                }
            }
        }

        return $list;
    }

    /**
     * 处理客户端的文章商品信息
     * @param array $list
     * @param array $options
     * @return array
     * @author yuning
     */
    private function processClientArticleGoods(array $list = [], array $options = []): array
    {
        $options = array_merge([
            'memberId' => 0,
            'shopType' => 0,
        ], $options);

        // 获取商品数据
        if (!empty($this->goodsIds)) {
            $params = $this->getArticleGoodsParams();
            $goodsList = GoodsModel::getColl($params, [
                'callable' => function (&$row) use ($options) {
                    // 活动类型初始化
                    $row['activity_type'] = '';
                    // 活动预热初始化
                    $row['has_activity'] = 0;
                    $row['has_preheat_activity'] = 0;

                    // 扩展字段
                    $row['ext_field'] = $row['ext_field'] ? Json::decode($row['ext_field']) : [];

                    // 价格面议
                    $row['buy_button_status'] = GoodsService::getBuyButtonStatus($row['ext_field']['buy_button_type'], $row['ext_field']['buy_button_settings']);

                    // 商品单位
                    $row['goods_unit'] = '件';

                    // 活动预热
                    $preheat_activity = GoodsActivityService::getPreheatActivity($row['id'], 0, $this->clientType, 1);
                    if (!empty($preheat_activity)) {
                        $row['has_preheat_activity'] = 1;
                    }

                    // 权限
                    $row['perm_price'] = GoodsPermMapModel::checkGoodsPerm( $row['id'], $options['memberId'], GoodsPermMapModel::PERM_PRICE);
                },
                'onlyList' => true,
                'asArray' => true,
                'pager' => false,
            ]);
            // 挂载活动
            $this->getActivity($goodsList);

            // 处理为前端所需数据
            $this->processArticleData($goodsList);

            // 填充详情数据
            if (!empty($goodsList)) {
                foreach ($list as &$item) {
                    if (empty($item['goods_ids'])) {
                        continue;
                    }
                    // 获取对应的商品数据
                    foreach ($item['goods_ids'] as $goodsId) {
                        if (!isset($goodsList[$goodsId])) {
                            continue;
                        }
                        $item['goods'][] = $goodsList[$goodsId];
                    }
                }
            }
        }


        return $list;
    }

    /**
     * 获取parma
     * @return array
     * @author yuning
     */
    private function getArticleGoodsParams(): array
    {
        $select = [
            'id',
            'title',
            'sub_name',
            'short_name',
            'thumb',
            'stock',
            'price',
            'min_price',
            'max_price',
            'cost_price',
            'original_price',
            'has_option',
            'status',
            'ext_field',
        ];
        //追加连表前缀
        array_walk($select, function (&$result) {
            $result = 'goods.' . $result;
        });

        //拼装传参
        $params = [
            'alias' => 'goods',
            'where' => [
                'goods.id' => $this->goodsIds,
                'goods.status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY],// 上架商品
                'goods.is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO,// 未删除
            ],
            'select' => $select,
            'indexBy' => 'id',
        ];


        return $params;
    }

    /**
     * 获取活动
     * @param array $goodsList 商品列表
     * @return void
     * @author yuning
     */
    private function getActivity(array &$goodsList = []): void
    {
        //初始化商品列表营销活动加载器
        $goodsListActivityHandler = GoodsListActivityHandler::init( $goodsList, $this->memberId, $this->clientType, 0);

        //执行
        $goodsListActivityHandler->automation();

        //获取活动
        $activities = $goodsListActivityHandler->getActivity('all');
        //挂载商品活动
        foreach ($activities as $goodsId => $activity) {
            if (array_filter($activity, function ($result) {
                if (!empty($result) || $result === 0) {
                    return false;
                }
                return true;
            })) continue;

            $goodsList[$goodsId]['activities'] = $activity;
        }

    }

    /**
     * 处理活动数据为前端所需数据
     * @param array $list 数据列表
     * @param string $type goods 商品  coupons 优惠券
     * @author yuning
     */
    private function processArticleData(array &$list = [], string $type = 'goods')
    {
        /**
         * 活动类型对应的key
         */
        $activeTypeKey = [
            'presell',
            'seckill',
            'groups',
            'groupsRebate',
            'fullDeduct',
        ];
        // 商品数据
        if (!empty($list) && $type === 'goods') {
            foreach ($list as $k => $item) {

                // 处理活动
                if (!empty($item['activities'])) {
                    // 循环活动
                    foreach ($item['activities'] as $activityType => $activity) {
                        if (!in_array($activityType, $activeTypeKey)) {
                            continue;
                        }
                        // 赋值互动类型
                        $list[$k]['activity_type'] = $activityType;
                        $list[$k]['has_activity'] = 1;
                        // 有活动, 强制价格面议改为0
                        $list[$k]['buy_button_status'] = 0;
                        if ($item['has_option'] == 0) {
                            if (!isset($activity['activity_price'])) {
                                continue;
                            }
                            // 单规格, 活动的activity_price 覆盖商品的price
                            $list[$k]['price'] = $list[$k]['min_price'] = $list[$k]['max_price'] = $activity['activity_price'];
                        } elseif ($item['has_option'] == 1) {
                            if (!isset($activity['price_range']['min_price']) || !isset($activity['price_range']['max_price'])) {
                                continue;
                            }
                            // 多规格, 活动的price_range['min_price'] 和 price_range['max_price'] 覆盖商品的对应价格
                            $list[$k]['price'] = $activity['price_range']['min_price'];
                            $list[$k]['min_price'] = $activity['price_range']['min_price'];
                            $list[$k]['max_price'] = $activity['price_range']['max_price'];
                        }

                    }
                }

                // 有活动预热, 强制价格面议改为0
                if ($item['has_preheat_activity']) {
                    $list[$k]['buy_button_status'] = 0;
                }

                // 删除信息
                unset($list[$k]['activities']);
            }
        }

        // 优惠券
        if (!empty($list) && $type === 'coupons') {
            // 获取用户券的状态
            $couponMember = CouponMemberModel::getColl(
                [
                    'andWhere' => [
                        ['coupon_id' => $this->couponIds],
                        ['member_id' => $this->memberId],
                    ],
                    'select' => 'id,coupon_id',
                    'groupBy' => 'id',
                ],
                [
                    'onlyList' => true,
                    'pager' => false,
                    'asArray' => true,
                ]
            );
            if ($couponMember) {
                $couponMember = array_column($couponMember, null, 'coupon_id');
                foreach ($list as $k => $item) {
                    // 领取状态
                    if (!isset($couponMember[$item['coupon_id']])) {
                        continue;
                    }
                    $list[$k]['receive_status'] = 1;
                }
            }
        }

    }

    /**
     * 获取文章的发布人
     * @param array $list
     * @return void
     * @author yuning
     */
    private function getArticlePublisher(array &$list = []): void
    {
        $articleIds = array_column($list['list'], 'id');
        if (empty($articleIds)) {
            return;
        }
        $log = $this->getArticleCreateLog( $articleIds);
        if ($log) {
            $log = array_column($log, null, 'article_id');
            // 处理数据
            foreach ($list['list'] as $index => $article) {
                if (!isset($log[$article['id']])) {
                    continue;
                }
                $list['list'][$index]['publisher'] = $log[$article['id']]['username'];
            }
        }
    }

    /**
     * 获取文章创建时的log
     * @param array $articleIds 文章ids 数组形式
     * @return array|ActiveRecord[]
     * @author yuning
     */
    public static function getArticleCreateLog( array $articleIds = []): array
    {
        // 从操作日志里, 查询创建文章时的操作员名称
        $where = [
            'log.identify_code' => ArticleLogConstant::ARTICLE_ADD,
            'log.relation_ids' => $articleIds,
        ];

        // 查找
        return LogModel::find()
            ->alias('log')
            ->leftJoin(UserModel::tableName() . ' user', 'user.id = log.uid')
            ->select('log.relation_ids as article_id,user.username')
            ->where($where)
            ->orderBy(['log.id' => SORT_ASC])
            ->get();
    }

    /**
     * 验证额外数据 商品, 优惠券
     * @param array $data
     * @return void
     * @throws ArticleException
     * @author yuning
     */
    private function saveValidateExtData(array $data = []): void
    {
        // 商品的数量验证(最多添加5个),及去重
        if (!empty($data['goods_ids'])) {
            $goodsIds = Json::decode($data['goods_ids']) ?? [];
            if ($goodsIds) {
                if (count($goodsIds) > ArticleConstant::ARTICLE_SAVE_GOODS_NUM_LIMIT) {
                    throw new ArticleException(ArticleException::ARTICLE_SAVE_GOODS_NUM_LIMIT);
                }
                if (count($goodsIds) != count(array_unique($goodsIds))) {
                    throw new ArticleException(ArticleException::ARTICLE_SAVE_GOODS_REPEAT_ERROR);
                }

                // 商品状态验证(上架,未删除)
                $where = [
                    'id' => $goodsIds,
                    'status' => [GoodsStatusConstant::GOODS_STATUS_PUTAWAY, GoodsStatusConstant::GOODS_STATUS_PUTAWAY_NOT_DISPLAY],// 上架商品
                    'is_deleted' => GoodsDeleteConstant::GOODS_IS_DELETE_NO,// 未删除
                ];

                $goodsCount = GoodsModel::find()->where($where)->count('id');
                if (!$goodsCount || $goodsCount != count($goodsIds)) {
                    throw new ArticleException(ArticleException::ARTICLE_SAVE_GOODS_ERROR);
                }
            }

        }

        // 优惠券状态验证
        if (!empty($data['coupon_ids'])) {
            $couponIds = Json::decode($data['coupon_ids']) ?? [];
            if ($couponIds) {
                if (count($couponIds) != count(array_unique($couponIds))) {
                    throw new ArticleException(ArticleException::ARTICLE_SAVE_COUPON_REPEAT_ERROR);
                }

                $where = [
                    'id' => $couponIds,
                ];

                $couponCount = CouponModel::find()->where($where)->count('id');
                if (!$couponCount || $couponCount != count($couponIds)) {
                    throw new ArticleException(ArticleException::ARTICLE_SAVE_COUPON_ERROR);
                }
            }
        }

    }

    /**
     * 点赞
     * @param int $id 文章id
     * @param int $status 0:取消点赞 1:点赞
     * @return array|bool
     * @throws ArticleException
     * @author yuning
     */
    public function thumpsUp(int $id = 0, int $status = 0)
    {
        // 限流
        $this->thumpsUpLimit();

        // 获取对应的model
        /**
         * @var $article ArticleModel
         */
        $article = ArticleModel::getModel( $id, 'id,thumps_up_number,thumps_up_number_real');

        /**
         * @var $thumpsUpModel ArticleThumpsUpModel
         */
        $thumpsUpModel = ArticleThumpsUpModel::getModel($id, $this->memberId );

        $tr = \Yii::$app->db->beginTransaction();
        try {
            if ($status == ArticleConstant::ARTICLE_COMMON_CLOSE) {
                // 取消点赞

                // 不存在数据, 取消点赞返回true
                if (!$thumpsUpModel) {
                    return true;
                }

                if ($thumpsUpModel->status == 1) {
                    $thumpsUpModel->status = 0;
                }

            } elseif ($status == ArticleConstant::ARTICLE_COMMON_OPEN) {
                // 点赞

                if (!$thumpsUpModel) {
                    // 新增
                    $data = [
                        'article_id' => $id,
                        'member_id' => $this->memberId,
                        'status' => 1,
                    ];
                    $thumpsUpModel = new ArticleThumpsUpModel();
                    $thumpsUpModel->setAttributes($data);
                } elseif ($thumpsUpModel->status == 0) {
                    // 已存在数据
                    $thumpsUpModel->status = 1;
                }
            } else {
                throw new ArticleThumpsUpException(ArticleThumpsUpException::STATUS_ERROR);
            }

            // 保存结果
            $saveResult = $thumpsUpModel->save();
            if (!$saveResult) {
                throw new ArticleThumpsUpException(ArticleThumpsUpException::SAVE_ERROR);
            }

            // 更改文章的点赞数据
            $n = $status == 0 ? -1 : 1;
            // 避免减到负数
            if ($n === 1 || ($article->thumps_up_number >= 1 && $article->thumps_up_number_real >= 1)) {
                ArticleModel::updateAllCounters(
                    ['thumps_up_number' => $n, 'thumps_up_number_real' => $n],
                    [
                        'id' => $id,
                        'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
                    ]
                );
            }

            $tr->commit();
            return true;
        } catch (\Throwable $e) {
            $tr->rollBack();
            return error($e->getMessage(), $e->getCode());
        }

    }

    /**
     * 处理客户端的文章优惠券信息
     * @param array $article
     * @return array
     * @author yuning
     */
    private function processClientArticleCoupons(array $article = []): array
    {
        // 获取优惠券数据
        if (!empty($this->couponIds)) {
            $params = $this->getArticleCouponsParams();
            $couponList = CouponModel::getColl($params, [
                'callable' => function (&$row) {
                    $row['receive_status'] = 0;

                    // 优惠文字
                    if ($row['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                        $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '元减' . ValueHelper::delZero($row['discount_price']) . '元';
                    } else {
                        // 打折类型
                        $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '元享' . ValueHelper::delZero($row['discount_price']) . '折';
                    }
                },
                'onlyList' => true,
                'asArray' => true,
                'pager' => false,
            ]);

            // 处理为前端所需数据
            $this->processArticleData($couponList, 'coupons');

            // 填充详情数据
            if (!empty($couponList)) {
                // 获取对应的商品数据
                foreach ($article['coupon_ids'] as $couponId) {
                    if (!isset($couponList[$couponId])) {
                        continue;
                    }
                    $article['coupons'][] = $couponList[$couponId];
                }
            }
        }

        return $article;
    }

    /**
     * 文章优惠券参数
     * @return array
     * @author yuning
     */
    private function getArticleCouponsParams(): array
    {
        $select = [
            'coupon.id as coupon_id',
            'coupon.coupon_name',
            'coupon.coupon_sale_type',
            'coupon.discount_price',
            'coupon.enough',
            'coupon.is_free',
            'coupon.credit',
            'coupon.balance',
            'coupon.time_limit',
            'coupon.limit_day',
            'coupon.start_time',
            'coupon.goods_limit',
            'coupon.end_time',
        ];

        $where = [
            ['coupon.id' => $this->couponIds],
            ['coupon.state' => 1], // 发放
            ['coupon.pick_type' => [CouponConstant::COUPON_PICK_TYPE_CENTER, CouponConstant::COUPON_PICK_TYPE_LINK]], // 领取方式
            [ // 领取时间
                'or',
                ['coupon.time_limit' => CouponConstant::COUPON_TIME_LIMIT_DAYS],
                [
                    'and',
                    ['coupon.time_limit' => CouponConstant::COUPON_TIME_LIMIT_AREA],
                    ['>', 'coupon.end_time', DateTimeHelper::now()]
                ]
            ]
        ];

        //拼装传参
        return [
            'alias' => 'coupon',
            'andWhere' => $where,
            'groupBy' => 'coupon.id',
            'select' => $select,
            'orderBy' => ['coupon.id' => SORT_DESC],
            'indexBy' => 'coupon_id',
        ];
    }

    /**
     * 发放奖励
     * @param int $articleId 文章id
     * @param int $toMemberId 发放给的用户memberId
     * @return array|bool
     * @throws ArticleException
     * @author yuning
     */
    public function sendReward(int $articleId = 0, int $toMemberId = 0)
    {
        if ($toMemberId == $this->memberId) {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_REWARD_SEND_ERROR_SAME_MEMBER), ArticleException::ARTICLE_REWARD_SEND_ERROR_SAME_MEMBER);
        }

        // 获取配置
        $settings = (new ArticleSettingsService())->get();
        if (!$settings || !$settings['reward_time_limit']) {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_GET_SETTINGS_ERROR), ArticleException::ARTICLE_SEND_REWARD_GET_SETTINGS_ERROR);
        }

        // 领取时间限制
        $cacheKey = 'start_shop_plugin_article_reward_time_limit_' . $this->memberId;
        $cacheExist = CacheHelper::get($cacheKey);
        if ($cacheExist) {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_TIME_LIMIT_ERROR), ArticleException::ARTICLE_SEND_REWARD_TIME_LIMIT_ERROR);
        }
        CacheHelper::set($cacheKey, DateTimeHelper::now(), $settings['reward_time_limit']);

        /**
         * 获取奖励规则
         * @var $article ArticleModel
         */
        $article = ArticleModel::getModel($articleId, 'id,reward_type,reward_rule');

        // 无奖励
        if ($article['reward_type'] == 0) {
            return true;
        }

        $rewardRule = Json::decode($article['reward_rule']) ?? [];
        $rule = [];

        if ($article['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT) {
            // 积分
            $rule = $rewardRule['credit'] ?? [];
        } elseif ($article['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE) {
            // 余额
            $rule = $rewardRule['balance'] ?? [];
        } elseif ($article['reward_type'] == ArticleConstant::ARTICLE_REWARD_TYPE_COUPON) {
            // 优惠券
            $rule = $rewardRule['coupon'] ?? [];
        } else {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_TYPE_ERROR), ArticleException::ARTICLE_SEND_REWARD_TYPE_ERROR);
        }

        // 奖励规则为空
        if (empty($rule)) {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_RULE_EMPTY), ArticleException::ARTICLE_SEND_REWARD_RULE_EMPTY);
        }

        // 获取被奖励会员信息
        $toMember = MemberModel::find()
            ->where([
                'id' => $toMemberId,
                'is_deleted' => 0,
                'is_black' => 0,
            ])
            ->select('id,credit,balance')
            ->first();
        if (!$toMember) {
            return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_TO_MEMBER_ERROR), ArticleException::ARTICLE_SEND_REWARD_TO_MEMBER_ERROR);
        }


        if (in_array($article['reward_type'], [ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT, ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE])) {

            // 验证发放where条件
            $where = [
                'article_id' => $articleId,
                'to_member_id' => $toMemberId,
                'reward_type' => $article->reward_type,
            ];

            // 基本规则验证
            if ($rule['once'] < 0 || $rule['max'] < 0 || $rule['once'] > $rule['max']) {
                return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_RULE_ERROR), ArticleException::ARTICLE_SEND_REWARD_RULE_ERROR);
            }

            // 获取的奖励总数
            $sendSum = ArticleRewardLogModel::find()->select('sum(number) as sum')->where($where)->first();
            if ($sendSum && $sendSum['sum'] > 0 && ($sendSum['sum'] + $rule['once']) > $rule['max']) {
                return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_NUMBER_LIMIT), ArticleException::ARTICLE_SEND_REWARD_NUMBER_LIMIT);
            }

            // 触发奖励者是否有过记录
            $where['from_member_id'] = $this->memberId;
            $exist = ArticleRewardLogModel::find()->where($where)->count('id');
            if ($exist) {
                return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_FROM_MEMBER_REPEAT), ArticleException::ARTICLE_SEND_REWARD_FROM_MEMBER_REPEAT);
            }

            // 平台最大积分限制
            $creditSet = ShopSettings::get('sysset.credit');
            if (!empty($creditSet) && $creditSet['credit_limit_type'] == 2) {
                $creditLimit = $creditSet['credit_limit'] ?? 0;
                if (($toMember['credit'] >= $creditLimit) || (($toMember['credit'] + $rule['once']) >= $creditLimit)) {
                    return error(ArticleException::getMessages(ArticleException::ARTICLE_SEND_REWARD_SHOP_NUMBER_LIMIT), ArticleException::ARTICLE_SEND_REWARD_SHOP_NUMBER_LIMIT);
                }
            }
        }


        // 开启事务
        $tr = \Yii::$app->db->beginTransaction();
        try {
            // 发放
            if ($article->reward_type == ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT) {
                // 积分只能是整数
                $rule['once'] = (int)$rule['once'];
                $member = MemberModel::updateCredit($toMemberId, $rule['once'], 0, 'credit', 1, '文章营销奖励', MemberCreditRecordStatusConstant::ARTICLE_REWARD_SEND_CREDIT);
            } elseif ($article->reward_type == ArticleConstant::ARTICLE_REWARD_TYPE_BALANCE) {
                $member = MemberModel::updateCredit($toMemberId, $rule['once'], 0, 'balance', 1, '文章营销奖励', MemberCreditRecordStatusConstant::ARTICLE_REWARD_SEND_BALANCE);
            } else {
                $member = $this->sendRewardCoupon($toMemberId, $rule['coupon_id']);
            }

            if (is_error($member)) {
                throw new \Exception($member['message'], $member['error']);
            }

            // 记录log
            $data = [
                'article_id' => $articleId,
                'to_member_id' => $toMemberId,
                'from_member_id' => $this->memberId,
                'reward_type' => $article->reward_type,
                'number' => $rule['once'] ?? 0,
            ];

            $ArticleRewardLogModel = new ArticleRewardLogModel();
            $ArticleRewardLogModel->setAttributes($data);
            if (!$ArticleRewardLogModel->save()) {
                throw new \Exception(ArticleException::getMessages(ArticleException::ARTICLE_REWARD_LOG_SAVE_ERROR), ArticleException::ARTICLE_REWARD_LOG_SAVE_ERROR);
            }

            $tr->commit();
        } catch (\Throwable $exception) {
            $tr->rollBack();
            return error($exception->getMessage(), $exception->getCode());
        }

        // 消息通知
        $messageData = [
            'member_nickname' => $member['nickname'],
            'nickname' => $member['nickname'],
            'recharge_price' => $rule['once'] ?? 0,
            'recharge_time' => DateTimeHelper::now(),
        ];

        if ($article->reward_type == ArticleConstant::ARTICLE_REWARD_TYPE_CREDIT) {
            $desc = '专题文章分享获得积分';
            $messageData['recharge_method'] = $desc;
            $messageData['recharge_pay_method'] = $desc;
            $messageData['change_reason'] = $desc;
            $messageData['member_credit'] = $member['credit'];
            // 积分变动

            $result = NoticeComponent::getInstance( NoticeTypeConstant::BUYER_PAY_CREDIT, $messageData, '', 0, ['domain' => \Yii::$app->getUrlManager()->hostInfo]);
        } else {
            $desc = '专题文章分享获得余额';
            $messageData['recharge_method'] = $desc;
            $messageData['recharge_pay_method'] = $desc;
            $messageData['change_reason'] = $desc;
            $messageData['member_balance'] = $member['balance'];
            $messageData['balance_change_reason'] = $desc;
            // 余额
            $result = NoticeComponent::getInstance( NoticeTypeConstant::BUYER_PAY_RECHARGE, $messageData, '', 0, ['domain' => \Yii::$app->getUrlManager()->hostInfo]);
        }

        if (!is_error($result)) {
            $result->sendMessage($toMemberId);
        }

        return true;
    }

    /**
     * 点赞限流 m分钟 n次
     * @return void
     * @throws ArticleException
     * @author yuning
     */
    private function thumpsUpLimit(): void
    {
        $timeLimit = 60;// 60秒内
        $countLimit = 20; // 最多20次
        $currentTime = time();
        $key = 'start_shop_plugin_article_thumps_up_limit_' . $this->memberId;
        $redis = \Yii::$app->redis;
        if ($redis->exists($key)) {
            // 获取范围内数据
            $cache = $redis->zrangebyscore($key, $currentTime - $timeLimit, $currentTime);

            // 判断次数
            if ($cache && count($cache) >= $countLimit) {
                throw new ArticleException(ArticleException::ARTICLE_THUMPS_UP_LIMIT);
            }
        }

        // 添加有序集合数据 score为当前时间, value保持唯一
        $redis->zadd($key, $currentTime, StringHelper::random(22) . $currentTime);
        // 更新过期时间, 偏移量10秒
        $redis->expire($key, $timeLimit + 10);
    }

    /**
     * 保存检测分组
     * @param int $groupId
     * @return void
     * @throws ArticleGroupException
     * @author yuning
     */
    private function saveCheckGroup(int $groupId = 0): void
    {
        $group = ArticleGroupModel::find()->select('name,status')->where(['id' => $groupId])->first();
        if (!$group) {
            throw new ArticleGroupException(ArticleGroupException::FIND_ERROR);
        }
        if ($group['status'] != 1) {
            throw new ArticleGroupException(ArticleGroupException::STATUS_ERROR_HIDE);
        }
        $this->logPrimary['group'] = $group['name'];

    }

    /**
     * 发放优惠券
     * @param int $memberId
     * @param string $data
     * @return array|void
     * @author yuning
     */
    private function sendRewardCoupon(int $memberId, string $data = '')
    {
        $data = explode(',', $data);
        foreach ($data as $value) {
            if (!$value) {
                continue;
            }
            // 检查优惠券 优惠券失效跳过领取下一张
            $checkReceive = CouponService::checkReceive( $memberId, $value);
            if (is_error($checkReceive)) {
                continue;
            }

            // 发送优惠券
            $rewardCoupon = CouponMemberService::sendCoupon( $memberId, $checkReceive, 20, [
                'isBeginTransaction' => false
            ]);
            if (is_error($rewardCoupon)) {
                return error($rewardCoupon['message']);
            }
        }
    }
}