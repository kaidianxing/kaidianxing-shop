<?php

namespace shopstar\admin\article;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\article\ArticleConstant;
use shopstar\constants\article\ArticleGroupConstant;
use shopstar\constants\article\ArticleLogConstant;
use shopstar\exceptions\article\ArticleGroupException;
use shopstar\helpers\RequestHelper;
use shopstar\models\article\ArticleGroupModel;
use shopstar\models\article\ArticleModel;
use shopstar\models\log\LogModel;
use shopstar\services\article\ArticleGroupService;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * 商家端分组
 * Class GroupController
 * @package shopstar\admin\article
 * @author yuning
 */
class GroupController extends KdxAdminApiController
{

    public $configActions = [
        'allowActions' => [
            'list',
        ],
        'allowHeaderActions' => [
            'list',
        ],
    ];

    /**
     * 分类列表
     * @author yuning
     */
    public function actionList()
    {
        // 保存
        $ArticleGroupService = new ArticleGroupService($this->userId);
        $res = $ArticleGroupService->getList('', RequestHelper::get('status'), RequestHelper::get('get_article_count'));

        return $this->result($res);
    }

    /**
     * 保存分类
     * @return array|int[]|Response
     * @throws ArticleGroupException
     * @author yuning
     */
    public function actionSave()
    {
        // 获取数据
        $data = RequestHelper::post('data');
        if (!$data) {
            return $this->error('缺少id');
        }
        // 保存
        $ArticleGroupService = new ArticleGroupService($this->userId);
        $ArticleGroupService->save($data);

        return $this->success();
    }

    /**
     * 更改显隐状态
     * @return array|int[]|Response
     * @throws ArticleGroupException
     * @author yuning
     */
    public function actionChangeStatus()
    {
        $id = RequestHelper::post('id', 0);

        if (empty($id)) {
            throw new ArticleGroupException(ArticleGroupException::SAVE_PARAMS_ERROR);
        }

        // 开启事务
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $res = ArticleGroupModel::easySwitch('status', [
                'andWhere' => [
                ],
                'validateValue' => [0, 1],
                'afterAction' => function ($model) {

                    // 处理分组下文章
                    $this->processGroupArticle($model->id, ArticleGroupConstant::GROUP_SCENE_EDIT, $model->status);

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
                        ArticleLogConstant::ARTICLE_HIDE_GROUP,
                        ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_HIDE_GROUP),
                        $model->id,
                        [
                            'log_data' => $logData,
                            'log_primary' => $model->getLogAttributeRemark($logPrimary),
                        ]
                    );
                },
            ]);

            if (is_error($res)) {
                throw new \Exception($res['message'], $res['error']);
            }

            $tr->commit();
        } catch (\Throwable $exception) {
            $tr->rollBack();
            return error($exception->getMessage(), $exception->getCode());
        }

        return $this->success();
    }

    /**
     * 处理分组下文章
     * @param int $groupId 分组id
     * @param int $scene 情景 1:编辑 2:删除
     * @param int $status 状态 0:启动 1:不启用
     * @return void
     * @throws ArticleGroupException
     * @author yuning
     */
    private function processGroupArticle(int $groupId = 0, int $scene = ArticleGroupConstant::GROUP_SCENE_DELETE, int $status = 0): void
    {
        $data = [];
        $where = [];
        // 删除和编辑隐藏
        if ($scene == ArticleGroupConstant::GROUP_SCENE_DELETE || ($scene == ArticleGroupConstant::GROUP_SCENE_EDIT && $status == 0)) {
            // 查找分类下文章
            $where = [
                'group_id' => $groupId,
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ];
            $count = ArticleModel::find()->where($where)->count('id');

            if ($count) {
                // 处理文章的分类
                if ($scene == ArticleGroupConstant::GROUP_SCENE_DELETE) {  // 删除
                    $data = [
                        'group_id' => 0
                    ];
                } elseif ($scene == ArticleGroupConstant::GROUP_SCENE_EDIT && $status == ArticleGroupConstant::GROUP_STATUS_HIDE) { // 编辑(操作显隐)
                    // 隐藏, 记录group_id_origin
                    $data = [
                        'group_id' => 0,
                        'group_id_origin' => $groupId,
                    ];

                }
                if (!empty($data) && ArticleModel::updateAll($data, $where) === false) {
                    throw new ArticleGroupException(ArticleGroupException::SAVE_UPDATE_ARTICLE_ERROR);
                }
            }
        } elseif ($scene == ArticleGroupConstant::GROUP_SCENE_EDIT && $status == 1) { // 分组显示
            // 更新当前没有分组的文章的分组id
            $where = [
                'group_id' => 0, // 只操作没有分组的文章
                'group_id_origin' => $groupId,
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ];
            $data = [
                'group_id' => $groupId,
                'group_id_origin' => 0,
            ];

            ArticleModel::updateAll($data, $where);
        }

        // 删除和编辑显示, 重置所有文章的group_id_origin 为 0
        if (($scene == ArticleGroupConstant::GROUP_SCENE_EDIT && $status == 1) || $scene == ArticleGroupConstant::GROUP_SCENE_DELETE) {
            $where = [
                'group_id_origin' => $groupId,
                'is_deleted' => ArticleConstant::ARTICLE_NOT_DELETE,
            ];
            $data = [
                'group_id_origin' => 0,
            ];
            ArticleModel::updateAll($data, $where);
        }

    }

    /**
     * 删除
     * @return array|int[]|Response
     * @throws \Throwable
     * @throws StaleObjectException
     * @author yuning
     */
    public function actionDelete()
    {
        $res = ArticleGroupModel::easyDelete([
            'andWhere' => [
            ],
            'beforeDelete' => function ($model) {
                // 处理分组下文章
                $this->processGroupArticle($model->id);
            },
            'afterDelete' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    ArticleLogConstant::ARTICLE_DELETE_GROUP,
                    ArticleLogConstant::getText(ArticleLogConstant::ARTICLE_DELETE_GROUP),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => ['id' => $model->id, 'name' => $model->name],
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->success();
    }
}