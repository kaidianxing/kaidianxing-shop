<?php

namespace shopstar\admin\article;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\article\ArticleConstant;
use shopstar\constants\article\ArticleLogConstant;
use shopstar\exceptions\article\ArticleException;
use shopstar\exceptions\article\ArticleGroupException;
use shopstar\exceptions\article\ArticleSellDataException;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\article\ArticleModel;
use shopstar\models\byteDance\ByteDanceUploadLogModel;
use shopstar\models\log\LogModel;
use shopstar\models\wxapp\WxappUploadLogModel;
use shopstar\services\article\ArticleSellDataService;
use shopstar\services\article\ArticleService;
use shopstar\services\article\WxArticleService;
use yii\web\Response;

/**
 * 商家端文章
 * Class ArticleController
 * @package shopstar\admin\article
 * @author yuning
 */
class ArticleController extends KdxAdminApiController
{

    public  $configActions = [
        'allowClientActions' => [
            'tools',
        ],
        'allowSessionActions' => [
            'tools',
        ],
        'allowShopActions' => [
            'tools'
        ],
        'allowHeaderActions' => [
            'list',
            'download-image',
            'tools'
        ],
        'allowActions' => [
            'list',
            'tools'
        ],
    ];


    /**
     * 推广route
     * @var string
     */
    private string $promoteRoute = '/kdxArticle/detail/index';

    /**
     * 下载图片
     * @param $url
     * @return void
     * @author xukaixuan
     */
    public function actionDownloadImage($url)
    {
        ImageHelper::downloadImage($url, '推广码');
    }

    /**
     * 文章列表
     * @author yuning
     */
    public function actionList()
    {
        $params = RequestHelper::get();
        $ArticleService = new ArticleService($this->userId);
        $data = $ArticleService->getList($params);

        return $this->result($data);
    }

    /**
     * 保存文章
     * @return array|int[]|Response
     * @throws ArticleGroupException
     * @throws ArticleException
     * @author yuning
     */
    public function actionSave()
    {
        // 获取数据
        $data = RequestHelper::post();
        // 保存
        $ArticleService = new ArticleService($this->userId);
        $ArticleService->save($data);
        return $this->success();
    }

    /**
     * 获取文章信息
     * @return array|int[]|Response
     * @throws ArticleException
     * @author yuning
     */
    public function actionGet()
    {
        $articleId = RequestHelper::getInt('article_id');
        if (!$articleId) {
            return $this->error('缺少文章id');
        }
        $ArticleService = new ArticleService($this->userId);
        $data = $ArticleService->getDetail($articleId);

        return $this->result(['data' => $data]);
    }

    /**
     * 检测文章标题唯一性
     * @return array|int[]|Response
     * @throws ArticleException
     * @author yuning
     */
    public function actionCheckTitle()
    {
        $title = RequestHelper::get('title');
        $articleId = RequestHelper::getInt('article_id');
        if (!$title) {
            return $this->error('缺少文章标题');
        }
        ArticleModel::saveCheckTitleExist($articleId, $title);
        return $this->success();
    }

    /**
     * 导入微信公众号文章信息
     * @return array|int[]|Response
     * @throws ArticleException
     * @author yuning
     */
    public function actionImportWxArticle()
    {
        $url = RequestHelper::post('url');
        if (!$url) {
            return $this->error('缺少文章url');
        }
        $res = parse_url($url);
        if (empty($res) || !isset($res['host']) || empty($res['host'])) {
            return $this->error('文章url格式错误');
        }
        if ($res['host'] != WxArticleService::$needHost) {
            return $this->error('文章url非微信文章地址');
        }
        $WxArticleService = new WxArticleService();
        $res = $WxArticleService->crawByUrl($url);

        return $this->result(['data' => $res]);
    }

    /**
     * 更改文章状态
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionChangeStatus()
    {
        $res = ArticleModel::easySwitch('status', [
            'andWhere' => [
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ],
            'validateValue' => [0, 1],
            'afterAction' => function ($model) {
                // 日志
                $logPrimary = [
                    'id' => $model->id,
                    'status' => $model->status ? '显示' : '隐藏',
                ];
                $logData = [
                    'id' => $model->id,
                    'status' => $model->status,
                ];
                LogModel::write(
                    $this->userId,
                    ArticleLogConstant::ARTICLE_STATUS,
                    ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_STATUS),
                    $model->id,
                    [
                        'log_data' => $logData,
                        'log_primary' => $model->getLogAttributeRemark($logPrimary),
                    ]
                );
            },
        ]);

        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->success();
    }

    /**
     * 文章置顶
     * @author yuning
     */
    public function actionChangeTop()
    {
        $isTop = RequestHelper::postInt('is_top');
        $res = ArticleModel::easySwitch('is_top', [
            'andWhere' => [
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
                'is_top' => !$isTop,
            ],
            'validateValue' => [0, 1],
            'beforeAction' => function ($model) use ($isTop) {
                // 置顶操作
                if ($isTop == 1) {
                    $where = [
                        'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
                        'is_top' => ArticleConstant::ARTICLE_COMMON_OPEN,
                    ];
                    // 验证已置顶的文字数量
                    $count = ArticleModel::where($where)->count('id');
                    if ($count >= ArticleConstant::ARTICLE_TOPPING_NUM_LIMIT) {
                        throw new ArticleException(ArticleException::ARTICLE_TOPPING_NUM_LIMIT);
                    }

                }
            },
            'afterAction' => function ($model) {
                // 日志
                $logPrimary = [
                    'id' => $model->id,
                    'is_top' => $model->is_top ? '是' : '否',
                ];
                $logData = [
                    'id' => $model->id,
                    'is_top' => $model->is_top,
                ];
                LogModel::write(
                    $this->userId,
                    ArticleLogConstant::ARTICLE_TOPPING,
                    ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_TOPPING),
                    $model->id,
                    [
                        'log_data' => $logData,
                        'log_primary' => $model->getLogAttributeRemark($logPrimary),
                    ]
                );

            },
        ]);
        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->success();
    }

    /**
     * 数据统计: 访问/点赞/分享等真实数据
     * @throws ArticleException
     * @author yuning
     */
    public function actionStatistics()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            return $this->error('缺少id');
        }
        $field = [
            'id',
            'title',
            'created_at',
            'share_number_real as share_number',
            'read_number_real as read_number',
            'thumps_up_number_real as thumps_up_number'
        ];

        $data = ArticleModel::getModel($id, $field)->toArray();

        // 发布人
        $data['publisher'] = '';
        $log = ArticleService::getArticleCreateLog([$id]);
        if ($log) {
            $data['publisher'] = current($log)['username'];
        }
        return $this->result(['data' => $data]);
    }

    /**
     * 获取引流销售数据
     * @return array|int[]|Response
     * @throws ArticleException
     * @throws ArticleSellDataException
     * @author yuning
     */
    public function actionGetSellData()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            return $this->error('缺少id');
        }

        $type = RequestHelper::getInt('type');
        if (!$type) {
            return $this->error('缺少type');
        }

        $ArticleService = new ArticleSellDataService($this->userId);
        $data = $ArticleService->getList($id, $type);

        return $this->result($data);
    }

    /**
     * 删除文章(软删除)
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionDelete()
    {
        $res = ArticleModel::easyRecycle([
            'andWhere' => [
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ],
            'afterSave' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    ArticleLogConstant::ARTICLE_DELETE,
                    ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_DELETE),
                    $model->id,
                    [
                        'log_data' => ['id' => $model->id, 'is_deleted' => 1],
                        'log_primary' => ['id' => $model->id, '文章状态' => '删除'],
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->success();
    }

    /**
     * 推广
     * @return array|int[]|Response
     * @throws ArticleException
     * @author yuning
     */
    public function actionPromote()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            return $this->error('缺少id');
        }

        // 验证文章是否存在
        ArticleModel::getModel($id, 'id');

        $data = [];

        //获取h5链接
//        if (ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_H5), 0)) {
        $data['h5']['url'] = ShopUrlHelper::wap($this->promoteRoute, [
            'id' => $id
        ], true);
//        }

        //获取微信公众号
//        if (ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WECHAT), 0)) {
        $data['wechat']['qrcode'] = ShopUrlHelper::wap($this->promoteRoute, [
            'id' => $id
        ], true);
//        }

//        if (ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP))) {
        try {
            //获取小程序二维码
            $data['wxapp']['qrcode'] = WxappUploadLogModel::getWxappQRcode($this->promoteRoute, [
                'id' => $id
            ]);

            if (empty($data['wxapp']['qrcode'])) unset($data['wxapp']['qrcode']);
        } catch (\Throwable $throwable) {
        }
//        }

//        if (ShopSettings::get('channel.byte_dance')) {
        try {
            //获取头条小程序二维码
            $data['toutiao']['qrcode'] = ByteDanceUploadLogModel::getByteDanceQrcode('toutiao', $this->promoteRoute, [
                'id' => $id
            ]);

            if (empty($data['toutiao']['qrcode'])) unset($data['wxapp']['qrcode']);
        } catch (\Throwable $throwable) {
        }

        try {
            //获取抖音小程序二维码
            $data['douyin']['qrcode'] = ByteDanceUploadLogModel::getByteDanceQrcode('douyin', $this->promoteRoute, [
                'id' => $id
            ]);

            if (empty($data['douyin']['qrcode'])) unset($data['wxapp']['qrcode']);
        } catch (\Throwable $throwable) {
        }
//        }

        return $this->result(['data' => $data]);
    }

    /**
     * 获取微信文章图片tools
     * @author yuning
     */
    public function actionTools()
    {
        $url = RequestHelper::get('url');
        $fileContents = '';
        if ($url) {
            $refer = 'https://mp.weixin.qq.com/';
            $opt = [
                'http' => [
                    'header' => 'Referer: ' . $refer . '\r\n' . 'Content-type: image/jpeg'
                ]
            ];
            $context = stream_context_create($opt);
            $fileContents = file_get_contents($url, false, $context);
        }
        ob_clean();
        header('Content-type: image/jpeg');
        echo $fileContents;
        exit;
    }


}